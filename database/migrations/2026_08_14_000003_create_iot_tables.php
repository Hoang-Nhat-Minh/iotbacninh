<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monitoring_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garden_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('active');
            $table->integer('data_interval')->default(60);
            $table->timestamps();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_station_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type');
            $table->string('sensor_type')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id');
            $table->double('value');
            $table->dateTime('recorded_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['device_id', 'recorded_at']);
        });

        Schema::create('camera_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id');
            $table->string('type');
            $table->string('name');
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('image_collection_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_station_id')->nullable();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('interval')->default(60);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('image_capture_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_station_id');
            $table->string('name');
            $table->decimal('pan_angle', 5, 2)->default(0.00);
            $table->decimal('tilt_angle', 5, 2)->default(0.00);
            $table->decimal('zoom_level', 3, 1)->default(1.0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_capture_locations');
        Schema::dropIfExists('image_collection_schedules');
        Schema::dropIfExists('camera_media');
        Schema::dropIfExists('sensor_readings');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('monitoring_stations');
    }
};
