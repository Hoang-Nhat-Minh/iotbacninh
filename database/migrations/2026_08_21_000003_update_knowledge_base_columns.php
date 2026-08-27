<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('label_knowledge_documents') && !Schema::hasColumn('label_knowledge_documents', 'source_type')) {
            Schema::table('label_knowledge_documents', function (Blueprint $table) {
                $table->string('source_type')->default('manual_entry')->after('content');
            });
        }

        if (Schema::hasTable('label_knowledge_chunks')) {
            Schema::table('label_knowledge_chunks', function (Blueprint $table) {
                if (!Schema::hasColumn('label_knowledge_chunks', 'chunk_text')) {
                    $table->text('chunk_text')->nullable()->after('document_id');
                }
                if (!Schema::hasColumn('label_knowledge_chunks', 'token_count')) {
                    $table->integer('token_count')->default(0)->after('chunk_text');
                }
                if (!Schema::hasColumn('label_knowledge_chunks', 'vector_id')) {
                    $table->string('vector_id')->nullable()->after('token_count');
                }
                if (!Schema::hasColumn('label_knowledge_chunks', 'status')) {
                    $table->string('status')->default('indexed')->after('vector_id');
                }
            });
        }
    }

    public function down(): void
    {
    }
};
