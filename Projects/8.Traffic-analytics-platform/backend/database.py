"""
Async SQLite Database for Traffic Analytics
"""

from datetime import datetime
from sqlalchemy import Column, Integer, String, Float, DateTime, create_engine
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession, async_sessionmaker
from sqlalchemy.orm import declarative_base

# Database URL
DATABASE_URL = "sqlite+aiosqlite:///./traffic_analytics.db"

# Create async engine
engine = create_async_engine(DATABASE_URL, echo=False)
async_session_maker = async_sessionmaker(engine, class_=AsyncSession, expire_on_commit=False)

Base = declarative_base()


class AnalyticsRecord(Base):
    """Traffic analytics database model"""
    __tablename__ = "analytics_records"

    id = Column(Integer, primary_key=True, index=True)
    video_id = Column(String, unique=True, index=True)
    filename = Column(String)
    cars = Column(Integer, default=0)
    bikes = Column(Integer, default=0)
    buses = Column(Integer, default=0)
    trucks = Column(Integer, default=0)
    total_vehicles = Column(Integer, default=0)
    traffic_density = Column(String, default="low")
    processing_time = Column(Float, default=0.0)
    created_at = Column(DateTime, default=datetime.now)


async def init_db():
    """Initialize database tables"""
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)


async def get_db():
    """Database session dependency"""
    async with async_session_maker() as session:
        try:
            yield session
            await session.commit()
        except Exception:
            await session.rollback()
            raise
        finally:
            await session.close()
