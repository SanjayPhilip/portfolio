"""
Traffic Analytics Engine
Handles data analysis, trends, and dashboard statistics
"""

from datetime import datetime, timedelta
from typing import Dict, List
from collections import defaultdict
import numpy as np

from database import get_db, AnalyticsRecord


class AnalyticsEngine:
    """Traffic analytics and statistics engine"""

    async def get_dashboard_stats(self, days: int = 7) -> Dict:
        """Generate comprehensive dashboard statistics"""

        async for db in get_db():
            from sqlalchemy import select, func, desc

            # Calculate date range
            end_date = datetime.now()
            start_date = end_date - timedelta(days=days)

            # Get all records in date range
            result = await db.execute(
                select(AnalyticsRecord)
                .where(AnalyticsRecord.created_at >= start_date)
                .order_by(AnalyticsRecord.created_at)
            )
            records = result.scalars().all()

            if not records:
                return self._get_empty_stats(days)

            # Calculate totals
            total_cars = sum(r.cars for r in records)
            total_bikes = sum(r.bikes for r in records)
            total_buses = sum(r.buses for r in records)
            total_trucks = sum(r.trucks for r in records)
            total_vehicles = total_cars + total_bikes + total_buses + total_trucks

            # Traffic density distribution
            density_counts = {"low": 0, "medium": 0, "high": 0}
            for r in records:
                if r.traffic_density in density_counts:
                    density_counts[r.traffic_density] += 1

            # Daily breakdown
            daily_stats = defaultdict(lambda: {
                "cars": 0, "bikes": 0, "buses": 0, 
                "trucks": 0, "total": 0, "count": 0
            })

            for r in records:
                day_key = r.created_at.strftime("%Y-%m-%d")
                daily_stats[day_key]["cars"] += r.cars
                daily_stats[day_key]["bikes"] += r.bikes
                daily_stats[day_key]["buses"] += r.buses
                daily_stats[day_key]["trucks"] += r.trucks
                daily_stats[day_key]["total"] += r.total_vehicles
                daily_stats[day_key]["count"] += 1

            # Format daily data for charts
            daily_data = []
            for date_key in sorted(daily_stats.keys()):
                stats = daily_stats[date_key]
                daily_data.append({
                    "date": date_key,
                    "cars": stats["cars"],
                    "bikes": stats["bikes"],
                    "buses": stats["buses"],
                    "trucks": stats["trucks"],
                    "total": stats["total"],
                    "videos_processed": stats["count"]
                })

            # Vehicle type distribution (for pie chart)
            vehicle_distribution = [
                {"name": "Cars", "value": total_cars, "color": "#00C49F"},
                {"name": "Bikes", "value": total_bikes, "color": "#FFBB28"},
                {"name": "Buses", "value": total_buses, "color": "#FF8042"},
                {"name": "Trucks", "value": total_trucks, "color": "#8884D8"}
            ]

            # Traffic density distribution (for pie chart)
            density_distribution = [
                {"name": "Low", "value": density_counts["low"], "color": "#00C49F"},
                {"name": "Medium", "value": density_counts["medium"], "color": "#FFBB28"},
                {"name": "High", "value": density_counts["high"], "color": "#FF8042"}
            ]

            # Recent activity
            recent_activity = []
            for r in records[-10:]:  # Last 10 records
                recent_activity.append({
                    "video_id": r.video_id,
                    "filename": r.filename,
                    "total_vehicles": r.total_vehicles,
                    "traffic_density": r.traffic_density,
                    "created_at": r.created_at.isoformat()
                })
            recent_activity.reverse()

            # Congestion alerts count
            congestion_count = sum(1 for r in records if r.traffic_density == "high")

            return {
                "total_vehicles": total_vehicles,
                "total_cars": total_cars,
                "total_bikes": total_bikes,
                "total_buses": total_buses,
                "total_trucks": total_trucks,
                "total_videos_processed": len(records),
                "congestion_alerts": congestion_count,
                "avg_vehicles_per_video": round(total_vehicles / len(records), 1) if records else 0,
                "daily_stats": daily_data,
                "vehicle_distribution": vehicle_distribution,
                "density_distribution": density_distribution,
                "recent_activity": recent_activity,
                "period_days": days
            }

    def _get_empty_stats(self, days: int) -> Dict:
        """Return empty stats structure"""
        return {
            "total_vehicles": 0,
            "total_cars": 0,
            "total_bikes": 0,
            "total_buses": 0,
            "total_trucks": 0,
            "total_videos_processed": 0,
            "congestion_alerts": 0,
            "avg_vehicles_per_video": 0,
            "daily_stats": [],
            "vehicle_distribution": [
                {"name": "Cars", "value": 0, "color": "#00C49F"},
                {"name": "Bikes", "value": 0, "color": "#FFBB28"},
                {"name": "Buses", "value": 0, "color": "#FF8042"},
                {"name": "Trucks", "value": 0, "color": "#8884D8"}
            ],
            "density_distribution": [
                {"name": "Low", "value": 0, "color": "#00C49F"},
                {"name": "Medium", "value": 0, "color": "#FFBB28"},
                {"name": "High", "value": 0, "color": "#FF8042"}
            ],
            "recent_activity": [],
            "period_days": days
        }
