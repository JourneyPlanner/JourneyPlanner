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
        Schema::table("activities", function (Blueprint $table) {
            $table
                ->enum("repeat_type", ["daily", "weekly", "custom"])
                ->nullable();
            $table->integer("repeat_interval")->nullable();
            $table->enum("repeat_interval_unit", ["days", "weeks"])->nullable();
            $table
                ->set("repeat_on", [
                    "Mon",
                    "Tue",
                    "Wed",
                    "Thu",
                    "Fri",
                    "Sat",
                    "Sun",
                ])
                ->nullable();
            $table->date("repeat_end_date")->nullable();
            $table->integer("repeat_end_occurrences")->nullable();
            $table
                ->foreignUuid("parent_id")
                ->nullable()
                ->constrained("activities")
                ->nullOnDelete();
        });

        Schema::table("calendar_activities", function (Blueprint $table) {
            $table->boolean("is_repeat_series_starter")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("activities", function (Blueprint $table) {
            $table->dropColumn("repeat_type");
            $table->dropColumn("repeat_interval");
            $table->dropColumn("repeat_interval_unit");
            $table->dropColumn("repeat_on");
            $table->dropColumn("repeat_end_date");
            $table->dropColumn("repeat_end_occurrences");
            $table->dropForeign(["parent_id"]);
            $table->dropColumn("parent_id");
        });

        Schema::table("calendar_activities", function (Blueprint $table) {
            $table->dropColumn("is_repeat_series_starter");
        });
    }
};
