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
        Schema::create('tron_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('address', 34)->unique(); // TRON address (T + 33 chars)
            $table->text('private_key'); // Encrypted private key
            $table->text('public_key'); // Public key
            $table->text('mnemonic'); // Encrypted mnemonic phrase
            $table->boolean('is_active')->default(true);
            $table->decimal('balance_usdt', 20, 6)->default(0); // USDT balance
            $table->decimal('balance_trx', 20, 6)->default(0); // TRX balance
            $table->timestamp('last_sync_at')->nullable(); // Last balance sync
            $table->json('metadata')->nullable(); // Additional wallet data
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index('address');
            $table->index('last_sync_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tron_wallets');
    }
};


