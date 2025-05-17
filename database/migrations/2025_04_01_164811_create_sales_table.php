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
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('inventories_id');
            $table->decimal('price')->unsigned();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign(['users_id'], 'fk_Sale_users1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['inventories_id'], 'fk_sales_inventories1')->references(['id'])->on('inventories')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('fk_Sale_users1');
            $table->dropForeign('fk_sales_inventories1');
        });
        Schema::dropIfExists('sales');
    }
};
