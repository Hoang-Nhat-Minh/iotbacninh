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
        if (Schema::hasTable('image_collection_schedules')) {
            Schema::table('image_collection_schedules', function (Blueprint $table) {
                if (!Schema::hasColumn('image_collection_schedules', 'camera_id')) {
                    $table->string('camera_id', 50)->default('all')->after('interval');
                }
            });
        }

        if (Schema::hasTable('image_capture_locations')) {
            Schema::table('image_capture_locations', function (Blueprint $table) {
                if (!Schema::hasColumn('image_capture_locations', 'camera_id')) {
                    $table->string('camera_id', 50)->default('cam_1')->after('monitoring_station_id');
                }
                if (!Schema::hasColumn('image_capture_locations', 'schedule_id')) {
                    $table->unsignedBigInteger('schedule_id')->nullable()->after('camera_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('image_collection_schedules')) {
            Schema::table('image_collection_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('image_collection_schedules', 'camera_id')) {
                    $table->dropColumn('camera_id');
                }
            });
        }

        if (Schema::hasTable('image_capture_locations')) {
            Schema::table('image_capture_locations', function (Blueprint $table) {
                if (Schema::hasColumn('image_capture_locations', 'schedule_id')) {
                    $table->dropColumn('schedule_id');
                }
                if (Schema::hasColumn('image_capture_locations', 'camera_id')) {
                    $table->dropColumn('camera_id');
                }
            });
        }
    }
};
