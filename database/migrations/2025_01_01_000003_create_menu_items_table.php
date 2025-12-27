<?php
/**
 * =============================================================================
 * Menu Items Table Migration - Haerine Deepak Singh (Menu & Catalog Module)
 * =============================================================================
 * 
 * @author     Haerine Deepak Singh
 * @module     Menu & Catalog Module
 * 
 * Creates the menu_items table for storing food items.
 * Includes pricing, availability, and sales statistics.
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // for discounts
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('prep_time')->nullable(); // minutes
            $table->integer('calories')->nullable();
            $table->integer('total_sold')->default(0);
            $table->timestamps();

            $table->unique(['vendor_id', 'slug']);
            $table->index(['is_available', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
