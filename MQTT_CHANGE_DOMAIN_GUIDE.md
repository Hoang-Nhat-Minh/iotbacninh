# HƯỚNG DẪN CHI TIẾT KHI ĐỔI DOMAIN / CHUYỂN SERVER CHO HỆ THỐNG IoT QUAN TRẮC

Tài liệu này cung cấp hướng dẫn từng bước khi hệ thống thực hiện:
* **Kịch bản 1**: Đổi Tên miền (Domain) trên cùng VPS hiện tại (Ví dụ: từ `smartagriculture.kennatech.vn` $\rightarrow$ `iot.bacninh.gov.vn`).
* **Kịch bản 2**: Chuyển toàn bộ hệ thống sang VPS mới / Bàn giao cho Sở KH&CN Bắc Ninh.

---

## 1. TỔNG QUAN CÁC THÀNH PHẦN CẦN LƯU Ý KHI ĐỔI DOMAIN

```
[ 1. Tên Miền / DNS ] ──► [ 2. Nginx Web Server & SSL ] ──► [ 3. Laravel Backend (.env) ]
                                                                     │
                                                       [ 4. Supervisor Worker ]
                                                                     │
                                                       [ 5. Mosquitto Broker ]
                                                                     ▲
                                                       [ 6. Máy Trạm IoT (4G) ]
```

| Thành phần | Cần làm gì khi đổi Domain? | Mức độ phức tạp |
| :--- | :--- | :---: |
| **DNS** | Trỏ bản ghi A về IP máy chủ | Rất dễ (1 phút) |
| **Nginx & SSL** | Cập nhật `server_name` và cấp lại SSL Certbot | Dễ (2 phút) |
| **Laravel (.env)** | Sửa `APP_URL` và xóa cache config | Dễ (1 phút) |
| **Supervisor** | Giữ nguyên nếu không đổi tên thư mục code | Không cần làm gì |
| **Mosquitto** | Giữ nguyên cấu hình port và user/pass | Không cần làm gì |
| **Trạm IoT (Python)** | Cập nhật `MQTT_BROKER_HOST` nếu trạm dùng Domain | Rất dễ (1 dòng code) |

---

## 2. HƯỚNG DẪN CHI TIẾT TỪNG BƯỚC

### BƯỚC 1: Cấu hình DNS & Nginx Web Server

1. **Trỏ bản ghi DNS**:
   - Truy cập trang quản lý tên miền mới (như Cloudflare, PA Vietnam, Mat Bao,...).
   - Thêm bản ghi **`A`** trỏ tên miền mới về IP của VPS (Ví dụ: `117.6.44.206`).

2. **Cập nhật cấu hình Nginx trên VPS**:
   - Mở file cấu hình Nginx:
     ```bash
     sudo nano /etc/nginx/sites-available/smartagriculture.kennatech.vn
     ```
   - Sửa dòng `server_name` thành domain mới:
     ```nginx
     server_name iot.bacninh.gov.vn;
     ```
   - Kiểm tra cú pháp và reload Nginx:
     ```bash
     sudo nginx -t
     sudo systemctl reload nginx
     ```

3. **Cấp chứng chỉ bảo mật SSL (HTTPS) cho domain mới**:
   ```bash
   sudo certbot --nginx -d iot.bacninh.gov.vn
   ```

---

### BƯỚC 2: Cập nhật cấu hình Laravel Backend

1. **Sửa file `.env` trên VPS**:
   ```bash
   nano /var/www/smartagriculture.kennatech.vn/.env
   ```
   Cập nhật biến `APP_URL`:
   ```env
   APP_URL=https://iot.bacninh.gov.vn
   ```

2. **Xóa Cache cấu hình của Laravel**:
   ```bash
   sudo -u www-data php /var/www/smartagriculture.kennatech.vn/artisan config:clear
   sudo -u www-data php /var/www/smartagriculture.kennatech.vn/artisan cache:clear
   sudo -u www-data php /var/www/smartagriculture.kennatech.vn/artisan route:clear
   ```

---

### BƯỚC 3: Kiểm tra Supervisor (Tiến trình lắng nghe MQTT)

* **Trường hợp 1: Bạn GIỮ NGUYÊN tên thư mục code** (vẫn nằm ở `/var/www/smartagriculture.kennatech.vn/`):
  👉 **BẠN KHÔNG CẦN SỬA BẤT KỲ CÁI GÌ TRONG SUPERVISOR!** Worker vẫn tự động chạy bình thường 100%.

* **Trường hợp 2: Bạn ĐỔI TÊN thư mục code** (thành `/var/www/iot.bacninh.gov.vn/`):
  1. Mở file cấu hình:
     ```bash
     sudo nano /etc/supervisor/conf.d/khcn-mqtt.conf
     ```
  2. Sửa lại 2 dòng đường dẫn thư mục mới:
     ```ini
     [program:khcn-mqtt]
     process_name=%(program_name)s_%(process_num)02d
     command=php /var/www/iot.bacninh.gov.vn/artisan mqtt:listen
     autostart=true
     autorestart=true
     user=www-data
     numprocs=1
     redirect_stderr=true
     stdout_logfile=/var/www/iot.bacninh.gov.vn/storage/logs/mqtt_worker.log
     ```
  3. Cập nhật và khởi động lại Supervisor:
     ```bash
     sudo supervisorctl reread
     sudo supervisorctl update
     sudo supervisorctl restart khcn-mqtt:*
     ```

---

### BƯỚC 4: Cập nhật các Trạm IoT hiện trường (Python Client)

Trong file script chạy tại các trạm ngoài hiện trường (`iot_station_client_*.py`):

* **Nếu trạm đang dùng IP Public** (Ví dụ `MQTT_BROKER_HOST = "117.6.44.206"`):
  👉 **HOÀN TOÀN KHÔNG CẦN CHỈNH SỬA GÌ**. Trạm vẫn gửi dữ liệu liên tục về VPS bình thường.

* **Nếu trạm đang dùng Domain cũ** (Ví dụ `MQTT_BROKER_HOST = "smartagriculture.kennatech.vn"`):
  - Mở file code trạm và sửa thành domain mới:
    ```python
    MQTT_BROKER_HOST = "iot.bacninh.gov.vn"
    MQTT_BROKER_PORT = 9070  # Port MQTT đã cấu hình
    ```
  - Khởi động lại dịch vụ trạm:
    ```bash
    sudo systemctl restart iot-station.service
    ```

---

## 3. CHECKLIST KIỂM TRA NHANH KHI BÀN GIAO / ĐỔI DOMAIN

Sau khi hoàn tất, hãy kiểm tra theo bảng sau:

- [ ] Truy cập `https://domain-moi.vn` trên trình duyệt: Web tải nhanh, có biểu tượng ổ khóa bảo mật SSL (HTTPS).
- [ ] Kiểm tra Supervisor trên VPS: `sudo supervisorctl status khcn-mqtt:*` hiển thị chữ màu xanh **`RUNNING`**.
- [ ] Chạy trạm gửi thử dữ liệu và kiểm tra log:
  ```bash
  tail -f /var/www/smartagriculture.kennatech.vn/storage/logs/mqtt_worker.log
  ```
- [ ] Vào trang Dashboard Web quản lý xem các số liệu cảm biến của 4 trạm cập nhật theo thời gian thực.
