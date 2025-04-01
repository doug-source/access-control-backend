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
        Schema::create('characteristics', function (Blueprint $table) {
            $table->bigInteger('id', true, true);
            $table->string('name', 100);
            $table->unsignedBigInteger('enterprises_id');
            $table->timestamps();
        });
        Schema::table('characteristics', function (Blueprint $table) {
            $table->foreign(['enterprises_id'], 'fk_Characteristic_enterprises1')->references(['id'])->on('enterprises')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            $table->dropForeign('fk_Characteristic_enterprises1');
        });
        Schema::dropIfExists('characteristics');
    }
};
