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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Уникальный ID транзакции
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('token_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['buy', 'sell', 'transfer', 'refund']); // Тип операции
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled']);
            $table->decimal('amount', 20, 8); // Количество токенов
            $table->decimal('price', 20, 8); // Цена за единицу
            $table->decimal('total_amount', 20, 8); // Общая сумма
            $table->decimal('fee', 20, 8)->default(0); // Комиссия
            $table->string('payment_method')->nullable(); // Способ оплаты
            $table->string('payment_reference')->nullable(); // Референс платежа
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
