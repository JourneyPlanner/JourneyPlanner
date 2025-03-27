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
        Schema::create('business_texts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('key');
            $table->text('value');
            $table->enum('language', ['en', 'de']);
            $table
                ->foreignUuid('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();
            $table->unique(['key', 'language', 'business_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_texts');
    }
};
