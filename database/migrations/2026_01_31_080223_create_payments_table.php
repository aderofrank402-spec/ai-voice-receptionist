<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('phone_number'); // The M-Pesa Number
            $table->string('transaction_reference')->unique()->nullable(); // M-Pesa Receipt Code
            $table->string('status')->default('pending'); // pending, completed, failed, cancelled
            $table->string('provider')->default('mpesa'); // mpesa, card, cash
            $table->json('metadata')->nullable(); // Store raw callback JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
