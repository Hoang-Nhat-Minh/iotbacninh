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
        Schema::table('sensor_readings', function (Blueprint $table) {
            if (!Schema::hasColumn('sensor_readings', 'monitoring_station_id')) {
                $table->foreignId('monitoring_station_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('sensor_readings', 'data')) {
                $table->json('data')->nullable()->after('monitoring_station_id');
            }
            // Cho phép device_id và value nullable vì giờ lưu nguyên gói JSON theo trạm
            if (Schema::hasColumn('sensor_readings', 'device_id')) {
                $table->unsignedBigInteger('device_id')->nullable()->change();
            }
            if (Schema::hasColumn('sensor_readings', 'value')) {
                $table->double('value')->nullable()->change();
            }

            $table->index(['monitoring_station_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->dropIndex(['monitoring_station_id', 'recorded_at']);
            $table->dropColumn(['monitoring_station_id', 'data']);
        });
    }
};
