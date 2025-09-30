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
        Schema::create('brokers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название брокера
            $table->string('api_key')->unique(); // API ключ для интеграции
            $table->string('api_secret'); // Секретный ключ (зашифрован)
            $table->string('exchange_url'); // URL биржи
            $table->decimal('reserve_balance', 20, 8)->default(0); // Резервный баланс
            $table->decimal('min_reserve_threshold', 20, 8); // Минимальный порог резерва
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Настройки брокера
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brokers');
    }
};
