"""Clipboard monitoring service for ClipFlow."""

import threading
import time
from typing import Callable, Optional

import pyperclip
from PyQt6.QtCore import QObject, pyqtSignal

from clipflow.core.models import DatabaseManager


class ClipboardMonitor(QObject):
    """Monitors system clipboard for changes and persists them."""

    # Signals for UI updates
    item_copied = pyqtSignal(str, int)  # content, item_id
    error_occurred = pyqtSignal(str)

    def __init__(self, db_manager: DatabaseManager, poll_interval: float = 0.5):
        super().__init__()
        self.db = db_manager
        self.poll_interval = poll_interval
        self._last_content: Optional[str] = None
        self._running = False
        self._thread: Optional[threading.Thread] = None
        self._lock = threading.Lock()

    def start(self) -> None:
        """Start monitoring the clipboard in a background thread."""
        if self._running:
            return

        self._running = True
        self._thread = threading.Thread(target=self._monitor_loop, daemon=True)
        self._thread.start()
        print("[ClipboardMonitor] Started monitoring clipboard")

    def stop(self) -> None:
        """Stop monitoring the clipboard."""
        self._running = False
        if self._thread and self._thread.is_alive():
            self._thread.join(timeout=2.0)
        print("[ClipboardMonitor] Stopped monitoring clipboard")

    def _monitor_loop(self) -> None:
        """Main monitoring loop that runs in background thread."""
        # Initialize with current clipboard content to avoid immediate trigger
        try:
            self._last_content = pyperclip.paste()
        except Exception:
            self._last_content = ""

        while self._running:
            try:
                current = pyperclip.paste()

                with self._lock:
                    if current != self._last_content and current.strip():
                        self._last_content = current
                        item = self.db.add_item(current)
                        self.item_copied.emit(current, item.id)

            except Exception as e:
                self.error_occurred.emit(str(e))

            time.sleep(self.poll_interval)

    def get_current(self) -> str:
        """Get current clipboard content."""
        return pyperclip.paste()

    def set_clipboard(self, content: str) -> None:
        """Set clipboard content and update internal tracking."""
        with self._lock:
            self._last_content = content
        pyperclip.copy(content)
