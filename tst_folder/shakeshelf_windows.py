#!/usr/bin/env python3
"""
ShakeShelf Windows - single-file version.

Install:
    py -m pip install PySide6

Run:
    py shakeshelf_windows.py
"""
from __future__ import annotations

import ctypes
import os
import sys
import tempfile
import time
import traceback
import urllib.parse
import urllib.request
from dataclasses import dataclass, field
from pathlib import Path
from typing import Callable, Iterable

from PySide6.QtCore import QEvent, QMimeData, QPoint, QRect, QSettings, QSize, Qt, QTimer, QUrl
from PySide6.QtGui import (
    QAction,
    QColor,
    QCursor,
    QDrag,
    QFont,
    QGuiApplication,
    QIcon,
    QImage,
    QImageReader,
    QLinearGradient,
    QMouseEvent,
    QPainter,
    QPen,
    QPixmap,
    QPixmapCache,
)
from PySide6.QtWidgets import (
    QApplication,
    QFrame,
    QHBoxLayout,
    QLabel,
    QMenu,
    QPushButton,
    QScrollArea,
    QVBoxLayout,
    QWidget,
)


APP_NAME = "ShakeShelf"
ORG_NAME = "Andivio"
TEMP_DIR = Path(tempfile.gettempdir()) / APP_NAME
LOG_PATH = Path.home() / "ShakeShelf-Windows.log"
INTERNAL_MIME = "application/x-shakeshelf-internal"

MAX_ITEMS = 25
COMPACT_SIZE = 88
PANEL_W = 324
PANEL_H = 220
WEB_IMAGE_MAX_BYTES = 12 * 1024 * 1024
WEB_IMAGE_TIMEOUT = 4
MAX_VISIBLE_NAME_CHARS = 18
VK_MENU = 0x12
VK_SPACE = 0x20

IMAGE_SUFFIXES = {
    ".apng",
    ".bmp",
    ".gif",
    ".ico",
    ".jpeg",
    ".jpg",
    ".png",
    ".tif",
    ".tiff",
    ".webp",
}


def log(message: str) -> None:
    try:
        with LOG_PATH.open("a", encoding="utf-8") as fh:
            fh.write(f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] {message}\n")
    except Exception:
        pass


def compact_display_name(name: str) -> str:
    clean = " ".join((name or "").split())
    if len(clean) <= MAX_VISIBLE_NAME_CHARS:
        return clean
    return "..." + clean[-(MAX_VISIBLE_NAME_CHARS - 3) :]


def normalise_paths(paths: Iterable[str]) -> list[str]:
    out: list[str] = []
    seen: set[str] = set()
    for raw in paths:
        if len(out) >= MAX_ITEMS:
            break
        if not raw:
            continue
        try:
            path = str(Path(raw).expanduser().resolve())
        except Exception:
            path = os.path.abspath(raw)
        if path in seen or not os.path.exists(path):
            continue
        seen.add(path)
        out.append(path)
    return out


def is_image_file(path: str | None) -> bool:
    return bool(path and os.path.isfile(path) and Path(path).suffix.lower() in IMAGE_SUFFIXES)


def looks_like_image_url(url: str) -> bool:
    parsed = urllib.parse.urlparse(url)
    return parsed.scheme in {"http", "https"} and Path(parsed.path).suffix.lower() in IMAGE_SUFFIXES


def dropped_paths_from_mime(mime) -> list[str]:
    if not mime.hasUrls():
        return []
    return normalise_paths(url.toLocalFile() for url in mime.urls() if url.toLocalFile())


def remote_image_urls_from_mime(mime) -> list[str]:
    urls: list[str] = []
    seen: set[str] = set()

    def add(raw: str, allow_unknown: bool = False) -> None:
        url = raw.strip()
        if not url or url in seen:
            return
        parsed = urllib.parse.urlparse(url)
        if parsed.scheme not in {"http", "https"}:
            return
        if not allow_unknown and not looks_like_image_url(url):
            return
        seen.add(url)
        urls.append(url)

    if mime.hasUrls():
        for url in mime.urls():
            add(url.toString())
    if mime.hasText():
        for line in mime.text().splitlines():
            add(line)
    return urls[:3]


@dataclass
class ShelfItem:
    kind: str
    name: str
    parent: str = ""
    path: str | None = None
    temp_path: str | None = None
    thumb_path: str | None = None
    id: str = field(default_factory=lambda: f"item-{time.time_ns()}")

    @property
    def copy_path(self) -> str | None:
        if self.kind == "file":
            return self.path
        if self.kind == "image":
            return self.temp_path
        return None


def file_item(path: str) -> ShelfItem:
    p = Path(path)
    kind = "folder" if p.is_dir() else "file"
    return ShelfItem(kind=kind, name=p.name or path, parent=str(p.parent), path=path)


def image_item(image: QImage, name: str | None = None, parent: str | None = None) -> ShelfItem | None:
    if image is None or image.isNull():
        return None
    TEMP_DIR.mkdir(parents=True, exist_ok=True)
    stamp = f"{time.strftime('%Y%m%d-%H%M%S')}-{time.time_ns()}"
    temp_path = TEMP_DIR / f"image-{stamp}.png"
    thumb_path = TEMP_DIR / f"thumb-{stamp}.png"
    if not image.save(str(temp_path), "PNG"):
        return None
    thumb = image.scaled(96, 96, Qt.AspectRatioMode.KeepAspectRatio, Qt.TransformationMode.SmoothTransformation)
    if not thumb.save(str(thumb_path), "PNG"):
        thumb_path = temp_path
    display_parent = parent or f"Temporary PNG - {image.width()}x{image.height()}"
    return ShelfItem("image", name or "Clipboard image", display_parent, temp_path=str(temp_path), thumb_path=str(thumb_path))


def temp_image_file_item(path: str, name: str | None = None, parent: str | None = None) -> ShelfItem | None:
    image_path = Path(path)
    if not image_path.exists():
        return None
    TEMP_DIR.mkdir(parents=True, exist_ok=True)
    stamp = f"{time.strftime('%Y%m%d-%H%M%S')}-{time.time_ns()}"
    thumb_path = TEMP_DIR / f"web-thumb-{stamp}.png"
    reader = QImageReader(str(image_path))
    reader.setAutoTransform(True)
    source_size = reader.size()
    if source_size.isValid():
        scaled_size = QSize(source_size)
        scaled_size.scale(QSize(96, 96), Qt.AspectRatioMode.KeepAspectRatio)
        reader.setScaledSize(scaled_size)
    image = reader.read()
    if image.isNull():
        return None
    if not image.save(str(thumb_path), "PNG"):
        thumb_path = image_path
    size = source_size if source_size.isValid() else image.size()
    details = f" - {size.width()}x{size.height()}" if size.isValid() else ""
    return ShelfItem("image", name or image_path.name or "Web image", (parent or "Temporary image") + details, temp_path=str(image_path), thumb_path=str(thumb_path))


class FileStore:
    def __init__(self) -> None:
        self.items: list[ShelfItem] = []
        self._subscribers: list[Callable[[], None]] = []

    def subscribe(self, callback: Callable[[], None]) -> None:
        self._subscribers.append(callback)

    def notify(self) -> None:
        for cb in list(self._subscribers):
            try:
                cb()
            except Exception:
                log(traceback.format_exc())

    def count(self) -> int:
        return len(self.items)

    @property
    def paths(self) -> list[str]:
        return [item.path for item in self.items if item.path]

    def add(self, paths: Iterable[str]) -> bool:
        incoming = normalise_paths(paths)
        if not incoming or len(self.items) >= MAX_ITEMS:
            return False
        existing = set(self.paths)
        changed = False
        for path in incoming:
            if len(self.items) >= MAX_ITEMS:
                break
            if path not in existing:
                self.items.append(file_item(path))
                existing.add(path)
                changed = True
        if changed:
            self.notify()
        return changed

    def add_image(self, image: QImage, name: str | None = None, parent: str | None = None) -> bool:
        if len(self.items) >= MAX_ITEMS:
            return False
        item = image_item(image, name=name, parent=parent)
        if item is None:
            return False
        self.items.append(item)
        self.notify()
        return True

    def add_temp_image_file(self, path: str, name: str | None = None, parent: str | None = None) -> bool:
        if len(self.items) >= MAX_ITEMS:
            return False
        item = temp_image_file_item(path, name=name, parent=parent)
        if item is None:
            try:
                Path(path).unlink(missing_ok=True)
            except Exception:
                pass
            return False
        self.items.append(item)
        self.notify()
        return True

    def rename(self, identifier: str, name: str) -> bool:
        clean = " ".join((name or "").split())
        if not clean:
            return False
        for item in self.items:
            if item.id == identifier:
                item.name = clean
                self.notify()
                return True
        return False

    def remove(self, identifier: str) -> None:
        removed: list[ShelfItem] = []
        kept: list[ShelfItem] = []
        for item in self.items:
            if item.id == identifier:
                removed.append(item)
            else:
                kept.append(item)
        if removed:
            self.items = kept
            for item in removed:
                self._delete_temp(item)
            self.notify()

    def clear(self) -> None:
        removed = list(self.items)
        self.items.clear()
        for item in removed:
            self._delete_temp(item)
        self.notify()

    def clear_after_copy(self) -> None:
        if self.items:
            self.items.clear()
            self.notify()

    def _delete_temp(self, item: ShelfItem) -> None:
        if item.kind != "image":
            return
        for value in (item.temp_path, item.thumb_path):
            try:
                if value:
                    Path(value).unlink(missing_ok=True)
            except Exception:
                pass


def mime_for_items(items: Iterable[ShelfItem]) -> QMimeData:
    selected = list(items)[:MAX_ITEMS]
    mime = QMimeData()
    mime.setData(INTERNAL_MIME, ",".join(item.id for item in selected).encode("utf-8"))
    urls: list[QUrl] = []
    text_lines: list[str] = []
    first_image = None
    for item in selected:
        copy_path = item.copy_path
        if copy_path and os.path.exists(copy_path):
            urls.append(QUrl.fromLocalFile(copy_path))
            text_lines.append(copy_path)
        if item.kind == "image" and first_image is None and item.temp_path:
            image = QImage(item.temp_path)
            if not image.isNull():
                first_image = image
    if urls:
        mime.setUrls(urls)
        mime.setText("\n".join(text_lines))
        mime.setData('application/x-qt-windows-mime;value="Preferred DropEffect"', (1).to_bytes(4, "little"))
    if first_image is not None:
        mime.setImageData(first_image)
    return mime


def copy_items_to_clipboard(store: FileStore, clear_after_copy: bool) -> bool:
    if not store.items:
        return False
    mime = mime_for_items(store.items)
    if not (mime.hasUrls() or mime.hasImage() or mime.hasText()):
        return False
    QApplication.clipboard().setMimeData(mime)
    if clear_after_copy:
        store.clear_after_copy()
    return True


def add_web_image_to_store(store: FileStore, url: str) -> bool:
    TEMP_DIR.mkdir(parents=True, exist_ok=True)
    temp_path = None
    try:
        request = urllib.request.Request(url, headers={"User-Agent": f"{APP_NAME}/1.0"})
        with urllib.request.urlopen(request, timeout=WEB_IMAGE_TIMEOUT) as response:
            content_type = response.headers.get("Content-Type", "application/octet-stream")
            parsed = urllib.parse.urlparse(url)
            raw_name = Path(urllib.parse.unquote(parsed.path)).name
            ext = Path(raw_name).suffix or ".png"
            name = raw_name or f"Web image{ext}"
            temp_path = TEMP_DIR / f"web-image-{time.time_ns()}{ext}"
            total = 0
            with temp_path.open("wb") as fh:
                while True:
                    chunk = response.read(128 * 1024)
                    if not chunk:
                        break
                    total += len(chunk)
                    if total > WEB_IMAGE_MAX_BYTES:
                        raise ValueError("web image too large")
                    fh.write(chunk)
        return store.add_temp_image_file(str(temp_path), name=name, parent=parsed.netloc or "web")
    except Exception:
        if temp_path:
            try:
                temp_path.unlink(missing_ok=True)
            except Exception:
                pass
        return False


def add_mime_to_store(store: FileStore, mime) -> bool:
    if mime.hasFormat(INTERNAL_MIME):
        return False
    paths = dropped_paths_from_mime(mime)
    if paths:
        return store.add(paths)
    for url in remote_image_urls_from_mime(mime):
        if add_web_image_to_store(store, url):
            return True
    if mime.hasImage():
        image = mime.imageData()
        if isinstance(image, QImage):
            return store.add_image(image)
    return False


def add_clipboard_to_store(store: FileStore) -> bool:
    clipboard = QApplication.clipboard()
    mime = clipboard.mimeData()
    if mime and mime.hasFormat(INTERNAL_MIME):
        return False
    if mime and add_mime_to_store(store, mime):
        return True
    image = clipboard.image()
    if not image.isNull():
        return store.add_image(image)
    return False


def button(label: str, name: str = "secondaryButton") -> QPushButton:
    btn = QPushButton(label)
    btn.setObjectName(name)
    btn.setMinimumHeight(24)
    btn.setCursor(Qt.CursorShape.PointingHandCursor)
    return btn


def make_icon(size: int = 64) -> QPixmap:
    pix = QPixmap(size, size)
    pix.fill(Qt.GlobalColor.transparent)
    painter = QPainter(pix)
    painter.setRenderHint(QPainter.RenderHint.Antialiasing)
    rect = QRect(7, 7, size - 14, size - 14)
    grad = QLinearGradient(0, 7, size, size)
    grad.setColorAt(0.0, QColor("#ffffff"))
    grad.setColorAt(1.0, QColor("#d9ecff"))
    painter.setBrush(grad)
    painter.setPen(QPen(QColor("#7db8ff"), 2))
    painter.drawRoundedRect(rect, 18, 18)
    painter.setPen(QColor("#0f172a"))
    font = painter.font()
    font.setPointSize(max(18, size // 3))
    painter.setFont(font)
    painter.drawText(pix.rect(), Qt.AlignmentFlag.AlignCenter, "+")
    painter.end()
    return pix


def base_flags(on_top: bool = True) -> Qt.WindowType:
    flags = Qt.WindowType.FramelessWindowHint | Qt.WindowType.Window
    if on_top:
        flags |= Qt.WindowType.WindowStaysOnTopHint
    return flags


def apply_flags(widget: QWidget, on_top: bool) -> None:
    visible = widget.isVisible()
    pos = widget.pos()
    widget.setWindowFlags(base_flags(on_top))
    widget.move(pos)
    if visible:
        widget.show()
        if on_top:
            widget.raise_()


class ImageThumb(QWidget):
    def __init__(self, path: str) -> None:
        super().__init__()
        self.setFixedSize(38, 34)
        self.thumb = QPixmap()
        reader = QImageReader(path)
        reader.setAutoTransform(True)
        size = reader.size()
        if size.isValid():
            scaled = QSize(size)
            scaled.scale(QSize(34, 30), Qt.AspectRatioMode.KeepAspectRatio)
            reader.setScaledSize(scaled)
        image = reader.read()
        if not image.isNull():
            self.thumb = QPixmap.fromImage(image)

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        painter.setBrush(QColor(255, 255, 255, 150))
        painter.setPen(QPen(QColor(147, 197, 253, 90), 1))
        painter.drawRoundedRect(self.rect().adjusted(1, 1, -1, -1), 8, 8)
        if self.thumb.isNull():
            painter.setPen(QColor("#2563eb"))
            painter.drawText(self.rect(), Qt.AlignmentFlag.AlignCenter, "IMG")
        else:
            x = (self.width() - self.thumb.width()) // 2
            y = (self.height() - self.thumb.height()) // 2
            painter.drawPixmap(x, y, self.thumb)
        painter.end()


class KindIcon(QWidget):
    def __init__(self, kind: str) -> None:
        super().__init__()
        self.kind = kind
        self.setFixedSize(34, 34)

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        painter.setBrush(QColor(255, 255, 255, 120))
        painter.setPen(QPen(QColor(147, 197, 253, 82), 1))
        painter.drawRoundedRect(self.rect().adjusted(1, 1, -1, -1), 8, 8)
        painter.setPen(QColor("#2563eb"))
        painter.drawText(self.rect(), Qt.AlignmentFlag.AlignCenter, "DIR" if self.kind == "folder" else "FILE")
        painter.end()


class ElidedLabel(QLabel):
    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setPen(QColor("#0f172a"))
        painter.setFont(self.font())
        text = painter.fontMetrics().elidedText(self.text(), Qt.TextElideMode.ElideLeft, max(0, self.width()))
        painter.drawText(self.rect(), Qt.AlignmentFlag.AlignVCenter | Qt.AlignmentFlag.AlignLeft, text)
        painter.end()


class ScreenshotOverlay(QWidget):
    def __init__(self, store: FileStore, restore_callback: Callable[[], None]) -> None:
        super().__init__()
        self.store = store
        self.restore_callback = restore_callback
        self.origin: QPoint | None = None
        self.selection = QRect()
        self.setWindowFlags(Qt.WindowType.FramelessWindowHint | Qt.WindowType.WindowStaysOnTopHint | Qt.WindowType.Tool)
        self.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.setCursor(Qt.CursorShape.CrossCursor)
        geometry = QRect()
        for screen in QApplication.screens():
            geometry = geometry.united(screen.geometry())
        self.setGeometry(geometry if geometry.isValid() else QApplication.primaryScreen().geometry())

    def begin(self) -> None:
        self.show()
        self.raise_()
        self.activateWindow()

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.fillRect(self.rect(), QColor(15, 23, 42, 76))
        if self.selection.isValid():
            local = self.selection.translated(-self.geometry().topLeft())
            painter.setCompositionMode(QPainter.CompositionMode.CompositionMode_Clear)
            painter.fillRect(local, QColor(0, 0, 0, 0))
            painter.setCompositionMode(QPainter.CompositionMode.CompositionMode_SourceOver)
            painter.setPen(QPen(QColor(96, 165, 250, 230), 2))
            painter.drawRect(local.adjusted(1, 1, -1, -1))
        painter.end()

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.origin = event.globalPosition().toPoint()
            self.selection = QRect(self.origin, self.origin).normalized()
            self.update()
            event.accept()

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if self.origin is not None:
            self.selection = QRect(self.origin, event.globalPosition().toPoint()).normalized()
            self.update()
            event.accept()

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton and self.origin is not None:
            rect = QRect(self.selection)
            if rect.width() < 8 or rect.height() < 8:
                self.finish()
            else:
                self.hide()
                QTimer.singleShot(80, lambda: self.capture(rect))
            event.accept()

    def keyPressEvent(self, event) -> None:
        if event.key() == Qt.Key.Key_Escape:
            self.finish()
        else:
            super().keyPressEvent(event)

    def capture(self, rect: QRect) -> None:
        screen = QApplication.screenAt(rect.center()) or QApplication.primaryScreen()
        screen_rect = screen.geometry()
        grab_rect = rect.intersected(screen_rect)
        if grab_rect.isValid():
            local = grab_rect.translated(-screen_rect.topLeft())
            pixmap = screen.grabWindow(0, local.x(), local.y(), local.width(), local.height())
            if not pixmap.isNull():
                name = f"Shot {time.strftime('%H.%M.%S')}"
                parent = f"Screenshot - {grab_rect.width()}x{grab_rect.height()}"
                self.store.add_image(pixmap.toImage(), name=name, parent=parent)
        self.finish()

    def finish(self) -> None:
        self.hide()
        self.restore_callback()
        self.deleteLater()


def start_screenshot_selection(store: FileStore, restore_callback: Callable[[], None]) -> None:
    overlay = ScreenshotOverlay(store, restore_callback)
    QApplication.instance()._shakeshelf_capture_overlay = overlay  # type: ignore[attr-defined]
    overlay.begin()


class FileRow(QFrame):
    def __init__(self, store: FileStore, item: ShelfItem, drag_callback: Callable[[list[ShelfItem]], None]) -> None:
        super().__init__()
        self.store = store
        self.item = item
        self.drag_callback = drag_callback
        self._press_pos: QPoint | None = None
        self.renaming = False
        self.name_edit = None
        self.setObjectName("fileRow")
        self.setFixedHeight(38)
        self.setCursor(Qt.CursorShape.OpenHandCursor)

        self.root_layout = QHBoxLayout(self)
        self.root_layout.setContentsMargins(7, 2, 6, 2)
        self.root_layout.setSpacing(7)
        image_source = None
        if item.kind == "image" and item.temp_path:
            image_source = item.thumb_path or item.temp_path
        elif item.path and is_image_file(item.path):
            image_source = item.path
        icon = ImageThumb(image_source) if image_source else KindIcon("folder" if item.kind == "folder" else "file")
        self.name_label = ElidedLabel(compact_display_name(item.name))
        self.name_label.setObjectName("fileName")
        self.name_label.setToolTip("Double-click to rename")
        self.name_label.mouseDoubleClickEvent = self.begin_rename
        close_btn = QPushButton("x")
        close_btn.setObjectName("smallButton")
        close_btn.setFixedSize(22, 22)
        close_btn.clicked.connect(lambda: self.store.remove(item.id))
        self.root_layout.addWidget(icon)
        self.root_layout.addWidget(self.name_label, 1)
        self.root_layout.addWidget(close_btn)

    def begin_rename(self, event=None) -> None:
        if self.name_edit is None:
            from PySide6.QtWidgets import QLineEdit

            self.name_edit = QLineEdit(self.item.name)
            self.name_edit.setObjectName("renameEdit")
            self.name_edit.returnPressed.connect(self.commit_rename)
            self.name_edit.editingFinished.connect(self.commit_rename)
            self.root_layout.insertWidget(2, self.name_edit, 1)
        self.renaming = True
        self.name_edit.setText(self.item.name)
        self.name_label.hide()
        self.name_edit.show()
        self.name_edit.setFocus()
        self.name_edit.selectAll()

    def commit_rename(self) -> None:
        if not self.renaming or self.name_edit is None:
            return
        self.renaming = False
        name = self.name_edit.text().strip()
        if name and name != self.item.name:
            self.store.rename(self.item.id, name)
            self.name_label.setText(compact_display_name(name))
        else:
            self.name_edit.hide()
            self.name_label.show()

    def keyPressEvent(self, event) -> None:
        if self.renaming and self.name_edit is not None and event.key() == Qt.Key.Key_Escape:
            self.renaming = False
            self.name_edit.hide()
            self.name_label.show()
            event.accept()
            return
        super().keyPressEvent(event)

    def mouseDoubleClickEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.begin_rename(event)
            event.accept()
        else:
            super().mouseDoubleClickEvent(event)

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self._press_pos = event.position().toPoint()
            self.setCursor(Qt.CursorShape.ClosedHandCursor)
        super().mousePressEvent(event)

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if event.buttons() & Qt.MouseButton.LeftButton and self._press_pos is not None:
            if (event.position().toPoint() - self._press_pos).manhattanLength() >= QApplication.startDragDistance():
                self.drag_callback([self.item])
                self._press_pos = None
                return
        super().mouseMoveEvent(event)

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        self.setCursor(Qt.CursorShape.OpenHandCursor)
        super().mouseReleaseEvent(event)


class ShelfPanel(QWidget):
    def __init__(self, store: FileStore, settings: QSettings) -> None:
        super().__init__()
        self.store = store
        self.settings = settings
        self.anchor_compact: QWidget | None = None
        self.stay_on_top = True
        self.clear_after_copy = settings.value("clear_after_copy", True, bool)
        self.drag_pos: QPoint | None = None
        self.setWindowTitle(APP_NAME)
        self.setFixedSize(PANEL_W, PANEL_H)
        self.setWindowFlags(base_flags(True))
        self.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.setAcceptDrops(True)

        outer = QVBoxLayout(self)
        outer.setContentsMargins(8, 8, 8, 8)
        root = QFrame()
        root.setObjectName("panelRoot")
        outer.addWidget(root)
        layout = QVBoxLayout(root)
        layout.setContentsMargins(10, 9, 10, 10)
        layout.setSpacing(6)

        header = QHBoxLayout()
        title = QLabel("ShakeShelf")
        title.setObjectName("appTitle")
        self.count_badge = QLabel("0")
        self.count_badge.setObjectName("countBadge")
        self.count_badge.setAlignment(Qt.AlignmentFlag.AlignCenter)
        collapse_btn = button("-", "ghostButton")
        collapse_btn.setFixedWidth(28)
        collapse_btn.clicked.connect(self.collapse_to_compact)
        header.addWidget(title, 1)
        header.addWidget(self.count_badge)
        header.addWidget(collapse_btn)
        layout.addLayout(header)

        self.scroll = QScrollArea()
        self.scroll.setObjectName("itemScroll")
        self.scroll.setWidgetResizable(True)
        self.scroll.setFrameShape(QFrame.Shape.NoFrame)
        self.rows_host = QWidget()
        self.rows_host.setObjectName("rowsHost")
        self.rows_layout = QVBoxLayout(self.rows_host)
        self.rows_layout.setContentsMargins(0, 0, 0, 0)
        self.rows_layout.setSpacing(4)
        self.scroll.setWidget(self.rows_host)
        layout.addWidget(self.scroll, 1)

        footer = QHBoxLayout()
        shot_btn = button("Shot")
        shot_btn.clicked.connect(self.start_screenshot)
        paste_btn = button("Paste")
        paste_btn.clicked.connect(lambda: add_clipboard_to_store(self.store))
        self.copy_btn = button("Copy", "primaryButton")
        self.copy_btn.clicked.connect(lambda: copy_items_to_clipboard(self.store, self.clear_after_copy))
        clear_btn = button("Clear")
        clear_btn.clicked.connect(self.store.clear)
        footer.addWidget(shot_btn)
        footer.addWidget(paste_btn)
        footer.addWidget(self.copy_btn, 1)
        footer.addWidget(clear_btn)
        layout.addLayout(footer)

        self.store.subscribe(self.refresh)
        self.refresh()

    def show_from_compact(self, compact: QWidget) -> None:
        self.anchor_compact = compact
        screen = QApplication.screenAt(compact.frameGeometry().center()) or QApplication.primaryScreen()
        geo = screen.availableGeometry()
        x = min(max(compact.x(), geo.left() + 8), geo.right() - self.width() - 8)
        y = min(max(compact.y(), geo.top() + 8), geo.bottom() - self.height() - 8)
        self.move(x, y)
        self.show()
        self.raise_()
        self.activateWindow()
        compact.hide()

    def collapse_to_compact(self) -> None:
        pos = self.pos()
        self.hide()
        if self.anchor_compact is not None:
            self.anchor_compact.move(pos)
            self.anchor_compact.show()
            self.anchor_compact.raise_()

    def refresh(self) -> None:
        while self.rows_layout.count():
            item = self.rows_layout.takeAt(0)
            widget = item.widget()
            if widget:
                widget.deleteLater()
        if not self.store.items:
            empty = QLabel("Empty")
            empty.setObjectName("muted")
            empty.setAlignment(Qt.AlignmentFlag.AlignCenter)
            self.rows_layout.addWidget(empty)
        else:
            for item in self.store.items:
                self.rows_layout.addWidget(FileRow(self.store, item, self.start_drag))
        self.rows_layout.addStretch(1)
        self.count_badge.setText(str(self.store.count()))
        self.copy_btn.setEnabled(self.store.count() > 0)

    def start_drag(self, items: list[ShelfItem]) -> None:
        drag = QDrag(self)
        drag.setMimeData(mime_for_items(items))
        drag.exec(Qt.DropAction.CopyAction)

    def start_screenshot(self) -> None:
        anchor = self.anchor_compact

        def restore() -> None:
            if anchor is not None:
                self.show_from_compact(anchor)
            else:
                self.show()

        self.hide()
        if anchor is not None:
            anchor.hide()
        QTimer.singleShot(120, lambda: start_screenshot_selection(self.store, restore))

    def mouseDoubleClickEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.collapse_to_compact()
            event.accept()
        else:
            super().mouseDoubleClickEvent(event)

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton and event.position().y() < 38:
            self.drag_pos = event.globalPosition().toPoint() - self.frameGeometry().topLeft()
            event.accept()
        else:
            super().mousePressEvent(event)

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if self.drag_pos is not None and event.buttons() & Qt.MouseButton.LeftButton:
            self.move(event.globalPosition().toPoint() - self.drag_pos)
            event.accept()
        else:
            super().mouseMoveEvent(event)

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        self.drag_pos = None
        super().mouseReleaseEvent(event)

    def dragEnterEvent(self, event) -> None:
        if not event.mimeData().hasFormat(INTERNAL_MIME):
            event.acceptProposedAction()
        else:
            event.ignore()

    def dropEvent(self, event) -> None:
        if add_mime_to_store(self.store, event.mimeData()):
            event.acceptProposedAction()
        else:
            event.ignore()


class FrontHotkeyWatcher:
    def __init__(self, compact) -> None:
        self.compact = compact
        self.timer = QTimer(compact)
        self.timer.setTimerType(Qt.TimerType.CoarseTimer)
        self.timer.setInterval(120)
        self.timer.timeout.connect(self.tick)
        self.was_down = False
        self.last_pressed = 0.0
        self.last_triggered = -999.0
        self.timer.start()

    def alt_space_pressed(self) -> bool:
        if not sys.platform.startswith("win"):
            return False
        try:
            user32 = ctypes.windll.user32
            return bool(user32.GetAsyncKeyState(VK_MENU) & 0x8000) and bool(user32.GetAsyncKeyState(VK_SPACE) & 0x8000)
        except Exception:
            return False

    def tick(self) -> None:
        now = time.monotonic()
        pressed = self.alt_space_pressed()
        if pressed and not self.was_down:
            if self.last_pressed > 0 and now - self.last_pressed <= 0.75 and now - self.last_triggered >= 1.0:
                self.last_triggered = now
                self.last_pressed = 0.0
                self.compact.bring_front()
            else:
                self.last_pressed = now
        self.was_down = pressed


class CompactShelf(QWidget):
    def __init__(self, store: FileStore, settings: QSettings, panel: ShelfPanel) -> None:
        super().__init__()
        self.store = store
        self.settings = settings
        self.panel = panel
        self.drag_pos: QPoint | None = None
        self.drag_start: QPoint | None = None
        self.moved = False
        self.setWindowTitle(APP_NAME)
        self.setWindowFlags(base_flags(True))
        self.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.setAcceptDrops(True)
        self.setFixedSize(COMPACT_SIZE, COMPACT_SIZE)
        self.setCursor(Qt.CursorShape.OpenHandCursor)
        self.store.subscribe(self.update)
        saved = self.settings.value("compact_pos")
        if isinstance(saved, QPoint):
            self.move(saved)
        else:
            geo = QApplication.primaryScreen().availableGeometry()
            self.move(geo.right() - COMPACT_SIZE - 24, geo.bottom() - COMPACT_SIZE - 24)
        self.hotkey = FrontHotkeyWatcher(self)

    def bring_front(self) -> None:
        apply_flags(self, True)
        if self.panel.isVisible():
            self.panel.raise_()
            self.panel.activateWindow()
        else:
            self.show()
            self.raise_()
            self.activateWindow()

    def toggle_panel(self) -> None:
        if self.panel.isVisible():
            self.panel.collapse_to_compact()
        else:
            self.panel.show_from_compact(self)

    def start_screenshot(self) -> None:
        def restore() -> None:
            self.show()
            self.raise_()

        self.hide()
        QTimer.singleShot(120, lambda: start_screenshot_selection(self.store, restore))

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        rect = QRect(5, 5, self.width() - 10, self.height() - 10)
        grad = QLinearGradient(0, 0, self.width(), self.height())
        grad.setColorAt(0, QColor(255, 255, 255, 236))
        grad.setColorAt(1, QColor(211, 234, 255, 214))
        painter.setBrush(grad)
        painter.setPen(QPen(QColor(125, 184, 255, 120), 1.2))
        painter.drawRoundedRect(rect, 18, 18)
        painter.setPen(QColor("#0f172a"))
        font = painter.font()
        font.setPointSize(24 if self.store.count() else 28)
        font.setWeight(QFont.Weight.Medium)
        painter.setFont(font)
        painter.drawText(rect, Qt.AlignmentFlag.AlignCenter, str(self.store.count()) if self.store.count() else "+")
        painter.end()

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.drag_start = event.globalPosition().toPoint()
            self.drag_pos = event.globalPosition().toPoint() - self.frameGeometry().topLeft()
            self.moved = False
            event.accept()
        elif event.button() == Qt.MouseButton.RightButton:
            self.show_menu(event.globalPosition().toPoint())
            event.accept()

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if self.drag_pos is not None and event.buttons() & Qt.MouseButton.LeftButton:
            global_pos = event.globalPosition().toPoint()
            if (global_pos - self.drag_start).manhattanLength() < QApplication.startDragDistance():
                return
            if self.store.count() > 0 and event.position().y() > self.height() * 0.42 and not self.moved:
                drag = QDrag(self)
                drag.setMimeData(mime_for_items(self.store.items))
                drag.exec(Qt.DropAction.CopyAction)
                self.drag_pos = None
                return
            self.move(global_pos - self.drag_pos)
            self.settings.setValue("compact_pos", self.pos())
            self.moved = True
            event.accept()

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        should_toggle = self.drag_start is not None and not self.moved and (event.globalPosition().toPoint() - self.drag_start).manhattanLength() < QApplication.startDragDistance()
        self.drag_start = None
        self.drag_pos = None
        if should_toggle and event.button() == Qt.MouseButton.LeftButton:
            self.toggle_panel()
            event.accept()
        else:
            super().mouseReleaseEvent(event)

    def show_menu(self, pos: QPoint) -> None:
        menu = QMenu(self)
        open_action = QAction("Open shelf", self)
        open_action.triggered.connect(self.toggle_panel)
        shot_action = QAction("Screenshot", self)
        shot_action.triggered.connect(self.start_screenshot)
        paste_action = QAction("Add from clipboard", self)
        paste_action.triggered.connect(lambda: add_clipboard_to_store(self.store))
        copy_action = QAction("Copy", self)
        copy_action.setEnabled(self.store.count() > 0)
        copy_action.triggered.connect(lambda: copy_items_to_clipboard(self.store, True))
        clear_action = QAction("Clear", self)
        clear_action.setEnabled(self.store.count() > 0)
        clear_action.triggered.connect(self.store.clear)
        quit_action = QAction("Quit", self)
        quit_action.triggered.connect(QApplication.quit)
        menu.addAction(open_action)
        menu.addAction(shot_action)
        menu.addAction(paste_action)
        menu.addSeparator()
        menu.addAction(copy_action)
        menu.addAction(clear_action)
        menu.addSeparator()
        menu.addAction(quit_action)
        menu.exec(pos)

    def dragEnterEvent(self, event) -> None:
        if not event.mimeData().hasFormat(INTERNAL_MIME):
            event.acceptProposedAction()
        else:
            event.ignore()

    def dropEvent(self, event) -> None:
        if add_mime_to_store(self.store, event.mimeData()):
            event.acceptProposedAction()
            self.update()
        else:
            event.ignore()


APP_STYLESHEET = """
QWidget {
    font-family: Segoe UI, Arial;
    font-size: 12px;
    color: #0f172a;
}
QFrame#panelRoot {
    background: rgba(247, 252, 255, 232);
    border: 1px solid rgba(180, 210, 245, 120);
    border-radius: 16px;
}
QLabel#appTitle {
    font-size: 13px;
    font-weight: 650;
}
QLabel#muted {
    color: rgba(71,85,105,155);
}
QLabel#countBadge {
    background: rgba(255,255,255,90);
    color: #2563eb;
    border: 1px solid rgba(147,197,253,75);
    border-radius: 9px;
    padding: 2px 7px;
    font-weight: 650;
}
QFrame#fileRow {
    background: rgba(255,255,255,84);
    border: 1px solid rgba(203,213,225,50);
    border-radius: 9px;
}
QLabel#fileName {
    font-size: 11px;
    font-weight: 600;
}
QLineEdit#renameEdit {
    background: rgba(255,255,255,190);
    border: 1px solid rgba(96,165,250,130);
    border-radius: 7px;
    padding: 2px 5px;
    font-size: 11px;
    font-weight: 600;
}
QPushButton#primaryButton {
    background: rgba(37,99,235,210);
    color: white;
    border: 1px solid rgba(255,255,255,120);
    border-radius: 11px;
    padding: 5px 10px;
    font-weight: 680;
}
QPushButton#secondaryButton, QPushButton#ghostButton {
    background: rgba(255,255,255,72);
    color: #0f172a;
    border: 1px solid rgba(203,213,225,95);
    border-radius: 11px;
    padding: 5px 9px;
    font-weight: 610;
}
QPushButton#smallButton {
    background: transparent;
    border: none;
    color: rgba(71,85,105,160);
}
QScrollArea, QWidget#rowsHost {
    border: none;
    background: transparent;
}
"""


def main() -> int:
    app = QApplication(sys.argv)
    app.setApplicationName(APP_NAME)
    app.setOrganizationName(ORG_NAME)
    app.setQuitOnLastWindowClosed(False)
    if hasattr(app, "setQuitLockEnabled"):
        app.setQuitLockEnabled(False)
    QPixmapCache.setCacheLimit(2048)
    app.setStyleSheet(APP_STYLESHEET)
    app.setWindowIcon(QIcon(make_icon()))

    settings = QSettings(ORG_NAME, APP_NAME + "Windows")
    store = FileStore()
    panel = ShelfPanel(store, settings)
    compact = CompactShelf(store, settings, panel)
    app._shakeshelf_objects = (store, panel, compact)  # type: ignore[attr-defined]

    def rescue_last_window() -> None:
        if not compact.isVisible() and not panel.isVisible():
            compact.show()
            compact.raise_()

    app.lastWindowClosed.connect(rescue_last_window)
    compact.show()
    compact.raise_()
    return app.exec()


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except SystemExit:
        raise
    except Exception:
        log(traceback.format_exc())
        raise
