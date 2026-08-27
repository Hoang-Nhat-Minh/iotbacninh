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
        Schema::create('annotation_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by');
            $table->timestamps();
        });

        Schema::create('annotation_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('annotation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id');
            $table->foreignId('assignee_id')->nullable();
            $table->string('status')->default('assigned');
            $table->string('stage')->default('annotation');
            $table->integer('progress')->default(0);
            $table->timestamps();
        });

        Schema::create('annotation_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('annotation_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('image_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id');
            $table->foreignId('label_id');
            $table->string('type');
            $table->json('data');
            $table->text('description')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });

        Schema::create('annotation_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id');
            $table->foreignId('image_id');
            $table->foreignId('annotation_id')->nullable();
            $table->foreignId('created_by');
            $table->text('description');
            $table->json('data')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annotation_issues');
        Schema::dropIfExists('image_annotations');
        Schema::dropIfExists('annotation_labels');
        Schema::dropIfExists('annotation_images');
        Schema::dropIfExists('annotation_jobs');
        Schema::dropIfExists('annotation_tasks');
        Schema::dropIfExists('annotation_projects');
    }
};
