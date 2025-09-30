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
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('token_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 20, 8)->default(0); // Текущий баланс
            $table->decimal('locked_balance', 20, 8)->default(0); // Заблокированный баланс
            $table->decimal('total_purchased', 20, 8)->default(0); // Всего куплено
            $table->decimal('total_sold', 20, 8)->default(0); // Всего продано
            $table->timestamps();
            
            $table->unique(['user_id', 'token_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_balances');
    }
};
