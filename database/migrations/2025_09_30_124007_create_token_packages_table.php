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
        Schema::create('token_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название пакета
            $table->text('description')->nullable(); // Описание пакета
            $table->decimal('token_amount', 20, 8); // Количество токенов в пакете
            $table->decimal('price', 20, 8); // Цена пакета
            $table->decimal('discount_percentage', 5, 2)->default(0); // Скидка в процентах
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // Порядок сортировки
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_packages');
    }
};
