<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Drop foreign keys (old table)
        Schema::table('business_users', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['user_id']);
        });

        // Step 2: Rename the table
        Schema::rename('business_users', 'business_user');

        // Step 3: Recreate foreign keys (new table)
        Schema::table('business_user', function (Blueprint $table) {
            $table
                ->foreign('business_id')
                ->references('id')
                ->on('businesses')
                ->cascadeOnDelete();
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        // Rollback: Drop new foreign keys
        Schema::table('business_user', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['user_id']);
        });

        // Rename back to the old table name
        Schema::rename('business_user', 'business_users');

        // Recreate old foreign keys
        Schema::table('business_users', function (Blueprint $table) {
            $table
                ->foreign('business_id')
                ->references('id')
                ->on('businesses')
                ->cascadeOnDelete();
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
