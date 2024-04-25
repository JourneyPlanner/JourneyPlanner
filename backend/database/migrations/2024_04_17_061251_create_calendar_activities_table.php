<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("calendar_activities", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("activity_id")
                ->constrained()
                ->cascadeOnDelete();
            $table->dateTime("start");
            $table->dateTime("end");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("calendar_activities");
    }
};
