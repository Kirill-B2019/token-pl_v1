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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название банка
            $table->string('code')->unique(); // Код банка
            $table->string('api_endpoint'); // API endpoint для платежей
            $table->string('merchant_id'); // ID мерчанта
            $table->string('api_key'); // API ключ (зашифрован)
            $table->string('api_secret'); // Секретный ключ (зашифрован)
            $table->decimal('commission_rate', 5, 4)->default(0); // Комиссия банка
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Настройки банка
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
