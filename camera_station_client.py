#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
=============================================================================
HỆ THỐNG QUAN TRẮC IoT NÔNG NGHIỆP THÔNG MINH BẮC NINH
MÃ NGUỒN ĐIỀU KHIỂN & STREAMING CAMERA HIỆN TRƯỜNG (ON-DEMAND)
=============================================================================
Mục đích:
  - Chạy trên máy Mini PC (Ubuntu) tại 4 trạm quan trắc, kết nối Internet qua SIM 4G.
  - Mini PC nối Switch LAN với 2 Camera IP (RTSP nội bộ 192.168.1.x).
  - Tối ưu hóa băng thông & chi phí 4G bằng cơ chế ON-DEMAND STREAMING:
      + Bình thường: KHÔNG phát video lên VPS (tiêu thụ 0 MB data).
      + Khi có người xem trên Web: Server gửi lệnh MQTT START_STREAM.
      + Trạm dùng FFmpeg đẩy luồng (Push RTMP/RTSP) lên Media Server trên VPS.
      + Tự động ngắt luồng sau N giây (Watchdog Timeout) hoặc khi nhận STOP_STREAM.
  - Hỗ trợ chụp ảnh (Snapshot) gửi về VPS theo chu kỳ hoặc theo lệnh.
  - Hỗ trợ điều khiển góc quay PTZ (nếu camera hỗ trợ Onvif / HTTP API).

Yêu cầu môi trường trên Mini PC (Ubuntu):
  sudo apt update && sudo apt install -y python3-pip ffmpeg
  pip3 install paho-mqtt requests
=============================================================================
"""

import os
import sys
import time
import json
import signal
import logging
import threading
import subprocess
import datetime
import urllib.request
import paho.mqtt.client as mqtt

# =============================================================================
# 0. TỰ ĐỘNG NẠP BIẾN MÔI TRƯỜNG TỪ FILE .env (NẾU CÓ)
# =============================================================================
def load_dotenv(path=None):
    """Nạp biến môi trường từ file .env cùng thư mục để bảo mật thông tin nhạy cảm."""
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
        except Exception as e:
            print(f"[WARN] Không thể đọc file .env: {e}")

load_dotenv()

# =============================================================================
# 1. CẤU HÌNH TRẠM, MQTT BROKER & STREAMING SERVER (TẤT CẢ LẤY TỪ .ENV HOẶC OS ENV)
# =============================================================================
STATION_CODE = os.getenv("STATION_CODE", "ST-PHUCHOA-01")

# Cấu hình MQTT Broker trên VPS (Mặc định để localhost và cổng chuẩn mẫu)
MQTT_BROKER_HOST = os.getenv("MQTT_BROKER_HOST", "127.0.0.1")
MQTT_BROKER_PORT = int(os.getenv("MQTT_BROKER_PORT", 1883))
MQTT_USERNAME    = os.getenv("MQTT_USERNAME", "")
MQTT_PASSWORD    = os.getenv("MQTT_PASSWORD", "")

# Cấu hình Media Server (MediaMTX / RTMP Server trên VPS)
MEDIA_SERVER_HOST = os.getenv("MEDIA_SERVER_HOST", "127.0.0.1")
RTMP_PORT         = int(os.getenv("RTMP_PORT", 1935))
HLS_PORT          = int(os.getenv("HLS_PORT", 8888))
WEBRTC_PORT       = int(os.getenv("WEBRTC_PORT", 8889))
MEDIA_API_URL     = os.getenv("MEDIA_API_URL", "http://127.0.0.1:8000/api/iot/camera/image")

# Cấu hình Topics MQTT cho Camera
TOPIC_CAM_COMMAND = f"khcn/stations/{STATION_CODE}/camera/command"
TOPIC_CAM_ACK     = f"khcn/stations/{STATION_CODE}/camera/ack"
TOPIC_CAM_STATUS  = f"khcn/stations/{STATION_CODE}/camera/status"

# =============================================================================
# 2. DANH SÁCH 2 CAMERA TRONG MẠNG LAN NỘI BỘ (QUA SWITCH)
# =============================================================================
# Đọc URL RTSP từ .env: CAM1_RTSP, CAM1_SUB_RTSP, CAM2_RTSP, CAM2_SUB_RTSP
CAMERAS = {
    "cam_1": {
        "id": "cam_1",
        "name": "Camera 01 - Giám sát toàn cảnh trạm",
        "rtsp_url": os.getenv("CAM1_RTSP", "rtsp://admin:password@192.168.1.101:554/stream1"),
        "sub_rtsp_url": os.getenv("CAM1_SUB_RTSP", "rtsp://admin:password@192.168.1.101:554/stream2"),
        "ptz_ip": os.getenv("CAM1_IP", "192.168.1.101")
    },
    "cam_2": {
        "id": "cam_2",
        "name": "Camera 02 - Soi cận cảnh cây trồng / sensor",
        "rtsp_url": os.getenv("CAM2_RTSP", "rtsp://admin:password@192.168.1.102:554/stream1"),
        "sub_rtsp_url": os.getenv("CAM2_SUB_RTSP", "rtsp://admin:password@192.168.1.102:554/stream2"),
        "ptz_ip": os.getenv("CAM2_IP", "192.168.1.102")
    }
}

# Thư mục lưu snapshot tạm trước khi upload
SNAPSHOT_DIR = os.path.join(os.path.dirname(__file__), "camera_snapshots")
os.makedirs(SNAPSHOT_DIR, exist_ok=True)

# Cấu hình logging
logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] [CAMERA-%(levelname)s] %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)

# =============================================================================
# 3. QUẢN LÝ TIẾN TRÌNH STREAMING ON-DEMAND (FFMPEG SUBPROCESS & TIMEOUT)
# =============================================================================
class CameraStreamManager:
    """Quản lý các tiến trình đẩy luồng video lên Media Server theo nhu cầu."""

    def __init__(self, mqtt_client):
        self.mqtt_client = mqtt_client
        # Lưu tiến trình: { "cam_1": {"proc": Popen, "timer": Timer, "expire_at": float} }
        self.active_streams = {}
        self.lock = threading.Lock()

    def start_stream(self, cam_id: str, duration_sec: int = 180, quality: str = "sub") -> tuple[bool, str, dict]:
        """
        Bật đẩy luồng video từ RTSP LAN lên VPS Media Server.
        - duration_sec: Tự động ngắt sau N giây nếu người dùng không gia hạn (bảo vệ data 4G).
        - quality: 'sub' (luồng phụ nhẹ, khuyến nghị cho 4G) hoặc 'main' (luồng chính HD).
        """
        if cam_id not in CAMERAS:
            return False, f"Không tìm thấy cấu hình camera [{cam_id}].", {}

        cam_config = CAMERAS[cam_id]
        rtsp_source = cam_config["sub_rtsp_url"] if quality == "sub" else cam_config["rtsp_url"]
        
        # Đường dẫn RTMP PUSH lên VPS: rtmp://<VPS_IP>:1935/live/<STATION_CODE>_<cam_id>
        stream_key = f"{STATION_CODE}_{cam_id}"
        rtmp_target = f"rtmp://{MEDIA_SERVER_HOST}:{RTMP_PORT}/live/{stream_key}"

        with self.lock:
            # Nếu đang stream thì hủy timer cũ và gia hạn thêm thời gian
            if cam_id in self.active_streams:
                logging.info(f"[*] Camera [{cam_id}] đang stream, gia hạn thêm {duration_sec}s...")
                current = self.active_streams[cam_id]
                if current.get("timer"):
                    current["timer"].cancel()
                
                expire_at = time.time() + duration_sec
                timer = threading.Timer(duration_sec, self._on_stream_timeout, args=[cam_id])
                timer.daemon = True
                timer.start()
                current["timer"] = timer
                current["expire_at"] = expire_at

                return True, f"Đã gia hạn luồng {cam_id} thêm {duration_sec}s", {
                    "stream_key": stream_key,
                    "stream_url_rtmp": rtmp_target,
                    "hls_url": f"http://{MEDIA_SERVER_HOST}:{HLS_PORT}/live/{stream_key}/index.m3u8",
                    "webrtc_url": f"http://{MEDIA_SERVER_HOST}:{WEBRTC_PORT}/live/{stream_key}",
                    "expire_at": expire_at
                }

            # Khởi chạy tiến trình FFmpeg để PUSH stream qua 4G lên VPS
            # Lệnh FFmpeg copy codec không encode lại để giảm tải CPU của Mini PC
            ffmpeg_cmd = [
                "ffmpeg",
                "-loglevel", "warning",
                "-rtsp_transport", "tcp",       # Dùng TCP chống rụng gói trên mạng Switch
                "-i", rtsp_source,              # RTSP input từ camera LAN
                "-c:v", "copy",                 # Copy stream video trực tiếp (H.264), không decode
                "-c:a", "aac",                  # Chuẩn hóa âm thanh AAC nếu có
                "-f", "flv",                    # Đóng gói FLV gửi qua RTMP
                rtmp_target
            ]

            logging.info(f"[*] Đang kích hoạt FFmpeg stream cho [{cam_id}] -> {rtmp_target}")
            try:
                proc = subprocess.Popen(
                    ffmpeg_cmd,
                    stdout=subprocess.DEVNULL,
                    stderr=subprocess.PIPE,
                    preexec_fn=os.setsid if hasattr(os, 'setsid') else None
                )
            except FileNotFoundError:
                logging.error("✗ Lệnh 'ffmpeg' chưa được cài đặt trên Mini PC!")
                return False, "Hệ thống trạm chưa cài đặt FFmpeg.", {}
            except Exception as e:
                logging.error(f"✗ Không thể khởi chạy FFmpeg: {e}")
                return False, f"Lỗi khởi chạy FFmpeg: {str(e)}", {}

            # Hẹn giờ tự động tắt sau duration_sec để tiết kiệm 4G
            expire_at = time.time() + duration_sec
            timer = threading.Timer(duration_sec, self._on_stream_timeout, args=[cam_id])
            timer.daemon = True
            timer.start()

            self.active_streams[cam_id] = {
                "proc": proc,
                "timer": timer,
                "expire_at": expire_at,
                "stream_key": stream_key
            }

            logging.info(f"✓ Đã bật stream On-Demand [{cam_id}]. Tự động tắt sau {duration_sec}s.")
            return True, f"Bật luồng trực tiếp thành công ({duration_sec}s)", {
                "stream_key": stream_key,
                "stream_url_rtmp": rtmp_target,
                "hls_url": f"http://{MEDIA_SERVER_HOST}:{HLS_PORT}/live/{stream_key}/index.m3u8",
                "webrtc_url": f"http://{MEDIA_SERVER_HOST}:{WEBRTC_PORT}/live/{stream_key}",
                "expire_at": expire_at
            }

    def stop_stream(self, cam_id: str) -> tuple[bool, str]:
        """Tắt stream camera và giải phóng băng thông 4G."""
        with self.lock:
            if cam_id not in self.active_streams:
                return True, f"Camera [{cam_id}] hiện không phát luồng."

            item = self.active_streams.pop(cam_id)
            if item.get("timer"):
                item["timer"].cancel()

            proc = item.get("proc")
            if proc and proc.poll() is None:
                logging.info(f"[*] Đang dừng tiến trình FFmpeg của [{cam_id}] (PID: {proc.pid})...")
                try:
                    if hasattr(os, 'killpg'):
                        os.killpg(os.getpgid(proc.pid), signal.SIGTERM)
                    else:
                        proc.terminate()
                    proc.wait(timeout=3)
                except Exception:
                    proc.kill()

            logging.info(f"✓ Đã dừng stream camera [{cam_id}], tiết kiệm data 4G.")
            return True, f"Đã dừng luồng camera [{cam_id}]."

    def _on_stream_timeout(self, cam_id: str):
        """Hết thời gian xem mà không gia hạn -> tự động ngắt luồng."""
        logging.info(f"[TIMEOUT] Luồng [{cam_id}] hết thời gian On-Demand. Tiến hành ngắt luồng...")
        self.stop_stream(cam_id)
        
        # Báo cho VPS biết luồng đã tắt
        status_payload = {
            "station_code": STATION_CODE,
            "camera_id": cam_id,
            "event": "stream_stopped",
            "reason": "timeout_auto_kill",
            "timestamp": datetime.datetime.now().astimezone().isoformat()
        }
        self.mqtt_client.publish(TOPIC_CAM_STATUS, json.dumps(status_payload), qos=1)

    def capture_snapshot(self, cam_id: str) -> tuple[bool, str, str]:
        """Chụp 1 ảnh tĩnh từ RTSP camera qua FFmpeg."""
        if cam_id not in CAMERAS:
            return False, f"Không tìm thấy camera [{cam_id}]", ""

        cam_config = CAMERAS[cam_id]
        rtsp_url = cam_config["rtsp_url"]
        timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"{STATION_CODE}_{cam_id}_{timestamp}.jpg"
        filepath = os.path.join(SNAPSHOT_DIR, filename)

        cmd = [
            "ffmpeg",
            "-y",
            "-rtsp_transport", "tcp",
            "-i", rtsp_url,
            "-vframes", "1",
            "-q:v", "2",
            filepath
        ]
        logging.info(f"[*] Đang chụp ảnh snapshot từ {cam_id}...")
        try:
            res = subprocess.run(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.PIPE, timeout=10)
            if res.returncode == 0 and os.path.exists(filepath):
                logging.info(f"✓ Chụp ảnh thành công: {filepath}")
                return True, "Chụp ảnh thành công", filepath
            else:
                return False, "FFmpeg không lấy được frame ảnh từ RTSP", ""
        except Exception as e:
            return False, f"Lỗi chụp snapshot: {str(e)}", ""

    def cleanup_all(self):
        """Dừng toàn bộ luồng khi tắt client."""
        cam_ids = list(self.active_streams.keys())
        for cid in cam_ids:
            self.stop_stream(cid)

# =============================================================================
# 4. HÀM ĐIỀU KHIỂN QUAY GÓC CAMERA (PTZ CONTROL)
# =============================================================================
def execute_ptz(cam_id: str, action: str, speed: int = 5) -> tuple[bool, str]:
    """
    Điều khiển quay quét Camera PTZ trong mạng LAN qua HTTP/ONVIF API.
    Action: UP, DOWN, LEFT, RIGHT, ZOOM_IN, ZOOM_OUT, STOP.
    """
    if cam_id not in CAMERAS:
        return False, f"Camera [{cam_id}] không tồn tại."

    cam_ip = CAMERAS[cam_id].get("ptz_ip")
    logging.info(f"-> [PTZ] Điều khiển {cam_id} ({cam_ip}) -> Thao tác: {action} (Tốc độ: {speed})")

    # Ví dụ tích hợp gọi API chuẩn Hikvision / Dahua / ONVIF:
    # URL Dahua: http://{cam_ip}/cgi-bin/ptz.cgi?action=start&channel=1&code={action}&arg1=0&arg2={speed}&arg3=0
    # URL Hikvision ISAPI: http://{cam_ip}/ISAPI/PTZCtrl/channels/1/continuous
    # Ở đây giả lập ghi log xử lý thành công
    return True, f"Đã gửi lệnh PTZ [{action}] tới camera {cam_id} ({cam_ip})."

# =============================================================================
# 5. XỬ LÝ LỆNH TỪ MQTT (MQTT CALLBACKS)
# =============================================================================
stream_manager = None

def on_connect(client, userdata, flags, rc):
    """Kết nối thành công với Mosquitto Broker trên VPS."""
    if rc == 0:
        logging.info(f"✓ Kết nối MQTT Broker thành công [{MQTT_BROKER_HOST}:{MQTT_BROKER_PORT}]")
        client.subscribe(TOPIC_CAM_COMMAND, qos=1)
        logging.info(f"✓ Đã subscribe topic lệnh camera: [{TOPIC_CAM_COMMAND}]")

        # Báo Online trạng thái dịch vụ Camera của trạm
        init_payload = {
            "station_code": STATION_CODE,
            "service": "camera_controller",
            "status": "ready",
            "cameras_available": list(CAMERAS.keys()),
            "connected_at": datetime.datetime.now().astimezone().isoformat()
        }
        client.publish(TOPIC_CAM_STATUS, json.dumps(init_payload), qos=1, retain=True)
    else:
        logging.error(f"✗ Kết nối MQTT Broker thất bại, rc={rc}")

def on_message(client, userdata, msg):
    """Nhận và điều phối lệnh điều khiển Camera từ Server."""
    global stream_manager
    try:
        payload_str = msg.payload.decode('utf-8')
        logging.info(f"[NHẬN LỆNH CAMERA] Topic: {msg.topic} | Payload: {payload_str}")

        data = json.loads(payload_str)
        command_id = data.get("command_id", f"CAM-CMD-{int(time.time())}")
        action = data.get("action", "").upper()
        params = data.get("params", {})
        cam_id = params.get("camera_id", "cam_1")

        success = False
        message = ""
        extra_data = {}

        # 1. Bật stream xem trực tiếp On-Demand
        if action == "START_STREAM":
            duration = int(params.get("duration_seconds", 180)) # Mặc định xem 3 phút tự ngắt
            quality = params.get("quality", "sub")               # Mặc định dùng sub-stream nhẹ
            success, message, extra_data = stream_manager.start_stream(cam_id, duration, quality)

        # 2. Tắt stream thủ công khi người dùng đóng trình xem
        elif action == "STOP_STREAM":
            success, message = stream_manager.stop_stream(cam_id)

        # 3. Chụp ảnh tức thời (Snapshot)
        elif action == "CAPTURE_SNAPSHOT":
            success, message, file_path = stream_manager.capture_snapshot(cam_id)
            if success:
                extra_data["local_path"] = file_path
                extra_data["filename"] = os.path.basename(file_path)

        # 4. Điều khiển PTZ (Xoay, Thu phóng)
        elif action == "PTZ_CONTROL":
            ptz_action = params.get("direction", "STOP").upper()
            speed = int(params.get("speed", 5))
            success, message = execute_ptz(cam_id, ptz_action, speed)

        # 5. Lấy trạng thái danh sách Camera
        elif action == "GET_STATUS":
            active_list = list(stream_manager.active_streams.keys())
            success = True
            message = "Lấy trạng thái camera thành công"
            extra_data = {
                "active_streams": active_list,
                "configured_cameras": list(CAMERAS.keys())
            }

        else:
            success = False
            message = f"Hành động không hợp lệ: {action}"

        # Gửi phản hồi ACK về cho Laravel Server
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
        client.publish(TOPIC_CAM_ACK, json.dumps(ack_payload, ensure_ascii=False), qos=1)
        logging.info(f"[GỬI ACK CAMERA] Lệnh {command_id} ({action}) -> {success}: {message}")

    except Exception as e:
        logging.error(f"✗ Lỗi khi xử lý lệnh MQTT: {e}")

# =============================================================================
# 6. HÀM MAIN KHỞI CHẠY TIẾN TRÌNH CAMERA CLIENT
# =============================================================================
def main():
    global stream_manager
    logging.info("=================================================================")
    logging.info(f" KHỞI ĐỘNG DỊCH VỤ QUẢN LÝ CAMERA ON-DEMAND: {STATION_CODE}")
    logging.info("=================================================================")
    logging.info(f"MQTT Broker   : {MQTT_BROKER_HOST}:{MQTT_BROKER_PORT}")
    logging.info(f"Media Server  : {MEDIA_SERVER_HOST}:{RTMP_PORT}")
    logging.info(f"Số lượng cam  : {len(CAMERAS)} camera qua switch LAN")
    logging.info("=================================================================")

    client = mqtt.Client(
        client_id=f"camera_agent_{STATION_CODE}_{int(time.time())}",
        clean_session=False
    )

    if MQTT_USERNAME and MQTT_PASSWORD:
        client.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)

    # LWT báo offline khi dịch vụ bị crash hoặc ngắt kết nối
    lwt_payload = {
        "station_code": STATION_CODE,
        "service": "camera_controller",
        "status": "offline",
        "timestamp": datetime.datetime.now().astimezone().isoformat()
    }
    client.will_set(TOPIC_CAM_STATUS, json.dumps(lwt_payload), qos=1, retain=True)

    client.on_connect = on_connect
    client.on_message = on_message

    stream_manager = CameraStreamManager(client)

    try:
        client.connect(MQTT_BROKER_HOST, MQTT_BROKER_PORT, keepalive=60)
    except Exception as e:
        logging.error(f"✗ Không thể kết nối MQTT Broker tại {MQTT_BROKER_HOST}:{MQTT_BROKER_PORT}: {e}")
        sys.exit(1)

    client.loop_start()

    # Giữ tiến trình chạy nền
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        logging.info("Đang tắt tiến trình camera theo yêu cầu...")
    finally:
        if stream_manager:
            stream_manager.cleanup_all()
        client.loop_stop()
        client.disconnect()
        logging.info("✓ Dịch vụ camera đã dừng an toàn.")

if __name__ == "__main__":
    main()
