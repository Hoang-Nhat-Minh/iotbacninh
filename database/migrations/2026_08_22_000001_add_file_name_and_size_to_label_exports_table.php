<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('label_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('label_exports', 'file_name')) {
                $table->string('file_name')->nullable()->after('format');
            }
            if (!Schema::hasColumn('label_exports', 'file_size')) {
                $table->bigInteger('file_size')->default(0)->after('file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('label_exports', function (Blueprint $table) {
            if (Schema::hasColumn('label_exports', 'file_name')) {
                $table->dropColumn('file_name');
            }
            if (Schema::hasColumn('label_exports', 'file_size')) {
                $table->dropColumn('file_size');
            }
        });
    }
};
