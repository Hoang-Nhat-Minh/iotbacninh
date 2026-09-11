<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Iot\CameraScheduleService;

class ProcessCameraSchedulesCommand extends Command
{
    /**
     * Tên và chữ ký của artisan command.
     *
     * @var string
     */
    protected $signature = 'camera:process-schedules {--force : Bỏ qua kiểm tra thời gian và chu kỳ để kích hoạt ngay}';

    /**
     * Mô tả command.
     *
     * @var string
     */
    protected $description = 'Kiểm tra khung giờ và chu kỳ của các lịch chụp ảnh tự động và gửi lệnh MQTT xuống trạm';

    /**
     * Thực thi command.
     */
    public function handle(CameraScheduleService $scheduleService)
    {
        $force = (bool) $this->option('force');
        $this->info("=================================================================");
        $this->info("   HỆ THỐNG QUAN TRẮC IoT BẮC NINH - TỰ ĐỘNG CHỤP ẢNH THEO LỊCH  ");
        $this->info("=================================================================");
        $this->info("Thời gian hiện tại: " . now()->format('Y-m-d H:i:s'));
        if ($force) {
            $this->warn("Chế độ FORCE: Bỏ qua kiểm tra khung giờ và chu kỳ.");
        }

        $triggered = $scheduleService->checkAndTriggerSchedules($force);

        if (empty($triggered)) {
            $this->line("Không có lịch trình nào đến hạn kích hoạt tại thời điểm này.");
        } else {
            $this->info("Đã kích hoạt chụp ảnh tự động cho " . count($triggered) . " lượt:");
            foreach ($triggered as $item) {
                $statusStr = $item['success'] ? '<info>THÀNH CÔNG</info>' : '<error>THẤT BẠI</error>';
                $this->line(" - Lịch: [{$item['schedule_name']}] | Trạm: [{$item['station_code']}] | Cam: [{$item['camera_id']}] -> {$statusStr}");
            }
        }

        return Command::SUCCESS;
    }
}
