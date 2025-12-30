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
        Schema::table('invoices', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('invoices', 'payment_term_id')) {
                $table->foreignId('payment_term_id')->nullable()->after('payment_terms')->constrained('payment_terms');
            }
            
            if (!Schema::hasColumn('invoices', 'discount_amount_applied')) {
                $table->decimal('discount_amount_applied', 10, 2)->default(0)->after('remaining_amount');
            }
            
            if (!Schema::hasColumn('invoices', 'last_reminder_sent')) {
                $table->datetime('last_reminder_sent')->nullable()->after('paid_at');
            }
            
            if (!Schema::hasColumn('invoices', 'reminder_count')) {
                $table->integer('reminder_count')->default(0)->after('last_reminder_sent');
            }
            
            if (!Schema::hasColumn('invoices', 'collection_status')) {
                $table->enum('collection_status', ['none', 'gentle', 'standard', 'urgent', 'final_notice', 'collection'])->default('none')->after('reminder_count');
            }
            
            if (!Schema::hasColumn('invoices', 'escalated_at')) {
                $table->datetime('escalated_at')->nullable()->after('collection_status');
            }
            
            if (!Schema::hasColumn('invoices', 'collection_notes')) {
                $table->text('collection_notes')->nullable()->after('escalated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['payment_term_id']);
            $table->dropColumn([
                'payment_term_id',
                'discount_amount_applied',
                'last_reminder_sent',
                'reminder_count',
                'collection_status',
                'escalated_at',
                'collection_notes'
            ]);
        });
    }
};
