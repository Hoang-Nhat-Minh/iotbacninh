<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dọn dẹp toàn bộ dữ liệu demo/ảo gây lỗi 404
        DB::table('camera_media')
            ->where('file_path', 'like', 'http%')
            ->orWhere('file_path', 'like', '%sample.mp4%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không khôi phục dữ liệu ảo
    }
};
