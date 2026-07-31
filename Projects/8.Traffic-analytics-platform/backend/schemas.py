"""
Pydantic Schemas for API Request/Response Validation
"""

from pydantic import BaseModel
from typing import List, Optional
from datetime import datetime


class VideoInfo(BaseModel):
    """Video metadata"""
    width: int
    height: int
    fps: int
    total_frames: int
    duration_seconds: int


class UploadResponse(BaseModel):
    """Video upload response"""
    video_id: str
    original_filename: str
    status: str
    message: str
    video_info: VideoInfo


class VehicleCount(BaseModel):
    """Vehicle count breakdown"""
    car: int
    motorcycle: int
    bus: int
    truck: int


class AnalyticsResult(BaseModel):
    """Video processing result"""
    video_id: str
    status: str
    vehicle_counts: VehicleCount
    total_vehicles: int
    traffic_density: str
    processing_time: float
    output_video_url: str
    congestion_alert: bool
    peak_hour: bool


class TrafficStatus(BaseModel):
    """Real-time traffic status"""
    density: str
    vehicle_count: int
    status_message: str
    alert: bool


class DailyStat(BaseModel):
    """Daily statistics entry"""
    date: str
    cars: int
    bikes: int
    buses: int
    trucks: int
    total: int
    videos_processed: int


class DistributionItem(BaseModel):
    """Distribution chart item"""
    name: str
    value: int
    color: str


class ActivityItem(BaseModel):
    """Recent activity item"""
    video_id: str
    filename: str
    total_vehicles: int
    traffic_density: str
    created_at: str


class DashboardStats(BaseModel):
    """Dashboard statistics"""
    total_vehicles: int
    total_cars: int
    total_bikes: int
    total_buses: int
    total_trucks: int
    total_videos_processed: int
    congestion_alerts: int
    avg_vehicles_per_video: float
    daily_stats: List[DailyStat]
    vehicle_distribution: List[DistributionItem]
    density_distribution: List[DistributionItem]
    recent_activity: List[ActivityItem]
    period_days: int
