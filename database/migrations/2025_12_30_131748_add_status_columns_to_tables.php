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
        // Add status column to patients table if it doesn't exist
        if (!Schema::hasColumn('patients', 'status')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('id');
            });
        }

        // Add status column to doctors table if it doesn't exist
        if (!Schema::hasColumn('doctors', 'status')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended'])->default('active')->after('id');
            });
        }

        // Add status column to insurance_companies table if it doesn't exist
        if (!Schema::hasColumn('insurance_companies', 'status')) {
            Schema::table('insurance_companies', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active')->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('insurance_companies', function (Blueprint $table) {
            if (Schema::hasColumn('insurance_companies', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};