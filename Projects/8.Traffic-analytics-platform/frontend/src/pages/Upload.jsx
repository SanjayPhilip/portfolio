import React, { useState, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useDropzone } from 'react-dropzone'
import { Upload, FileVideo, CheckCircle, AlertCircle, Loader } from 'lucide-react'
import toast from 'react-hot-toast'
import { uploadVideo, processVideo } from '../services/api'

function UploadPage() {
  const [uploading, setUploading] = useState(false)
  const [processing, setProcessing] = useState(false)
  const [uploadProgress, setUploadProgress] = useState(0)
  const [uploadedVideo, setUploadedVideo] = useState(null)
  const [result, setResult] = useState(null)
  const navigate = useNavigate()

  const onDrop = useCallback(async (acceptedFiles) => {
    const file = acceptedFiles[0]
    if (!file) return

    const validTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo']
    if (!validTypes.includes(file.type)) {
      toast.error('Please upload a valid video file (MP4, AVI, MOV)')
      return
    }
    if (file.size > 500 * 1024 * 1024) {
      toast.error('File size must be less than 500MB')
      return
    }

    setUploading(true)
    setUploadProgress(0)

    try {
      const response = await uploadVideo(file, (progress) => setUploadProgress(progress))
      setUploadedVideo(response)
      toast.success('Video uploaded successfully!')
      startProcessing(response.video_id)
    } catch (error) {
      toast.error(error.response?.data?.detail || 'Upload failed')
      setUploading(false)
    }
  }, [])

  const startProcessing = async (videoId) => {
    setProcessing(true)
    setUploading(false)
    try {
      const result = await processVideo(videoId)
      setResult(result)
      toast.success('Processing complete!')
    } catch (error) {
      toast.error(error.response?.data?.detail || 'Processing failed')
    } finally {
      setProcessing(false)
    }
  }

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop, accept: { 'video/*': ['.mp4', '.avi', '.mov', '.mkv'] },
    disabled: uploading || processing, multiple: false
  })

  return (
    <div className="max-w-4xl mx-auto space-y-8">
      <div>
        <h1 className="text-3xl font-bold gradient-text">Upload Traffic Video</h1>
        <p className="text-slate-400 mt-1">Upload a video for AI-powered traffic analysis</p>
      </div>

      {!uploadedVideo && !result && (
        <div {...getRootProps()} className={`dropzone ${isDragActive ? 'dropzone-active' : ''}`}>
          <input {...getInputProps()} />
          <div className="space-y-4">
            <div className="w-16 h-16 mx-auto rounded-2xl bg-blue-500/20 flex items-center justify-center">
              <Upload className="w-8 h-8 text-blue-400" />
            </div>
            <div>
              <p className="text-lg font-medium">{isDragActive ? 'Drop video here' : 'Drag & drop video here'}</p>
              <p className="text-slate-500 text-sm mt-1">or click to browse (MP4, AVI, MOV up to 500MB)</p>
            </div>
          </div>
        </div>
      )}

      {uploading && (
        <div className="glass-panel p-8 text-center">
          <FileVideo className="w-12 h-12 text-blue-400 mx-auto mb-4 animate-pulse" />
          <h3 className="text-lg font-medium mb-2">Uploading...</h3>
          <div className="w-full max-w-md mx-auto h-2 bg-dark-700 rounded-full overflow-hidden">
            <div className="h-full bg-gradient-to-r from-blue-500 to-cyan-500 transition-all duration-300" style={{ width: `${uploadProgress}%` }} />
          </div>
          <p className="text-slate-500 mt-2">{uploadProgress}%</p>
        </div>
      )}

      {processing && (
        <div className="glass-panel p-8 text-center">
          <Loader className="w-12 h-12 text-blue-400 mx-auto mb-4 animate-spin" />
          <h3 className="text-lg font-medium mb-2">Processing with YOLOv8...</h3>
          <p className="text-slate-500">Detecting and counting vehicles. This may take a few minutes.</p>
          <div className="mt-4 flex items-center justify-center gap-2 text-sm text-slate-400">
            <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse" /> Running AI inference
          </div>
        </div>
      )}

      {result && (
        <div className="space-y-6">
          <div className="glass-panel p-6 text-center">
            <CheckCircle className="w-12 h-12 text-green-400 mx-auto mb-4" />
            <h3 className="text-xl font-bold mb-2">Analysis Complete!</h3>
            <p className="text-slate-400">Your traffic video has been processed successfully</p>
          </div>

          <div className="glass-panel p-6">
            <h3 className="text-lg font-semibold mb-4">Detection Results</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              {[
                { label: 'Cars', value: result.vehicle_counts.car, color: 'text-green-400' },
                { label: 'Bikes', value: result.vehicle_counts.motorcycle, color: 'text-yellow-400' },
                { label: 'Buses', value: result.vehicle_counts.bus, color: 'text-red-400' },
                { label: 'Trucks', value: result.vehicle_counts.truck, color: 'text-purple-400' },
              ].map((item) => (
                <div key={item.label} className="bg-dark-800/50 rounded-xl p-4 text-center">
                  <p className={`text-2xl font-bold ${item.color}`}>{item.value}</p>
                  <p className="text-slate-400 text-sm">{item.label}</p>
                </div>
              ))}
            </div>
            <div className="mt-4 flex items-center gap-4">
              <span className="text-slate-400">Traffic Density:</span>
              <span className={`badge badge-${result.traffic_density === 'high' ? 'red' : result.traffic_density === 'medium' ? 'yellow' : 'green'}`}>
                {result.traffic_density.toUpperCase()}
              </span>
              {result.congestion_alert && (
                <span className="badge badge-red flex items-center gap-1">
                  <AlertCircle className="w-3 h-3" /> Congestion Alert
                </span>
              )}
            </div>
          </div>

          <div className="flex gap-4">
            <button onClick={() => navigate(`/analytics/${result.video_id}`)} className="btn-primary flex-1">View Detailed Analytics</button>
            <button onClick={() => { setUploadedVideo(null); setResult(null); setUploadProgress(0) }} className="btn-secondary">Upload Another</button>
          </div>
        </div>
      )}
    </div>
  )
}

export default UploadPage
