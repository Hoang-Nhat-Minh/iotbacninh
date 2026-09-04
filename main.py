#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
=============================================================================
HỆ THỐNG QUAN TRẮC IoT NÔNG NGHIỆP THÔNG MINH BẮC NINH
TRÌNH QUẢN LÝ VÀ CHẠY THỬ NGHIỆM ĐỒNG THỜI CÁC TRẠM (STATION RUNNER / TESTER)
=============================================================================
File: main.py
Mục đích:
  - Khởi chạy đồng thời hoặc riêng lẻ 4 trạm IoT:
      1. iot_station_client_1.py (Trạm TPH-01)
      2. iot_station_client_2.py (Trạm TPH-02)
      3. iot_station_client_3.py (Trạm TLN-02)
      4. iot_station_client_4.py (Trạm TLN-01)
  - Quản lý tiến trình, gán tiền tố log theo từng trạm để dễ quan sát.
  - Hỗ trợ kiểm tra kết nối MQTT Broker trước khi chạy.
  - Tự động dọn dẹp và dừng an toàn tất cả tiến trình khi bấm Ctrl+C.
=============================================================================
"""

import sys
import os
import io
import time
import socket
import argparse
import subprocess
import threading
from typing import List, Dict

# Đảm bảo mã hóa UTF-8 trên Windows Console
if sys.platform == "win32":
    if hasattr(sys.stdout, 'buffer'):
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    if hasattr(sys.stderr, 'buffer'):
        sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')
    os.system("color")

# Danh sách cấu hình 4 trạm
STATIONS: List[Dict[str, str]] = [
    {
        "id": 1,
        "name": "Trạm 1 (TP. Bắc Ninh 1)",
        "code": "TPH-01",
        "file": "iot_station_client_1.py",
        "color": "\033[94m" # Xanh lam
    },
    {
        "id": 2,
        "name": "Trạm 2 (TP. Bắc Ninh 2)",
        "code": "TPH-02",
        "file": "iot_station_client_2.py",
        "color": "\033[92m" # Xanh lá
    },
    {
        "id": 3,
        "name": "Trạm 3 (Thuận Thành 2)",
        "code": "TLN-02",
        "file": "iot_station_client_3.py",
        "color": "\033[93m" # Vàng
    },
    {
        "id": 4,
        "name": "Trạm 4 (Thuận Thành 1)",
        "code": "TLN-01",
        "file": "iot_station_client_4.py",
        "color": "\033[95m" # Tím hồng
    },
]

RESET_COLOR = "\033[0m"


def print_banner():
    """In banner thông tin hệ thống"""
    banner = f"""
=============================================================================
   HỆ THỐNG QUAN TRẮC IoT NÔNG NGHIỆP THÔNG MINH BẮC NINH
   TRÌNH CHẠY THỬ NGHIỆM VÀ ĐIỀU PHỐI CÁC TRẠM CẢM BIẾN (STATION RUNNER)
=============================================================================
 Danh sách các trạm:
   [1] iot_station_client_1.py  -->  Mã trạm: TPH-01
   [2] iot_station_client_2.py  -->  Mã trạm: TPH-02
   [3] iot_station_client_3.py  -->  Mã trạm: TLN-02
   [4] iot_station_client_4.py  -->  Mã trạm: TLN-01
=============================================================================
"""
    print(banner)


def check_mqtt_broker(host: str = None, port: int = None, timeout: float = 3.0) -> bool:
    """Kiểm tra kết nối TCP tới máy chủ MQTT Broker"""
    host = host or os.getenv("MQTT_BROKER_HOST", "127.0.0.1")
    port = port or int(os.getenv("MQTT_BROKER_PORT", 9070))
    print(f"[*] Đang kiểm tra kết nối tới MQTT Broker [{host}:{port}] (timeout {timeout}s)...")
    try:
        sock = socket.create_connection((host, port), timeout=timeout)
        sock.close()
        print(f"[✓] Kết nối tới MQTT Broker [{host}:{port}] THÀNH CÔNG!\n")
        return True
    except Exception as e:
        print(f"[!] CẢNH BÁO: Không thể kết nối tới Broker [{host}:{port}] ({e}).")
        print(f"    (Các trạm vẫn sẽ thử kết nối lại và gửi dữ liệu khi có mạng/VPN).\n")
        return False


def stream_process_output(station_info: Dict[str, str], process: subprocess.Popen):
    """Đọc output từ subprocess và in ra terminal với tiền tố định danh trạm"""
    prefix = f"{station_info['color']}[{station_info['code']}]{RESET_COLOR} "
    try:
        for line in iter(process.stdout.readline, ''):
            if not line:
                break
            # In dòng log kèm tiền tố
            print(f"{prefix}{line}", end='', flush=True)
    except Exception:
        pass
    finally:
        if process.stdout:
            try:
                process.stdout.close()
            except Exception:
                pass


def run_single_station(station_info: Dict[str, str]):
    """Chạy 1 trạm đơn lẻ (blocking)"""
    script_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), station_info["file"])
    if not os.path.exists(script_path):
        print(f"[✗] Lỗi: Không tìm thấy file {script_path}")
        return

    print(f"[*] Đang khởi động {station_info['name']} ({station_info['file']})...")
    print(f"[*] Nhấn Ctrl+C để dừng trạm.\n")
    try:
        subprocess.run([sys.executable, script_path])
    except KeyboardInterrupt:
        print(f"\n[*] Đã dừng {station_info['name']}.")


def run_all_stations(selected_stations: List[Dict[str, str]]):
    """Chạy đồng thời các trạm được chọn bằng subprocess và thread đọc log"""
    processes: List[subprocess.Popen] = []
    threads: List[threading.Thread] = []
    base_dir = os.path.dirname(os.path.abspath(__file__))

    print(f"[*] Đang khởi chạy song song {len(selected_stations)} trạm IoT...")
    print(f"[*] Nhấn Ctrl+C bất cứ lúc nào để dừng TẤT CẢ các trạm.\n" + "-"*75)

    try:
        for st in selected_stations:
            script_path = os.path.join(base_dir, st["file"])
            if not os.path.exists(script_path):
                print(f"[✗] Không tìm thấy file {st['file']}, bỏ qua.")
                continue

            # Khởi chạy subprocess với stdout/stderr gộp lại
            # PYTHONUNBUFFERED=1 để output được đẩy ra ngay tức thì
            env = os.environ.copy()
            env["PYTHONUNBUFFERED"] = "1"
            env["PYTHONIOENCODING"] = "utf-8"

            p = subprocess.Popen(
                [sys.executable, "-u", script_path],
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                text=True,
                bufsize=1,
                encoding='utf-8',
                errors='replace',
                env=env
            )
            processes.append(p)

            # Thread đọc log không bị nghẽn
            t = threading.Thread(
                target=stream_process_output,
                args=(st, p),
                daemon=True
            )
            t.start()
            threads.append(t)
            time.sleep(0.3)  # Delay nhẹ giữa các lần khởi chạy trạm

        # Chờ đợi các tiến trình chạy
        while any(p.poll() is None for p in processes):
            time.sleep(0.5)

    except KeyboardInterrupt:
        print(f"\n\n[*] Đang gửi tín hiệu dừng tới tất cả {len(processes)} trạm...")
        for p in processes:
            if p.poll() is None:
                try:
                    p.terminate()
                except Exception:
                    pass

        # Chờ tối đa 3 giây để các tiến trình đóng an toàn
        deadline = time.time() + 3.0
        for p in processes:
            while p.poll() is None and time.time() < deadline:
                time.sleep(0.2)
            if p.poll() is None:
                try:
                    p.kill()
                except Exception:
                    pass

        print("[✓] Đã dừng toàn bộ các tiến trình trạm thành công.\n")


def interactive_menu():
    """Hiển thị menu tương tác để người dùng lựa chọn"""
    while True:
        print_banner()
        print(" CHỌN CHẾ ĐỘ CHẠY:")
        print("   [0] Chạy ĐỒNG THỜI CẢ 4 TRẠM (Mặc định)")
        print("   [1] Chỉ chạy Trạm 1 (TPH-01)")
        print("   [2] Chỉ chạy Trạm 2 (TPH-02)")
        print("   [3] Chỉ chạy Trạm 3 (TLN-02)")
        print("   [4] Chỉ chạy Trạm 4 (TLN-01)")
        print("   [5] Kiểm tra kết nối mạng tới MQTT Broker")
        print("   [Q] Thoát chương trình")
        print("-" * 75)

        try:
            choice = input(" Nhập lựa chọn của bạn (0-5 hoặc Enter để chạy tất cả): ").strip().upper()
        except (EOFError, KeyboardInterrupt):
            print("\nThoát chương trình.")
            sys.exit(0)

        if choice in ("", "0", "ALL"):
            check_mqtt_broker()
            run_all_stations(STATIONS)
            break
        elif choice == "1":
            run_single_station(STATIONS[0])
            break
        elif choice == "2":
            run_single_station(STATIONS[1])
            break
        elif choice == "3":
            run_single_station(STATIONS[2])
            break
        elif choice == "4":
            run_single_station(STATIONS[3])
            break
        elif choice == "5":
            check_mqtt_broker()
            try:
                input("Nhấn Enter để quay lại menu...")
            except (EOFError, KeyboardInterrupt):
                break
        elif choice in ("Q", "QUIT", "EXIT"):
            print("Tạm biệt!")
            sys.exit(0)
        else:
            print("[!] Lựa chọn không hợp lệ, vui lòng chọn lại.\n")
            time.sleep(1)


def main():
    parser = argparse.ArgumentParser(
        description="Trình chạy và kiểm thử 4 trạm quan trắc IoT Bắc Ninh."
    )
    parser.add_argument(
        "--all", action="store_true", help="Chạy đồng thời cả 4 trạm không cần qua menu tương tác."
    )
    parser.add_argument(
        "--station", "-s", type=int, choices=[1, 2, 3, 4], help="Chỉ chạy trạm theo số thứ tự (1, 2, 3, 4)."
    )
    parser.add_argument(
        "--check-broker", action="store_true", help="Kiểm tra kết nối tới MQTT Broker rồi thoát."
    )

    args = parser.parse_args()

    if args.check_broker:
        check_mqtt_broker()
        return

    if args.station:
        st = next((s for s in STATIONS if s["id"] == args.station), None)
        if st:
            print_banner()
            run_single_station(st)
        return

    if args.all:
        print_banner()
        check_mqtt_broker()
        run_all_stations(STATIONS)
        return

    # Nếu không có tham số dòng lệnh, mở menu tương tác
    interactive_menu()


if __name__ == "__main__":
    main()
