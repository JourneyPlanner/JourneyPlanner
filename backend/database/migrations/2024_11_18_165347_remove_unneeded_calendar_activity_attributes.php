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
        Schema::table('calendar_activities', function (Blueprint $table) {
            $table->dropColumn('end');
            $table->dropColumn('is_repeat_series_starter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_activities', function (Blueprint $table) {
            $table->dateTime('end')->default('1970-01-01 00:00:00'); // End value wasn't even used anywhere so it doesn't matter what I set it to here
            $table->boolean('is_repeat_series_starter')->nullable();
        });
    }
};
