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
        Schema::create("template_ratings", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("template_id")
                ->references("id")
                ->on("journeys")
                ->cascadeOnDelete();
            $table
                ->foreignUuid("user_id")
                ->references("id")
                ->on("users")
                ->cascadeOnDelete();
            $table->unsignedTinyInteger("rating");
            $table->timestamps();
            $table->unique(["template_id", "user_id"]);
            $table->index("template_id");
            $table->index("user_id");
        });

        Schema::table("journeys", function (Blueprint $table) {
            $table->float("average_rating")->default(0);
            $table->unsignedInteger("total_ratings")->default(0);
            $table->index("average_rating");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("template_ratings");
        Schema::table("journeys", function (Blueprint $table) {
            $table->dropColumn("average_rating");
            $table->dropColumn("total_ratings");
        });
    }
};
