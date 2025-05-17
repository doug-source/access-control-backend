<?php

use App\Library\Enums\ColumnSize\ProviderSize;
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
        Schema::create('providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider', ProviderSize::PROVIDER->get());
            $table->string('provider_id', ProviderSize::PROVIDER_ID->get());
            $table->bigInteger('user_id')->unsigned();
            $table->string('avatar', ProviderSize::AVATAR->get())->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
