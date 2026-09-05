#!/usr/bin/env python3
"""
ShakeShelf
A lightweight always-on-top file shelf with a refined liquid-glass UI.

Install:
    python -m pip install -r requirements.txt

Run:
    python shakeshelf.py

Design goals:
- Always-on-top compact drop square.
- Liquid Apple-style UI: translucent panels, soft highlights, calm spacing.
- Keeps only file/folder paths, never file contents.
- Drag files/folders onto the widget, then copy/paste or drag them out.
- Clears the shelf after copy by default, so it stays light.
"""
from __future__ import annotations

import sys
import traceback
import os

from shakeshelf_core import APP_NAME, LOG_PATH, ORG_NAME, FileStore, fatal_dialog, log

try:
    from PySide6.QtCore import QSettings, QThreadPool, QTimer
    from PySide6.QtGui import QPixmapCache
    from PySide6.QtWidgets import QApplication

    from shakeshelf_ui import APP_STYLESHEET, SETTING_STAY_ON_TOP, CompactShelf, ShelfPanel, TrayController, make_icon
except Exception as exc:
    fatal_dialog(
        "ShakeShelf cannot start",
        "PySide6 is not installed or Qt cannot be imported.\n\n"
        "Run:\npython -m pip install -r requirements.txt\n\n"
        f"Details: {exc}\nLog: {LOG_PATH}",
    )
    raise SystemExit(1)


def excepthook(exc_type, exc, tb) -> None:
    text = "".join(traceback.format_exception(exc_type, exc, tb))
    log(text)
    fatal_dialog("ShakeShelf error", f"An error occurred.\n\n{exc}\n\nLog: {LOG_PATH}")


sys.excepthook = excepthook


def main() -> int:
    log("Starting ShakeShelf")
    app = QApplication(sys.argv)
    app.setApplicationName(APP_NAME)
    app.setOrganizationName(ORG_NAME)
    app.setQuitOnLastWindowClosed(False)
    if hasattr(app, "setQuitLockEnabled"):
        app.setQuitLockEnabled(False)
    app.setWindowIcon(make_icon())
    app.setStyleSheet(APP_STYLESHEET)
    QPixmapCache.setCacheLimit(2048)
    QThreadPool.globalInstance().setMaxThreadCount(1)

    settings = QSettings(ORG_NAME, APP_NAME)
    settings.setValue(SETTING_STAY_ON_TOP, True)
    store = FileStore()
    panel = ShelfPanel(store, settings)
    compact = CompactShelf(store, settings, panel)
    tray = None
    if os.environ.get("SHAKESHELF_ENABLE_TRAY") == "1":
        tray = TrayController(compact, panel, store)
    # Keep a reference alive.
    app._shakeshelf_objects = (store, panel, compact, tray)  # type: ignore[attr-defined]

    def rescue_last_window() -> None:
        log("lastWindowClosed")
        if not panel.isVisible() and not compact.isVisible():
            compact.show()
            compact.raise_()

    app.lastWindowClosed.connect(rescue_last_window)
    app.aboutToQuit.connect(
        lambda: log(
            "aboutToQuit: "
            f"compact_visible={compact.isVisible()} "
            f"panel_visible={panel.isVisible()} "
            f"tray_visible={bool(tray and tray.tray and tray.tray.isVisible())}"
        )
    )

    compact.show()
    compact.raise_()

    # Small delayed raise helps on macOS/Windows where Tool windows can appear
    # behind a focused app for a moment.
    QTimer.singleShot(200, compact.raise_)
    exit_code = app.exec()
    log(f"Exiting ShakeShelf event loop: {exit_code}")
    return exit_code


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except SystemExit:
        raise
    except Exception as exc:
        log(traceback.format_exc())
        fatal_dialog("ShakeShelf cannot start", f"{exc}\n\nLog: {LOG_PATH}")
        raise SystemExit(1)
