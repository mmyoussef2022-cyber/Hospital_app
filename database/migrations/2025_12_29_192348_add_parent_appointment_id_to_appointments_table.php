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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('parent_appointment_id')->nullable()->after('doctor_id')->constrained('appointments')->onDelete('cascade');
            $table->index('parent_appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['parent_appointment_id']);
            $table->dropIndex(['parent_appointment_id']);
            $table->dropColumn('parent_appointment_id');
        });
    }
};
