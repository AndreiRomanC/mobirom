# ShakeShelf

ShakeShelf is a compact always-on-top file shelf. Drop files or folders onto the small square, then copy them to the clipboard or drag them out somewhere else.

The app stores file/folder paths and can temporarily hold clipboard images as PNG files.

## Install

```bash
python -m pip install -r requirements.txt
```

## Run

```bash
python shakeshelf.py
```

## Windows

ShakeShelf is built with PySide6, so it should run on Windows, macOS, and Linux with a compatible Python install. On Windows, install Python, run the install command above, then start it with `python shakeshelf.py`.

## Project files

- `shakeshelf.py` - app launcher and error handling
- `shakeshelf_core.py` - constants, logging, path normalization, clipboard helper, shared store
- `shakeshelf_ui.py` - PySide6 widgets, tray menu, drag/drop UI

## Notes

- The shelf keeps up to 25 items.
- Use `Paste` or `Add from clipboard` after `Cmd+C`/`Ctrl+C` to capture copied files or an image from the clipboard.
- Clipboard images are stored as temporary PNG files and shown with small thumbnails in the shelf.
- `Clear after copy` can be toggled from the right-click/tray menu.
- When `Clear after copy` is on, the shelf clears only after the clipboard copy succeeds.
- Right-click the compact square to toggle `Stay on top` or hide it.
- Use the tray menu to show the square again after hiding it.
- Errors are logged to `~/ShakeShelf.log`.
