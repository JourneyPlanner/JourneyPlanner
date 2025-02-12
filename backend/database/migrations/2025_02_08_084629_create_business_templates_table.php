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
        Schema::create("business_templates", function (Blueprint $table) {
            $table->timestamps();
            $table
                ->foreignUuid("business_id")
                ->constrained("businesses")
                ->cascadeOnDelete();
            $table
                ->foreignUuid("template_id")
                ->constrained("journeys")
                ->cascadeOnDelete();
            $table->boolean("created_by_business")->default(false);
            $table->boolean("visible")->default(true);
            $table->primary(["business_id", "template_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("business_templates");
    }
};
