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
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('twocan_card_token')->unique(); // Токен карты от 2can
            $table->string('card_mask'); // Маска карты (например, 411111******1111)
            $table->string('card_brand')->nullable(); // Бренд карты (Visa, MasterCard и т.д.)
            $table->string('card_holder_name')->nullable(); // Имя держателя карты
            $table->tinyInteger('expiry_month'); // Месяц истечения
            $table->smallInteger('expiry_year'); // Год истечения
            $table->boolean('is_active')->default(true); // Активна ли карта
            $table->boolean('is_default')->default(false); // Карта по умолчанию
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};