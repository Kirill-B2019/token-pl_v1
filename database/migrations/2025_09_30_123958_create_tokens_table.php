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
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // Символ токена (например, BTC, ETH)
            $table->string('name'); // Название токена
            $table->decimal('current_price', 20, 8); // Текущая цена
            $table->decimal('total_supply', 20, 8); // Общее количество токенов
            $table->decimal('available_supply', 20, 8); // Доступное количество
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
