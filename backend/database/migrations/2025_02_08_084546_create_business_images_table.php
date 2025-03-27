<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('key');
            $table->string('file_name');
            $table
                ->foreignUuid('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();
            $table->unique(['key', 'business_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_images');
    }
};
