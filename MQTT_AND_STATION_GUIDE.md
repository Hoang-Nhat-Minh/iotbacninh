# HƯỚNG DẪN KẾT NỐI VÀ VẬN HÀNH HỆ THỐNG IoT QUAN TRẮC (MQTT 2 CHIỀU)

Tài liệu này hướng dẫn chi tiết cách kết nối giữa:
1. **Máy trạm hiện trường (Edge Station - Python Client)**: Đọc cảm biến qua cổng chuyển đổi RS485/Modbus, kết nối mạng 4G.
2. **Mosquitto MQTT Broker (Port 1883)**: Trung gian nhận và điều phối thông điệp.
3. **Laravel Server Backend**: Thu thập dữ liệu cảm biến (Telemetry) và gửi lệnh điều khiển 2 chiều (Command) xuống máy trạm.

---

## 1. TỔNG QUAN LUỒNG GIAO TIẾP 2 CHIỀU

Do máy trạm hiện trường sử dụng SIM 4G (IP động, không mở port trực tiếp được), trạm sẽ chủ động tạo kết nối TCP liên tục (Persistent TCP Connection) tới **Mosquitto Broker** trên Server.

```
[ Cảm Biến ]
     │ (RS485 / Modbus / Serial)
     ▼
[ Máy Trạm Python (SIM 4G) ] ───────── (Topic: Telemetry & Status) ──────────► [ Mosquitto Broker ] ──► [ Laravel Worker Daemon ]
                             ◄──────── (Topic: Command & Set Param) ──────── [ (Port 1883/8883) ] ◄── [ Laravel Web / API ]
```

* **Trạm $\rightarrow$ Server**: Trạm gửi định kỳ chỉ số nhiệt độ, độ ẩm, ánh sáng, mưa... vào topic `telemetry`, báo trạng thái sống/chết vào topic `status`.
* **Server $\rightarrow$ Trạm**: Server gửi lệnh (đổi chu kỳ thu thập, bật tắt bơm/quạt, xoay camera, reboot...) vào topic `command`. Trạm nhận lệnh, thực thi và báo lại kết quả vào topic `ack`.

---

## 2. CẤU HÌNH MOSQUITTO MQTT BROKER TRÊN MÁY CHỦ (VPS)

### 2.1. Cấu hình file `mosquitto.conf`
Mở file cấu hình Mosquitto (`/etc/mosquitto/mosquitto.conf`):

```conf
# Lắng nghe ở mọi card mạng trên cổng 1883
listener 1883 0.0.0.0

# Bắt buộc xác thực tài khoản
allow_anonymous false
password_file /etc/mosquitto/passwd
```

Tạo user MQTT (ví dụ: `iastadmin`) với mật khẩu bảo mật:
```bash
sudo mosquitto_passwd -c -b /etc/mosquitto/passwd <username> <your_password>
sudo systemctl restart mosquitto
```


---

## 3. CẤU HÌNH VÀ VẬN HÀNH PHÍA MÁY TRẠM (PYTHON CLIENT)

File mã nguồn mẫu: [`iot_station_client.py`](./iot_station_client.py)

### 3.1. Cài đặt thư viện trên máy trạm
```bash
pip install paho-mqtt
```

### 3.2. Cấu hình thông số trạm trong `iot_station_client.py`
Mở file `iot_station_client.py` và cập nhật các dòng đầu:
```python
STATION_CODE = "ST-PHUCHOA-01"       # Mã trạm (phải trùng với bảng monitoring_stations trên Web)
MQTT_BROKER_HOST = "[IP_ADDRESS]" # IP Public hoặc Domain của VPS
MQTT_BROKER_PORT = 1883              # Cổng MQTT Broker
MQTT_USERNAME = ""                   # Nếu có
MQTT_PASSWORD = ""                   # Nếu có
```

### 3.3. Tích hợp đọc cảm biến Modbus/RS485 thực tế
Tìm đến hàm `read_sensor_readings()` trong file `iot_station_client.py` để thay thế đoạn code giả lập bằng code đọc cổng COM/RS485 thực tế:
```python
# Ví dụ đọc qua thư viện minimalmodbus:
import minimalmodbus

instrument = minimalmodbus.Instrument('/dev/ttyUSB0', slaveaddress=1)
instrument.serial.baudrate = 9600
temp = instrument.read_register(0, numberOfDecimals=1)
humidity = instrument.read_register(1, numberOfDecimals=1)
```

### 3.4. Khởi chạy thử nghiệm
```bash
python iot_station_client.py
```

---

## 4. CẤU HÌNH VÀ VẬN HÀNH PHÍA LARAVEL SERVER

### 4.1. Cấu hình biến môi trường trong `.env`
Kiểm tra và cập nhật các biến sau trong `.env`:
```env
# Cấu hình kết nối MQTT Broker
MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel_backend_core
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLEAN_SESSION=false
MQTT_KEEP_ALIVE=60
MQTT_TLS_ENABLED=false
```

### 4.2. Chạy Background Worker lắng nghe dữ liệu
Trên máy chủ Laravel, chạy lệnh artisan sau để bắt đầu tiếp nhận dữ liệu từ tất cả các trạm:
```bash
php artisan mqtt:listen
```
*Worker này sẽ tự động:*
- Lưu trữ mọi giá trị đo đạc vào bảng `sensor_readings`.
- Tự động nhận diện thiết bị mới và lưu vào bảng `devices`.
- Cập nhật trạng thái trạm (`active` / `offline` / `maintenance`) trong bảng `monitoring_stations`.
- Ghi nhận phản hồi ACK khi trạm thực thi xong lệnh điều khiển.

### 4.3. Quản lý Worker chạy nền liên tục (Supervisor trên Ubuntu)
Tạo file `/etc/supervisor/conf.d/khcn_mqtt_listener.conf`:
```ini
[program:khcn-mqtt-listener]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/khcn-bac-ninh-laravel-10/artisan mqtt:listen
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/khcn-bac-ninh-laravel-10/storage/logs/mqtt_worker.log
```
Chạy cập nhật supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start khcn-mqtt-listener:*
```

---

## 5. ĐẶC TẢ TOPIC VÀ ĐỊNH DẠNG PAYLOAD JSON

### 5.1. Dữ liệu cảm biến (`khcn/stations/{station_code}/telemetry`)
Trạm bắn lên Server định kỳ:
```json
{
  "station_code": "ST-PHUCHOA-01",
  "timestamp": "2026-08-28T16:30:00+07:00",
  "battery_voltage": 12.4,
  "signal_csq": 26,
  "readings": [
    { "device_code": "TEMP_AIR_01",   "value": 28.5, "unit": "°C",  "status": "ok" },
    { "device_code": "HUM_AIR_01",    "value": 82.1, "unit": "%",   "status": "ok" },
    { "device_code": "SOIL_MOIST_01", "value": 74.0, "unit": "%",   "status": "ok" },
    { "device_code": "LIGHT_01",      "value": 18500,"unit": "Lux", "status": "ok" },
    { "device_code": "RAIN_01",       "value": 0.0,  "unit": "mm",  "status": "ok" }
  ]
}
```

### 5.2. Trạng thái Trạm & LWT (`khcn/stations/{station_code}/status`)
- Khi kết nối (Online):
```json
{
  "station_code": "ST-PHUCHOA-01",
  "status": "online",
  "connected_at": "2026-08-28T16:30:00+07:00"
}
```
- Khi mất mạng 4G/sập nguồn (Mosquitto tự động phát qua LWT):
```json
{
  "station_code": "ST-PHUCHOA-01",
  "status": "offline",
  "disconnected_at": "2026-08-28T16:35:00+07:00"
}
```

### 5.3. Gửi lệnh điều khiển từ Server xuống Trạm (`khcn/stations/{station_code}/command`)
Server gửi xuống trạm:
* Cách 1: Qua PHP code trong Controller:
  ```php
  app(\App\Services\Iot\MqttService::class)->publishCommand('ST-PHUCHOA-01', 'SET_INTERVAL', ['interval_seconds' => 30]);
  ```
* Cách 2: Qua HTTP API:
  `POST /api/iot/stations/ST-PHUCHOA-01/command`
  ```json
  {
    "action": "SET_INTERVAL",
    "params": { "interval_seconds": 30 }
  }
  ```

### 5.4. Trạm phản hồi xác nhận lệnh (`khcn/stations/{station_code}/ack`)
```json
{
  "command_id": "CMD-20260828-001",
  "station_code": "ST-PHUCHOA-01",
  "action": "SET_INTERVAL",
  "success": true,
  "message": "Đã cập nhật chu kỳ gửi dữ liệu thành 30 giây.",
  "executed_at": "2026-08-28T16:30:05+07:00"
}
```
