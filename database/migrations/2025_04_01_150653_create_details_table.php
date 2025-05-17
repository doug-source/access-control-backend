<?php

use App\Library\Enums\ColumnSize\DetailSize;
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
            $table->unsignedBigInteger('id', TRUE);
            $table->unsignedBigInteger('characteristics_id')->nullable(FALSE)->index('fk_details_characteristics1_idx');
            $table->unique(['id', 'characteristics_id']);
            $table->string('type', DetailSize::TYPE->get());
            $table->timestamps();
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
