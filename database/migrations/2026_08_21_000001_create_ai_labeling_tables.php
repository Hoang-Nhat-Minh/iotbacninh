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
        // 1. label_projects
        Schema::create('label_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 2. label_tasks
        Schema::create('label_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 3. label_jobs
        Schema::create('label_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('stage')->default('annotation');
            $table->integer('progress')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 4. label_images
        Schema::create('label_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('status')->default('unlabeled');
            $table->timestamps();
        });

        // 5. label_labels
        Schema::create('label_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#10b981');
            $table->timestamps();
        });

        // 6. label_annotations
        Schema::create('label_annotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('image_id');
            $table->unsignedBigInteger('job_id')->nullable();
            $table->unsignedBigInteger('label_id');
            $table->string('annotation_type')->default('bbox');
            $table->json('coordinates')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 7. label_reviews
        Schema::create('label_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('decision')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 8. label_review_issues
        Schema::create('label_review_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->unsignedBigInteger('annotation_id')->nullable();
            $table->string('issue_type');
            $table->text('description')->nullable();
            $table->json('coordinates')->nullable();
            $table->string('status')->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 9. label_exports
        Schema::create('label_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('export_type')->default('image');
            $table->string('format')->default('coco');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 10. label_text_projects
        Schema::create('label_text_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('task_type')->default('ner');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 11. label_text_tasks
        Schema::create('label_text_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('task_type')->default('ner');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 12. label_text_documents
        Schema::create('label_text_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('title')->nullable();
            $table->longText('content');
            $table->string('file_path')->nullable();
            $table->string('status')->default('unlabeled');
            $table->timestamps();
        });

        // 13. label_text_labels
        Schema::create('label_text_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->string('color')->default('#ef4444');
            $table->string('type')->default('entity');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 14. label_text_annotations
        Schema::create('label_text_annotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('label_id');
            $table->integer('start_position');
            $table->integer('end_position');
            $table->text('content');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 15. label_knowledge_bases
        Schema::create('label_knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 16. label_knowledge_documents
        Schema::create('label_knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('knowledge_base_id');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('source')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('processed');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 17. label_knowledge_chunks
        Schema::create('label_knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->text('content');
            $table->integer('chunk_index')->default(0);
            $table->json('embedding')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_knowledge_chunks');
        Schema::dropIfExists('label_knowledge_documents');
        Schema::dropIfExists('label_knowledge_bases');
        Schema::dropIfExists('label_text_annotations');
        Schema::dropIfExists('label_text_labels');
        Schema::dropIfExists('label_text_documents');
        Schema::dropIfExists('label_text_tasks');
        Schema::dropIfExists('label_text_projects');
        Schema::dropIfExists('label_exports');
        Schema::dropIfExists('label_review_issues');
        Schema::dropIfExists('label_reviews');
        Schema::dropIfExists('label_annotations');
        Schema::dropIfExists('label_labels');
        Schema::dropIfExists('label_images');
        Schema::dropIfExists('label_jobs');
        Schema::dropIfExists('label_tasks');
        Schema::dropIfExists('label_projects');
    }
};
