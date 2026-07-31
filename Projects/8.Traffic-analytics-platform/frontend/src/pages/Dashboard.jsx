import React, { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { Car, Bike, Bus, Truck, Upload, AlertTriangle, Video, Activity } from 'lucide-react'
import StatsCard from '../components/StatsCard'
import VehicleDistributionChart from '../components/VehicleDistributionChart'
import TrafficDensityChart from '../components/TrafficDensityChart'
import DailyTrendChart from '../components/DailyTrendChart'
import RecentActivity from '../components/RecentActivity'
import { getDashboardStats } from '../services/api'

function Dashboard() {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => { loadStats() }, [])

  const loadStats = async () => {
    try {
      const data = await getDashboardStats(7)
      setStats(data)
    } catch (error) {
      console.error('Failed to load stats:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full" />
      </div>
    )
  }

  const emptyStats = {
    total_vehicles: 0, total_cars: 0, total_bikes: 0, total_buses: 0, total_trucks: 0,
    total_videos_processed: 0, congestion_alerts: 0,
    vehicle_distribution: [], density_distribution: [], daily_stats: [], recent_activity: []
  }

  const data = stats || emptyStats

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold gradient-text">Dashboard</h1>
          <p className="text-slate-400 mt-1">Real-time traffic analytics overview</p>
        </div>
        <Link to="/upload" className="btn-primary flex items-center gap-2">
          <Upload className="w-5 h-5" /> Upload Video
        </Link>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatsCard title="Total Vehicles" value={data.total_vehicles} icon={Car} color="blue" subtitle={`${data.total_videos_processed} videos processed`} />
        <StatsCard title="Cars Detected" value={data.total_cars} icon={Car} color="green" />
        <StatsCard title="Bikes Detected" value={data.total_bikes} icon={Bike} color="yellow" />
        <StatsCard title="Heavy Vehicles" value={data.total_buses + data.total_trucks} icon={Truck} color="purple" subtitle={`${data.total_buses} buses, ${data.total_trucks} trucks`} />
      </div>

      {data.congestion_alerts > 0 && (
        <div className="bg-red-500/10 border border-red-500/30 rounded-xl p-4 flex items-center gap-3">
          <AlertTriangle className="w-6 h-6 text-red-400" />
          <div>
            <p className="font-medium text-red-400">Congestion Alerts</p>
            <p className="text-sm text-red-300/70">{data.congestion_alerts} high traffic incidents detected</p>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <VehicleDistributionChart data={data.vehicle_distribution} />
        <TrafficDensityChart data={data.density_distribution} />
      </div>

      <DailyTrendChart data={data.daily_stats} />
      <RecentActivity activities={data.recent_activity} />
    </div>
  )
}

export default Dashboard
