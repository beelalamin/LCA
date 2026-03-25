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
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->unique()->nullable();
            $table->json('name'); // translatable
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('PURCHASED'); // enum: PURCHASED|AVAILABLE|ASSIGNED|IN_REPAIR|RETIRED|DISPOSED
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('location')->nullable();
            $table->json('notes')->nullable(); // translatable
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
