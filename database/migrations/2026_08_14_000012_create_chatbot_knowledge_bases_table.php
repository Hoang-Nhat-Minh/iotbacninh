<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->string('intent', 100);
            $table->text('question_pattern');
            $table->text('answer');
            $table->json('entities')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_knowledge_bases');
    }
};
