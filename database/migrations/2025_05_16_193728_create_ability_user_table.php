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
        Schema::create('ability_user', function (Blueprint $table) {
            $table->foreignId('ability_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->primary(['ability_id', 'user_id']);
            $table->boolean('include')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ability_user', function (Blueprint $table) {
            $table->dropForeign(['ability_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('ability_user');
    }
};
