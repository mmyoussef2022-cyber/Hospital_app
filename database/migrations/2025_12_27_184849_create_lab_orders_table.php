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
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Lab order number');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            $table->enum('status', ['ordered', 'collected', 'processing', 'completed', 'cancelled'])
                  ->default('ordered')->comment('Order status');
            $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
            $table->datetime('ordered_at')->comment('When the test was ordered');
            $table->datetime('collected_at')->nullable()->comment('When specimen was collected');
            $table->datetime('completed_at')->nullable()->comment('When results were completed');
            $table->text('clinical_notes')->nullable()->comment('Clinical information from doctor');
            $table->text('collection_notes')->nullable()->comment('Notes from specimen collection');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('Total cost');
            $table->boolean('is_paid')->default(false)->comment('Payment status');
            $table->timestamps();
            
            $table->index(['patient_id']);
            $table->index(['doctor_id']);
            $table->index(['status']);
            $table->index(['priority']);
            $table->index(['ordered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
