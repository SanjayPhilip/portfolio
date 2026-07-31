import React, { useState, useRef } from 'react'
import { Play, Pause, Volume2, VolumeX, Download } from 'lucide-react'

function VideoPlayer({ videoUrl, title }) {
  const [isPlaying, setIsPlaying] = useState(false)
  const [isMuted, setIsMuted] = useState(false)
  const [progress, setProgress] = useState(0)
  const videoRef = useRef(null)

  const togglePlay = () => {
    if (videoRef.current) {
      if (isPlaying) videoRef.current.pause()
      else videoRef.current.play()
      setIsPlaying(!isPlaying)
    }
  }

  const toggleMute = () => {
    if (videoRef.current) {
      videoRef.current.muted = !isMuted
      setIsMuted(!isMuted)
    }
  }

  const handleTimeUpdate = () => {
    if (videoRef.current) {
      setProgress((videoRef.current.currentTime / videoRef.current.duration) * 100)
    }
  }

  const handleSeek = (e) => {
    const rect = e.target.getBoundingClientRect()
    const pos = (e.clientX - rect.left) / rect.width
    if (videoRef.current) videoRef.current.currentTime = pos * videoRef.current.duration
  }

  return (
    <div className="glass-panel overflow-hidden">
      <div className="relative aspect-video bg-black">
        <video ref={videoRef} src={videoUrl} className="w-full h-full" onTimeUpdate={handleTimeUpdate} onEnded={() => setIsPlaying(false)} onClick={togglePlay} />
        {!isPlaying && (
          <div className="absolute inset-0 flex items-center justify-center bg-black/40 cursor-pointer" onClick={togglePlay}>
            <div className="w-16 h-16 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
              <Play className="w-8 h-8 text-white ml-1" />
            </div>
          </div>
        )}
      </div>
      <div className="p-4">
        <div className="flex items-center gap-4 mb-3">
          <button onClick={togglePlay} className="p-2 hover:bg-dark-700 rounded-lg transition-colors">
            {isPlaying ? <Pause className="w-5 h-5" /> : <Play className="w-5 h-5" />}
          </button>
          <button onClick={toggleMute} className="p-2 hover:bg-dark-700 rounded-lg transition-colors">
            {isMuted ? <VolumeX className="w-5 h-5" /> : <Volume2 className="w-5 h-5" />}
          </button>
          <div className="flex-1 h-1 bg-dark-700 rounded-full cursor-pointer" onClick={handleSeek}>
            <div className="h-full bg-blue-500 rounded-full transition-all" style={{ width: `${progress}%` }} />
          </div>
          <a href={videoUrl} download className="p-2 hover:bg-dark-700 rounded-lg transition-colors" title="Download video">
            <Download className="w-5 h-5" />
          </a>
        </div>
        <h4 className="font-medium text-sm text-slate-300">{title}</h4>
      </div>
    </div>
  )
}

export default VideoPlayer
