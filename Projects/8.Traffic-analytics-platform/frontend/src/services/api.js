import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 300000, // 5 minutes for video processing
})

// Upload video
export const uploadVideo = async (file, onProgress) => {
  const formData = new FormData()
  formData.append('file', file)

  const response = await api.post('/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
    onUploadProgress: (progressEvent) => {
      if (onProgress) {
        const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        onProgress(progress)
      }
    },
  })

  return response.data
}

// Process video
export const processVideo = async (videoId) => {
  const response = await api.post(`/process/${videoId}`)
  return response.data
}

// Get analytics for a video
export const getAnalytics = async (videoId) => {
  const response = await api.get(`/analytics/${videoId}`)
  return response.data
}

// Get dashboard stats
export const getDashboardStats = async (days = 7) => {
  const response = await api.get(`/dashboard/stats`, {
    params: { days }
  })
  return response.data
}

// Get video list
export const getVideoList = async () => {
  const response = await api.get('/videos/list')
  return response.data
}

// Delete video
export const deleteVideo = async (videoId) => {
  const response = await api.delete(`/videos/${videoId}`)
  return response.data
}

// Get processed video URL
export const getProcessedVideoUrl = (filename) => {
  return `${API_BASE_URL}/videos/processed/${filename}`
}

export default api
