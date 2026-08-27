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
        Schema::create('disease_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('garden_id')->nullable();
            $table->string('image_path');
            $table->string('disease')->nullable();
            $table->float('confidence')->nullable();
            $table->text('result');
            $table->timestamps();
        });

        Schema::create('diagnosis_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnosis_id');
            $table->foreignId('user_id');
            $table->integer('rating');
            $table->text('content')->nullable();
            $table->timestamps();
        });

        Schema::create('pest_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garden_id');
            $table->string('current_stage')->nullable();
            $table->dateTime('outbreak_at')->nullable();
            $table->text('result')->nullable();
            $table->float('confidence')->nullable();
            $table->timestamps();

            $table->index(['garden_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pest_predictions');
        Schema::dropIfExists('diagnosis_feedback');
        Schema::dropIfExists('disease_diagnoses');
    }
};
