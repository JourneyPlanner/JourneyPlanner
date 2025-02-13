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
        Schema::create("business_activities", function (Blueprint $table) {
            $table->timestamps();
            $table
                ->foreignUuid("business_id")
                ->constrained("businesses")
                ->cascadeOnDelete();
            $table
                ->foreignUuid("activity_id")
                ->constrained("activities")
                ->cascadeOnDelete();
            $table->primary(["business_id", "activity_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("business_activities");
    }
};
