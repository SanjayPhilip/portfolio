import React from 'react'
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts'

function TrafficDensityChart({ data }) {
  const CustomTooltip = ({ active, payload }) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-dark-800 border border-slate-700 rounded-lg p-3 shadow-xl">
          <p className="text-white font-medium">{payload[0].name} Density</p>
          <p className="text-slate-400">{payload[0].value} videos</p>
        </div>
      )
    }
    return null
  }

  return (
    <div className="glass-panel p-6">
      <h3 className="text-lg font-semibold mb-6">Traffic Density Distribution</h3>
      <ResponsiveContainer width="100%" height={300}>
        <PieChart>
          <Pie data={data} cx="50%" cy="50%" outerRadius={100} dataKey="value" label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}>
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={entry.color} />
            ))}
          </Pie>
          <Tooltip content={<CustomTooltip />} />
        </PieChart>
      </ResponsiveContainer>
    </div>
  )
}

export default TrafficDensityChart
