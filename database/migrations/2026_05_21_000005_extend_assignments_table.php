<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('assignment_number')->nullable()->unique()->after('id');
            $table->foreignUuid('department_id')->nullable()->after('employee_id')
                ->constrained('departments')->nullOnDelete();
            $table->foreignUuid('office_location_id')->nullable()->after('department_id')
                ->constrained('office_locations')->nullOnDelete();
            $table->foreignUuid('condition_out_id')->nullable()->after('checked_in_at')
                ->constrained('asset_conditions')->nullOnDelete();
            $table->foreignUuid('condition_in_id')->nullable()->after('condition_out_id')
                ->constrained('asset_conditions')->nullOnDelete();
            $table->foreignUuid('assignment_status_id')->nullable()->after('condition_in_id')
                ->constrained('asset_assignment_statuses')->nullOnDelete();
            $table->foreignUuid('return_reason_id')->nullable()->after('assignment_status_id')
                ->constrained('asset_return_reasons')->nullOnDelete();
            $table->foreignUuid('maintenance_status_id')->nullable()->after('return_reason_id')
                ->constrained('maintenance_statuses')->nullOnDelete();
            $table->foreignUuid('maintenance_type_id')->nullable()->after('maintenance_status_id')
                ->constrained('maintenance_types')->nullOnDelete();
            $table->foreignUuid('warranty_provider_id')->nullable()->after('maintenance_type_id')
                ->constrained('warranty_providers')->nullOnDelete();
            $table->string('attachment_path')->nullable()->after('notes');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['condition_out', 'condition_in']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->json('notes_new')->nullable()->after('warranty_provider_id');
        });

        // Migrate existing text notes to JSON {en: ...}
        \DB::table('assignments')->whereNotNull('notes')->orderBy('id')->each(function ($row) {
            \DB::table('assignments')->where('id', $row->id)->update([
                'notes_new' => json_encode(['en' => $row->notes]),
            ]);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->renameColumn('notes_new', 'notes');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->text('notes_old')->nullable();
        });

        \DB::table('assignments')->whereNotNull('notes')->orderBy('id')->each(function ($row) {
            $decoded = json_decode($row->notes, true);
            $text = $decoded['en'] ?? null;
            \DB::table('assignments')->where('id', $row->id)->update(['notes_old' => $text]);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('notes');
            $table->renameColumn('notes_old', 'notes');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $columns = [
                'assignment_number', 'department_id', 'office_location_id',
                'condition_out_id', 'condition_in_id', 'assignment_status_id',
                'return_reason_id', 'maintenance_status_id', 'maintenance_type_id',
                'warranty_provider_id', 'attachment_path',
            ];

            foreach ($columns as $column) {
                try { $table->dropForeign([$column]); } catch (\Throwable $e) {}
            }

            $table->dropColumn($columns);

            $table->string('condition_out')->nullable();
            $table->string('condition_in')->nullable();
        });
    }
};
