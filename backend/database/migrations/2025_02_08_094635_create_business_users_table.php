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
        Schema::create("business_users", function (Blueprint $table) {
            $table->timestamps();
            $table
                ->foreignUuid("business_id")
                ->constrained("businesses")
                ->cascadeOnDelete();
            $table
                ->foreignUuid("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table->primary(["business_id", "user_id"]);
            // Could add roles here if needed in the future
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("business_users");
    }
};
