import React from 'react'
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts'

function DailyTrendChart({ data }) {
  const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload) {
      return (
        <div className="bg-dark-800 border border-slate-700 rounded-lg p-3 shadow-xl">
          <p className="text-white font-medium mb-2">{label}</p>
          {payload.map((entry, index) => (
            <p key={index} className="text-sm" style={{ color: entry.color }}>
              {entry.name}: {entry.value}
            </p>
          ))}
        </div>
      )
    }
    return null
  }

  return (
    <div className="glass-panel p-6">
      <h3 className="text-lg font-semibold mb-6">Daily Traffic Trends</h3>
      <ResponsiveContainer width="100%" height={350}>
        <BarChart data={data}>
          <CartesianGrid strokeDasharray="3 3" stroke="#334155" />
          <XAxis dataKey="date" stroke="#94a3b8" fontSize={12} tickFormatter={(value) => new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} />
          <YAxis stroke="#94a3b8" fontSize={12} />
          <Tooltip content={<CustomTooltip />} />
          <Legend formatter={(value) => <span className="text-slate-300">{value}</span>} />
          <Bar dataKey="cars" name="Cars" fill="#00C49F" radius={[4, 4, 0, 0]} />
          <Bar dataKey="bikes" name="Bikes" fill="#FFBB28" radius={[4, 4, 0, 0]} />
          <Bar dataKey="buses" name="Buses" fill="#FF8042" radius={[4, 4, 0, 0]} />
          <Bar dataKey="trucks" name="Trucks" fill="#8884D8" radius={[4, 4, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}

export default DailyTrendChart
