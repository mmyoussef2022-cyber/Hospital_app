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
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('handover_number')->unique();
            $table->foreignId('from_shift_id')->constrained('shifts')->onDelete('cascade');
            $table->foreignId('to_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            $table->timestamp('handover_date');
            $table->decimal('cash_balance_handed_over', 10, 2)->default(0);
            $table->decimal('cash_balance_received', 10, 2)->default(0);
            $table->decimal('cash_difference', 10, 2)->default(0);
            $table->boolean('cash_balance_verified')->default(false);
            $table->boolean('register_keys_handed_over')->default(false);
            $table->boolean('pending_transactions_reviewed')->default(false);
            $table->boolean('system_access_transferred')->default(false);
            $table->text('outstanding_tasks')->nullable();
            $table->text('pending_issues')->nullable();
            $table->text('important_notes')->nullable();
            $table->text('equipment_status')->nullable();
            $table->text('handover_notes')->nullable();
            $table->json('checklist_items')->nullable();
            $table->json('pending_transactions')->nullable();
            $table->json('shift_summary')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'disputed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('witnessed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('witnessed_at')->nullable();
            $table->text('witness_notes')->nullable();
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->index(['from_shift_id', 'handover_date']);
            $table->index(['to_shift_id', 'handover_date']);
            $table->index(['department_id', 'handover_date']);
            $table->index(['status', 'handover_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};