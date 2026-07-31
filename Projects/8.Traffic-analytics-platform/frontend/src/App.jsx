import React from 'react'
import { Routes, Route } from 'react-router-dom'
import Layout from './components/Layout'
import Dashboard from './pages/Dashboard'
import Upload from './pages/Upload'
import Analytics from './pages/Analytics'
import VideoList from './pages/VideoList'

function App() {
  return (
    <Layout>
      <Routes>
        <Route path="/" element={<Dashboard />} />
        <Route path="/upload" element={<Upload />} />
        <Route path="/analytics/:videoId" element={<Analytics />} />
        <Route path="/videos" element={<VideoList />} />
      </Routes>
    </Layout>
  )
}

export default App
