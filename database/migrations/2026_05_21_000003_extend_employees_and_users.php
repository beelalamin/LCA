<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('department_id')->nullable()->after('phone')
                ->constrained('departments')->nullOnDelete();
            $table->foreignUuid('job_title_id')->nullable()->after('department_id')
                ->constrained('job_titles')->nullOnDelete();
            $table->foreignUuid('employment_type_id')->nullable()->after('job_title_id')
                ->constrained('employment_types')->nullOnDelete();
            $table->foreignUuid('office_location_id')->nullable()->after('employment_type_id')
                ->constrained('office_locations')->nullOnDelete();
            $table->foreignUuid('line_manager_id')->nullable()->after('office_location_id');
            $table->foreignUuid('status_id')->nullable()->after('line_manager_id')
                ->constrained('statuses')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('line_manager_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['job_title_id']);
            $table->dropForeign(['employment_type_id']);
            $table->dropForeign(['office_location_id']);
            $table->dropForeign(['line_manager_id']);
            $table->dropForeign(['status_id']);

            $table->dropColumn([
                'department_id',
                'job_title_id',
                'employment_type_id',
                'office_location_id',
                'line_manager_id',
                'status_id',
            ]);
        });
    }
};
