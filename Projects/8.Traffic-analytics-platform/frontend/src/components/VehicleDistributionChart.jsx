import React from 'react'
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts'

function VehicleDistributionChart({ data }) {
  const CustomTooltip = ({ active, payload }) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-dark-800 border border-slate-700 rounded-lg p-3 shadow-xl">
          <p className="text-white font-medium">{payload[0].name}</p>
          <p className="text-slate-400">{payload[0].value.toLocaleString()} vehicles</p>
        </div>
      )
    }
    return null
  }

  return (
    <div className="glass-panel p-6">
      <h3 className="text-lg font-semibold mb-6">Vehicle Distribution</h3>
      <ResponsiveContainer width="100%" height={300}>
        <PieChart>
          <Pie data={data} cx="50%" cy="50%" innerRadius={60} outerRadius={100} paddingAngle={5} dataKey="value">
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={entry.color} />
            ))}
          </Pie>
          <Tooltip content={<CustomTooltip />} />
          <Legend formatter={(value) => <span className="text-slate-300">{value}</span>} />
        </PieChart>
      </ResponsiveContainer>
    </div>
  )
}

export default VehicleDistributionChart
