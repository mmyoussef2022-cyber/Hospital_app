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
        Schema::table('surgeries', function (Blueprint $table) {
            $table->foreign('surgical_procedure_id')->references('id')->on('surgical_procedures')->onDelete('set null');
            $table->foreign('operating_room_id')->references('id')->on('operating_rooms')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surgeries', function (Blueprint $table) {
            $table->dropForeign(['surgical_procedure_id']);
            $table->dropForeign(['operating_room_id']);
        });
    }
};
