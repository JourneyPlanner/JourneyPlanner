<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->string("email_hash", 64)->nullable();
        });

        // Hash all emails
        DB::table("users")->get()->each(function ($user) {
            DB::table("users")
                ->where("id", $user->id)
                ->update([
                    "email_hash" => hash("sha256", $user->email),
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("email_hash");
        });
    }
};
