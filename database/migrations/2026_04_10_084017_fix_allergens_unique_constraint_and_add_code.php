<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allergens', function (Blueprint $table) {
            // Drop the global unique on name (allergens are per-tenant)
            $table->dropUnique(['name']);

            // Add code column for UE allergen codes (e.g. 'gluten', 'eggs')
            $table->string('code', 30)->nullable()->after('name');

            // Composite unique: one allergen name per tenant
            $table->unique(['tenant_id', 'name']);

            // Composite unique: one code per tenant (nullable codes are excluded)
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('allergens', function (Blueprint $table) {
            $table->dropUnique(['allergens_tenant_id_name_unique']);
            $table->dropUnique(['allergens_tenant_id_code_unique']);
            $table->dropColumn('code');
            $table->unique('name');
        });
    }
};
