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
        // |KB Создаем таблицу групп пользователей для управления привязками при банковских платежах
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // |KB Человекопонятное имя группы
            $table->string('code')->unique(); // |KB Системный код группы
            $table->text('description')->nullable(); // |KB Описание назначения группы
            $table->boolean('is_active')->default(true); // |KB Флаг активности группы
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_groups');
    }
};


