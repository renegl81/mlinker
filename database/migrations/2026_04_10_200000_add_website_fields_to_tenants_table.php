<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('has_website')->default(false)->after('onboarding_step');
            $table->string('business_type', 30)->default('restaurant')->after('has_website');
            $table->string('home_template', 50)->default('HomeClassic')->after('business_type');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['has_website', 'business_type', 'home_template']);
        });
    }
};
