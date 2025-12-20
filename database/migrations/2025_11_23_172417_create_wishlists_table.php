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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id('wishlist_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('menu_items')->onDelete('cascade');

            // Unique constraint to prevent duplicate wishlist entries
            $table->unique(['user_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
