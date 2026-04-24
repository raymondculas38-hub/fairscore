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
        Schema::table('events', function (Blueprint $table) {
            // 'overall' = show weighted rankings | 'criteria' = show a specific criterion
            $table->string('public_display_mode')->default('overall')->after('pin');
            // null means no specific criterion selected (used when mode = 'criteria')
            $table->foreignId('public_criteria_id')
                  ->nullable()
                  ->constrained('criteria')
                  ->nullOnDelete()
                  ->after('public_display_mode');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['public_criteria_id']);
            $table->dropColumn(['public_display_mode', 'public_criteria_id']);
        });
    }
};
