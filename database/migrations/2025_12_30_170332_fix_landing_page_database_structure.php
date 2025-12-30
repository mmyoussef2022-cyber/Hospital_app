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
        // Fix landing_page_offers table
        if (Schema::hasTable('landing_page_offers')) {
            Schema::table('landing_page_offers', function (Blueprint $table) {
                if (!Schema::hasColumn('landing_page_offers', 'start_date')) {
                    $table->date('start_date')->nullable()->after('discount_value');
                }
                if (!Schema::hasColumn('landing_page_offers', 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }
            });
        }

        // Fix chatbot_settings table
        if (Schema::hasTable('chatbot_settings')) {
            Schema::table('chatbot_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('chatbot_settings', 'whatsapp_number')) {
                    $table->string('whatsapp_number')->nullable()->after('welcome_message');
                }
                if (!Schema::hasColumn('chatbot_settings', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('whatsapp_number');
                }
            });
        }

        // Fix doctors table
        if (Schema::hasTable('doctors')) {
            Schema::table('doctors', function (Blueprint $table) {
                if (!Schema::hasColumn('doctors', 'name')) {
                    $table->string('name')->after('id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns
        if (Schema::hasTable('landing_page_offers')) {
            Schema::table('landing_page_offers', function (Blueprint $table) {
                if (Schema::hasColumn('landing_page_offers', 'start_date')) {
                    $table->dropColumn('start_date');
                }
                if (Schema::hasColumn('landing_page_offers', 'end_date')) {
                    $table->dropColumn('end_date');
                }
            });
        }

        if (Schema::hasTable('chatbot_settings')) {
            Schema::table('chatbot_settings', function (Blueprint $table) {
                if (Schema::hasColumn('chatbot_settings', 'whatsapp_number')) {
                    $table->dropColumn('whatsapp_number');
                }
                if (Schema::hasColumn('chatbot_settings', 'is_enabled')) {
                    $table->dropColumn('is_enabled');
                }
            });
        }

        if (Schema::hasTable('doctors')) {
            Schema::table('doctors', function (Blueprint $table) {
                if (Schema::hasColumn('doctors', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }
    }
};