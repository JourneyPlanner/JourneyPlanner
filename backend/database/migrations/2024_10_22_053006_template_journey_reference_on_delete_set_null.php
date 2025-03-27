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
        Schema::table('journeys', function (Blueprint $table) {
            // Step 1: Drop foreign key constraint
            $table->dropForeign(['created_from']);

            // Step 2: Change the column to nullable
            $table->uuid('created_from')->nullable()->default(null)->change();

            // Step 3: Add the foreign key constraint back
            $table
                ->foreign('created_from')
                ->references('id')
                ->on('journeys')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journeys', function (Blueprint $table) {
            // Step 1: Drop foreign key constraint
            $table->dropForeign(['created_from']);

            // Step 2: Change the column to not nullable
            $table->uuid('created_from')->nullable()->default(null)->change();

            // Step 3: Add the foreign key constraint back
            $table->foreign('created_from')->references('id')->on('journeys');
        });
    }
};
