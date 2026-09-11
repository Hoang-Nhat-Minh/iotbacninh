<?php

namespace App\Services\Iot;

use App\Models\Iot\ImageCollectionSchedule;
use App\Models\Iot\ImageCaptureLocation;
use App\Models\Iot\MonitoringStation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class CameraScheduleService
{
    public function __construct(
        protected MqttService $mqttService
    ) {}

    /**
     * Quét và thực thi các lịch chụp ảnh tự động đang active theo khung giờ và chu kỳ.
     *
     * @param bool $force Bỏ qua kiểm tra khung giờ và chu kỳ để chạy ngay lập tức (dùng khi test)
     * @return array Danh sách các lệnh đã được kích hoạt
     */
    public function checkAndTriggerSchedules(bool $force = false): array
    {
        $activeSchedules = ImageCollectionSchedule::where('status', 'active')->get();
        $triggered = [];
        $now = now();
        $currentTimeStr = $now->format('H:i:s');

        foreach ($activeSchedules as $schedule) {
            $startTime = Carbon::parse($schedule->start_time)->format('H:i:s');
            $endTime = Carbon::parse($schedule->end_time)->format('H:i:s');

            if (!$force) {
                // 1. Kiểm tra hiện tại có nằm trong khung giờ [start_time, end_time] không
                $inWindow = false;
                if ($startTime <= $endTime) {
                    $inWindow = ($currentTimeStr >= $startTime && $currentTimeStr <= $endTime);
                } else {
                    // Khung giờ qua đêm (ví dụ: 22:00 -> 05:00)
                    $inWindow = ($currentTimeStr >= $startTime || $currentTimeStr <= $endTime);
                }

                if (!$inWindow) {
                    continue;
                }

                // 2. Kiểm tra chu kỳ interval (phút) kể từ lần chụp gần nhất
                $intervalMinutes = max(1, (int) ($schedule->interval ?: 60));
                $cacheKey = "camera_schedule_last_run_{$schedule->id}";
                $lastRunTimestamp = Cache::get($cacheKey);

                if ($lastRunTimestamp && ($now->timestamp - $lastRunTimestamp) < ($intervalMinutes * 60 - 30)) {
                    continue;
                }
            }

            // 3. Xác định danh sách trạm mục tiêu
            if ($schedule->monitoring_station_id) {
                $stations = MonitoringStation::where('id', $schedule->monitoring_station_id)
                    ->where('status', '!=', 'inactive')
                    ->get();
            } else {
                $stations = MonitoringStation::where('status', '!=', 'inactive')->get();
            }

            if ($stations->isEmpty()) {
                continue;
            }

            // 4. Duyệt từng trạm và gửi lệnh chụp các điểm (kèm tọa độ góc quay PTZ từ iot/locations)
            foreach ($stations as $station) {
                $targets = $this->getCaptureTargets($schedule, $station);

                foreach ($targets as $idx => $target) {
                    $cmdPayload = [
                        'camera_id' => $target['camera_id'],
                        'quality' => 'main',
                        'trigger_source' => 'auto_schedule',
                        'schedule_id' => $schedule->id,
                        'schedule_name' => $schedule->name,
                    ];

                    $hasPtz = false;
                    if ($target['pan'] !== null || $target['tilt'] !== null || $target['zoom'] !== null) {
                        $cmdPayload['pan'] = $target['pan'];
                        $cmdPayload['tilt'] = $target['tilt'];
                        $cmdPayload['zoom'] = $target['zoom'];
                        $hasPtz = true;
                    }
                    if (!empty($target['location_id'])) {
                        $cmdPayload['location_id'] = $target['location_id'];
                    }
                    if (!empty($target['location_name'])) {
                        $cmdPayload['location_name'] = $target['location_name'];
                    }

                    $cmdResult = $this->mqttService->publishCameraCommand($station->code, 'CAPTURE_SNAPSHOT', $cmdPayload);

                    $locDesc = !empty($target['location_name']) ? " - Góc: {$target['location_name']} (Pan: {$target['pan']}°, Tilt: {$target['tilt']}°, Zoom: {$target['zoom']}x)" : "";
                    $logMsg = "[AUTO_SCHEDULE_CAPTURE] Kích hoạt chụp ảnh theo lịch '{$schedule->name}' tới trạm {$station->code} ({$target['camera_id']}){$locDesc}";
                    Log::info($logMsg, [
                        'schedule_id' => $schedule->id,
                        'station' => $station->code,
                        'camera_id' => $target['camera_id'],
                        'target' => $target,
                        'command_result' => $cmdResult,
                    ]);

                    $triggered[] = [
                        'schedule_id' => $schedule->id,
                        'schedule_name' => $schedule->name,
                        'station_code' => $station->code,
                        'camera_id' => $target['camera_id'],
                        'location_name' => $target['location_name'] ?? null,
                        'pan' => $target['pan'] ?? null,
                        'tilt' => $target['tilt'] ?? null,
                        'zoom' => $target['zoom'] ?? null,
                        'success' => $cmdResult['success'] ?? false,
                        'command_id' => $cmdResult['command_id'] ?? null,
                    ];

                    // Nếu chụp nhiều điểm (hoặc có quay PTZ), giãn cách thời gian để trạm quay motor & chụp tuần tự
                    if (count($targets) > 1 && $idx < count($targets) - 1) {
                        sleep($hasPtz ? 4 : 1);
                    }
                }
            }

            // 5. Cập nhật thời điểm vừa kích hoạt vào Cache để tính chu kỳ tiếp theo
            Cache::put("camera_schedule_last_run_{$schedule->id}", $now->timestamp, now()->addDays(2));
        }

        return $triggered;
    }

    /**
     * Kích hoạt chụp ảnh ngay lập tức cho một lịch trình cụ thể.
     */
    public function triggerScheduleNow(int $scheduleId): array
    {
        $schedule = ImageCollectionSchedule::findOrFail($scheduleId);
        $triggered = [];
        $now = now();

        if ($schedule->monitoring_station_id) {
            $stations = MonitoringStation::where('id', $schedule->monitoring_station_id)->get();
        } else {
            $stations = MonitoringStation::where('status', '!=', 'inactive')->get();
        }

        foreach ($stations as $station) {
            $targets = $this->getCaptureTargets($schedule, $station);

            foreach ($targets as $idx => $target) {
                $cmdPayload = [
                    'camera_id' => $target['camera_id'],
                    'quality' => 'main',
                    'trigger_source' => 'manual_schedule_trigger',
                    'schedule_id' => $schedule->id,
                    'schedule_name' => $schedule->name,
                ];

                $hasPtz = false;
                if ($target['pan'] !== null || $target['tilt'] !== null || $target['zoom'] !== null) {
                    $cmdPayload['pan'] = $target['pan'];
                    $cmdPayload['tilt'] = $target['tilt'];
                    $cmdPayload['zoom'] = $target['zoom'];
                    $hasPtz = true;
                }
                if (!empty($target['location_id'])) {
                    $cmdPayload['location_id'] = $target['location_id'];
                }
                if (!empty($target['location_name'])) {
                    $cmdPayload['location_name'] = $target['location_name'];
                }

                $cmdResult = $this->mqttService->publishCameraCommand($station->code, 'CAPTURE_SNAPSHOT', $cmdPayload);

                $locDesc = !empty($target['location_name']) ? " - Góc: {$target['location_name']} (Pan: {$target['pan']}°, Tilt: {$target['tilt']}°, Zoom: {$target['zoom']}x)" : "";
                Log::info("[MANUAL_SCHEDULE_TRIGGER] Kích hoạt chụp thủ công theo lịch '{$schedule->name}' tới trạm {$station->code} ({$target['camera_id']}){$locDesc}", [
                    'schedule_id' => $schedule->id,
                    'station' => $station->code,
                    'camera_id' => $target['camera_id'],
                    'target' => $target,
                    'command_result' => $cmdResult,
                ]);

                $triggered[] = [
                    'schedule_id' => $schedule->id,
                    'schedule_name' => $schedule->name,
                    'station_code' => $station->code,
                    'camera_id' => $target['camera_id'],
                    'location_name' => $target['location_name'] ?? null,
                    'pan' => $target['pan'] ?? null,
                    'tilt' => $target['tilt'] ?? null,
                    'zoom' => $target['zoom'] ?? null,
                    'success' => $cmdResult['success'] ?? false,
                    'command_id' => $cmdResult['command_id'] ?? null,
                ];

                if (count($targets) > 1 && $idx < count($targets) - 1) {
                    sleep($hasPtz ? 4 : 1);
                }
            }
        }

        Cache::put("camera_schedule_last_run_{$schedule->id}", $now->timestamp, now()->addDays(2));

        return $triggered;
    }

    /**
     * Xác định danh sách các điểm chụp (camera & góc PTZ) cho một trạm theo lịch trình.
     */
    protected function getCaptureTargets(ImageCollectionSchedule $schedule, MonitoringStation $station): array
    {
        // 1. Ưu tiên các điểm góc chụp được gán trực tiếp với lịch trình này cho trạm
        $linkedLocations = ImageCaptureLocation::where('monitoring_station_id', $station->id)
            ->where('schedule_id', $schedule->id)
            ->where('status', 'active')
            ->get();

        if ($linkedLocations->isNotEmpty()) {
            return $linkedLocations->map(function ($loc) {
                return [
                    'camera_id' => $loc->camera_id ?: 'cam_1',
                    'pan' => (float) $loc->pan_angle,
                    'tilt' => (float) $loc->tilt_angle,
                    'zoom' => (float) $loc->zoom_level,
                    'location_id' => $loc->id,
                    'location_name' => $loc->name,
                ];
            })->all();
        }

        // 2. Nếu không có điểm chụp nào gắn trực tiếp schedule_id, lấy theo danh sách camera cấu hình trong schedule
        $targetCams = [];
        if (empty($schedule->camera_id) || $schedule->camera_id === 'all') {
            $targetCams = ['cam_1', 'cam_2', 'cam_3', 'cam_4'];
        } else {
            $targetCams = [$schedule->camera_id];
        }

        $targets = [];
        foreach ($targetCams as $camId) {
            // Kiểm tra xem camera này của trạm có cấu hình góc chụp nào trong ImageCaptureLocation không
            $camLocations = ImageCaptureLocation::where('monitoring_station_id', $station->id)
                ->where('camera_id', $camId)
                ->where('status', 'active')
                ->get();

            if ($camLocations->isNotEmpty()) {
                foreach ($camLocations as $loc) {
                    $targets[] = [
                        'camera_id' => $camId,
                        'pan' => (float) $loc->pan_angle,
                        'tilt' => (float) $loc->tilt_angle,
                        'zoom' => (float) $loc->zoom_level,
                        'location_id' => $loc->id,
                        'location_name' => $loc->name,
                    ];
                }
            } else {
                $targets[] = [
                    'camera_id' => $camId,
                    'pan' => null,
                    'tilt' => null,
                    'zoom' => null,
                    'location_id' => null,
                    'location_name' => null,
                ];
            }
        }

        return $targets;
    }
}

