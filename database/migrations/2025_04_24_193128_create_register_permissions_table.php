<?php

use App\Library\Enums\ColumnSize\RegisterPermissionSize;
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
        Schema::create('register_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', RegisterPermissionSize::EMAIL->get())->unique();
            $table->string('phone', RegisterPermissionSize::PHONE->get())->nullable();
            $table->string('token', RegisterPermissionSize::TOKEN->get())->nullable(FALSE);
            $table->timestamp("expiration_data")->nullable(FALSE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_permissions');
    }
};
