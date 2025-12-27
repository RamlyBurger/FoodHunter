<?php
/**
 * =============================================================================
 * Cart Items Table Migration - Lee Song Yan (Cart, Checkout & Notifications)
 * =============================================================================
 * 
 * @author     Lee Song Yan
 * @module     Cart, Checkout & Notifications Module
 * 
 * Creates the cart_items table for storing shopping cart items.
 * Supports quantity and special instructions per item.
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->text('special_instructions')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'menu_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
