<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('assignments', 'transactions');

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type')->default('check_out')->after('id');
            $table->renameColumn('assignment_number', 'transaction_number');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('transaction_number', 'assignment_number');
            $table->dropColumn('type');
        });

        Schema::rename('transactions', 'assignments');
    }
};
