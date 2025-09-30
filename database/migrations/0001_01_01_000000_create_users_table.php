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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable(); // Телефон для SMS
            $table->enum('role', ['client', 'broker', 'admin'])->default('client'); // Роль пользователя
            $table->string('unique_id', 10)->unique(); // Уникальный 10-символьный ID
            $table->boolean('two_factor_enabled')->default(false); // Включена ли 2FA
            $table->string('two_factor_secret')->nullable(); // Секрет для 2FA
            $table->json('two_factor_recovery_codes')->nullable(); // Коды восстановления
            $table->boolean('is_active')->default(true); // Активен ли аккаунт
            $table->timestamp('last_login_at')->nullable(); // Последний вход
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
