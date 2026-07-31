# 🚦 AI Traffic Analytics Platform

> **Real-time AI Traffic Analytics using YOLOv8, FastAPI, OpenCV, and Interactive Dashboards**

[![Python](https://img.shields.io/badge/Python-3.10+-blue.svg)](https://python.org)
[![FastAPI](https://img.shields.io/badge/FastAPI-0.109+-00a393.svg)](https://fastapi.tiangolo.com)
[![YOLOv8](https://img.shields.io/badge/YOLOv8-Ultralytics-ff6f00.svg)](https://ultralytics.com)
[![React](https://img.shields.io/badge/React-18.2+-61dafb.svg)](https://react.dev)

## ✨ Features

### Phase 1 (Current)
- ✅ Upload traffic videos (MP4, AVI, MOV)
- ✅ YOLOv8 real-time vehicle detection
- ✅ Vehicle counting (Cars, Bikes, Buses, Trucks)
- ✅ Annotated video output with bounding boxes
- ✅ Interactive dashboard with charts
- ✅ Traffic density analysis (Low/Medium/High)
- ✅ Congestion alerts
- ✅ Video library management

### Phase 2 (Planned)
- 🔄 Live CCTV stream processing
- 🔄 Heatmap generation
- 🔄 Multi-camera support
- 🔄 ML-based traffic prediction

## 🏗️ Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   React +       │────▶│   FastAPI       │────▶│   YOLOv8        │
│   Tailwind      │◄────│   Backend       │◄────│   + OpenCV      │
│   Recharts      │     │   SQLite        │     │   Detector      │
└─────────────────┘     └─────────────────┘     └─────────────────┘
        │                                               │
        │              ┌─────────────────┐              │
        └─────────────▶│   Processed     │◄─────────────┘
                       │   Videos        │
                       └─────────────────┘
```

## 🚀 Quick Start

### Prerequisites
- Python 3.10+
- Node.js 18+
- CUDA (optional, for GPU acceleration)

### Backend Setup

```bash
cd backend

# Create virtual environment
python -m venv venv

# Activate (Linux/Mac)
source venv/bin/activate
# Activate (Windows)
venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Start server
python main.py
# Or: uvicorn main:app --reload
```

Backend runs at: `http://localhost:8000`
API docs: `http://localhost:8000/api/docs`

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Start dev server
npm run dev
```

Frontend runs at: `http://localhost:3000`

## 📁 Project Structure

```
traffic-analytics-platform/
├── backend/
│   ├── main.py              # FastAPI application
│   ├── detector.py          # YOLOv8 detection engine
│   ├── analytics.py         # Analytics & statistics
│   ├── database.py          # Async SQLite ORM
│   ├── schemas.py           # Pydantic models
│   ├── requirements.txt
│   └── .env.example
├── frontend/
│   ├── src/
│   │   ├── components/      # Reusable UI components
│   │   ├── pages/           # Route pages
│   │   ├── services/        # API client
│   │   ├── App.jsx
│   │   └── main.jsx
│   ├── package.json
│   └── vite.config.js
├── uploads/                 # Uploaded videos
├── processed/               # Annotated output videos
└── README.md
```

## 📊 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/upload` | Upload traffic video |
| POST | `/api/process/{video_id}` | Process with YOLOv8 |
| GET | `/api/analytics/{video_id}` | Get video analytics |
| GET | `/api/dashboard/stats` | Dashboard statistics |
| GET | `/api/videos/list` | List all videos |
| DELETE | `/api/videos/{video_id}` | Delete video |

## 🎯 What Recruiters See

Instead of:
> "Vehicle counter using OpenCV"

They see:
> **"Real-time AI Traffic Analytics Platform using YOLOv8, FastAPI, OpenCV, and Interactive Dashboards"**

**Tech Stack Demonstrated:**
- 🧠 **AI/ML**: YOLOv8 object detection, computer vision
- ⚡ **Backend**: FastAPI, async Python, SQLAlchemy
- 🎨 **Frontend**: React, Tailwind CSS, Recharts
- 🗄️ **Database**: SQLite (upgradeable to PostgreSQL)
- 📹 **Media**: OpenCV video processing

## 🛣️ Roadmap

- [x] Phase 1: Core detection + dashboard
- [ ] Phase 2: Live streams, heatmaps, multi-camera
- [ ] Phase 3: ML prediction, PostgreSQL, Docker
- [ ] Phase 4: Cloud deployment, Kubernetes

## 📄 License

MIT License - feel free to use for your portfolio!

---

**Built with ❤️ for AI-focused portfolios**
