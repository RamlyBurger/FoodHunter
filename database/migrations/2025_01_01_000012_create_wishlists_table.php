<?php
/**
 * =============================================================================
 * Wishlists Table Migration - Haerine Deepak Singh (Menu & Catalog Module)
 * =============================================================================
 * 
 * @author     Haerine Deepak Singh
 * @module     Menu & Catalog Module
 * 
 * Creates the wishlists table for user's saved/favorite menu items.
 * Links users to their favorite food items.
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'menu_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
