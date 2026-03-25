<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignUuid('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // REGISTERED, CHECKED_OUT, CHECKED_IN, STATUS_CHANGED, BULK_IMPORTED, LABEL_PRINTED
            $table->string('entity_type')->nullable();
            $table->json('old_values')->nullable(); // sqlite uses json instead of jsonb
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
