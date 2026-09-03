<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('degree_days_surveys', function (Blueprint $table) {
            $table->id();
            
            // Thông tin người khảo sát (Lưu độc lập, không đặt khóa ngoại cứng để bảo toàn dữ liệu)
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_name', 100)->nullable();

            // Thông tin trạm & vùng trồng (Lưu độc lập, dù trạm có bị xóa/sửa thì dữ liệu khảo sát vẫn nguyên vẹn)
            $table->unsignedBigInteger('monitoring_station_id')->nullable()->index();
            $table->string('station_code', 50)->nullable();
            $table->string('station_name', 150)->nullable();
            $table->string('garden_name', 150)->nullable();

            $table->dateTime('surveyed_at'); // Thời gian khảo sát thực địa

            // Loại đối tượng: 'pest' (Sâu đục cuống quả) | 'disease' (Bệnh hại)
            $table->string('object_type', 30);

            // Các trường riêng cho Sâu đục cuống quả
            $table->string('development_stage', 50)->nullable(); // none, egg, larva, pupa, adult, unknown
            $table->string('quantity_range', 50)->nullable();    // unknown, 1_5, 6_20, 21_50, gt_50

            // Các trường riêng cho Bệnh hại
            $table->string('affected_part', 50)->nullable();       // leaf, flower, fruit, branch, other
            $table->string('infection_rate_range', 50)->nullable(); // lt_5, 5_10, 10_25, 25_50, gt_50

            // Mức độ chung
            $table->string('severity', 30); // none, low, medium, high, outbreak

            // Ảnh chụp thực địa & Ghi chú
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();

            // Snapshot vi khí hậu IoT tại thời điểm khảo sát (Lưu độc lập, không đặt khóa ngoại cứng)
            $table->unsignedBigInteger('iot_sensor_reading_id')->nullable()->index();
            $table->dateTime('iot_recorded_at')->nullable();
            $table->decimal('iot_temperature', 5, 2)->nullable();
            $table->decimal('iot_humidity', 5, 2)->nullable();
            $table->decimal('iot_rainfall', 5, 2)->nullable();
            $table->integer('iot_light')->nullable();
            $table->decimal('iot_wind_speed', 5, 2)->nullable();
            $table->decimal('iot_soil_moisture', 5, 2)->nullable();
            $table->decimal('iot_soil_temp', 5, 2)->nullable();
            $table->decimal('iot_soil_ph', 4, 2)->nullable();
            $table->json('iot_snapshot')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'surveyed_at']);
            $table->index(['monitoring_station_id', 'surveyed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('degree_days_surveys');
    }
};
