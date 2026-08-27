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
        Schema::create('gardens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('crop_type')->nullable();
            $table->string('location')->nullable();
            $table->integer('area_m2')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('boundary')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('care_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('care_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('care_category_id');
            $table->foreignId('garden_id')->nullable();
            $table->dateTime('performed_at');
            $table->text('content');
            $table->timestamps();

            $table->index(['user_id', 'performed_at']);
        });

        Schema::create('used_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('care_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->string('crop_type');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_processes');
        Schema::dropIfExists('used_products');
        Schema::dropIfExists('care_histories');
        Schema::dropIfExists('care_categories');
        Schema::dropIfExists('gardens');
    }
};
