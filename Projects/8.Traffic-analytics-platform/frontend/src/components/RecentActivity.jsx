import React from 'react'
import { Link } from 'react-router-dom'
import { Clock, ArrowRight, AlertTriangle } from 'lucide-react'

function RecentActivity({ activities }) {
  const getDensityBadge = (density) => {
    switch (density) {
      case 'high': return 'badge-red'
      case 'medium': return 'badge-yellow'
      default: return 'badge-green'
    }
  }

  return (
    <div className="glass-panel p-6">
      <h3 className="text-lg font-semibold mb-6">Recent Activity</h3>
      <div className="space-y-4">
        {activities.length === 0 ? (
          <p className="text-slate-500 text-center py-8">No recent activity</p>
        ) : (
          activities.map((activity) => (
            <div key={activity.video_id} className="flex items-center justify-between p-4 bg-dark-800/50 rounded-xl hover:bg-dark-800 transition-colors">
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                  <Clock className="w-5 h-5 text-blue-400" />
                </div>
                <div>
                  <p className="font-medium text-sm truncate max-w-[200px]">{activity.filename}</p>
                  <p className="text-slate-500 text-xs">{new Date(activity.created_at).toLocaleString()}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <span className={`badge ${getDensityBadge(activity.traffic_density)}`}>
                  {activity.traffic_density === 'high' && <AlertTriangle className="w-3 h-3 inline mr-1" />}
                  {activity.traffic_density}
                </span>
                <span className="text-sm font-medium">{activity.total_vehicles} vehicles</span>
                <Link to={`/analytics/${activity.video_id}`} className="p-2 hover:bg-dark-700 rounded-lg transition-colors">
                  <ArrowRight className="w-4 h-4 text-slate-400" />
                </Link>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  )
}

export default RecentActivity
