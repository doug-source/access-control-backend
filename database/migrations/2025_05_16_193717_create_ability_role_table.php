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
        Schema::create('ability_role', function (Blueprint $table) {
            $table->foreignId('ability_id')->constrained();
            $table->foreignId('role_id')->constrained();
            $table->primary(['ability_id', 'role_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ability_role', function (Blueprint $table) {
            $table->dropForeign(['ability_id']);
            $table->dropForeign(['role_id']);
        });
        Schema::dropIfExists('ability_role');
    }
};
