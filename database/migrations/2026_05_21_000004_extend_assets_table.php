<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignUuid('sub_category_id')->nullable()->after('category_id')
                ->constrained('categories')->nullOnDelete();
            $table->foreignUuid('manufacturer_id')->nullable()->after('sub_category_id')
                ->constrained('manufacturers')->nullOnDelete();
            $table->foreignUuid('model_id')->nullable()->after('manufacturer_id')
                ->constrained('models')->nullOnDelete();
            $table->smallInteger('manufacturer_year')->nullable()->after('model_id');
            $table->foreignUuid('status_id')->nullable()->after('manufacturer_year')
                ->constrained('statuses')->nullOnDelete();
            $table->foreignUuid('office_location_id')->nullable()->after('status_id')
                ->constrained('office_locations')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->after('office_location_id')
                ->constrained('departments')->nullOnDelete();
            $table->foreignUuid('condition_id')->nullable()->after('department_id')
                ->constrained('asset_conditions')->nullOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->after('condition_id')
                ->constrained('suppliers')->nullOnDelete();
            $table->string('invoice_number')->nullable()->after('supplier_id');
            $table->string('purchase_order_number')->nullable()->after('invoice_number');
            $table->foreignUuid('warranty_status_id')->nullable()->after('purchase_order_number')
                ->constrained('warranty_statuses')->nullOnDelete();
            $table->foreignUuid('warranty_provider_id')->nullable()->after('warranty_status_id')
                ->constrained('warranty_providers')->nullOnDelete();
            $table->string('image_path')->nullable()->after('warranty_provider_id');
            $table->json('description')->nullable()->after('image_path');
            $table->boolean('is_active')->default(true)->after('description');
            $table->string('qr_code')->nullable()->after('is_active');
            $table->foreignUuid('criticality_id')->nullable()->after('qr_code')
                ->constrained('criticality_levels')->nullOnDelete();
            $table->date('last_maintenance_date')->nullable()->after('criticality_id');
            $table->date('next_maintenance_date')->nullable()->after('last_maintenance_date');
            $table->foreignUuid('assigned_to_employee_id')->nullable()->after('next_maintenance_date')
                ->constrained('employees')->nullOnDelete();
            $table->foreignUuid('assignment_status_id')->nullable()->after('assigned_to_employee_id')
                ->constrained('asset_assignment_statuses')->nullOnDelete();
            $table->date('assigned_date')->nullable()->after('assignment_status_id');
            $table->date('return_date')->nullable()->after('assigned_date');
            $table->foreignUuid('return_reason_id')->nullable()->after('return_date')
                ->constrained('asset_return_reasons')->nullOnDelete();
            $table->foreignUuid('ownership_type_id')->nullable()->after('return_reason_id')
                ->constrained('ownership_types')->nullOnDelete();
            $table->foreignUuid('maintenance_status_id')->nullable()->after('ownership_type_id')
                ->constrained('maintenance_statuses')->nullOnDelete();
            $table->foreignUuid('maintenance_type_id')->nullable()->after('maintenance_status_id')
                ->constrained('maintenance_types')->nullOnDelete();
            $table->date('disposal_date')->nullable()->after('maintenance_type_id');
            $table->foreignUuid('disposal_method_id')->nullable()->after('disposal_date')
                ->constrained('disposal_methods')->nullOnDelete();
            $table->foreignUuid('disposal_reason_id')->nullable()->after('disposal_method_id')
                ->constrained('disposal_reasons')->nullOnDelete();
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['status', 'manufacturer', 'model', 'location']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->index(['is_active', 'status_id'], 'assets_active_status_idx');
            $table->index('warranty_expiry');
            $table->index('next_maintenance_date');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('assets_active_status_idx');
            $table->dropIndex(['warranty_expiry']);
            $table->dropIndex(['next_maintenance_date']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->string('status')->default('PURCHASED');
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('location')->nullable();
        });

        Schema::table('assets', function (Blueprint $table) {
            $columns = [
                'sub_category_id', 'manufacturer_id', 'model_id', 'manufacturer_year',
                'status_id', 'office_location_id', 'department_id', 'condition_id',
                'supplier_id', 'invoice_number', 'purchase_order_number',
                'warranty_status_id', 'warranty_provider_id', 'image_path',
                'description', 'is_active', 'qr_code',
                'criticality_id', 'last_maintenance_date', 'next_maintenance_date',
                'assigned_to_employee_id', 'assignment_status_id', 'assigned_date',
                'return_date', 'return_reason_id', 'ownership_type_id',
                'maintenance_status_id', 'maintenance_type_id', 'disposal_date',
                'disposal_method_id', 'disposal_reason_id',
            ];

            foreach ($columns as $column) {
                try {
                    $table->dropForeign([$column]);
                } catch (\Throwable $e) {
                    // not a FK
                }
            }

            $table->dropColumn($columns);
        });
    }
};
