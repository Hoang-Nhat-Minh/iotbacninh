<?php

namespace App\Services\Iot;

use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MqttService
{
    /**
     * Gửi lệnh điều khiển 2 chiều xuống trạm quan trắc IoT.
     *
     * @param string $stationCode Mã trạm (vd: ST-PHUCHOA-01)
     * @param string $action Hành động (SET_INTERVAL, TRIGGER_COLLECT, CONTROL_ACTUATOR, REBOOT_STATION, PTZ_CAMERA)
     * @param array $params Các tham số kèm theo hành động
     * @param string|null $commandId Mã lệnh tuỳ chọn (nếu null sẽ tự sinh)
     * @return array Kết quả gửi lệnh bao gồm command_id và payload
     */
    public function publishCommand(string $stationCode, string $action, array $params = [], ?string $commandId = null): array
    {
        $commandId = $commandId ?: 'CMD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        $topic = "khcn/stations/{$stationCode}/command";

        $payload = [
            'command_id' => $commandId,
            'station_code' => $stationCode,
            'action' => strtoupper($action),
            'params' => $params,
            'created_at' => now()->toIso8601String(),
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        try {
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $jsonPayload, 1, false);

            Log::info("MQTT Command Published to [{$topic}]: {$jsonPayload}");

            return [
                'success' => true,
                'command_id' => $commandId,
                'topic' => $topic,
                'payload' => $payload,
            ];
        } catch (\Throwable $e) {
            Log::error("Failed to publish MQTT Command to [{$topic}]: " . $e->getMessage());

            return [
                'success' => false,
                'command_id' => $commandId,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish một message bất kỳ lên MQTT Broker.
     *
     * @param string $topic Topic MQTT
     * @param array|string $message Nội dung message
     * @param int $qos QoS (0, 1, 2)
     * @param bool $retain Cờ retain
     * @return bool
     */
    public function publish(string $topic, array|string $message, int $qos = 1, bool $retain = false): bool
    {
        try {
            $payload = is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : (string) $message;
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, $qos, $retain);
            return true;
        } catch (\Throwable $e) {
            Log::error("MQTT Publish Error on topic [{$topic}]: " . $e->getMessage());
            return false;
        }
    }
}
