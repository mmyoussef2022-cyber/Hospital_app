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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('shift_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('cash_register_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('shift_type', ['morning', 'afternoon', 'evening', 'night', 'emergency'])->default('morning');
            $table->date('shift_date');
            $table->time('scheduled_start');
            $table->time('scheduled_end');
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->decimal('opening_cash_balance', 10, 2)->default(0);
            $table->decimal('closing_cash_balance', 10, 2)->default(0);
            $table->decimal('expected_cash_balance', 10, 2)->default(0);
            $table->decimal('cash_difference', 10, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('total_collections', 10, 2)->default(0);
            $table->integer('patients_served')->default(0);
            $table->text('shift_notes')->nullable();
            $table->text('handover_notes')->nullable();
            $table->boolean('cash_verified')->default(false);
            $table->timestamp('cash_verified_at')->nullable();
            $table->foreignId('cash_verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'shift_date']);
            $table->index(['department_id', 'shift_date']);
            $table->index(['status', 'shift_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};