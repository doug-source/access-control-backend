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
        Schema::create('products', function (Blueprint $table) {
            $table->bigInteger('id', true, true);
            $table->string('nome');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('enterprises_id');
            $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->foreign(['enterprises_id'], 'fk_Product_enterprises1')->references(['id'])->on('enterprises')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('fk_Product_enterprises1');
        });
        Schema::dropIfExists('products');
    }
};
