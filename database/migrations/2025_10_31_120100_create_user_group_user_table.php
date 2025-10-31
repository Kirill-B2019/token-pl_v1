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
        // |KB Таблица связей пользователь↔группа с метаданными для платежной логики банка
        Schema::create('user_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // |KB Пользователь
            $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete(); // |KB Группа
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete(); // |KB Кто назначил
            $table->string('comment')->nullable(); // |KB Комментарий назначения
            $table->timestamps();

            $table->unique(['user_id', 'user_group_id']); // |KB Один пользователь не может повторно состоять в одной группе
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_group_user');
    }
};


