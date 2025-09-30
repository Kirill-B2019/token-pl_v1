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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event'); // Событие (login, transaction, etc.)
            $table->string('entity_type'); // Тип сущности (User, Transaction, etc.)
            $table->unsignedBigInteger('entity_id'); // ID сущности
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45); // IP адрес
            $table->string('user_agent')->nullable(); // User Agent
            $table->json('old_values')->nullable(); // Старые значения
            $table->json('new_values')->nullable(); // Новые значения
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamps();
            
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
