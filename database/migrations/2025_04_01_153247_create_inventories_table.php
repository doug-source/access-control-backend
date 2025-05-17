<?php

use App\Library\Enums\ColumnSize\InventorySize;
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
        Schema::create('inventories', function (Blueprint $table) {
            $table->bigInteger('id', TRUE, TRUE);
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('products_id');
            $table->unsignedInteger('quantity');
            $table->decimal('price')->unsigned();
            $table->string('image', InventorySize::IMAGE->get())->nullable();
            $table->timestamps();
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreign(['products_id'], 'fk_inventories_products1')->references(['id'])->on('products')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['users_id'], 'fk_inventories_users')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign('fk_inventories_products1');
            $table->dropForeign('fk_inventories_users');
        });
        Schema::dropIfExists('inventories');
    }
};
