<?php

use App\Models\Journey;
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
        Schema::table("journeys", function (Blueprint $table) {
            $table->uuid("share_id")->nullable();
        });

        Journey::chunk(10, function ($journeys) {
            foreach ($journeys as $journey) {
                $journey->share_id = Str::uuid();
                $journey->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("journeys", function (Blueprint $table) {
            $table->dropColumn("share_id");
        });
    }
};
