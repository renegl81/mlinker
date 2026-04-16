<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('page_type', 30); // 'home', 'menu', 'product'
            $table->unsignedBigInteger('resource_id')->nullable(); // menu_id or product_id
            $table->string('event', 30)->default('view'); // 'view', 'qr_download'
            $table->timestamp('viewed_at');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('device_type', 20)->nullable(); // 'mobile', 'tablet', 'desktop'
            $table->index(['tenant_id', 'page_type', 'viewed_at']);
            $table->index(['tenant_id', 'resource_id', 'page_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
