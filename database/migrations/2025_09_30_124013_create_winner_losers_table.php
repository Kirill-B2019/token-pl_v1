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
        Schema::create('winner_losers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['winner', 'loser']); // Тип результата
            $table->decimal('amount', 20, 8); // Сумма выигрыша/проигрыша
            $table->decimal('token_amount', 20, 8); // Количество токенов
            $table->foreignId('token_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processed', 'paid']); // Статус выплаты
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winner_losers');
    }
};
