"""Database models for ClipFlow clipboard history."""

from datetime import datetime
from typing import Optional

from sqlalchemy import create_engine, event
from sqlalchemy.orm import DeclarativeBase, Mapped, mapped_column, Session


class Base(DeclarativeBase):
    pass


class ClipboardItem(Base):
    """Represents a single clipboard history entry."""

    __tablename__ = "clipboard_items"

    id: Mapped[int] = mapped_column(primary_key=True)
    content: Mapped[str] = mapped_column(nullable=False)
    content_type: Mapped[str] = mapped_column(default="text", nullable=False)
    created_at: Mapped[datetime] = mapped_column(default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(
        default=datetime.utcnow, onupdate=datetime.utcnow
    )
    is_pinned: Mapped[bool] = mapped_column(default=False)
    is_favorite: Mapped[bool] = mapped_column(default=False)
    tags: Mapped[Optional[str]] = mapped_column(default=None)
    source_application: Mapped[Optional[str]] = mapped_column(default=None)
    copy_count: Mapped[int] = mapped_column(default=1)

    def __repr__(self) -> str:
        return f"<ClipboardItem(id={self.id}, content={self.content[:30]!r}...)>"

    def to_dict(self) -> dict:
        """Convert item to dictionary for serialization."""
        return {
            "id": self.id,
            "content": self.content,
            "content_type": self.content_type,
            "created_at": self.created_at.isoformat(),
            "is_pinned": self.is_pinned,
            "is_favorite": self.is_favorite,
            "tags": self.tags,
            "copy_count": self.copy_count,
        }


class DatabaseManager:
    """Manages database connections and operations."""

    def __init__(self, db_path: str = "clipflow.db"):
        self.engine = create_engine(f"sqlite:///{db_path}")
        Base.metadata.create_all(self.engine)

    def get_session(self) -> Session:
        """Get a new database session."""
        return Session(self.engine)

    def add_item(self, content: str, content_type: str = "text") -> ClipboardItem:
        """Add a new clipboard item to the database."""
        with self.get_session() as session:
            # Check if identical content exists recently
            existing = (
                session.query(ClipboardItem)
                .filter(ClipboardItem.content == content)
                .order_by(ClipboardItem.created_at.desc())
                .first()
            )

            if existing:
                existing.copy_count += 1
                existing.updated_at = datetime.utcnow()
                session.commit()
                return existing

            item = ClipboardItem(content=content, content_type=content_type)
            session.add(item)
            session.commit()
            session.refresh(item)
            return item

    def get_recent_items(self, limit: int = 50, offset: int = 0) -> list[ClipboardItem]:
        """Get recent clipboard items, pinned first."""
        with self.get_session() as session:
            return (
                session.query(ClipboardItem)
                .order_by(ClipboardItem.is_pinned.desc(), ClipboardItem.created_at.desc())
                .limit(limit)
                .offset(offset)
                .all()
            )

    def search_items(self, query: str) -> list[ClipboardItem]:
        """Search clipboard items by content."""
        with self.get_session() as session:
            return (
                session.query(ClipboardItem)
                .filter(ClipboardItem.content.ilike(f"%{query}%"))
                .order_by(ClipboardItem.created_at.desc())
                .all()
            )

    def toggle_pin(self, item_id: int) -> bool:
        """Toggle pin status of an item."""
        with self.get_session() as session:
            item = session.get(ClipboardItem, item_id)
            if item:
                item.is_pinned = not item.is_pinned
                session.commit()
                return item.is_pinned
            return False

    def delete_item(self, item_id: int) -> bool:
        """Delete a clipboard item."""
        with self.get_session() as session:
            item = session.get(ClipboardItem, item_id)
            if item:
                session.delete(item)
                session.commit()
                return True
            return False

    def get_stats(self) -> dict:
        """Get database statistics."""
        with self.get_session() as session:
            total = session.query(ClipboardItem).count()
            pinned = session.query(ClipboardItem).filter(ClipboardItem.is_pinned).count()
            favorites = session.query(ClipboardItem).filter(ClipboardItem.is_favorite).count()
            return {
                "total_items": total,
                "pinned_items": pinned,
                "favorite_items": favorites,
            }
