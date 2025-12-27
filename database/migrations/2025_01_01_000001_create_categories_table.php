<?php
/**
 * =============================================================================
 * Categories Table Migration - Haerine Deepak Singh (Menu & Catalog Module)
 * =============================================================================
 * 
 * @author     Haerine Deepak Singh
 * @module     Menu & Catalog Module
 * 
 * Creates the categories table for organizing menu items by type.
 * Supports sorting, activation status, and image display.
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
