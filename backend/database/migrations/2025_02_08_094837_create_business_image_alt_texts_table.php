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
        Schema::create("business_image_alt_texts", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->timestamps();
            $table
                ->foreignUuid("business_image_id")
                ->constrained("business_images")
                ->cascadeOnDelete();
            $table->enum("language", ["en", "de"]);
            $table->string("alt_text");
            $table->unique(["business_image_id", "language"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("business_image_alt_texts");
    }
};
