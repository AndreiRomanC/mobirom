from __future__ import annotations

import ctypes
import os
import sys
import time
from pathlib import Path
from typing import Callable

from PySide6.QtCore import QEvent, QPoint, QRect, QSettings, QSize, Qt, QTimer
from PySide6.QtGui import QAction, QColor, QCursor, QDrag, QFont, QIcon, QImageReader, QLinearGradient, QMouseEvent, QPainter, QPen, QPixmap
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

from shakeshelf_core import (
    APP_NAME,
    COMPACT_SIZE,
    FileStore,
    INTERNAL_MIME,
    PANEL_H,
    PANEL_W,
    ShelfItem,
    TEMP_DIR,
    copy_items_to_clipboard,
    dropped_paths_from_event,
    log,
    mime_for_items,
)


# ---------------------------------------------------------------------------
# Flat app icon
# ---------------------------------------------------------------------------


def make_icon(size: int = 64) -> QIcon:
    pix = QPixmap(size, size)
    pix.fill(Qt.GlobalColor.transparent)
    painter = QPainter(pix)
    painter.setRenderHint(QPainter.RenderHint.Antialiasing)
    r = QRect(7, 7, size - 14, size - 14)
    grad = QLinearGradient(0, 7, size, size)
    grad.setColorAt(0.00, QColor("#ffffff"))
    grad.setColorAt(0.46, QColor("#eef7ff"))
    grad.setColorAt(1.00, QColor("#d9ecff"))
    painter.setPen(QPen(QColor(255, 255, 255, 210), 1))
    painter.setBrush(grad)
    painter.drawRoundedRect(r, 18, 18)
    painter.setPen(QPen(QColor("#6aa9ff"), 2))
    painter.drawRoundedRect(r.adjusted(1, 1, -1, -1), 17, 17)
    painter.setPen(QColor("#0f172a"))
    font = painter.font()
    font.setPointSize(max(18, size // 3))
    font.setWeight(QFont.Weight.Light)
    painter.setFont(font)
    painter.drawText(pix.rect(), Qt.AlignmentFlag.AlignCenter, "+")
    painter.end()
    return QIcon(pix)


# ---------------------------------------------------------------------------
# UI helpers
# ---------------------------------------------------------------------------


SETTING_STAY_ON_TOP = "stay_on_top"
SETTING_CLEAR_AFTER_COPY = "clear_after_copy"
IMAGE_SUFFIXES = {
    ".apng",
    ".avif",
    ".bmp",
    ".gif",
    ".heic",
    ".heif",
    ".ico",
    ".jpeg",
    ".jpg",
    ".png",
    ".tif",
    ".tiff",
    ".webp",
}
WEB_IMAGE_MAX_BYTES = 12 * 1024 * 1024
WEB_IMAGE_TIMEOUT = 4
MAX_VISIBLE_NAME_CHARS = 18


def is_image_file(path: str | None) -> bool:
    if not path:
        return False
    return os.path.isfile(path) and Path(path).suffix.lower() in IMAGE_SUFFIXES


def compact_display_name(name: str) -> str:
    clean = " ".join((name or "").split())
    if len(clean) <= MAX_VISIBLE_NAME_CHARS:
        return clean
    return "…" + clean[-(MAX_VISIBLE_NAME_CHARS - 1) :]


def looks_like_image_url(url: str) -> bool:
    import urllib.parse

    parsed = urllib.parse.urlparse(url)
    return parsed.scheme in {"http", "https"} and Path(parsed.path).suffix.lower() in IMAGE_SUFFIXES


def remote_image_urls_from_mime(mime) -> list[str]:
    import re
    import urllib.parse

    urls: list[str] = []
    seen: set[str] = set()
    img_src_re = re.compile(r"""<img[^>]+src=["']([^"']+)["']""", re.IGNORECASE)

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

    if mime.hasHtml():
        for match in img_src_re.finditer(mime.html()):
            add(match.group(1), allow_unknown=True)

    return urls[:3]


def can_accept_mime(event) -> bool:
    mime = event.mimeData()
    if mime.hasFormat(INTERNAL_MIME):
        return False
    return bool(
        dropped_paths_from_event(event)
        or mime.hasImage()
        or mime.hasUrls()
        or mime.hasText()
        or mime.hasHtml()
    )


def web_image_name(url: str, content_type: str) -> tuple[str, str]:
    import mimetypes
    import urllib.parse

    parsed = urllib.parse.urlparse(url)
    raw_name = Path(urllib.parse.unquote(parsed.path)).name
    ext = Path(raw_name).suffix
    if not raw_name or not ext:
        guessed = mimetypes.guess_extension(content_type.split(";")[0].strip()) or ".img"
        raw_name = f"Web image{guessed}"
    return raw_name, parsed.netloc or "web"


def add_web_image_to_store(store: FileStore, url: str) -> bool:
    import mimetypes
    import urllib.request

    TEMP_DIR.mkdir(parents=True, exist_ok=True)
    try:
        request = urllib.request.Request(url, headers={"User-Agent": f"{APP_NAME}/1.0"})
        with urllib.request.urlopen(request, timeout=WEB_IMAGE_TIMEOUT) as response:
            content_type = response.headers.get("Content-Type", "application/octet-stream")
            name, parent = web_image_name(url, content_type)
            ext = Path(name).suffix or (mimetypes.guess_extension(content_type.split(";")[0].strip()) or ".img")
            temp_path = TEMP_DIR / f"web-image-{time.strftime('%Y%m%d-%H%M%S')}-{time.time_ns()}{ext}"
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
        return store.add_temp_image_file(str(temp_path), name=name, parent=parent)
    except Exception:
        try:
            if "temp_path" in locals():
                temp_path.unlink(missing_ok=True)
        except Exception:
            pass
        log(f"Web image drop failed: {url}")
        return False


def add_mime_to_store(store: FileStore, event) -> bool:
    mime = event.mimeData()
    if mime.hasFormat(INTERNAL_MIME):
        return False

    paths = dropped_paths_from_event(event)
    if paths:
        return store.add(paths)

    for url in remote_image_urls_from_mime(mime):
        if add_web_image_to_store(store, url):
            return True

    if mime.hasImage():
        try:
            return store.add_image(mime.imageData())
        except Exception:
            log("Image drop failed from mime image data")

    return False


def settings_bool(settings: QSettings, key: str, default: bool) -> bool:
    value = settings.value(key, default)
    if isinstance(value, bool):
        return value
    if isinstance(value, str):
        return value.lower() in {"1", "true", "yes", "on"}
    return bool(value)


def base_window_flags(stay_on_top: bool = True) -> Qt.WindowType:
    flags = Qt.WindowType.FramelessWindowHint | Qt.WindowType.Window
    if stay_on_top:
        flags |= Qt.WindowType.WindowStaysOnTopHint
    return flags


def apply_window_flags(widget: QWidget, stay_on_top: bool) -> None:
    was_visible = widget.isVisible()
    pos = widget.pos()
    widget.setWindowFlags(base_window_flags(stay_on_top))
    widget.move(pos)
    if was_visible:
        widget.show()
        if stay_on_top:
            widget.raise_()


_CORE_GRAPHICS = None
_CORE_GRAPHICS_READY = False
_MAC_OPTION_FLAG_MASK = 0x00080000
_MAC_OPTION_KEY_CODES = (58, 61)
_MAC_SPACE_KEY_CODE = 49
_MAC_CARBON_OPTION_MODIFIER = 1 << 11
_WINDOWS_ALT_KEY_CODE = 0x12
_WINDOWS_SPACE_KEY_CODE = 0x20


def four_char_code(value: str) -> int:
    return int.from_bytes(value.encode("ascii"), "big")


class EventTypeSpec(ctypes.Structure):
    _fields_ = [("eventClass", ctypes.c_uint32), ("eventKind", ctypes.c_uint32)]


class EventHotKeyID(ctypes.Structure):
    _fields_ = [("signature", ctypes.c_uint32), ("id", ctypes.c_uint32)]


def ensure_core_graphics() -> bool:
    global _CORE_GRAPHICS, _CORE_GRAPHICS_READY

    if not sys.platform == "darwin":
        return False
    if not _CORE_GRAPHICS_READY:
        try:
            _CORE_GRAPHICS = ctypes.CDLL("/System/Library/Frameworks/CoreGraphics.framework/CoreGraphics")
            _CORE_GRAPHICS.CGEventSourceButtonState.argtypes = [ctypes.c_int, ctypes.c_uint32]
            _CORE_GRAPHICS.CGEventSourceButtonState.restype = ctypes.c_bool
            _CORE_GRAPHICS.CGEventSourceFlagsState.argtypes = [ctypes.c_int]
            _CORE_GRAPHICS.CGEventSourceFlagsState.restype = ctypes.c_uint64
            _CORE_GRAPHICS.CGEventSourceKeyState.argtypes = [ctypes.c_int, ctypes.c_uint16]
            _CORE_GRAPHICS.CGEventSourceKeyState.restype = ctypes.c_bool
        except Exception:
            _CORE_GRAPHICS = None
        _CORE_GRAPHICS_READY = True
    return _CORE_GRAPHICS is not None


def left_mouse_pressed() -> bool:
    if sys.platform == "darwin" and ensure_core_graphics():
        return bool(_CORE_GRAPHICS.CGEventSourceButtonState(0, 0))
    if sys.platform.startswith("win"):
        try:
            return bool(ctypes.windll.user32.GetAsyncKeyState(0x01) & 0x8000)
        except Exception:
            pass

    return bool(QApplication.mouseButtons() & Qt.MouseButton.LeftButton)


def global_option_pressed() -> bool:
    if sys.platform == "darwin" and ensure_core_graphics():
        if _CORE_GRAPHICS.CGEventSourceFlagsState(0) & _MAC_OPTION_FLAG_MASK:
            return True
        return any(_CORE_GRAPHICS.CGEventSourceKeyState(0, code) for code in _MAC_OPTION_KEY_CODES)
    if sys.platform.startswith("win"):
        try:
            return bool(ctypes.windll.user32.GetAsyncKeyState(_WINDOWS_ALT_KEY_CODE) & 0x8000)
        except Exception:
            pass
    return False


def global_option_space_pressed() -> bool:
    if sys.platform == "darwin" and ensure_core_graphics():
        space_pressed = bool(_CORE_GRAPHICS.CGEventSourceKeyState(0, _MAC_SPACE_KEY_CODE))
        return global_option_pressed() and space_pressed
    if sys.platform.startswith("win"):
        try:
            user32 = ctypes.windll.user32
            alt_pressed = bool(user32.GetAsyncKeyState(_WINDOWS_ALT_KEY_CODE) & 0x8000)
            space_pressed = bool(user32.GetAsyncKeyState(_WINDOWS_SPACE_KEY_CODE) & 0x8000)
            return alt_pressed and space_pressed
        except Exception:
            pass
    return False


class NativeFrontHotkey:
    def __init__(self, compact) -> None:
        self.compact = compact
        self.registered = False
        self.last_pressed_at = 0.0
        self.last_triggered_at = -999.0
        self._carbon = None
        self._callback = None
        self._handler_ref = ctypes.c_void_p()
        self._hotkey_ref = ctypes.c_void_p()
        if sys.platform == "darwin":
            self.registered = self._register_macos()

    def _register_macos(self) -> bool:
        try:
            carbon = ctypes.CDLL("/System/Library/Frameworks/Carbon.framework/Carbon")
            callback_type = ctypes.CFUNCTYPE(ctypes.c_int, ctypes.c_void_p, ctypes.c_void_p, ctypes.c_void_p)

            carbon.GetApplicationEventTarget.restype = ctypes.c_void_p
            carbon.InstallEventHandler.argtypes = [
                ctypes.c_void_p,
                callback_type,
                ctypes.c_uint32,
                ctypes.POINTER(EventTypeSpec),
                ctypes.c_void_p,
                ctypes.POINTER(ctypes.c_void_p),
            ]
            carbon.InstallEventHandler.restype = ctypes.c_int
            carbon.RegisterEventHotKey.argtypes = [
                ctypes.c_uint32,
                ctypes.c_uint32,
                EventHotKeyID,
                ctypes.c_void_p,
                ctypes.c_uint32,
                ctypes.POINTER(ctypes.c_void_p),
            ]
            carbon.RegisterEventHotKey.restype = ctypes.c_int

            def on_event(_next_handler, _event, _user_data):
                QTimer.singleShot(0, self._handle_press)
                return 0

            self._callback = callback_type(on_event)
            event_type = EventTypeSpec(four_char_code("keyb"), 5)
            target = carbon.GetApplicationEventTarget()
            status = carbon.InstallEventHandler(
                target,
                self._callback,
                1,
                ctypes.byref(event_type),
                None,
                ctypes.byref(self._handler_ref),
            )
            if status != 0:
                log(f"Native hotkey handler failed: {status}")
                return False

            hotkey_id = EventHotKeyID(four_char_code("shsf"), 1)
            status = carbon.RegisterEventHotKey(
                _MAC_SPACE_KEY_CODE,
                _MAC_CARBON_OPTION_MODIFIER,
                hotkey_id,
                target,
                0,
                ctypes.byref(self._hotkey_ref),
            )
            if status != 0:
                log(f"Native Option+Space hotkey registration failed: {status}")
                return False

            self._carbon = carbon
            log("Native Option+Space hotkey registered")
            return True
        except Exception:
            log("Native Option+Space hotkey registration failed")
            return False

    def _handle_press(self) -> None:
        now = time.monotonic()
        double_press = self.last_pressed_at > 0 and now - self.last_pressed_at <= 0.75
        cooldown_done = now - self.last_triggered_at >= 1.0
        if double_press and cooldown_done:
            self.last_triggered_at = now
            self.last_pressed_at = 0.0
            self.compact.bring_to_front_once()
        else:
            self.last_pressed_at = now


class ShakeToShowWatcher:
    def __init__(self, compact) -> None:
        self.compact = compact
        self.timer = QTimer(compact)
        self.timer.setTimerType(Qt.TimerType.CoarseTimer)
        self.timer.setInterval(120)
        self.timer.timeout.connect(self.tick)
        self.last_pos: QPoint | None = None
        self.last_sign = 0
        self.direction_flips = 0
        self.travel = 0
        self.press_started_at = 0.0
        self.last_triggered_at = 0.0
        self.hotkey_was_down = False
        self.last_hotkey_pressed_at = 0.0
        self.last_hotkey_triggered_at = -999.0
        self.timer.start()

    def reset(self) -> None:
        self.last_pos = None
        self.last_sign = 0
        self.direction_flips = 0
        self.travel = 0
        self.press_started_at = 0.0

    def start(self) -> None:
        if not self.timer.isActive():
            self.reset()
            self.timer.start()

    def stop(self) -> None:
        if self.timer.isActive():
            self.timer.stop()
        self.reset()

    def handle_front_hotkey(self, now: float) -> bool:
        pressed = global_option_space_pressed()
        triggered = False
        if pressed and not self.hotkey_was_down:
            double_press = self.last_hotkey_pressed_at > 0 and now - self.last_hotkey_pressed_at <= 0.65
            cooldown_done = now - self.last_hotkey_triggered_at >= 1.0
            if double_press and cooldown_done:
                self.last_hotkey_triggered_at = now
                self.last_hotkey_pressed_at = 0.0
                self.compact.bring_to_front_once()
                self.reset()
                triggered = True
            else:
                self.last_hotkey_pressed_at = now
        self.hotkey_was_down = pressed
        return triggered

    def tick(self) -> None:
        now = time.monotonic()
        native_hotkey = getattr(self.compact, "native_hotkey", None)
        native_hotkey_active = bool(native_hotkey and native_hotkey.registered)
        if not native_hotkey_active and self.handle_front_hotkey(now):
            return

        panel = getattr(self.compact, "panel", None)
        if panel is not None and panel.isVisible():
            self.reset()
            return

        if not left_mouse_pressed():
            self.reset()
            return

        pos = QCursor.pos()
        if self.last_pos is None:
            self.last_pos = pos
            self.press_started_at = now
            return

        dx = pos.x() - self.last_pos.x()
        dy = pos.y() - self.last_pos.y()
        self.travel += abs(dx) + abs(dy)

        movement = dx if abs(dx) >= abs(dy) else dy
        if abs(movement) >= 6:
            sign = 1 if movement > 0 else -1
            if self.last_sign and sign != self.last_sign:
                self.direction_flips += 1
            self.last_sign = sign

        self.last_pos = pos
        elapsed = max(0.001, now - self.press_started_at)
        enough_shake = self.direction_flips >= 2 and self.travel >= 72 and elapsed <= 2.8
        cooldown_done = now - self.last_triggered_at >= 1.1
        if enough_shake and cooldown_done:
            self.last_triggered_at = now
            self.compact.show_from_shake(pos)
            self.reset()


def button(label: str, object_name: str = "secondaryButton") -> QPushButton:
    btn = QPushButton(label)
    btn.setObjectName(object_name)
    btn.setCursor(Qt.CursorShape.PointingHandCursor)
    btn.setMinimumHeight(24)
    return btn


def add_clipboard_to_store(store: FileStore) -> bool:
    clipboard = QApplication.clipboard()
    mime = clipboard.mimeData()
    if mime is not None and mime.hasFormat(INTERNAL_MIME):
        return False
    if mime is not None and mime.hasUrls():
        paths = [url.toLocalFile() for url in mime.urls() if url.toLocalFile()]
        if paths and store.add(paths):
            return True

    image = clipboard.image()
    try:
        if not image.isNull():
            return store.add_image(image)
    except Exception:
        return False

    if mime is not None:
        for url in remote_image_urls_from_mime(mime):
            if add_web_image_to_store(store, url):
                return True
    return False


def copy_store_to_clipboard(store: FileStore, clear_after_copy: bool) -> bool:
    if not store.items:
        return False
    copied = copy_items_to_clipboard(store.items)
    if copied and clear_after_copy:
        store.clear_after_copy()
    return copied


APP_STYLESHEET = """
QWidget {
    font-family: Arial;
    font-size: 12px;
    color: #0f172a;
}
QFrame#panelRoot {
    background: rgba(247, 252, 255, 222);
    border: 1px solid rgba(255, 255, 255, 142);
    border-radius: 18px;
}
QLabel#appTitle {
    font-size: 13px;
    font-weight: 620;
    color: #0f172a;
}
QLabel#muted {
    color: rgba(71, 85, 105, 142);
    font-size: 10px;
}
QLabel#countBadge {
    background: rgba(255, 255, 255, 58);
    color: rgba(37, 99, 235, 198);
    border: 1px solid rgba(147, 197, 253, 56);
    border-radius: 10px;
    padding: 2px 7px;
    font-size: 10px;
    font-weight: 650;
}
QFrame#fileRow {
    background: rgba(255, 255, 255, 78);
    border: 1px solid rgba(203, 213, 225, 42);
    border-radius: 10px;
}
QFrame#fileRow:hover {
    background: rgba(255, 255, 255, 124);
    border: 1px solid rgba(96, 165, 250, 74);
}
QLabel#imageThumb {
    background: rgba(255,255,255,132);
    border: 1px solid rgba(147,197,253,82);
    border-radius: 8px;
}
QLabel#fileName {
    color: #0f172a;
    font-weight: 600;
    font-size: 11px;
}
QLineEdit#renameEdit {
    background: rgba(255,255,255,176);
    border: 1px solid rgba(96,165,250,112);
    border-radius: 7px;
    color: #0f172a;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 5px;
}
QPushButton#primaryButton {
    background: rgba(37, 99, 235, 206);
    color: #ffffff;
    border: 1px solid rgba(255,255,255,112);
    border-radius: 12px;
    padding: 5px 10px;
    font-weight: 680;
}
QPushButton#primaryButton:hover {
    background: rgba(29, 78, 216, 232);
}
QPushButton#primaryButton:disabled {
    background: rgba(226, 232, 240, 126);
    color: rgba(100, 116, 139, 142);
    border: 1px solid rgba(255,255,255,96);
}
QPushButton#secondaryButton {
    background: rgba(255, 255, 255, 62);
    color: rgba(15, 23, 42, 202);
    border: 1px solid rgba(203, 213, 225, 82);
    border-radius: 12px;
    padding: 5px 9px;
    font-weight: 610;
}
QPushButton#secondaryButton:hover {
    background: rgba(255, 255, 255, 150);
    border: 1px solid rgba(96, 165, 250, 104);
}
QPushButton#ghostButton {
    background: transparent;
    color: rgba(51, 65, 85, 174);
    border: none;
    border-radius: 10px;
    padding: 4px 7px;
}
QPushButton#ghostButton:hover {
    background: rgba(255, 255, 255, 118);
    color: #0f172a;
}
QPushButton#smallButton {
    background: transparent;
    color: rgba(71, 85, 105, 190);
    border: none;
    border-radius: 9px;
    font-size: 14px;
}
QPushButton#smallButton:hover {
    background: rgba(226, 240, 255, 180);
    color: #0f172a;
}
QFrame#emptyState {
    background: rgba(255, 255, 255, 48);
    border: 1px solid rgba(203,213,225,38);
    border-radius: 12px;
}
QLabel#emptyOrb {
    background: transparent;
    color: rgba(37, 99, 235, 170);
    border: none;
    border-radius: 0px;
    font-size: 22px;
    font-weight: 260;
}
QScrollArea {
    border: none;
    background: transparent;
}
QScrollArea#itemScroll,
QScrollArea#itemScroll QWidget,
QWidget#rowsHost {
    background: transparent;
}
QMenu {
    background: rgba(255,255,255,248);
    border: 1px solid rgba(203,213,225,150);
    border-radius: 14px;
    padding: 7px;
}
QMenu::item {
    padding: 8px 24px 8px 13px;
    border-radius: 9px;
}
QMenu::item:selected {
    background: rgba(226, 240, 255, 210);
}
"""


# ---------------------------------------------------------------------------
# File row
# ---------------------------------------------------------------------------


class ImageThumb(QWidget):
    def __init__(self, image_path: str) -> None:
        super().__init__()
        self.setFixedSize(38, 34)
        self.thumb = QPixmap()
        reader = QImageReader(image_path)
        reader.setAutoTransform(True)
        source_size = reader.size()
        target_size = QSize(34, 30)
        if source_size.isValid():
            scaled_size = QSize(source_size)
            scaled_size.scale(target_size, Qt.AspectRatioMode.KeepAspectRatio)
            reader.setScaledSize(scaled_size)
        image = reader.read()
        if not image.isNull():
            self.thumb = QPixmap.fromImage(image)
            if self.thumb.width() > target_size.width() or self.thumb.height() > target_size.height():
                self.thumb = self.thumb.scaled(
                    target_size,
                    Qt.AspectRatioMode.KeepAspectRatio,
                    Qt.TransformationMode.SmoothTransformation,
                )

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        rect = self.rect().adjusted(1, 1, -1, -1)
        painter.setBrush(QColor(255, 255, 255, 150))
        painter.setPen(QPen(QColor(147, 197, 253, 82), 1))
        painter.drawRoundedRect(rect, 8, 8)

        if self.thumb.isNull():
            painter.setPen(QColor(29, 78, 216, 210))
            font = painter.font()
            font.setPointSize(7)
            font.setWeight(QFont.Weight.Bold)
            painter.setFont(font)
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
        painter.setPen(QPen(QColor(147, 197, 253, 76), 1))
        painter.drawRoundedRect(self.rect().adjusted(1, 1, -1, -1), 8, 8)

        painter.setBrush(QColor(219, 235, 255, 126))
        painter.setPen(QPen(QColor(37, 99, 235, 156), 1.2))
        if self.kind == "folder":
            painter.drawRoundedRect(QRect(7, 11, 10, 5), 2, 2)
            painter.drawRoundedRect(QRect(6, 14, 22, 13), 3, 3)
        else:
            painter.drawRoundedRect(QRect(10, 7, 15, 21), 3, 3)
            painter.drawLine(20, 7, 25, 12)
            painter.drawLine(20, 7, 20, 12)
            painter.drawLine(20, 12, 25, 12)
            painter.drawLine(13, 17, 22, 17)
            painter.drawLine(13, 21, 21, 21)
        painter.end()


class ElidedLabel(QLabel):
    def __init__(self, text: str) -> None:
        super().__init__(text)
        self.setMinimumWidth(0)

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setPen(QColor("#0f172a"))
        painter.setFont(self.font())
        text = painter.fontMetrics().elidedText(
            self.text(),
            Qt.TextElideMode.ElideLeft,
            max(0, self.width()),
        )
        painter.drawText(self.rect(), Qt.AlignmentFlag.AlignVCenter | Qt.AlignmentFlag.AlignLeft, text)
        painter.end()


class ScreenshotOverlay(QWidget):
    def __init__(self, store: FileStore, restore_callback: Callable[[], None]) -> None:
        super().__init__()
        self.store = store
        self.restore_callback = restore_callback
        self.origin: QPoint | None = None
        self.current: QPoint | None = None
        self.selection = QRect()

        flags = Qt.WindowType.FramelessWindowHint | Qt.WindowType.WindowStaysOnTopHint | Qt.WindowType.Tool
        self.setWindowFlags(flags)
        self.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.setMouseTracking(True)
        self.setCursor(Qt.CursorShape.CrossCursor)
        self.setFocusPolicy(Qt.FocusPolicy.StrongFocus)

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
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        painter.fillRect(self.rect(), QColor(15, 23, 42, 76))

        if self.selection.isValid():
            local = self.selection.translated(-self.geometry().topLeft())
            painter.setCompositionMode(QPainter.CompositionMode.CompositionMode_Clear)
            painter.fillRect(local, QColor(0, 0, 0, 0))
            painter.setCompositionMode(QPainter.CompositionMode.CompositionMode_SourceOver)
            painter.setPen(QPen(QColor(96, 165, 250, 230), 2))
            painter.setBrush(Qt.BrushStyle.NoBrush)
            painter.drawRoundedRect(local.adjusted(1, 1, -1, -1), 5, 5)
        painter.end()

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.origin = event.globalPosition().toPoint()
            self.current = self.origin
            self.selection = QRect(self.origin, self.current).normalized()
            self.update()
            event.accept()
        else:
            super().mousePressEvent(event)

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if self.origin is not None:
            self.current = event.globalPosition().toPoint()
            self.selection = QRect(self.origin, self.current).normalized()
            self.update()
            event.accept()
        else:
            super().mouseMoveEvent(event)

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton and self.origin is not None:
            self.current = event.globalPosition().toPoint()
            self.selection = QRect(self.origin, self.current).normalized()
            if self.selection.width() < 8 or self.selection.height() < 8:
                self.finish()
            else:
                rect = QRect(self.selection)
                self.hide()
                QTimer.singleShot(80, lambda: self.capture(rect))
            event.accept()
        else:
            super().mouseReleaseEvent(event)

    def keyPressEvent(self, event) -> None:
        if event.key() == Qt.Key.Key_Escape:
            self.finish()
            event.accept()
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
                parent = f"Screenshot · {grab_rect.width()}×{grab_rect.height()}"
                self.store.add_image(pixmap.toImage(), name=name, parent=parent)
        self.finish()

    def finish(self) -> None:
        self.hide()
        app = QApplication.instance()
        if app is not None and getattr(app, "_shakeshelf_capture_overlay", None) is self:
            app._shakeshelf_capture_overlay = None  # type: ignore[attr-defined]
        self.restore_callback()
        self.deleteLater()


def start_screenshot_selection(store: FileStore, restore_callback: Callable[[], None]) -> None:
    app = QApplication.instance()
    overlay = ScreenshotOverlay(store, restore_callback)
    if app is not None:
        app._shakeshelf_capture_overlay = overlay  # type: ignore[attr-defined]
    overlay.begin()


class FileRow(QFrame):
    def __init__(self, store: FileStore, item: ShelfItem, drag_callback: Callable[[list[ShelfItem]], None]) -> None:
        super().__init__()
        self.store = store
        self.item = item
        self.drag_callback = drag_callback
        self._press_pos: QPoint | None = None
        self.renaming = False
        self.setObjectName("fileRow")
        self.setCursor(Qt.CursorShape.OpenHandCursor)
        self.setFixedHeight(38)
        tooltip_path = item.path or item.temp_path or item.name
        self.setToolTip("Drag out to drop/copy\n" + tooltip_path)

        self.root_layout = QHBoxLayout(self)
        self.root_layout.setContentsMargins(7, 2, 6, 2)
        self.root_layout.setSpacing(7)

        image_source = None
        if item.kind == "image" and item.temp_path:
            image_source = item.thumb_path or item.temp_path
        elif item.kind == "file" and is_image_file(item.path):
            image_source = item.path

        if image_source:
            kind = ImageThumb(image_source)
        else:
            icon_kind = "folder" if item.path and os.path.isdir(item.path) else "file"
            kind = KindIcon(icon_kind)

        self.name_label = ElidedLabel(compact_display_name(item.name))
        self.name_label.setObjectName("fileName")
        self.name_label.setTextInteractionFlags(Qt.TextInteractionFlag.NoTextInteraction)
        self.name_label.setToolTip("Double-click to rename\n" + tooltip_path)
        self.name_label.mouseDoubleClickEvent = self.begin_rename

        self.name_edit = None

        close_btn = QPushButton("×")
        close_btn.setObjectName("smallButton")
        close_btn.setFixedSize(22, 22)
        close_btn.setCursor(Qt.CursorShape.PointingHandCursor)
        close_btn.clicked.connect(lambda: self.store.remove(item.id))

        self.root_layout.addWidget(kind)
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
        if not self.renaming:
            return
        self.renaming = False
        if self.name_edit is None:
            return
        new_name = self.name_edit.text().strip()
        if new_name and new_name != self.item.name:
            self.store.rename(self.item.id, new_name)
            self.name_label.setText(compact_display_name(new_name))
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

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self._press_pos = event.position().toPoint()
            self.setCursor(Qt.CursorShape.ClosedHandCursor)
        super().mousePressEvent(event)

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if not (event.buttons() & Qt.MouseButton.LeftButton) or self._press_pos is None:
            super().mouseMoveEvent(event)
            return
        distance = (event.position().toPoint() - self._press_pos).manhattanLength()
        if distance >= QApplication.startDragDistance():
            self.drag_callback([self.item])
            self._press_pos = None
        else:
            super().mouseMoveEvent(event)

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        self.setCursor(Qt.CursorShape.OpenHandCursor)
        super().mouseReleaseEvent(event)

    def mouseDoubleClickEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.begin_rename(event)
            event.accept()
        else:
            super().mouseDoubleClickEvent(event)


# ---------------------------------------------------------------------------
# Full panel
# ---------------------------------------------------------------------------


class ShelfPanel(QWidget):
    def __init__(self, store: FileStore, settings: QSettings) -> None:
        super().__init__()
        self.store = store
        self.settings = settings
        self.anchor_compact: QWidget | None = None
        self.drag_pos: QPoint | None = None
        self.drop_active = False
        self.stay_on_top = settings_bool(self.settings, SETTING_STAY_ON_TOP, True)
        self.clear_after_copy = settings_bool(self.settings, SETTING_CLEAR_AFTER_COPY, True)

        self.setWindowTitle(APP_NAME)
        self.setWindowFlags(base_window_flags(self.stay_on_top))
        self.setAttribute(Qt.WidgetAttribute.WA_ShowWithoutActivating, True)
        self.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.setAcceptDrops(True)
        self.setFixedSize(PANEL_W, PANEL_H)

        outer = QVBoxLayout(self)
        outer.setContentsMargins(8, 8, 8, 8)

        self.root = QFrame()
        self.root.setObjectName("panelRoot")
        outer.addWidget(self.root)

        root_layout = QVBoxLayout(self.root)
        root_layout.setContentsMargins(10, 9, 10, 10)
        root_layout.setSpacing(6)

        self.drag_header = QFrame()
        self.drag_header.setFixedHeight(24)
        self.drag_header.setCursor(Qt.CursorShape.OpenHandCursor)

        drag_header_layout = QVBoxLayout(self.drag_header)
        drag_header_layout.setContentsMargins(0, 0, 0, 0)
        drag_header_layout.setSpacing(0)

        header = QHBoxLayout()
        header.setContentsMargins(0, 0, 0, 0)
        header.setSpacing(5)
        title = QLabel("ShakeShelf")
        title.setObjectName("appTitle")
        title.setCursor(Qt.CursorShape.OpenHandCursor)

        self.count_badge = QLabel("0")
        self.count_badge.setObjectName("countBadge")
        self.count_badge.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.count_badge.setFixedHeight(19)
        self.count_badge.setCursor(Qt.CursorShape.OpenHandCursor)

        compact_btn = button("−", "ghostButton")
        compact_btn.setFixedWidth(28)
        compact_btn.setToolTip("Collapse")
        compact_btn.clicked.connect(self.collapse_to_compact)

        header.addWidget(title, 1)
        header.addWidget(self.count_badge)
        header.addWidget(compact_btn)
        drag_header_layout.addLayout(header)
        root_layout.addWidget(self.drag_header)

        self.drag_targets = (self.drag_header, title, self.count_badge)
        for target in self.drag_targets:
            target.installEventFilter(self)

        self.scroll = QScrollArea()
        self.scroll.setObjectName("itemScroll")
        self.scroll.setFrameShape(QFrame.Shape.NoFrame)
        self.scroll.setWidgetResizable(True)
        self.scroll.viewport().setAutoFillBackground(False)
        self.scroll.viewport().setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.rows_host = QWidget()
        self.rows_host.setObjectName("rowsHost")
        self.rows_host.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.rows_layout = QVBoxLayout(self.rows_host)
        self.rows_layout.setContentsMargins(0, 0, 0, 0)
        self.rows_layout.setSpacing(4)
        self.rows_layout.addStretch(1)
        self.scroll.setWidget(self.rows_host)
        root_layout.addWidget(self.scroll, 1)

        footer = QHBoxLayout()
        shot_btn = button("Shot", "secondaryButton")
        shot_btn.clicked.connect(self.start_screenshot)
        paste_btn = button("Paste", "secondaryButton")
        paste_btn.clicked.connect(self.add_from_clipboard)
        self.copy_btn = button("Copy", "primaryButton")
        self.copy_btn.clicked.connect(self.copy_files)
        drag_all_btn = button("Drag", "secondaryButton")
        drag_all_btn.clicked.connect(lambda: self.start_drag(self.store.items))
        clear_btn = button("Clear", "secondaryButton")
        clear_btn.clicked.connect(self.store.clear)
        footer.addWidget(shot_btn)
        footer.addWidget(paste_btn)
        footer.addWidget(self.copy_btn, 1)
        footer.addWidget(drag_all_btn)
        footer.addWidget(clear_btn)
        root_layout.addLayout(footer)

        self.store.subscribe(self.refresh)
        self.refresh()

    def show_near(self, compact: QWidget | None = None) -> None:
        self.anchor_compact = compact
        if compact is not None:
            screen = QApplication.screenAt(compact.frameGeometry().center()) or QApplication.primaryScreen()
            screen_geo = screen.availableGeometry()
            target = compact.geometry()
            x = min(max(target.left(), screen_geo.left() + 8), screen_geo.right() - self.width() - 8)
            y = min(max(target.top(), screen_geo.top() + 8), screen_geo.bottom() - self.height() - 8)
            self.move(x, y)
        else:
            self.center_on_screen()
        self.show()
        self.raise_()
        self.activateWindow()
        if compact is not None:
            compact.hide()

    def collapse_to_compact(self) -> None:
        panel_pos = self.pos()
        self.hide()
        if self.anchor_compact is not None:
            screen = QApplication.screenAt(panel_pos) or QApplication.primaryScreen()
            geo = screen.availableGeometry()
            x = min(max(panel_pos.x(), geo.left() + 4), geo.right() - self.anchor_compact.width() - 4)
            y = min(max(panel_pos.y(), geo.top() + 4), geo.bottom() - self.anchor_compact.height() - 4)
            self.anchor_compact.move(x, y)
            if hasattr(self.anchor_compact, "settings"):
                self.anchor_compact.settings.setValue("compact_pos", self.anchor_compact.pos())
            self.anchor_compact.show()
            self.anchor_compact.raise_()

    def center_on_screen(self) -> None:
        geo = QApplication.primaryScreen().availableGeometry()
        self.move(geo.center().x() - self.width() // 2, geo.center().y() - self.height() // 2)

    def set_stay_on_top(self, enabled: bool) -> None:
        if self.stay_on_top == enabled:
            return
        self.stay_on_top = enabled
        apply_window_flags(self, enabled)

    def set_clear_after_copy(self, enabled: bool) -> None:
        self.clear_after_copy = enabled
        self.settings.setValue(SETTING_CLEAR_AFTER_COPY, enabled)
        self.settings.sync()

    def refresh(self) -> None:
        while self.rows_layout.count() > 0:
            item = self.rows_layout.takeAt(0)
            widget = item.widget()
            if widget is not None:
                widget.deleteLater()
        if not self.store.items:
            empty = QFrame()
            empty.setObjectName("emptyState")
            empty.setMinimumHeight(58)
            layout = QHBoxLayout(empty)
            layout.setContentsMargins(12, 8, 12, 8)
            layout.setSpacing(6)
            orb = QLabel("+")
            orb.setObjectName("emptyOrb")
            orb.setAlignment(Qt.AlignmentFlag.AlignCenter)
            orb.setFixedSize(24, 24)
            h = QLabel("Empty")
            h.setObjectName("muted")
            h.setAlignment(Qt.AlignmentFlag.AlignVCenter)
            layout.addStretch(1)
            layout.addWidget(orb)
            layout.addWidget(h)
            layout.addStretch(1)
            self.rows_layout.addWidget(empty)
        else:
            for item in self.store.items:
                self.rows_layout.addWidget(FileRow(self.store, item, self.start_drag))
        self.rows_layout.addStretch(1)
        count = self.store.count()
        self.count_badge.setText(str(count))
        self.copy_btn.setEnabled(count > 0)

    def copy_files(self) -> None:
        if not self.store.items:
            return
        self.clear_after_copy = settings_bool(self.settings, SETTING_CLEAR_AFTER_COPY, self.clear_after_copy)
        copied = copy_store_to_clipboard(self.store, self.clear_after_copy)
        if copied and not self.clear_after_copy:
            self.refresh()

    def add_from_clipboard(self) -> None:
        add_clipboard_to_store(self.store)

    def start_screenshot(self) -> None:
        anchor = self.anchor_compact

        def restore() -> None:
            self.show_near(anchor)

        self.hide()
        if anchor is not None:
            anchor.hide()
        QTimer.singleShot(120, lambda: start_screenshot_selection(self.store, restore))

    def start_drag(self, items: list[ShelfItem]) -> None:
        if not items:
            return
        drag = QDrag(self)
        drag.setMimeData(mime_for_items(items))
        drag.exec(Qt.DropAction.CopyAction)

    def dragEnterEvent(self, event) -> None:
        if can_accept_mime(event):
            event.acceptProposedAction()
            self.set_drop_active(True)
        else:
            event.ignore()

    def dragMoveEvent(self, event) -> None:
        if can_accept_mime(event):
            event.acceptProposedAction()
        else:
            event.ignore()

    def dragLeaveEvent(self, event) -> None:
        self.set_drop_active(False)
        super().dragLeaveEvent(event)

    def dropEvent(self, event) -> None:
        if add_mime_to_store(self.store, event):
            event.acceptProposedAction()
        else:
            event.ignore()
        self.set_drop_active(False)

    def set_drop_active(self, active: bool) -> None:
        if self.drop_active == active:
            return
        self.drop_active = active

    def begin_window_drag(self, event: QMouseEvent) -> None:
        self.drag_pos = event.globalPosition().toPoint() - self.frameGeometry().topLeft()
        for target in self.drag_targets:
            target.setCursor(Qt.CursorShape.ClosedHandCursor)
        event.accept()

    def continue_window_drag(self, event: QMouseEvent) -> None:
        if event.buttons() & Qt.MouseButton.LeftButton and self.drag_pos is not None:
            self.move(event.globalPosition().toPoint() - self.drag_pos)
            event.accept()

    def end_window_drag(self, event: QMouseEvent) -> None:
        self.drag_pos = None
        for target in self.drag_targets:
            target.setCursor(Qt.CursorShape.OpenHandCursor)
        event.accept()

    def eventFilter(self, obj, event) -> bool:
        if obj in getattr(self, "drag_targets", ()):
            if event.type() == QEvent.Type.MouseButtonPress and event.button() == Qt.MouseButton.LeftButton:
                self.begin_window_drag(event)
                return True
            if event.type() == QEvent.Type.MouseMove:
                self.continue_window_drag(event)
                return self.drag_pos is not None
            if event.type() == QEvent.Type.MouseButtonRelease and self.drag_pos is not None:
                self.end_window_drag(event)
                return True
        return super().eventFilter(obj, event)

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton and event.position().y() < 38:
            self.begin_window_drag(event)
        else:
            super().mousePressEvent(event)

    def mouseDoubleClickEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.collapse_to_compact()
            event.accept()
        else:
            super().mouseDoubleClickEvent(event)

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if self.drag_pos is not None:
            self.continue_window_drag(event)
        else:
            super().mouseMoveEvent(event)

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        if self.drag_pos is not None:
            self.end_window_drag(event)
        super().mouseReleaseEvent(event)

# ---------------------------------------------------------------------------
# Compact always-on-top square
# ---------------------------------------------------------------------------


class CompactShelf(QWidget):
    def __init__(self, store: FileStore, settings: QSettings, panel: ShelfPanel) -> None:
        super().__init__()
        self.store = store
        self.settings = settings
        self.panel = panel
        self.drag_pos: QPoint | None = None
        self.drag_start: QPoint | None = None
        self.hovered = False
        self.drop_active = False
        self.moved_as_window = False
        self.file_drag_started = False
        self.suppress_release_toggle = False
        self.stay_on_top = settings_bool(self.settings, SETTING_STAY_ON_TOP, True)
        self.clear_after_copy = settings_bool(self.settings, SETTING_CLEAR_AFTER_COPY, True)

        self.setWindowTitle(APP_NAME)
        self.setWindowFlags(base_window_flags(self.stay_on_top))
        self.setAttribute(Qt.WidgetAttribute.WA_ShowWithoutActivating, True)
        self.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.setAcceptDrops(True)
        self.setFixedSize(COMPACT_SIZE, COMPACT_SIZE)
        self.setCursor(Qt.CursorShape.OpenHandCursor)

        self.store.subscribe(self.update)
        self.restore_position()
        self.native_hotkey = NativeFrontHotkey(self)
        self.shake_watcher = ShakeToShowWatcher(self)

    def showEvent(self, event) -> None:
        self.shake_watcher.start()
        super().showEvent(event)

    def hideEvent(self, event) -> None:
        self.shake_watcher.start()
        super().hideEvent(event)

    def restore_position(self) -> None:
        geo = QApplication.primaryScreen().availableGeometry()
        saved = self.settings.value("compact_pos")
        if isinstance(saved, QPoint):
            p = saved
        else:
            p = QPoint(geo.right() - COMPACT_SIZE - 24, geo.bottom() - COMPACT_SIZE - 24)
        x = min(max(p.x(), geo.left() + 4), geo.right() - COMPACT_SIZE - 4)
        y = min(max(p.y(), geo.top() + 4), geo.bottom() - COMPACT_SIZE - 4)
        self.move(x, y)

    def move_near_cursor(self, cursor_pos: QPoint) -> None:
        screen = QApplication.screenAt(cursor_pos) or QApplication.primaryScreen()
        geo = screen.availableGeometry()
        x = cursor_pos.x() + 22
        y = cursor_pos.y() - self.height() - 18
        x = min(max(x, geo.left() + 6), geo.right() - self.width() - 6)
        y = min(max(y, geo.top() + 6), geo.bottom() - self.height() - 6)
        self.move(x, y)
        self.settings.setValue("compact_pos", self.pos())

    def show_from_shake(self, cursor_pos: QPoint | None = None) -> None:
        if not self.stay_on_top:
            self.set_stay_on_top(True)
        self.move_near_cursor(cursor_pos or QCursor.pos())
        self.show()
        self.raise_()
        QTimer.singleShot(80, self.raise_)

    def bring_to_front_once(self) -> None:
        if not self.stay_on_top:
            self.set_stay_on_top(True)
        if self.panel.isVisible():
            self.panel.raise_()
            self.panel.activateWindow()
            QTimer.singleShot(30, self.panel.raise_)
            QTimer.singleShot(120, self.panel.raise_)
        else:
            self.show()
            self.raise_()
            self.activateWindow()
            QTimer.singleShot(30, self.raise_)
            QTimer.singleShot(120, self.raise_)

    def restore_normal_stack(self) -> None:
        if self.stay_on_top:
            return
        apply_window_flags(self, False)
        if self.panel.isVisible() and not self.panel.stay_on_top:
            apply_window_flags(self.panel, False)
        self.raise_()

    def set_stay_on_top(self, enabled: bool) -> None:
        if self.stay_on_top == enabled:
            return
        self.stay_on_top = enabled
        self.settings.setValue(SETTING_STAY_ON_TOP, enabled)
        self.settings.sync()
        apply_window_flags(self, enabled)
        self.panel.set_stay_on_top(enabled)

    def set_clear_after_copy(self, enabled: bool) -> None:
        self.clear_after_copy = enabled
        self.settings.setValue(SETTING_CLEAR_AFTER_COPY, enabled)
        self.settings.sync()
        self.panel.set_clear_after_copy(enabled)

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)

        count = self.store.count()
        outer = QRect(4, 4, self.width() - 8, self.height() - 8)
        inner = outer.adjusted(1, 1, -1, -1)

        # Liquid glass base: painted manually, no heavy blur/shadow effect.
        if self.drop_active:
            border = QColor(59, 130, 246, 190)
            text = QColor("#0f172a")
            grad_top = QColor(255, 255, 255, 238)
            grad_mid = QColor(219, 239, 255, 224)
            grad_bottom = QColor(180, 215, 255, 206)
        elif count > 0:
            border = QColor(96, 165, 250, 132) if self.hovered else QColor(148, 163, 184, 86)
            text = QColor("#0f172a")
            grad_top = QColor(255, 255, 255, 224)
            grad_mid = QColor(239, 248, 255, 196)
            grad_bottom = QColor(225, 238, 255, 176)
        else:
            border = QColor(203, 213, 225, 102) if self.hovered else QColor(226, 232, 240, 70)
            text = QColor(71, 85, 105, 178)
            grad_top = QColor(255, 255, 255, 202)
            grad_mid = QColor(250, 253, 255, 176)
            grad_bottom = QColor(236, 246, 255, 150)

        grad = QLinearGradient(outer.topLeft(), outer.bottomRight())
        grad.setColorAt(0.0, grad_top)
        grad.setColorAt(0.52, grad_mid)
        grad.setColorAt(1.0, grad_bottom)
        painter.setBrush(grad)
        painter.setPen(QPen(border, 1.0))
        painter.drawRoundedRect(outer, 26, 26)

        painter.setPen(QPen(QColor(255, 255, 255, 142), 1.0))
        painter.drawRoundedRect(inner.adjusted(1, 1, -1, -1), 24, 24)

        if count == 0:
            painter.setPen(text)
            font = painter.font()
            font.setPointSize(29)
            font.setWeight(QFont.Weight.Light)
            painter.setFont(font)
            painter.drawText(QRect(0, 27, self.width(), 34), Qt.AlignmentFlag.AlignCenter, "+")
        else:
            painter.setPen(text)
            font = painter.font()
            font.setPointSize(28)
            font.setWeight(QFont.Weight.Medium)
            painter.setFont(font)
            painter.drawText(QRect(0, 29, self.width(), 32), Qt.AlignmentFlag.AlignCenter, str(count))

        painter.end()

    def enterEvent(self, event) -> None:
        self.hovered = True
        self.update()
        super().enterEvent(event)

    def leaveEvent(self, event) -> None:
        self.hovered = False
        self.update()
        super().leaveEvent(event)

    def mouseDoubleClickEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.suppress_release_toggle = True
            event.accept()
        else:
            super().mouseDoubleClickEvent(event)

    def toggle_panel(self) -> None:
        if self.panel.isVisible():
            self.panel.collapse_to_compact()
        else:
            self.panel.show_near(self)

    def start_screenshot(self) -> None:
        def restore() -> None:
            self.show()
            self.raise_()

        self.hide()
        if self.panel.isVisible():
            self.panel.hide()
        QTimer.singleShot(120, lambda: start_screenshot_selection(self.store, restore))

    def mousePressEvent(self, event: QMouseEvent) -> None:
        if event.button() == Qt.MouseButton.LeftButton:
            self.drag_start = event.globalPosition().toPoint()
            self.drag_pos = event.globalPosition().toPoint() - self.frameGeometry().topLeft()
            self.moved_as_window = False
            self.file_drag_started = False
            self.setCursor(Qt.CursorShape.ClosedHandCursor)
            event.accept()
        elif event.button() == Qt.MouseButton.RightButton:
            self.show_menu(event.globalPosition().toPoint())
            event.accept()
        else:
            super().mousePressEvent(event)

    def mouseMoveEvent(self, event: QMouseEvent) -> None:
        if not (event.buttons() & Qt.MouseButton.LeftButton) or self.drag_start is None or self.drag_pos is None:
            super().mouseMoveEvent(event)
            return

        global_pos = event.globalPosition().toPoint()
        distance = (global_pos - self.drag_start).manhattanLength()
        if distance < QApplication.startDragDistance():
            return

        local_y = event.position().y()
        if self.store.count() > 0 and local_y > self.height() * 0.42 and not self.moved_as_window:
            self.start_drag_all()
            self.file_drag_started = True
            self.drag_start = None
            self.drag_pos = None
            return

        self.move(global_pos - self.drag_pos)
        self.settings.setValue("compact_pos", self.pos())
        self.moved_as_window = True
        event.accept()

    def mouseReleaseEvent(self, event: QMouseEvent) -> None:
        should_toggle = (
            event.button() == Qt.MouseButton.LeftButton
            and self.drag_start is not None
            and not self.moved_as_window
            and not self.file_drag_started
            and not self.suppress_release_toggle
            and (event.globalPosition().toPoint() - self.drag_start).manhattanLength() < QApplication.startDragDistance()
        )
        self.suppress_release_toggle = False
        self.drag_start = None
        self.drag_pos = None
        self.setCursor(Qt.CursorShape.OpenHandCursor)
        if should_toggle:
            self.toggle_panel()
            event.accept()
        else:
            super().mouseReleaseEvent(event)

    def contextMenuEvent(self, event) -> None:
        self.show_menu(event.globalPos())

    def show_menu(self, pos: QPoint) -> None:
        menu = QMenu(self)
        show_action = QAction("Open shelf", self)
        show_action.triggered.connect(self.toggle_panel)
        screenshot_action = QAction("Screenshot", self)
        screenshot_action.triggered.connect(self.start_screenshot)
        add_clipboard_action = QAction("Add from clipboard", self)
        add_clipboard_action.triggered.connect(self.add_from_clipboard)
        stay_action = QAction("Stay on top", self)
        stay_action.setCheckable(True)
        stay_action.setChecked(self.stay_on_top)
        stay_action.triggered.connect(self.set_stay_on_top)
        clear_after_action = QAction("Clear after copy", self)
        clear_after_action.setCheckable(True)
        clear_after_action.setChecked(self.clear_after_copy)
        clear_after_action.triggered.connect(self.set_clear_after_copy)
        copy_action = QAction("Copy", self)
        copy_action.setEnabled(self.store.count() > 0)
        copy_action.triggered.connect(self.copy_files)
        drag_action = QAction("Drag all", self)
        drag_action.setEnabled(self.store.count() > 0)
        drag_action.triggered.connect(self.start_drag_all)
        clear_action = QAction("Clear", self)
        clear_action.setEnabled(self.store.count() > 0)
        clear_action.triggered.connect(self.store.clear)
        hide_action = QAction("Hide square", self)
        hide_action.triggered.connect(self.hide)
        quit_action = QAction("Quit", self)
        quit_action.triggered.connect(QApplication.quit)
        menu.addAction(show_action)
        menu.addAction(screenshot_action)
        menu.addAction(add_clipboard_action)
        menu.addAction(stay_action)
        menu.addAction(clear_after_action)
        menu.addSeparator()
        menu.addAction(copy_action)
        menu.addAction(drag_action)
        menu.addAction(clear_action)
        menu.addSeparator()
        menu.addAction(hide_action)
        menu.addAction(quit_action)
        menu.exec(pos)

    def copy_files(self) -> None:
        if not self.store.items:
            return
        self.clear_after_copy = settings_bool(self.settings, SETTING_CLEAR_AFTER_COPY, self.clear_after_copy)
        copied = copy_store_to_clipboard(self.store, self.clear_after_copy)
        if copied and not self.clear_after_copy:
            self.update()

    def add_from_clipboard(self) -> None:
        add_clipboard_to_store(self.store)

    def start_drag_all(self) -> None:
        if not self.store.items:
            return
        drag = QDrag(self)
        drag.setMimeData(mime_for_items(self.store.items))
        drag.exec(Qt.DropAction.CopyAction)

    def dragEnterEvent(self, event) -> None:
        if can_accept_mime(event):
            self.raise_()
            self.drop_active = True
            self.update()
            event.acceptProposedAction()
        else:
            event.ignore()

    def dragMoveEvent(self, event) -> None:
        if can_accept_mime(event):
            event.acceptProposedAction()
        else:
            event.ignore()

    def dragLeaveEvent(self, event) -> None:
        self.drop_active = False
        self.update()
        super().dragLeaveEvent(event)

    def dropEvent(self, event) -> None:
        if add_mime_to_store(self.store, event):
            event.acceptProposedAction()
            # Give immediate feedback without opening a big panel over the user.
            self.update()
        else:
            event.ignore()
        self.drop_active = False
        self.update()


# ---------------------------------------------------------------------------
# Tray controller
# ---------------------------------------------------------------------------


class TrayController:
    def __init__(self, compact: CompactShelf, panel: ShelfPanel, store: FileStore) -> None:
        from PySide6.QtWidgets import QSystemTrayIcon

        self.compact = compact
        self.panel = panel
        self.store = store
        self.tray_cls = QSystemTrayIcon
        self.tray = None
        if not QSystemTrayIcon.isSystemTrayAvailable():
            return
        self.tray = QSystemTrayIcon(make_icon(), QApplication.instance())
        self.tray.setToolTip(APP_NAME)
        menu = QMenu()
        show_square = QAction("Show square", menu)
        show_square.triggered.connect(self.show_square)
        show_panel = QAction("Open shelf", menu)
        show_panel.triggered.connect(lambda: self.panel.show_near(self.compact))
        add_clipboard_action = QAction("Add from clipboard", menu)
        add_clipboard_action.triggered.connect(self.add_from_clipboard)
        stay_action = QAction("Stay on top", menu)
        stay_action.setCheckable(True)
        stay_action.setChecked(self.compact.stay_on_top)
        stay_action.triggered.connect(self.compact.set_stay_on_top)
        clear_after_action = QAction("Clear after copy", menu)
        clear_after_action.setCheckable(True)
        clear_after_action.setChecked(self.compact.clear_after_copy)
        clear_after_action.triggered.connect(self.compact.set_clear_after_copy)
        copy_action = QAction("Copy", menu)
        copy_action.triggered.connect(self.copy_files)
        clear_action = QAction("Clear", menu)
        clear_action.triggered.connect(self.store.clear)
        quit_action = QAction("Quit", menu)
        quit_action.triggered.connect(QApplication.quit)
        menu.addAction(show_square)
        menu.addAction(show_panel)
        menu.addAction(add_clipboard_action)
        menu.addAction(stay_action)
        menu.addAction(clear_after_action)
        menu.addSeparator()
        menu.addAction(copy_action)
        menu.addAction(clear_action)
        menu.addSeparator()
        menu.addAction(quit_action)
        self.tray.setContextMenu(menu)
        self.tray.activated.connect(self.on_activated)
        self.tray.show()

    def show_square(self) -> None:
        self.compact.show()
        self.compact.raise_()

    def on_activated(self, reason) -> None:
        if reason in (self.tray_cls.ActivationReason.Trigger, self.tray_cls.ActivationReason.DoubleClick):
            self.show_square()
            self.panel.show_near(self.compact)

    def copy_files(self) -> None:
        if not self.store.items:
            return
        self.compact.clear_after_copy = settings_bool(
            self.compact.settings,
            SETTING_CLEAR_AFTER_COPY,
            self.compact.clear_after_copy,
        )
        copy_store_to_clipboard(self.store, self.compact.clear_after_copy)

    def add_from_clipboard(self) -> None:
        add_clipboard_to_store(self.store)
