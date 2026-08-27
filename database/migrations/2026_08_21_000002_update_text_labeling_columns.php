<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('label_text_projects') && !Schema::hasColumn('label_text_projects', 'task_type')) {
            Schema::table('label_text_projects', function (Blueprint $table) {
                $table->string('task_type')->default('ner')->after('name');
            });
        }

        if (Schema::hasTable('label_text_tasks') && !Schema::hasColumn('label_text_tasks', 'assignee_id')) {
            Schema::table('label_text_tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('assignee_id')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('label_text_documents') && !Schema::hasColumn('label_text_documents', 'title')) {
            Schema::table('label_text_documents', function (Blueprint $table) {
                $table->string('title')->nullable()->after('task_id');
            });
        }

        if (Schema::hasTable('label_text_labels') && !Schema::hasColumn('label_text_labels', 'color')) {
            Schema::table('label_text_labels', function (Blueprint $table) {
                $table->string('color')->default('#ef4444')->after('name');
            });
        }
    }

    public function down(): void
    {
    }
};
