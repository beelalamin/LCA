<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $simple = [
            'manufacturers',
            'suppliers',
            'departments',
            'job_titles',
            'employment_types',
            'office_locations',
            'asset_conditions',
            'ownership_types',
            'criticality_levels',
            'disposal_methods',
            'warranty_providers',
            'asset_return_reasons',
            'maintenance_types',
            'disposal_reasons',
        ];

        foreach ($simple as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            Schema::create($table, function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->string('code')->unique();
                $t->json('name');
                $t->json('description')->nullable();
                $t->integer('sort_order')->default(0);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('statuses')) {
            Schema::create('statuses', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->string('scope')->default('asset');
                $t->string('code');
                $t->json('name');
                $t->json('description')->nullable();
                $t->string('color')->nullable();
                $t->integer('sort_order')->default(0);
                $t->boolean('is_active')->default(true);
                $t->timestamps();

                $t->unique(['scope', 'code']);
                $t->index('scope');
            });
        }

        if (! Schema::hasTable('models')) {
            Schema::create('models', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->string('code')->unique();
                $t->json('name');
                $t->json('description')->nullable();
                $t->foreignUuid('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
                $t->integer('sort_order')->default(0);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'models',
            'disposal_reasons',
            'maintenance_types',
            'asset_return_reasons',
            'warranty_providers',
            'disposal_methods',
            'criticality_levels',
            'ownership_types',
            'asset_conditions',
            'office_locations',
            'employment_types',
            'job_titles',
            'departments',
            'suppliers',
            'manufacturers',
            'statuses',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
