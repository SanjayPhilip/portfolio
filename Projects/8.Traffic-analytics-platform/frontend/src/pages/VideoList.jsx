import React, { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { Video, Trash2, Eye, Clock, AlertTriangle } from 'lucide-react'
import toast from 'react-hot-toast'
import { getVideoList, deleteVideo } from '../services/api'

function VideoList() {
  const [videos, setVideos] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => { loadVideos() }, [])

  const loadVideos = async () => {
    try {
      const data = await getVideoList()
      setVideos(data.videos || [])
    } catch (error) {
      toast.error('Failed to load videos')
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async (videoId) => {
    if (!confirm('Are you sure you want to delete this video?')) return
    try {
      await deleteVideo(videoId)
      setVideos(videos.filter(v => v.video_id !== videoId))
      toast.success('Video deleted')
    } catch (error) {
      toast.error('Failed to delete video')
    }
  }

  const getDensityBadge = (density) => {
    switch (density) {
      case 'high': return 'badge-red'
      case 'medium': return 'badge-yellow'
      default: return 'badge-green'
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full" />
      </div>
    )
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold gradient-text">Video Library</h1>
        <p className="text-slate-400 mt-1">All processed traffic videos</p>
      </div>

      {videos.length === 0 ? (
        <div className="glass-panel p-12 text-center">
          <Video className="w-12 h-12 text-slate-600 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-slate-400">No videos yet</h3>
          <p className="text-slate-500 text-sm mt-1">Upload a video to get started</p>
          <Link to="/upload" className="btn-primary mt-4 inline-block">Upload Video</Link>
        </div>
      ) : (
        <div className="grid gap-4">
          {videos.map((video) => (
            <div key={video.video_id} className="glass-panel p-6 flex items-center gap-6">
              <div className="w-16 h-16 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                <Video className="w-8 h-8 text-blue-400" />
              </div>
              <div className="flex-1 min-w-0">
                <h3 className="font-medium truncate">{video.filename}</h3>
                <div className="flex items-center gap-4 mt-1 text-sm text-slate-500">
                  <span className="flex items-center gap-1"><Clock className="w-4 h-4" />{new Date(video.created_at).toLocaleDateString()}</span>
                  <span>{video.total_vehicles} vehicles</span>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <span className={`badge ${getDensityBadge(video.traffic_density)}`}>
                  {video.traffic_density === 'high' && <AlertTriangle className="w-3 h-3 inline mr-1" />}
                  {video.traffic_density}
                </span>
                <Link to={`/analytics/${video.video_id}`} className="p-2 hover:bg-dark-700 rounded-lg transition-colors" title="View analytics">
                  <Eye className="w-5 h-5 text-slate-400" />
                </Link>
                <button onClick={() => handleDelete(video.video_id)} className="p-2 hover:bg-red-500/20 rounded-lg transition-colors" title="Delete video">
                  <Trash2 className="w-5 h-5 text-red-400" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

export default VideoList
