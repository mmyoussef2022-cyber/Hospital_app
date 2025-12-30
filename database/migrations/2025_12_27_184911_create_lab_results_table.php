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
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->onDelete('cascade');
            $table->string('parameter_name')->comment('Test parameter name');
            $table->string('value')->comment('Result value');
            $table->string('unit')->nullable()->comment('Unit of measurement');
            $table->string('reference_range')->nullable()->comment('Normal reference range');
            $table->enum('flag', ['normal', 'high', 'low', 'critical_high', 'critical_low', 'abnormal'])
                  ->default('normal')->comment('Result interpretation flag');
            $table->text('notes')->nullable()->comment('Additional notes about the result');
            $table->foreignId('verified_by')->nullable()->constrained('users')->comment('Lab technician who verified');
            $table->datetime('verified_at')->nullable()->comment('When result was verified');
            $table->boolean('is_critical')->default(false)->comment('Whether result requires immediate attention');
            $table->datetime('critical_notified_at')->nullable()->comment('When critical value was notified');
            $table->timestamps();
            
            $table->index(['lab_order_id']);
            $table->index(['flag']);
            $table->index(['is_critical']);
            $table->index(['verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
