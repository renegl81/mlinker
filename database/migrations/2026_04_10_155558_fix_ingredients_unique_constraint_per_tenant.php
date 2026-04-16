<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            // Drop the global UNIQUE(name) that broke multi-tenant isolation.
            $table->dropUnique('ingredients_name_unique');

            // Unique per tenant: a tenant can't have the same ingredient twice,
            // but two tenants can each have their own "tomate".
            $table->unique(['tenant_id', 'name'], 'ingredients_tenant_id_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropUnique('ingredients_tenant_id_name_unique');
            $table->unique('name', 'ingredients_name_unique');
        });
    }
};
