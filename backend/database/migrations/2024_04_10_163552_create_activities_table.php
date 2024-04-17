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
        Schema::create("activities", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->foreignUuid("journey_id")->constrained()->cascadeOnDelete();
            $table->string("name");
            $table->time("estimated_duration");
            $table->text("opening_hourse")->nullable();
            $table->string("email")->nullable();
            $table->string("phone")->nullable();
            $table->text("link")->nullable();
            $table->double("cost")->nullable();
            $table->text("description")->nullable();
            $table->text("address")->nullable();
            $table->text("mapbox_id")->nullable();
            $table->decimal("longitude", 11, 8)->nullable();
            $table->decimal("latitude", 10, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("activities");
    }
};
