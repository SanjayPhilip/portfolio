"""Entry point for ClipFlow application."""

import sys

from PyQt6.QtWidgets import QApplication
from PyQt6.QtCore import Qt

from clipflow.ui.main_window import MainWindow


def main() -> int:
    """Main application entry point."""
    # Enable high DPI scaling
    QApplication.setHighDpiScaleFactorRoundingPolicy(
        Qt.HighDpiScaleFactorRoundingPolicy.PassThrough
    )

    app = QApplication(sys.argv)
    app.setApplicationName("ClipFlow")
    app.setApplicationVersion("0.1.0")
    app.setOrganizationName("clipflow")

    window = MainWindow()
    window.show()

    return app.exec()


if __name__ == "__main__":
    sys.exit(main())
