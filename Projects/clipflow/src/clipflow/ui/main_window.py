"""Main application window for ClipFlow."""

from PyQt6.QtWidgets import (
    QMainWindow,
    QWidget,
    QVBoxLayout,
    QHBoxLayout,
    QLineEdit,
    QListWidget,
    QListWidgetItem,
    QPushButton,
    QLabel,
    QSystemTrayIcon,
    QMenu,
    QApplication,
    QMessageBox,
)
from PyQt6.QtCore import Qt, QTimer
from PyQt6.QtGui import QIcon, QAction, QFont, QKeySequence, QShortcut

from clipflow.core.models import DatabaseManager, ClipboardItem
from clipflow.core.monitor import ClipboardMonitor


class HistoryItemWidget(QWidget):
    """Custom widget for displaying a clipboard history item."""

    def __init__(self, item: ClipboardItem, parent=None):
        super().__init__(parent)
        self.item_id = item.id
        self.is_pinned = item.is_pinned

        layout = QHBoxLayout(self)
        layout.setContentsMargins(10, 5, 10, 5)
        layout.setSpacing(10)

        # Pin indicator
        self.pin_label = QLabel("📌" if item.is_pinned else "")
        self.pin_label.setFixedWidth(20)
        layout.addWidget(self.pin_label)

        # Content preview
        preview = item.content.replace("\n", " ")[:80]
        if len(item.content) > 80:
            preview += "..."
        self.content_label = QLabel(preview)
        self.content_label.setFont(QFont("Segoe UI", 10))
        self.content_label.setWordWrap(True)
        layout.addWidget(self.content_label, stretch=1)

        # Copy count badge
        if item.copy_count > 1:
            count_label = QLabel(f"x{item.copy_count}")
            count_label.setStyleSheet("color: #888; font-size: 10px;")
            layout.addWidget(count_label)

        self.setStyleSheet("""
            HistoryItemWidget {
                background-color: #2d2d2d;
                border-radius: 6px;
                margin: 2px 0px;
            }
            HistoryItemWidget:hover {
                background-color: #3d3d3d;
            }
        """)


class MainWindow(QMainWindow):
    """Main application window for ClipFlow clipboard manager."""

    def __init__(self):
        super().__init__()
        self.setWindowTitle("ClipFlow")
        self.setMinimumSize(600, 500)

        # Initialize core components
        self.db = DatabaseManager()
        self.monitor = ClipboardMonitor(self.db)
        self.monitor.item_copied.connect(self._on_item_copied)
        self.monitor.error_occurred.connect(self._on_error)

        self._setup_ui()
        self._setup_tray()
        self._setup_shortcuts()
        self._load_history()

        # Start monitoring
        self.monitor.start()

        # Auto-refresh timer
        self.refresh_timer = QTimer(self)
        self.refresh_timer.timeout.connect(self._load_history)
        self.refresh_timer.start(2000)  # Refresh every 2 seconds

    def _setup_ui(self) -> None:
        """Set up the main user interface."""
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        layout = QVBoxLayout(central_widget)
        layout.setSpacing(10)
        layout.setContentsMargins(15, 15, 15, 15)

        # Search bar
        search_layout = QHBoxLayout()
        self.search_input = QLineEdit()
        self.search_input.setPlaceholderText("🔍 Search clipboard history...")
        self.search_input.setStyleSheet("""
            QLineEdit {
                padding: 10px;
                border: 2px solid #3d3d3d;
                border-radius: 8px;
                font-size: 13px;
                background-color: #2d2d2d;
                color: #ffffff;
            }
            QLineEdit:focus {
                border-color: #0078d4;
            }
        """)
        self.search_input.textChanged.connect(self._on_search)
        search_layout.addWidget(self.search_input)

        # Clear search button
        clear_btn = QPushButton("✕")
        clear_btn.setFixedSize(30, 30)
        clear_btn.setStyleSheet("border: none; font-size: 14px; color: #888;")
        clear_btn.clicked.connect(self.search_input.clear)
        search_layout.addWidget(clear_btn)
        layout.addLayout(search_layout)

        # Stats label
        self.stats_label = QLabel("Loading...")
        self.stats_label.setStyleSheet("color: #888; font-size: 11px; padding-left: 5px;")
        layout.addWidget(self.stats_label)

        # History list
        self.history_list = QListWidget()
        self.history_list.setSpacing(3)
        self.history_list.setStyleSheet("""
            QListWidget {
                border: none;
                background-color: transparent;
                outline: none;
            }
            QListWidget::item {
                border: none;
                padding: 0px;
            }
            QListWidget::item:selected {
                background-color: transparent;
            }
        """)
        self.history_list.itemClicked.connect(self._on_item_clicked)
        layout.addWidget(self.history_list)

        # Button bar
        btn_layout = QHBoxLayout()

        self.pin_btn = QPushButton("📌 Pin/Unpin")
        self.pin_btn.setStyleSheet(self._button_style("#0078d4"))
        self.pin_btn.clicked.connect(self._toggle_pin)
        btn_layout.addWidget(self.pin_btn)

        self.copy_btn = QPushButton("📋 Copy Selected")
        self.copy_btn.setStyleSheet(self._button_style("#2ea043"))
        self.copy_btn.clicked.connect(self._copy_selected)
        btn_layout.addWidget(self.copy_btn)

        self.delete_btn = QPushButton("🗑️ Delete")
        self.delete_btn.setStyleSheet(self._button_style("#d73a49"))
        self.delete_btn.clicked.connect(self._delete_selected)
        btn_layout.addWidget(self.delete_btn)

        layout.addLayout(btn_layout)

        # Dark theme
        self.setStyleSheet("""
            QMainWindow {
                background-color: #1e1e1e;
            }
            QWidget {
                background-color: #1e1e1e;
                color: #cccccc;
            }
        """)

    def _button_style(self, color: str) -> str:
        """Generate consistent button styling."""
        return f"""
            QPushButton {{
                background-color: {color};
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: bold;
            }}
            QPushButton:hover {{
                opacity: 0.8;
            }}
            QPushButton:pressed {{
                opacity: 0.6;
            }}
        """

    def _setup_tray(self) -> None:
        """Set up system tray icon and menu."""
        if not QSystemTrayIcon.isSystemTrayAvailable():
            return

        self.tray_icon = QSystemTrayIcon(self)
        # Use a simple text-based icon if no image available
        self.tray_icon.setToolTip("ClipFlow - Clipboard Manager")

        tray_menu = QMenu()
        show_action = QAction("Show", self)
        show_action.triggered.connect(self.show)
        tray_menu.addAction(show_action)

        tray_menu.addSeparator()

        quit_action = QAction("Quit", self)
        quit_action.triggered.connect(self._quit_app)
        tray_menu.addAction(quit_action)

        self.tray_icon.setContextMenu(tray_menu)
        self.tray_icon.activated.connect(self._tray_activated)
        self.tray_icon.show()

    def _setup_shortcuts(self) -> None:
        """Set up keyboard shortcuts."""
        # Ctrl+Shift+V to show window
        self.show_shortcut = QShortcut(
            QKeySequence("Ctrl+Shift+V"), self
        )
        self.show_shortcut.activated.connect(self.show_and_raise)

        # Escape to minimize to tray
        self.escape_shortcut = QShortcut(
            QKeySequence("Escape"), self
        )
        self.escape_shortcut.activated.connect(self.hide)

        # Delete key to remove selected item
        self.delete_shortcut = QShortcut(
            QKeySequence("Delete"), self
        )
        self.delete_shortcut.activated.connect(self._delete_selected)

    def _load_history(self) -> None:
        """Load clipboard history from database."""
        search_text = self.search_input.text().strip()

        if search_text:
            items = self.db.search_items(search_text)
        else:
            items = self.db.get_recent_items(limit=100)

        self.history_list.clear()

        for item in items:
            widget = HistoryItemWidget(item)
            list_item = QListWidgetItem()
            list_item.setSizeHint(widget.sizeHint())
            list_item.setData(Qt.ItemDataRole.UserRole, item.id)
            self.history_list.addItem(list_item)
            self.history_list.setItemWidget(list_item, widget)

        # Update stats
        stats = self.db.get_stats()
        self.stats_label.setText(
            f"📊 Total: {stats['total_items']} | 📌 Pinned: {stats['pinned_items']} | ⭐ Favorites: {stats['favorite_items']}"
        )

    def _on_search(self) -> None:
        """Handle search text changes."""
        self._load_history()

    def _on_item_copied(self, content: str, item_id: int) -> None:
        """Handle new clipboard item detected."""
        self._load_history()

    def _on_error(self, error_msg: str) -> None:
        """Handle monitor errors."""
        print(f"[Error] {error_msg}")

    def _on_item_clicked(self, item: QListWidgetItem) -> None:
        """Handle item click in history list."""
        item_id = item.data(Qt.ItemDataRole.UserRole)
        # Could show full content preview here in future

    def _toggle_pin(self) -> None:
        """Toggle pin status of selected item."""
        current = self.history_list.currentItem()
        if not current:
            return

        item_id = current.data(Qt.ItemDataRole.UserRole)
        self.db.toggle_pin(item_id)
        self._load_history()

    def _copy_selected(self) -> None:
        """Copy selected item back to clipboard."""
        current = self.history_list.currentItem()
        if not current:
            return

        item_id = current.data(Qt.ItemDataRole.UserRole)
        items = self.db.search_items("")  # Get all to find by ID
        for item in items:
            if item.id == item_id:
                self.monitor.set_clipboard(item.content)
                self.statusBar().showMessage("Copied to clipboard!", 2000)
                break

    def _delete_selected(self) -> None:
        """Delete selected item from history."""
        current = self.history_list.currentItem()
        if not current:
            return

        item_id = current.data(Qt.ItemDataRole.UserRole)
        self.db.delete_item(item_id)
        self._load_history()

    def _tray_activated(self, reason: QSystemTrayIcon.ActivationReason) -> None:
        """Handle tray icon activation."""
        if reason == QSystemTrayIcon.ActivationReason.DoubleClick:
            self.show_and_raise()

    def show_and_raise(self) -> None:
        """Show and raise the window to foreground."""
        self.show()
        self.raise_()
        self.activateWindow()

    def closeEvent(self, event) -> None:
        """Override close to minimize to tray instead."""
        if hasattr(self, 'tray_icon') and self.tray_icon.isVisible():
            self.hide()
            event.ignore()
        else:
            self._quit_app()

    def _quit_app(self) -> None:
        """Clean shutdown of the application."""
        self.monitor.stop()
        self.refresh_timer.stop()
        QApplication.instance().quit()
