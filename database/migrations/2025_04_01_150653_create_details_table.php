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
        Schema::create('details', function (Blueprint $table) {
            $table->bigInteger('id', true, true);
            $table->bigInteger(column: 'characteristics_id', unsigned: TRUE)->index('fk_details_characteristics1_idx');
            $table->string('type');
            $table->timestamps();

            $table->primary(['id', 'characteristics_id']);
        });
        Schema::table('details', function (Blueprint $table) {
            $table->foreign(['characteristics_id'], 'fk_Details_characteristics1')->references(['id'])->on('characteristics')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('details', function (Blueprint $table) {
            $table->dropForeign('fk_Details_characteristics1');
        });
        Schema::dropIfExists('details');
    }
};
