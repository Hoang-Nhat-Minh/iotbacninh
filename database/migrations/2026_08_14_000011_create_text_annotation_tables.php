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
        Schema::create('text_annotation_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by');
            $table->timestamps();
        });

        Schema::create('text_annotation_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->text('content');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('text_annotation_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });

        Schema::create('text_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id');
            $table->foreignId('label_id');
            $table->integer('start')->nullable();
            $table->integer('end')->nullable();
            $table->text('content')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('text_annotations');
        Schema::dropIfExists('text_annotation_labels');
        Schema::dropIfExists('text_annotation_tasks');
        Schema::dropIfExists('text_annotation_projects');
    }
};
