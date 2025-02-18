<?php

use App\Models\Journey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("journeys", function (Blueprint $table) {
            $table->integer("length")->default(0);
        });

        Journey::chunk(10, function ($journeys) {
            foreach ($journeys as $journey) {
                $journey->length = $journey->from->diff($journey->to)->d + 1;
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
            $table->dropColumn("length");
        });
    }
};
