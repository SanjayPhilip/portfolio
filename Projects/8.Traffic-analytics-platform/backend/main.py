"""
AI Traffic Analytics Platform - FastAPI Backend
Real-time vehicle detection, counting, and analytics using YOLOv8
"""

import os
import uuid
import json
from datetime import datetime, timedelta
from typing import List, Optional
from pathlib import Path

from fastapi import FastAPI, File, UploadFile, HTTPException, BackgroundTasks, Query
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import FileResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
import uvicorn

from detector import TrafficDetector
from analytics import AnalyticsEngine
from database import init_db, get_db, AnalyticsRecord
from schemas import (
    UploadResponse, 
    AnalyticsResult, 
    TrafficStatus,
    VehicleCount,
    DashboardStats,
    VideoInfo
)

# Configuration
UPLOAD_DIR = Path("uploads")
PROCESSED_DIR = Path("processed")
UPLOAD_DIR.mkdir(exist_ok=True)
PROCESSED_DIR.mkdir(exist_ok=True)

# Initialize FastAPI app
app = FastAPI(
    title="AI Traffic Analytics Platform",
    description="Real-time AI Traffic Analytics using YOLOv8, FastAPI, and Interactive Dashboards",
    version="1.0.0",
    docs_url="/api/docs",
    redoc_url="/api/redoc"
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize components
detector = TrafficDetector()
analytics_engine = AnalyticsEngine()

# Initialize database on startup
@app.on_event("startup")
async def startup_event():
    await init_db()

@app.get("/")
async def root():
    return {
        "message": "AI Traffic Analytics Platform API",
        "version": "1.0.0",
        "docs": "/api/docs"
    }

@app.get("/api/health")
async def health_check():
    return {"status": "healthy", "timestamp": datetime.now().isoformat()}

@app.post("/api/upload", response_model=UploadResponse)
async def upload_video(
    background_tasks: BackgroundTasks,
    file: UploadFile = File(...)
):
    """Upload a traffic video for processing"""

    # Validate file type
    allowed_extensions = {'.mp4', '.avi', '.mov', '.mkv', '.wmv'}
    file_ext = Path(file.filename).suffix.lower()

    if file_ext not in allowed_extensions:
        raise HTTPException(
            status_code=400, 
            detail=f"Invalid file type. Allowed: {', '.join(allowed_extensions)}"
        )

    # Generate unique ID and save path
    video_id = str(uuid.uuid4())
    original_filename = file.filename
    safe_filename = f"{video_id}{file_ext}"
    upload_path = UPLOAD_DIR / safe_filename

    # Save uploaded file
    content = await file.read()
    with open(upload_path, "wb") as f:
        f.write(content)

    # Get video info
    video_info = detector.get_video_info(str(upload_path))

    return UploadResponse(
        video_id=video_id,
        original_filename=original_filename,
        status="uploaded",
        message="Video uploaded successfully. Start processing to analyze traffic.",
        video_info=VideoInfo(**video_info)
    )

@app.post("/api/process/{video_id}", response_model=AnalyticsResult)
async def process_video(video_id: str, background_tasks: BackgroundTasks):
    """Process uploaded video with YOLOv8 vehicle detection"""

    # Find uploaded file
    upload_files = list(UPLOAD_DIR.glob(f"{video_id}.*"))
    if not upload_files:
        raise HTTPException(status_code=404, detail="Video not found. Please upload first.")

    input_path = upload_files[0]
    output_filename = f"processed_{video_id}.mp4"
    output_path = PROCESSED_DIR / output_filename

    # Process video
    try:
        result = detector.process_video(
            input_path=str(input_path),
            output_path=str(output_path)
        )

        # Save analytics to database
        async for db in get_db():
            record = AnalyticsRecord(
                video_id=video_id,
                filename=input_path.name,
                cars=result["vehicle_counts"]["car"],
                bikes=result["vehicle_counts"]["motorcycle"],
                buses=result["vehicle_counts"]["bus"],
                trucks=result["vehicle_counts"]["truck"],
                total_vehicles=result["total_vehicles"],
                traffic_density=result["traffic_density"],
                processing_time=result["processing_time"],
                created_at=datetime.now()
            )
            db.add(record)
            await db.commit()

        return AnalyticsResult(
            video_id=video_id,
            status="completed",
            vehicle_counts=VehicleCount(**result["vehicle_counts"]),
            total_vehicles=result["total_vehicles"],
            traffic_density=result["traffic_density"],
            processing_time=result["processing_time"],
            output_video_url=f"/api/videos/processed/{output_filename}",
            congestion_alert=result.get("congestion_alert", False),
            peak_hour=result.get("peak_hour", False)
        )

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Processing failed: {str(e)}")

@app.get("/api/analytics/{video_id}")
async def get_analytics(video_id: str):
    """Get detailed analytics for a processed video"""

    async for db in get_db():
        from sqlalchemy import select
        result = await db.execute(
            select(AnalyticsRecord).where(AnalyticsRecord.video_id == video_id)
        )
        record = result.scalar_one_or_none()

        if not record:
            raise HTTPException(status_code=404, detail="Analytics not found for this video")

        return {
            "video_id": record.video_id,
            "filename": record.filename,
            "vehicle_counts": {
                "car": record.cars,
                "motorcycle": record.bikes,
                "bus": record.buses,
                "truck": record.trucks
            },
            "total_vehicles": record.total_vehicles,
            "traffic_density": record.traffic_density,
            "processing_time": record.processing_time,
            "created_at": record.created_at.isoformat()
        }

@app.get("/api/dashboard/stats", response_model=DashboardStats)
async def get_dashboard_stats(
    days: int = Query(default=7, ge=1, le=30, description="Number of days to analyze")
):
    """Get dashboard statistics and trends"""

    stats = await analytics_engine.get_dashboard_stats(days)
    return DashboardStats(**stats)

@app.get("/api/videos/processed/{filename}")
async def get_processed_video(filename: str):
    """Stream processed video with annotations"""

    video_path = PROCESSED_DIR / filename
    if not video_path.exists():
        raise HTTPException(status_code=404, detail="Video not found")

    return FileResponse(
        str(video_path),
        media_type="video/mp4",
        filename=filename
    )

@app.get("/api/videos/list")
async def list_videos():
    """List all processed videos with analytics"""

    async for db in get_db():
        from sqlalchemy import select, desc
        result = await db.execute(
            select(AnalyticsRecord).order_by(desc(AnalyticsRecord.created_at))
        )
        records = result.scalars().all()

        videos = []
        for record in records:
            videos.append({
                "video_id": record.video_id,
                "filename": record.filename,
                "vehicle_counts": {
                    "car": record.cars,
                    "motorcycle": record.bikes,
                    "bus": record.buses,
                    "truck": record.trucks
                },
                "total_vehicles": record.total_vehicles,
                "traffic_density": record.traffic_density,
                "created_at": record.created_at.isoformat()
            })

        return {"videos": videos, "total": len(videos)}

@app.delete("/api/videos/{video_id}")
async def delete_video(video_id: str):
    """Delete a video and its analytics"""

    # Delete from database
    async for db in get_db():
        from sqlalchemy import select, delete
        result = await db.execute(
            select(AnalyticsRecord).where(AnalyticsRecord.video_id == video_id)
        )
        record = result.scalar_one_or_none()

        if record:
            await db.execute(
                delete(AnalyticsRecord).where(AnalyticsRecord.video_id == video_id)
            )
            await db.commit()

    # Delete files
    for pattern in [f"{video_id}.*", f"processed_{video_id}.*"]:
        for file in list(UPLOAD_DIR.glob(pattern)) + list(PROCESSED_DIR.glob(pattern)):
            file.unlink(missing_ok=True)

    return {"message": "Video deleted successfully", "video_id": video_id}

if __name__ == "__main__":
    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)
