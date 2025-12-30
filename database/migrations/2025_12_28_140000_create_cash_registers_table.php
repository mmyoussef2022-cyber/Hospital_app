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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('register_number')->unique();
            $table->string('register_name');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->decimal('expected_balance', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'reconciling'])->default('active');
            $table->string('location')->nullable();
            $table->timestamp('last_reconciled_at')->nullable();
            $table->foreignId('last_reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('reconciliation_difference', 10, 2)->default(0);
            $table->text('reconciliation_notes')->nullable();
            $table->json('audit_trail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};