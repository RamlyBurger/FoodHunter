<?php
/**
 * =============================================================================
 * Payments Table Migration - Lee Song Yan (Cart, Checkout & Notifications)
 * =============================================================================
 * 
 * @author     Lee Song Yan
 * @module     Cart, Checkout & Notifications Module
 * 
 * Creates the payments table for storing payment records.
 * Supports multiple payment methods (cash, card, e-wallet).
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'card', 'ewallet'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
