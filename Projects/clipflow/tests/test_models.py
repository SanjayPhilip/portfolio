"""Tests for ClipFlow database models."""

import pytest
from datetime import datetime

from clipflow.core.models import DatabaseManager, ClipboardItem


@pytest.fixture
def db(tmp_path):
    """Create a temporary database for testing."""
    db_path = tmp_path / "test.db"
    return DatabaseManager(str(db_path))


class TestClipboardItem:
    """Tests for the ClipboardItem model."""

    def test_create_item(self, db):
        """Test creating a clipboard item."""
        item = db.add_item("Hello, World!")
        assert item.content == "Hello, World!"
        assert item.content_type == "text"
        assert item.copy_count == 1
        assert not item.is_pinned

    def test_duplicate_detection(self, db):
        """Test that duplicate content increments copy count."""
        item1 = db.add_item("Duplicate text")
        item2 = db.add_item("Duplicate text")
        assert item1.id == item2.id
        assert item2.copy_count == 2

    def test_get_recent_items(self, db):
        """Test retrieving recent items."""
        db.add_item("Item 1")
        db.add_item("Item 2")
        db.add_item("Item 3")

        items = db.get_recent_items(limit=2)
        assert len(items) == 2
        assert items[0].content == "Item 3"

    def test_search_items(self, db):
        """Test searching items by content."""
        db.add_item("Python is great")
        db.add_item("JavaScript is okay")
        db.add_item("Python rocks")

        results = db.search_items("Python")
        assert len(results) == 2

    def test_toggle_pin(self, db):
        """Test pinning and unpinning items."""
        item = db.add_item("Important")
        assert not item.is_pinned

        pinned = db.toggle_pin(item.id)
        assert pinned is True

        unpinned = db.toggle_pin(item.id)
        assert unpinned is False

    def test_delete_item(self, db):
        """Test deleting an item."""
        item = db.add_item("To be deleted")
        assert db.delete_item(item.id) is True

        items = db.get_recent_items()
        assert len(items) == 0

    def test_delete_nonexistent(self, db):
        """Test deleting a non-existent item."""
        assert db.delete_item(999) is False

    def test_get_stats(self, db):
        """Test getting database statistics."""
        db.add_item("Item 1")
        db.add_item("Item 2")
        item3 = db.add_item("Item 3")
        db.toggle_pin(item3.id)

        stats = db.get_stats()
        assert stats["total_items"] == 3
        assert stats["pinned_items"] == 1
        assert stats["favorite_items"] == 0

    def test_to_dict(self, db):
        """Test item serialization."""
        item = db.add_item("Test content")
        data = item.to_dict()
        assert data["content"] == "Test content"
        assert "id" in data
        assert "created_at" in data
