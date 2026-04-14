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
        Schema::create('menu_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->timestamp('viewed_at');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->index(['menu_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_views');
    }
};
