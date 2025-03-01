<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename("business_users", "business_user");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename("business_user", "business_users");
    }
};
