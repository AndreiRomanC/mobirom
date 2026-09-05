from __future__ import annotations

import os
import sys
import tempfile
import time
import traceback
from dataclasses import dataclass, field
from pathlib import Path
from typing import Any, Callable, Iterable

APP_NAME = "ShakeShelf"
ORG_NAME = "Andivio"
LOG_PATH = Path.home() / "ShakeShelf.log"

MAX_ITEMS = 25
COMPACT_SIZE = 88
PANEL_W = 324
PANEL_H = 220
AUTO_CLEAR_AFTER_COPY = True
TEMP_DIR = Path(tempfile.gettempdir()) / APP_NAME
INTERNAL_MIME = "application/x-shakeshelf-internal"


def log(message: str) -> None:
    try:
        with LOG_PATH.open("a", encoding="utf-8") as fh:
            fh.write(f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] {message}\n")
    except Exception:
        pass


def fatal_dialog(title: str, message: str) -> None:
    log(f"FATAL: {title}: {message}")
    print(f"{title}\n{message}", file=sys.stderr)
    try:
        import tkinter as tk
        from tkinter import messagebox

        root = tk.Tk()
        root.withdraw()
        messagebox.showerror(title, message)
        root.destroy()
    except Exception:
        pass


# ---------------------------------------------------------------------------
# Path handling. This app stores only references/paths, never file contents.
# ---------------------------------------------------------------------------


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
        if path in seen:
            continue
        if not os.path.exists(path):
            continue
        seen.add(path)
        out.append(path)
    return out


def mime_for_paths(paths: Iterable[str]):
    from PySide6.QtCore import QMimeData, QUrl

    valid = normalise_paths(paths)
    mime = QMimeData()
    mime.setUrls([QUrl.fromLocalFile(p) for p in valid])
    mime.setText("\n".join(valid))

    # Explorer hint: copy operation, not plain text. Qt maps URL lists to
    # platform MIME formats, this is just an extra compatibility hint.
    if sys.platform.startswith("win") and valid:
        try:
            mime.setData(
                'application/x-qt-windows-mime;value="Preferred DropEffect"',
                (1).to_bytes(4, byteorder="little"),
            )
        except Exception:
            pass
    return mime


def dropped_paths_from_event(event) -> list[str]:
    if not event.mimeData().hasUrls():
        return []
    paths: list[str] = []
    for url in event.mimeData().urls():
        p = url.toLocalFile()
        if p:
            paths.append(p)
    return normalise_paths(paths)


def display_path(path: str) -> tuple[str, str]:
    p = Path(path)
    name = p.name or path
    parent = str(p.parent)
    return name, parent


@dataclass
class ShelfItem:
    kind: str
    name: str
    parent: str = ""
    path: str | None = None
    image: Any | None = None
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
    name, parent = display_path(path)
    return ShelfItem(kind="file", name=name, parent=parent, path=path)


def image_item(image: Any, name: str | None = None, parent: str | None = None) -> ShelfItem | None:
    try:
        if image is None or image.isNull():
            return None
    except Exception:
        return None

    TEMP_DIR.mkdir(parents=True, exist_ok=True)
    stamp = time.strftime("%Y%m%d-%H%M%S")
    temp_path = TEMP_DIR / f"clipboard-image-{stamp}-{time.time_ns()}.png"
    if not image.save(str(temp_path), "PNG"):
        return None
    thumb_path = TEMP_DIR / f"clipboard-thumb-{stamp}-{time.time_ns()}.png"
    try:
        from PySide6.QtCore import Qt

        thumb = image.scaled(
            96,
            96,
            Qt.AspectRatioMode.KeepAspectRatio,
            Qt.TransformationMode.SmoothTransformation,
        )
        if not thumb.save(str(thumb_path), "PNG"):
            thumb_path = temp_path
    except Exception:
        try:
            thumb = image.scaled(96, 96)
            if not thumb.save(str(thumb_path), "PNG"):
                thumb_path = temp_path
        except Exception:
            thumb_path = temp_path

    try:
        size = image.size()
        display_parent = parent or f"Temporary PNG · {size.width()}×{size.height()}"
    except Exception:
        display_parent = parent or "Temporary PNG"
    return ShelfItem(
        kind="image",
        name=name or "Clipboard image",
        parent=display_parent,
        temp_path=str(temp_path),
        thumb_path=str(thumb_path),
    )


def temp_image_file_item(path: str, name: str | None = None, parent: str | None = None) -> ShelfItem | None:
    image_path = Path(path)
    if not image_path.exists():
        return None

    TEMP_DIR.mkdir(parents=True, exist_ok=True)
    stamp = time.strftime("%Y%m%d-%H%M%S")
    thumb_path = TEMP_DIR / f"web-thumb-{stamp}-{time.time_ns()}.png"
    try:
        from PySide6.QtCore import QSize, Qt
        from PySide6.QtGui import QImageReader

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
        details = f"{size.width()}×{size.height()}" if size.isValid() else ""
    except Exception:
        log(traceback.format_exc())
        return None

    display_parent = parent or "Temporary image"
    if details:
        display_parent = f"{display_parent} · {details}"
    return ShelfItem(
        kind="image",
        name=name or image_path.name or "Web image",
        parent=display_parent,
        temp_path=str(image_path),
        thumb_path=str(thumb_path),
    )


def copy_paths_to_clipboard(paths: Iterable[str], clear_callback: Callable[[], None] | None = None) -> bool:
    items = [file_item(path) for path in normalise_paths(paths)]
    return copy_items_to_clipboard(items, clear_callback)


def mime_for_items(items: Iterable[ShelfItem]):
    from PySide6.QtCore import QMimeData, QUrl

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
        if item.kind == "image" and first_image is None:
            try:
                from PySide6.QtGui import QImage

                if item.temp_path:
                    loaded = QImage(item.temp_path)
                    if not loaded.isNull():
                        first_image = loaded
            except Exception:
                pass

    if urls:
        mime.setUrls(urls)
        mime.setText("\n".join(text_lines))
    elif text_lines:
        mime.setText("\n".join(text_lines))

    if first_image is not None:
        mime.setImageData(first_image)

    if sys.platform.startswith("win") and urls:
        try:
            mime.setData(
                'application/x-qt-windows-mime;value="Preferred DropEffect"',
                (1).to_bytes(4, byteorder="little"),
            )
        except Exception:
            pass
    return mime


def copy_items_to_clipboard(items: Iterable[ShelfItem], clear_callback: Callable[[], None] | None = None) -> bool:
    from PySide6.QtWidgets import QApplication

    selected = list(items)[:MAX_ITEMS]
    if not selected:
        return False

    mime = mime_for_items(selected)
    if not (mime.hasUrls() or mime.hasImage() or mime.hasText()):
        return False

    expected_urls = mime.hasUrls()
    expected_image = mime.hasImage()
    expected_text = mime.hasText()

    try:
        clipboard = QApplication.clipboard()
        clipboard.setMimeData(mime)
        placed = clipboard.mimeData()
    except Exception:
        log(traceback.format_exc())
        return False

    success = False
    if placed is not None:
        success = bool(
            (expected_image and placed.hasImage())
            or (expected_urls and (placed.hasUrls() or placed.hasText()))
            or (expected_text and placed.hasText())
            or placed.formats()
        )

    if success and clear_callback is not None:
        clear_callback()
    return success


# ---------------------------------------------------------------------------
# Shared store
# ---------------------------------------------------------------------------


class FileStore:
    def __init__(self) -> None:
        self.items: list[ShelfItem] = []
        self._subscribers: list[Callable[[], None]] = []

    @property
    def paths(self) -> list[str]:
        return [item.path for item in self.items if item.kind == "file" and item.path]

    def subscribe(self, callback: Callable[[], None]) -> None:
        self._subscribers.append(callback)

    def notify(self) -> None:
        for cb in list(self._subscribers):
            try:
                cb()
            except Exception:
                log(traceback.format_exc())

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

    def add_image(self, image: Any, name: str | None = None, parent: str | None = None) -> bool:
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
        item = temp_image_file_item(path, name, parent)
        if item is None:
            try:
                Path(path).unlink(missing_ok=True)
            except Exception:
                log(traceback.format_exc())
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

    def _delete_temp_file(self, item: ShelfItem) -> None:
        if item.kind != "image" or not item.temp_path:
            return
        try:
            Path(item.temp_path).unlink(missing_ok=True)
            if item.thumb_path and item.thumb_path != item.temp_path:
                Path(item.thumb_path).unlink(missing_ok=True)
        except Exception:
            log(traceback.format_exc())

    def remove(self, identifier: str) -> None:
        removed: list[ShelfItem] = []
        kept: list[ShelfItem] = []
        for item in self.items:
            if item.id == identifier or item.path == identifier or item.temp_path == identifier:
                removed.append(item)
            else:
                kept.append(item)
        if removed:
            self.items = kept
            for item in removed:
                self._delete_temp_file(item)
            self.notify()

    def clear(self) -> None:
        if self.items:
            removed = list(self.items)
            self.items.clear()
            for item in removed:
                self._delete_temp_file(item)
            self.notify()

    def clear_after_copy(self) -> None:
        if self.items:
            self.items.clear()
            self.notify()

    def count(self) -> int:
        return len(self.items)
