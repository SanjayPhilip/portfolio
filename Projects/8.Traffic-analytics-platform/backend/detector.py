"""
YOLOv8 Vehicle Detection Engine
Handles real-time vehicle detection, tracking, and counting
"""

import cv2
import numpy as np
import time
from pathlib import Path
from typing import Dict, List, Tuple, Optional
from collections import defaultdict, deque
import torch
from ultralytics import YOLO


class TrafficDetector:
    """YOLOv8-based traffic detection and analytics engine"""

    # Vehicle classes we care about (COCO dataset indices)
    VEHICLE_CLASSES = {
        2: "car",
        3: "motorcycle", 
        5: "bus",
        7: "truck"
    }

    # Traffic density thresholds (vehicles per frame average)
    DENSITY_THRESHOLDS = {
        "low": 5,
        "medium": 15,
        "high": 30
    }

    def __init__(self, model_path: str = "yolov8n.pt"):
        """Initialize YOLOv8 model"""
        print(f"Loading YOLOv8 model: {model_path}")

        # For PyTorch 2.6+, we need to handle weights_only
        try:
            self.model = YOLO(model_path)
        except Exception as e:
            if "weights_only" in str(e).lower() or "UnpicklingError" in str(type(e).__name__):
                print("Applying PyTorch 2.6+ compatibility patch...")
                import warnings
                with warnings.catch_warnings():
                    warnings.simplefilter("ignore")
                    original_load = torch.load
                    def patched_load(*args, **kwargs):
                        kwargs['weights_only'] = False
                        return original_load(*args, **kwargs)
                    torch.load = patched_load
                    self.model = YOLO(model_path)
                    torch.load = original_load
            else:
                raise

        self.model.conf = 0.4  # Confidence threshold
        self.model.iou = 0.45  # NMS IoU threshold

        # Tracking data structures
        self.vehicle_tracks = defaultdict(lambda: deque(maxlen=30))
        self.counted_vehicles = set()
        self.line_position = None

    def get_video_info(self, video_path: str) -> Dict:
        """Extract video metadata"""
        cap = cv2.VideoCapture(video_path)
        if not cap.isOpened():
            raise ValueError(f"Cannot open video: {video_path}")

        info = {
            "width": int(cap.get(cv2.CAP_PROP_FRAME_WIDTH)),
            "height": int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT)),
            "fps": int(cap.get(cv2.CAP_PROP_FPS)),
            "total_frames": int(cap.get(cv2.CAP_PROP_FRAME_COUNT)),
            "duration_seconds": int(cap.get(cv2.CAP_PROP_FRAME_COUNT) / cap.get(cv2.CAP_PROP_FPS))
        }
        cap.release()
        return info

    def detect_frame(self, frame: np.ndarray) -> Tuple[List, List, List]:
        """Detect vehicles in a single frame"""
        results = self.model(frame, verbose=False)

        boxes = []
        class_ids = []
        confidences = []

        for result in results:
            if result.boxes is None:
                continue

            for box in result.boxes:
                cls_id = int(box.cls.item())

                # Only track vehicle classes
                if cls_id in self.VEHICLE_CLASSES:
                    x1, y1, x2, y2 = box.xyxy[0].cpu().numpy()
                    conf = float(box.conf.item())

                    boxes.append([int(x1), int(y1), int(x2), int(y2)])
                    class_ids.append(cls_id)
                    confidences.append(conf)

        return boxes, class_ids, confidences

    def draw_annotations(self, frame: np.ndarray, boxes: List, class_ids: List, 
                        confidences: List, counts: Dict) -> np.ndarray:
        """Draw bounding boxes and labels on frame"""
        annotated = frame.copy()

        # Color map for different vehicle types
        colors = {
            2: (0, 255, 0),      # Car - Green
            3: (255, 165, 0),    # Motorcycle - Orange
            5: (0, 0, 255),      # Bus - Red
            7: (128, 0, 128)     # Truck - Purple
        }

        for box, cls_id, conf in zip(boxes, class_ids, confidences):
            x1, y1, x2, y2 = box
            color = colors.get(cls_id, (255, 255, 255))
            label = self.VEHICLE_CLASSES.get(cls_id, "unknown")

            # Draw bounding box
            cv2.rectangle(annotated, (x1, y1), (x2, y2), color, 2)

            # Draw label background
            label_text = f"{label}: {conf:.2f}"
            (text_w, text_h), _ = cv2.getTextSize(
                label_text, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2
            )
            cv2.rectangle(
                annotated, 
                (x1, y1 - text_h - 10), 
                (x1 + text_w, y1), 
                color, 
                -1
            )

            # Draw label text
            cv2.putText(
                annotated, 
                label_text, 
                (x1, y1 - 5), 
                cv2.FONT_HERSHEY_SIMPLEX, 
                0.6, 
                (255, 255, 255), 
                2
            )

        # Draw counts panel
        self._draw_counts_panel(annotated, counts)

        return annotated

    def _draw_counts_panel(self, frame: np.ndarray, counts: Dict):
        """Draw vehicle count overlay panel"""
        h, w = frame.shape[:2]

        # Panel background
        panel_w = 280
        panel_h = 160
        panel_x = w - panel_w - 20
        panel_y = 20

        overlay = frame.copy()
        cv2.rectangle(
            overlay, 
            (panel_x, panel_y), 
            (panel_x + panel_w, panel_y + panel_h), 
            (0, 0, 0), 
            -1
        )
        cv2.addWeighted(overlay, 0.7, frame, 0.3, 0, frame)

        # Title
        cv2.putText(
            frame, 
            "TRAFFIC ANALYTICS", 
            (panel_x + 10, panel_y + 30), 
            cv2.FONT_HERSHEY_SIMPLEX, 
            0.7, 
            (0, 255, 255), 
            2
        )

        # Counts
        y_offset = panel_y + 60
        items = [
            ("Cars", counts.get("car", 0), (0, 255, 0)),
            ("Bikes", counts.get("motorcycle", 0), (255, 165, 0)),
            ("Buses", counts.get("bus", 0), (0, 0, 255)),
            ("Trucks", counts.get("truck", 0), (128, 0, 128))
        ]

        for label, count, color in items:
            text = f"{label}: {count}"
            cv2.putText(
                frame, 
                text, 
                (panel_x + 15, y_offset), 
                cv2.FONT_HERSHEY_SIMPLEX, 
                0.6, 
                color, 
                2
            )
            y_offset += 25

    def calculate_traffic_density(self, avg_vehicles_per_frame: float) -> str:
        """Calculate traffic density level"""
        if avg_vehicles_per_frame < self.DENSITY_THRESHOLDS["low"]:
            return "low"
        elif avg_vehicles_per_frame < self.DENSITY_THRESHOLDS["medium"]:
            return "medium"
        else:
            return "high"

    def process_video(self, input_path: str, output_path: str) -> Dict:
        """Process entire video with detection and analytics"""

        cap = cv2.VideoCapture(input_path)
        if not cap.isOpened():
            raise ValueError(f"Cannot open video: {input_path}")

        # Get video properties
        width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
        height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
        fps = int(cap.get(cv2.CAP_PROP_FPS))
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

        # Initialize video writer
        fourcc = cv2.VideoWriter_fourcc(*'mp4v')
        out = cv2.VideoWriter(output_path, fourcc, fps, (width, height))

        # Initialize counters and tracking
        vehicle_counts = {"car": 0, "motorcycle": 0, "bus": 0, "truck": 0}
        frame_vehicle_counts = []
        processed_frames = 0
        start_time = time.time()

        print(f"Processing video: {input_path}")
        print(f"Resolution: {width}x{height}, FPS: {fps}, Total frames: {total_frames}")

        while True:
            ret, frame = cap.read()
            if not ret:
                break

            # Detect vehicles
            boxes, class_ids, confidences = self.detect_frame(frame)

            # Count vehicles in current frame
            frame_counts = {"car": 0, "motorcycle": 0, "bus": 0, "truck": 0}
            for cls_id in class_ids:
                label = self.VEHICLE_CLASSES.get(cls_id)
                if label:
                    frame_counts[label] += 1

            frame_vehicle_counts.append(sum(frame_counts.values()))

            # Update total counts (using simple frame-based counting with deduplication)
            # For MVP, we count unique detections across frames
            for cls_id in class_ids:
                label = self.VEHICLE_CLASSES.get(cls_id)
                if label and processed_frames % 30 == 0:  # Sample every 30 frames
                    vehicle_counts[label] += frame_counts[label]

            # Draw annotations
            annotated_frame = self.draw_annotations(frame, boxes, class_ids, confidences, vehicle_counts)

            # Add processing info
            progress = (processed_frames / total_frames) * 100 if total_frames > 0 else 0
            cv2.putText(
                annotated_frame,
                f"Progress: {progress:.1f}%",
                (20, height - 20),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.6,
                (255, 255, 255),
                2
            )

            out.write(annotated_frame)
            processed_frames += 1

            if processed_frames % 100 == 0:
                print(f"Processed {processed_frames}/{total_frames} frames ({progress:.1f}%)")

        cap.release()
        out.release()

        processing_time = time.time() - start_time

        # Calculate analytics
        avg_vehicles_per_frame = np.mean(frame_vehicle_counts) if frame_vehicle_counts else 0
        traffic_density = self.calculate_traffic_density(avg_vehicles_per_frame)
        total_vehicles = sum(vehicle_counts.values())

        # Congestion alert
        congestion_alert = traffic_density == "high" or avg_vehicles_per_frame > 25

        # Peak hour detection (simplified - based on density)
        peak_hour = traffic_density == "high"

        result = {
            "vehicle_counts": vehicle_counts,
            "total_vehicles": total_vehicles,
            "traffic_density": traffic_density,
            "processing_time": round(processing_time, 2),
            "frames_processed": processed_frames,
            "avg_vehicles_per_frame": round(float(avg_vehicles_per_frame), 2),
            "congestion_alert": congestion_alert,
            "peak_hour": peak_hour,
            "output_path": output_path
        }

        print(f"\nProcessing complete!")
        print(f"Total vehicles detected: {total_vehicles}")
        print(f"Traffic density: {traffic_density.upper()}")
        print(f"Processing time: {processing_time:.2f}s")

        return result

    def process_frame_realtime(self, frame: np.ndarray) -> Tuple[np.ndarray, Dict]:
        """Process a single frame for real-time streaming"""

        boxes, class_ids, confidences = self.detect_frame(frame)

        counts = {"car": 0, "motorcycle": 0, "bus": 0, "truck": 0}
        for cls_id in class_ids:
            label = self.VEHICLE_CLASSES.get(cls_id)
            if label:
                counts[label] += 1

        annotated = self.draw_annotations(frame, boxes, class_ids, confidences, counts)

        return annotated, counts
