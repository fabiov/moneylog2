<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('payday')->unsigned();
            $table->tinyInteger('months')->unsigned();
            $table->boolean('provisioning');
            $table->timestamps();

            $table->foreign('id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
