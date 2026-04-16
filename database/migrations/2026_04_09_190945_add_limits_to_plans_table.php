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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('slug', 30)->unique()->after('id');
            $table->string('stripe_price_id')->nullable()->after('description');
            $table->unsignedSmallInteger('max_locations')->default(1)->after('stripe_price_id');
            $table->unsignedSmallInteger('max_menus_per_location')->default(1)->after('max_locations');
            $table->unsignedSmallInteger('max_products')->default(25)->after('max_menus_per_location');
            $table->unsignedSmallInteger('max_images')->default(0)->after('max_products');
            $table->boolean('has_analytics')->default(false)->after('max_images');
            $table->boolean('has_custom_qr')->default(false)->after('has_analytics');
            $table->boolean('has_multilang')->default(false)->after('has_custom_qr');
            $table->boolean('has_api_access')->default(false)->after('has_multilang');
            $table->boolean('has_custom_domain')->default(false)->after('has_api_access');
            $table->boolean('show_branding')->default(true)->after('has_custom_domain');
            $table->unsignedSmallInteger('trial_days')->default(0)->after('show_branding');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('trial_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'stripe_price_id',
                'max_locations',
                'max_menus_per_location',
                'max_products',
                'max_images',
                'has_analytics',
                'has_custom_qr',
                'has_multilang',
                'has_api_access',
                'has_custom_domain',
                'show_branding',
                'trial_days',
                'sort_order',
            ]);
        });
    }
};
