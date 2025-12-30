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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            // Item Information
            $table->string('item_type'); // service, medication, lab_test, radiology, room, etc.
            $table->string('item_code')->nullable();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            
            // Reference to original item (polymorphic)
            $table->morphs('itemable'); // itemable_type, itemable_id
            
            // Pricing Information
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            // Insurance Coverage
            $table->boolean('covered_by_insurance')->default(false);
            $table->decimal('insurance_coverage_percentage', 5, 2)->default(0);
            $table->decimal('insurance_covered_amount', 10, 2)->default(0);
            $table->decimal('patient_responsibility', 10, 2)->default(0);
            
            // Additional Information
            $table->json('item_details')->nullable(); // For storing additional item-specific data
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'item_type']);
            $table->index(['itemable_type', 'itemable_id'], 'invoice_items_itemable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};