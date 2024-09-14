<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // create the new columns for usernames and display names
        Schema::table("users", function (Blueprint $table) {
            $table->string("username");
            $table->string("display_name");
        });

        // migrate existing data to the new columns
        foreach (User::all() as $user) {
            // username should only contain lowercase letters, numbers and underscores
            // it should also be unique
            $username = Str::lower(
                $user->firstName .
                    ($user->lastName ? "_" . $user->lastName : "")
            );
            $username = preg_replace("/[^a-z0-9_]/", "", $username);
            while (User::where("username", $username)->exists()) {
                $username .= Str::lower(Str::random(4));
            }
            $user->username = $username;
            $user->display_name = $user->firstName . " " . $user->lastName;
            $user->save();
        }

        // add unique constraint to the username column
        Schema::table("users", function (Blueprint $table) {
            $table->unique("username");
        });

        // remove the old columns
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("firstName");
            $table->dropColumn("lastName");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // create the old columns
        Schema::table("users", function (Blueprint $table) {
            $table->string("firstName");
            $table->string("lastName")->nullable();
        });

        // migrate existing data to the old columns
        foreach (User::all() as $user) {
            $names = explode(" ", $user->display_name);
            $user->firstName = $names[0];
            $user->lastName = $names[1];
            $user->save();
        }

        // remove the new columns
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("username");
            $table->dropColumn("display_name");
        });
    }
};
