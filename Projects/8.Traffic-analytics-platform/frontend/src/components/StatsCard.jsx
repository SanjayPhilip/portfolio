import React from 'react'

function StatsCard({ title, value, icon: Icon, color, subtitle, trend }) {
  const colorClasses = {
    blue: 'from-blue-500/20 to-blue-600/20 text-blue-400 border-blue-500/30',
    green: 'from-green-500/20 to-green-600/20 text-green-400 border-green-500/30',
    yellow: 'from-yellow-500/20 to-yellow-600/20 text-yellow-400 border-yellow-500/30',
    purple: 'from-purple-500/20 to-purple-600/20 text-purple-400 border-purple-500/30',
    red: 'from-red-500/20 to-red-600/20 text-red-400 border-red-500/30',
  }

  return (
    <div className="stat-card">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <p className="text-slate-400 text-sm font-medium mb-1">{title}</p>
          <h3 className="text-3xl font-bold text-white">{value.toLocaleString()}</h3>
          {subtitle && <p className="text-slate-500 text-sm mt-1">{subtitle}</p>}
          {trend && (
            <p className={`text-sm mt-2 font-medium ${trend > 0 ? 'text-green-400' : 'text-red-400'}`}>
              {trend > 0 ? '+' : ''}{trend}% from last period
            </p>
          )}
        </div>
        <div className={`p-3 rounded-xl bg-gradient-to-br ${colorClasses[color] || colorClasses.blue} border`}>
          <Icon className="w-6 h-6" />
        </div>
      </div>
    </div>
  )
}

export default StatsCard
