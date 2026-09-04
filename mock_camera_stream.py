#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
=============================================================================
MÔ PHỎNG CAMERA TRẠM (MOCK CAMERA CLIENT CHO MÁY CHỦ VPS / DEV)
=============================================================================
Mục đích:
  - Dùng để kiểm thử (Test) toàn bộ hệ thống Camera On-Demand trên VPS khi chưa có
    Mini PC và Camera thật tại hiện trường.
  - Script này đóng vai trò là "Máy Trạm ảo":
      + Kết nối MQTT Broker trên VPS (127.0.0.1:1883).
      + Lắng nghe lệnh START_STREAM / STOP_STREAM / PTZ_CONTROL từ Laravel.
      + Khi nhận START_STREAM: Dùng FFmpeg sinh một màn hình video test màu
        (testsrc kèm đồng hồ thời gian) rồi đẩy (Push) vào MediaMTX (RTMP port 1935).
      + Trả về ACK với URL HLS / WebRTC để Web Dashboard có thể phát video thật 100%!
=============================================================================
"""

import os
import sys
import time
import json
import signal
import logging
import datetime
import subprocess
import paho.mqtt.client as mqtt

def load_dotenv(path=None):
    if path is None:
        path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
    if os.path.isfile(path):
        try:
            with open(path, "r", encoding="utf-8") as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith("#") and "=" in line:
                        key, val = line.split("=", 1)
                        key = key.strip()
                        val = val.strip().strip("'\"")
                        if key not in os.environ:
                            os.environ[key] = val
        except Exception:
            pass

load_dotenv()

STATION_CODE = os.getenv("STATION_CODE", "ST-PHUCHOA-01")
MQTT_HOST    = os.getenv("MQTT_BROKER_HOST", "127.0.0.1")
MQTT_PORT    = int(os.getenv("MQTT_BROKER_PORT", 1883))
RTMP_PORT    = int(os.getenv("RTMP_PORT", 1935))
HLS_PORT     = int(os.getenv("HLS_PORT", 8888))
WEBRTC_PORT  = int(os.getenv("WEBRTC_PORT", 8889))

TOPIC_COMMAND = f"khcn/stations/{STATION_CODE}/camera/command"
TOPIC_ACK     = f"khcn/stations/{STATION_CODE}/camera/ack"
TOPIC_STATUS  = f"khcn/stations/{STATION_CODE}/camera/status"

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] [MOCK-CAM-%(levelname)s] %(message)s',
    datefmt='%H:%M:%S'
)

current_ffmpeg = None
current_cam_id = None

def start_mock_ffmpeg(cam_id: str):
    global current_ffmpeg, current_cam_id
    stop_mock_ffmpeg()

    stream_key = f"{STATION_CODE}_{cam_id}"
    rtmp_target = f"rtmp://{MQTT_HOST}:{RTMP_PORT}/live/{stream_key}"

    # Lệnh FFmpeg tạo luồng video test đồ họa kèm chữ hiển thị thời gian thực
    cmd = [
        "ffmpeg",
        "-re",
        "-f", "lavfi",
        "-i", f"testsrc=size=1280x720:rate=25",
        "-vf", f"drawtext=text='{STATION_CODE} - {cam_id.upper()} | %{{localtime}}':fontcolor=white:fontsize=36:box=1:boxcolor=black@0.6:x=30:y=30",
        "-c:v", "libx264",
        "-preset", "ultrafast",
        "-tune", "zerolatency",
        "-pix_fmt", "yuv420p",
        "-f", "flv",
        rtmp_target
    ]

    logging.info(f"[*] Đang khởi chạy FFmpeg Test Stream -> {rtmp_target}")
    try:
        current_ffmpeg = subprocess.Popen(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        current_cam_id = cam_id
        logging.info(f"✓ Luồng giả lập đang phát! (PID: {current_ffmpeg.pid})")
        return True
    except Exception as e:
        logging.error(f"✗ Không thể chạy FFmpeg: {e}")
        return False

def stop_mock_ffmpeg():
    global current_ffmpeg, current_cam_id
    if current_ffmpeg and current_ffmpeg.poll() is None:
        logging.info(f"[*] Dừng luồng FFmpeg (PID: {current_ffmpeg.pid})...")
        current_ffmpeg.terminate()
        try:
            current_ffmpeg.wait(timeout=2)
        except Exception:
            current_ffmpeg.kill()
        logging.info("✓ Đã dừng luồng giả lập.")
    current_ffmpeg = None
    current_cam_id = None

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        logging.info(f"✓ Đã kết nối MQTT Broker tại {MQTT_HOST}:{MQTT_PORT}")
        client.subscribe(TOPIC_COMMAND, qos=1)
        logging.info(f"✓ Đang lắng nghe lệnh tại: {TOPIC_COMMAND}")
        # Báo status sẵn sàng
        status_payload = {
            "station_code": STATION_CODE,
            "service": "mock_camera_station",
            "status": "ready",
            "cameras_available": ["cam_1", "cam_2"]
        }
        client.publish(TOPIC_STATUS, json.dumps(status_payload), qos=1, retain=True)
    else:
        logging.error(f"✗ Kết nối MQTT thất bại: rc={rc}")

def on_message(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode('utf-8'))
        command_id = data.get("command_id", "CMD-TEST")
        action = data.get("action", "").upper()
        params = data.get("params", {})
        cam_id = params.get("camera_id", "cam_1")

        logging.info(f"[LỆNH NHẬN ĐƯỢC] {action} (Camera: {cam_id})")

        success = True
        message = ""
        extra_data = {}

        if action == "START_STREAM":
            duration = int(params.get("duration_seconds", 180))
            ok = start_mock_ffmpeg(cam_id)
            stream_key = f"{STATION_CODE}_{cam_id}"
            success = ok
            message = f"Bật luồng test {cam_id} thành công" if ok else "Lỗi khởi chạy FFmpeg"
            extra_data = {
                "stream_key": stream_key,
                "hls_url": f"http://{MQTT_HOST}:{HLS_PORT}/live/{stream_key}/index.m3u8",
                "webrtc_url": f"http://{MQTT_HOST}:{WEBRTC_PORT}/live/{stream_key}",
                "expire_at": time.time() + duration
            }

        elif action == "STOP_STREAM":
            stop_mock_ffmpeg()
            success = True
            message = f"Đã dừng luồng camera {cam_id}"

        elif action == "PTZ_CONTROL":
            direction = params.get("direction", "STOP")
            logging.info(f"-> Giả lập xoay PTZ: {direction}")
            success = True
            message = f"Mô phỏng PTZ {direction} thành công"

        ack_payload = {
            "command_id": command_id,
            "station_code": STATION_CODE,
            "action": action,
            "camera_id": cam_id,
            "success": success,
            "message": message,
            "data": extra_data,
            "timestamp": datetime.datetime.now().astimezone().isoformat()
        }
        client.publish(TOPIC_ACK, json.dumps(ack_payload, ensure_ascii=False), qos=1)
        logging.info(f"[ĐÃ GỬI PHẢN HỒI ACK] {action} -> Success: {success}")

    except Exception as e:
        logging.error(f"Lỗi xử lý message: {e}")

def main():
    logging.info("======================================================")
    logging.info(f"   KHỞI ĐỘNG MOCK CAMERA CLIENT: {STATION_CODE}       ")
    logging.info("======================================================")
    client = mqtt.Client(client_id=f"mock_cam_{STATION_CODE}")
    client.on_connect = on_connect
    client.on_message = on_message

    try:
        client.connect(MQTT_HOST, MQTT_PORT, keepalive=60)
    except Exception as e:
        logging.error(f"Không thể kết nối MQTT: {e}")
        sys.exit(1)

    client.loop_start()

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        logging.info("Đang dừng mock camera...")
    finally:
        stop_mock_ffmpeg()
        client.loop_stop()
        client.disconnect()

if __name__ == "__main__":
    main()
