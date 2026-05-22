<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('full_name_ar');
            $table->foreignUuid('department_id')->nullable()->after('phone')
                ->constrained('departments')->nullOnDelete();
            $table->foreignUuid('job_title_id')->nullable()->after('department_id')
                ->constrained('job_titles')->nullOnDelete();
            $table->foreignUuid('employment_type_id')->nullable()->after('job_title_id')
                ->constrained('employment_types')->nullOnDelete();
            $table->foreignUuid('office_location_id')->nullable()->after('employment_type_id')
                ->constrained('office_locations')->nullOnDelete();
            $table->foreignUuid('line_manager_id')->nullable()->after('office_location_id')
                ->constrained('employees')->nullOnDelete();
            $table->foreignUuid('status_id')->nullable()->after('line_manager_id')
                ->constrained('statuses')->nullOnDelete();
            $table->date('joining_date')->nullable()->after('status_id');
            $table->date('leaving_date')->nullable()->after('joining_date');
            $table->string('emergency_contact_name')->nullable()->after('leaving_date');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('notes')->nullable()->after('emergency_contact_phone');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['department', 'job_title', 'location']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('employee_id')->nullable()->after('email')
                ->unique()
                ->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->json('department')->nullable();
            $table->string('job_title')->nullable();
            $table->string('location')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['job_title_id']);
            $table->dropForeign(['employment_type_id']);
            $table->dropForeign(['office_location_id']);
            $table->dropForeign(['line_manager_id']);
            $table->dropForeign(['status_id']);

            $table->dropColumn([
                'photo_path',
                'department_id',
                'job_title_id',
                'employment_type_id',
                'office_location_id',
                'line_manager_id',
                'status_id',
                'joining_date',
                'leaving_date',
                'emergency_contact_name',
                'emergency_contact_phone',
                'notes',
            ]);
        });
    }
};
