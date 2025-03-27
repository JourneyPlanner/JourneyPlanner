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
            $table->string('description')->nullable()->default(null);
            $table->boolean('is_template')->default(false);
            $table
                ->foreignUuid('created_from')
                ->nullable()
                ->constrained('journeys')
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journeys', function (Blueprint $table) {
            $table->dropForeign(['created_from']);
        });
        Schema::dropColumns('journeys', [
            'description',
            'is_template',
            'created_from',
        ]);
    }
};
