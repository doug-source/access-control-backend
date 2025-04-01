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
        Schema::create('products_characteristics', function (Blueprint $table) {
            $table->bigInteger(column: 'products_id', unsigned: TRUE)->index('fk_products_has_characteristics_products1_idx');
            $table->bigInteger(column: 'characteristics_id', unsigned: TRUE)->index('fk_products_has_characteristics_characteristics1_idx');
            $table->timestamps();

            $table->primary(['products_id', 'characteristics_id']);
        });
        Schema::table('products_characteristics', function (Blueprint $table) {
            $table->foreign(['characteristics_id'], 'fk_products_has_characteristics_characteristics1')->references(['id'])->on('characteristics')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['products_id'], 'fk_products_has_characteristics_products1')->references(['id'])->on('products')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products_characteristics', function (Blueprint $table) {
            $table->dropForeign('fk_products_has_characteristics_characteristics1');
            $table->dropForeign('fk_products_has_characteristics_products1');
        });
        Schema::dropIfExists('products_characteristics');
    }
};
