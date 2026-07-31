import React, { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { ArrowLeft, Car, Bike, Bus, Truck } from 'lucide-react'
import VehicleDistributionChart from '../components/VehicleDistributionChart'
import { getAnalytics } from '../services/api'

function Analytics() {
  const { videoId } = useParams()
  const [analytics, setAnalytics] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => { loadAnalytics() }, [videoId])

  const loadAnalytics = async () => {
    try {
      const data = await getAnalytics(videoId)
      setAnalytics(data)
    } catch (error) {
      console.error('Failed to load analytics:', error)
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

  if (!analytics) {
    return (
      <div className="text-center py-20">
        <p className="text-slate-500">Analytics not found</p>
        <Link to="/" className="btn-primary mt-4 inline-block">Go to Dashboard</Link>
      </div>
    )
  }

  const vehicleData = [
    { name: 'Cars', value: analytics.vehicle_counts.car, color: '#00C49F' },
    { name: 'Bikes', value: analytics.vehicle_counts.motorcycle, color: '#FFBB28' },
    { name: 'Buses', value: analytics.vehicle_counts.bus, color: '#FF8042' },
    { name: 'Trucks', value: analytics.vehicle_counts.truck, color: '#8884D8' },
  ]

  return (
    <div className="space-y-8">
      <div className="flex items-center gap-4">
        <Link to="/videos" className="p-2 hover:bg-dark-800 rounded-lg transition-colors">
          <ArrowLeft className="w-5 h-5" />
        </Link>
        <div>
          <h1 className="text-2xl font-bold">Video Analytics</h1>
          <p className="text-slate-400 text-sm">{analytics.filename}</p>
        </div>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="stat-card text-center">
          <Car className="w-6 h-6 text-green-400 mx-auto mb-2" />
          <p className="text-2xl font-bold">{analytics.vehicle_counts.car}</p>
          <p className="text-slate-400 text-sm">Cars</p>
        </div>
        <div className="stat-card text-center">
          <Bike className="w-6 h-6 text-yellow-400 mx-auto mb-2" />
          <p className="text-2xl font-bold">{analytics.vehicle_counts.motorcycle}</p>
          <p className="text-slate-400 text-sm">Bikes</p>
        </div>
        <div className="stat-card text-center">
          <Bus className="w-6 h-6 text-red-400 mx-auto mb-2" />
          <p className="text-2xl font-bold">{analytics.vehicle_counts.bus}</p>
          <p className="text-slate-400 text-sm">Buses</p>
        </div>
        <div className="stat-card text-center">
          <Truck className="w-6 h-6 text-purple-400 mx-auto mb-2" />
          <p className="text-2xl font-bold">{analytics.vehicle_counts.truck}</p>
          <p className="text-slate-400 text-sm">Trucks</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <VehicleDistributionChart data={vehicleData} />
        <div className="glass-panel p-6">
          <h3 className="text-lg font-semibold mb-4">Processing Details</h3>
          <div className="grid grid-cols-2 gap-4 text-sm">
            <div><p className="text-slate-500">Total Vehicles</p><p className="font-medium text-lg">{analytics.total_vehicles}</p></div>
            <div><p className="text-slate-500">Traffic Density</p><span className={`badge badge-${analytics.traffic_density === 'high' ? 'red' : analytics.traffic_density === 'medium' ? 'yellow' : 'green'}`}>{analytics.traffic_density.toUpperCase()}</span></div>
            <div><p className="text-slate-500">Processing Time</p><p className="font-medium">{analytics.processing_time}s</p></div>
            <div><p className="text-slate-500">Processed At</p><p className="font-medium">{new Date(analytics.created_at).toLocaleString()}</p></div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Analytics
