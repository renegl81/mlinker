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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('address', 255);
            $table->string('city', 255);
            $table->string('province', 255);
            $table->string('postal_code', 255);
            $table->string('phone', 30)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id');
            $table->foreignId('country_id');
            $table->string('image_url', 255)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->string('slug', 255);
            $table->string('url', 255);
            $table->string('lang', 255);
            $table->json('languages')->nullable();
            $table->string('currency', 255);
            $table->string('time_format', 255);
            $table->string('time_zone', 255);
            $table->json('social_medias')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
