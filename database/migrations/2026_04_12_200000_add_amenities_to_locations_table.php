<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('is_pet_friendly')->default(false)->after('order_whatsapp');
            $table->boolean('has_wifi')->default(false)->after('is_pet_friendly');
            $table->boolean('has_terrace')->default(false)->after('has_wifi');
            $table->boolean('has_parking')->default(false)->after('has_terrace');
            $table->boolean('is_accessible')->default(false)->after('has_parking');
            $table->string('reservation_url', 500)->nullable()->after('is_accessible');
            $table->string('reservation_phone', 20)->nullable()->after('reservation_url');
            $table->string('instagram')->nullable()->after('reservation_phone');
            $table->string('facebook')->nullable()->after('instagram');
            $table->string('google_maps_url', 500)->nullable()->after('facebook');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'is_pet_friendly',
                'has_wifi',
                'has_terrace',
                'has_parking',
                'is_accessible',
                'reservation_url',
                'reservation_phone',
                'instagram',
                'facebook',
                'google_maps_url',
            ]);
        });
    }
};
