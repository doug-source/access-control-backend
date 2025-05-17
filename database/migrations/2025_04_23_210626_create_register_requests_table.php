<?php

use App\Library\Enums\ColumnSize\RegisterRequestSize;
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
        Schema::create('register_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', RegisterRequestSize::EMAIL->get())->unique();
            $table->string('phone', RegisterRequestSize::PHONE->get())->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_requests');
    }
};
