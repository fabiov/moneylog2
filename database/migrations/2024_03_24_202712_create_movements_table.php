<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable(false);
            $table->unsignedBigInteger('category_id')->nullable(true);
            $table->date('date')->nullable(false);
            $table->decimal('amount', 8, 2)->nullable(false);
            $table->string('description')->nullable(false);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
