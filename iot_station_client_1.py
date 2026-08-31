#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
=============================================================================
HỆ THỐNG QUAN TRẮC IoT NÔNG NGHIỆP THÔNG MINH BẮC NINH
MÃ NGUỒN MẪU CHO MÁY TRẠM HIỆN TRƯỜNG (EDGE STATION CLIENT)
=============================================================================
Mục đích:
  - Chạy trên máy tính trạm (Raspberry Pi / Industrial PC / Mini PC) cắm SIM 4G.
  - Đọc dữ liệu từ các cảm biến qua cổng chuyển đổi RS485/Modbus/USB.
  - Kết nối và duy trì kết nối 2 chiều với Mosquitto MQTT Broker trên Server.
  - Định kỳ publish dữ liệu vi khí hậu & môi trường đất (Telemetry).
  - Lắng nghe và thực thi các lệnh điều khiển từ Server gửi xuống (Command).
  - Tự động phản hồi kết quả thực thi lệnh (ACK).
  - Tự động báo Offline khi mất nguồn/mất mạng 4G thông qua MQTT LWT (Last Will).

Yêu cầu môi trường Python tại trạm:
  pip install paho-mqtt
=============================================================================
"""

import sys
import time
import json
import random
import datetime
import logging
import paho.mqtt.client as mqtt

# =============================================================================
# 1. CẤU HÌNH TRẠM VÀ KẾT NỐI MQTT (Thay đổi theo thực tế trạm)
# =============================================================================
STATION_CODE = "TPH-01"        # Mã định danh trạm (khớp với mã trạm trên web Laravel)
MQTT_BROKER_HOST = "117.6.44.206"     # IP Public của máy chủ chạy Mosquitto Broker
MQTT_BROKER_PORT = 9070               # Cổng MQTT Broker (được cấp cổng 9070)
MQTT_USERNAME = "iastadmin"           # Tên tài khoản MQTT
MQTT_PASSWORD = "iast@6688"           # Mật khẩu MQTT



DEFAULT_DATA_INTERVAL = 60            # Chu kỳ gửi dữ liệu mặc định (giây)
CURRENT_INTERVAL = DEFAULT_DATA_INTERVAL

# Cấu hình Topics MQTT theo chuẩn hệ thống
TOPIC_TELEMETRY = f"khcn/stations/{STATION_CODE}/telemetry"  # Trạm -> Server: Bắn dữ liệu cảm biến
TOPIC_STATUS    = f"khcn/stations/{STATION_CODE}/status"     # Trạm -> Server: Báo Online / Offline (LWT)
TOPIC_COMMAND   = f"khcn/stations/{STATION_CODE}/command"    # Server -> Trạm: Nhận lệnh điều khiển
TOPIC_ACK       = f"khcn/stations/{STATION_CODE}/ack"        # Trạm -> Server: Báo kết quả thực thi lệnh

# Cấu hình logging ra màn hình terminal
logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] [%(levelname)s] %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S',
    stream=sys.stdout
)


# =============================================================================
# 2. HÀM ĐỌC DỮ LIỆU CẢM BIẾN TỪ CỔNG CHUYỂN ĐỔI (RS485 / MODBUS / USB)
# =============================================================================
def read_sensor_readings():
    """
    HÀM NÀY LÀ NƠI BẠN TÍCH HỢP CODE ĐỌC CỔNG COM/SERIAL/MODBUS THỰC TẾ.
    
    Ví dụ thực tế khi gắn RS485 to USB (dùng minimalmodbus hoặc pyserial):
      import minimalmodbus
      instrument = minimalmodbus.Instrument('/dev/ttyUSB0', 1) # Port & Slave Address
      temperature = instrument.read_register(0, 1) / 10.0
      humidity = instrument.read_register(1, 1) / 10.0
      
    Dưới đây là dữ liệu mẫu giả lập mô phỏng giá trị thực tế:
    """
    # Sinh dữ liệu mô phỏng trong ngưỡng thực tế (khớp 6 cảm biến tiêu chuẩn)
    temp = round(25.0 + random.uniform(0.5, 4.0), 1)        # Cảm biến ES-INTEGRATE-ODR-01: Nhiệt độ không khí (°C)
    humidity = round(75.0 + random.uniform(2.0, 15.0), 1)   # Cảm biến ES-INTEGRATE-ODR-01: Độ ẩm không khí (%)
    rain = round(random.uniform(0.0, 2.5), 1)               # Cảm biến ES-RAINF-01: Lượng mưa (mm)
    light = random.randint(12000, 35000)                    # Cảm biến ES-ALS20: Cường độ ánh sáng (Lux)
    wind = round(random.uniform(0.8, 3.5), 1)               # Cảm biến ES-WS-02: Tốc độ gió (m/s)
    soil_ph = round(6.2 + random.uniform(-0.3, 0.4), 1)     # Cảm biến ES-PH-SOIL-01: Độ pH của đất
    soil_moist = round(70.0 + random.uniform(1.0, 10.0), 1) # Cảm biến ES-SM-TH-01: Độ ẩm đất (%)
    soil_temp = round(24.0 + random.uniform(0.5, 3.0), 1)   # Cảm biến ES-SM-TH-01: Nhiệt độ đất (°C)
    battery_v = round(12.2 + random.uniform(-0.4, 0.4), 2)  # Điện áp ắc quy/pin mặt trời (V)
    signal_csq = random.randint(18, 31)                     # Chất lượng sóng 4G (0-31)

    readings = [
        {"device_code": "TEMP_AIR_01",   "name": "Nhiệt độ không khí",  "value": temp,       "unit": "°C",  "status": "ok"},
        {"device_code": "HUM_AIR_01",    "name": "Độ ẩm không khí",     "value": humidity,   "unit": "%",   "status": "ok"},
        {"device_code": "RAIN_01",       "name": "Lưu lượng mưa",       "value": rain,       "unit": "mm",  "status": "ok"},
        {"device_code": "LIGHT_01",      "name": "Cường độ ánh sáng",   "value": light,      "unit": "Lux", "status": "ok"},
        {"device_code": "WIND_01",       "name": "Tốc độ gió",          "value": wind,       "unit": "m/s", "status": "ok"},
        {"device_code": "SOIL_PH_01",    "name": "Độ pH đất",           "value": soil_ph,    "unit": "pH",  "status": "ok"},
        {"device_code": "SOIL_MOIST_01", "name": "Độ ẩm đất",          "value": soil_moist, "unit": "%",   "status": "ok"},
        {"device_code": "SOIL_TEMP_01",  "name": "Nhiệt độ đất",        "value": soil_temp,  "unit": "°C",  "status": "ok"},
    ]

    return readings, battery_v, signal_csq



# =============================================================================
# 3. CÁC HÀM XỬ LÝ LỆNH ĐIỀU KHIỂN TỪ SERVER (2-WAY CONTROL)
# =============================================================================
def execute_command(action: str, params: dict) -> tuple[bool, str]:
    """
    Thực thi hành động theo yêu cầu từ Server.
    Trả về (thành_công: bool, thông_điệp: str).
    """
    global CURRENT_INTERVAL
    action = action.upper()
    logging.info(f"-> Đang thực thi hành động: [{action}] với params: {params}")

    if action == "SET_INTERVAL":
        # Đổi chu kỳ đọc & gửi dữ liệu
        new_interval = int(params.get("interval_seconds", DEFAULT_DATA_INTERVAL))
        if new_interval < 5:
            return False, "Chu kỳ tối thiểu là 5 giây."
        CURRENT_INTERVAL = new_interval
        logging.info(f"★★★ [ĐỔI CHU KỲ THÀNH CÔNG] Đã chuyển chu kỳ trạm {STATION_CODE} thành {CURRENT_INTERVAL} giây ★★★")
        return True, f"Đã cập nhật chu kỳ gửi dữ liệu thành {CURRENT_INTERVAL} giây."


    elif action == "TRIGGER_COLLECT":
        # Yêu cầu đọc và gửi dữ liệu ngay tức thì
        return True, "Đã kích hoạt thu thập và gửi dữ liệu ngay lập tức."

    elif action == "CONTROL_ACTUATOR":
        # Điều khiển rơ-le / bơm tưới / đèn / quạt
        device_code = params.get("device_code", "UNKNOWN")
        state = params.get("state", "OFF").upper()
        # TODO: Gọi hàm bật tắt chân GPIO hoặc Module Relay Modbus tại đây
        logging.info(f"   [RELAY] Điều khiển thiết bị {device_code} -> Trạng thái: {state}")
        return True, f"Đã chuyển trạng thái thiết bị {device_code} thành {state}."

    elif action == "PTZ_CAMERA":
        # Điều khiển quay góc camera PTZ
        pan = params.get("pan", 0.0)
        tilt = params.get("tilt", 0.0)
        # TODO: Gửi lệnh RS485 Pelco-D/P hoặc ONVIF điều khiển camera PTZ
        return True, f"Đã điều khiển camera xoay đến góc Pan={pan}, Tilt={tilt}."

    elif action == "REBOOT_STATION":
        # Khởi động lại máy trạm
        logging.warning("   [REBOOT] Nhận lệnh khởi động lại trạm...")
        return True, "Trạm sẽ khởi động lại sau 5 giây."

    else:
        return False, f"Hành động không xác định hoặc chưa được hỗ trợ: {action}"


# =============================================================================
# 4. MQTT CALLBACKS
# =============================================================================
def on_connect(client, userdata, flags, rc):
    """Callback khi trạm kết nối thành công với Mosquitto Broker."""
    if rc == 0:
        logging.info(f"✓ Kết nối thành công tới Mosquitto Broker [{MQTT_BROKER_HOST}:{MQTT_BROKER_PORT}]")
        
        # 1. Đăng ký nhận lệnh điều khiển từ Server
        client.subscribe(TOPIC_COMMAND, qos=1)
        logging.info(f"✓ Đã subscribe topic nhận lệnh: [{TOPIC_COMMAND}]")

        # 2. Phát thông điệp ONLINE lên Broker (Retain = True để Server/Client vào sau vẫn biết)
        online_payload = {
            "station_code": STATION_CODE,
            "status": "online",
            "connected_at": datetime.datetime.now().astimezone().isoformat(),
            "firmware_version": "1.0.0"
        }
        client.publish(TOPIC_STATUS, json.dumps(online_payload), qos=1, retain=True)
        logging.info(f"✓ Đã phát tín hiệu ONLINE trạm [{STATION_CODE}]")
    else:
        logging.error(f"✗ Kết nối MQTT Broker thất bại, mã lỗi (rc): {rc}")


def on_message(client, userdata, msg):
    """Callback khi nhận được thông điệp từ Server gửi xuống (Command)."""
    try:
        topic = msg.topic
        payload_str = msg.payload.decode('utf-8')
        logging.info(f"[NHẬN LỆNH SERVER] Topic: {topic} | Payload: {payload_str}")

        data = json.loads(payload_str)
        command_id = data.get("command_id", "CMD-UNKNOWN")
        action = data.get("action", "")
        params = data.get("params", {})

        # Thực thi lệnh
        success, message = execute_command(action, params)

        # Nếu là lệnh đọc ngay, bắn telemetry ngay
        if action == "TRIGGER_COLLECT" and success:
            send_telemetry(client)

        # Gửi phản hồi ACK xác nhận về Server
        ack_payload = {
            "command_id": command_id,
            "station_code": STATION_CODE,
            "action": action,
            "success": success,
            "message": message,
            "executed_at": datetime.datetime.now().astimezone().isoformat()
        }
        client.publish(TOPIC_ACK, json.dumps(ack_payload, ensure_ascii=False), qos=1)
        logging.info(f"[GỬI PHẢN HỒI ACK] Lệnh {command_id} -> Thành công: {success} | {message}")

    except Exception as e:
        logging.error(f"✗ Lỗi khi xử lý message từ MQTT: {e}")


def on_disconnect(client, userdata, rc):
    """Callback khi mất kết nối với MQTT Broker."""
    if rc != 0:
        logging.warning(f"! Mất kết nối đột ngột với MQTT Broker (rc={rc}). Đang tự động kết nối lại...")


# =============================================================================
# 5. HÀM GỬI TELEMETRY ĐỊNH KỲ
# =============================================================================
def send_telemetry(client):
    """Đọc dữ liệu cảm biến và đóng gói gửi lên topic telemetry."""
    try:
        readings, battery_v, signal_csq = read_sensor_readings()

        telemetry_payload = {
            "station_code": STATION_CODE,
            "timestamp": datetime.datetime.now().astimezone().isoformat(),
            "battery_voltage": battery_v,
            "signal_csq": signal_csq,
            "readings": readings
        }

        json_str = json.dumps(telemetry_payload, ensure_ascii=False)
        result = client.publish(TOPIC_TELEMETRY, json_str, qos=1)
        
        if result.rc == mqtt.MQTT_ERR_SUCCESS:
            logging.info(f"→ [TELEMETRY] Đã gửi {len(readings)} chỉ số cảm biến từ trạm {STATION_CODE}")
        else:
            logging.warning(f"✗ Không thể gửi telemetry (mã lỗi: {result.rc})")

    except Exception as e:
        logging.error(f"✗ Lỗi khi thu thập và gửi Telemetry: {e}")


# =============================================================================
# 6. HÀM CHÍNH KHỞI CHẠY TIẾN TRÌNH TRẠM
# =============================================================================
def main():
    logging.info("==========================================================")
    logging.info(f" KHỞI ĐỘNG DỊCH VỤ TRẠM QUAN TRẮC IoT: {STATION_CODE}")
    logging.info("==========================================================")

    # 1. Khởi tạo MQTT Client
    client = mqtt.Client(
        client_id=f"station_{STATION_CODE}_{int(time.time())}",
        clean_session=False
    )

    # Cấu hình tài khoản nếu có
    if MQTT_USERNAME and MQTT_PASSWORD:
        client.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)

    # 2. CẤU HÌNH LAST WILL AND TESTAMENT (LWT)
    # Nếu trạm mất kết nối bất ngờ (mất điện, đứt sóng 4G), Mosquitto Broker sẽ tự động phát gói tin này
    lwt_payload = {
        "station_code": STATION_CODE,
        "status": "offline",
        "disconnected_at": datetime.datetime.now().astimezone().isoformat()
    }
    client.will_set(TOPIC_STATUS, json.dumps(lwt_payload), qos=1, retain=True)

    # 3. Gán các callback
    client.on_connect = on_connect
    client.on_message = on_message
    client.on_disconnect = on_disconnect

    # 4. Kết nối Broker & Bắt đầu loop nền
    try:
        client.connect(MQTT_BROKER_HOST, MQTT_BROKER_PORT, keepalive=60)
    except Exception as e:
        logging.error(f"Không thể kết nối đến MQTT Broker tại {MQTT_BROKER_HOST}:{MQTT_BROKER_PORT}: {e}")
        logging.info("Vui lòng kiểm tra lại cấu hình IP/Port và đảm bảo Mosquitto Broker đang chạy.")
        sys.exit(1)

    client.loop_start()

    # 5. Vòng lặp thu thập và gửi dữ liệu định kỳ
    try:
        while True:
            send_telemetry(client)
            
            # Đếm lùi theo từng giây để nếu Server đổi chu kỳ thì áp dụng ngay lập tức
            last_send_time = time.time()
            while (time.time() - last_send_time) < CURRENT_INTERVAL:
                time.sleep(1)

    except KeyboardInterrupt:
        logging.info("Đang dừng tiến trình trạm theo yêu cầu người dùng...")
        
        # Báo Offline khi tắt ứng dụng chủ động
        offline_payload = {
            "station_code": STATION_CODE,
            "status": "offline",
            "disconnected_at": datetime.datetime.now().astimezone().isoformat()
        }
        client.publish(TOPIC_STATUS, json.dumps(offline_payload), qos=1, retain=True)
        
        client.loop_stop()
        client.disconnect()
        logging.info("Đã đóng kết nối an toàn.")



if __name__ == "__main__":
    main()
