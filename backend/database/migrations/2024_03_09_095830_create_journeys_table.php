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
        Schema::create("journeys", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("name");
            $table->string("destination");
            $table->date("from");
            $table->date("to");
            $table->uuid("invite");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("journeys");
    }
};
