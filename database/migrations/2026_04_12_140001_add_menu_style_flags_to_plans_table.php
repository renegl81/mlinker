<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('has_menu_colors')->default(false)->after('has_custom_qr');
            $table->boolean('has_menu_fonts')->default(false)->after('has_menu_colors');
            $table->boolean('has_menu_layout')->default(false)->after('has_menu_fonts');
            $table->boolean('has_menu_advanced_style')->default(false)->after('has_menu_layout');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['has_menu_colors', 'has_menu_fonts', 'has_menu_layout', 'has_menu_advanced_style']);
        });
    }
};
