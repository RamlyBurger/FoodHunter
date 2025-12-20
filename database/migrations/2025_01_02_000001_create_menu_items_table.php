<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_available')->default(1);
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('cascade');
            $table->foreign('vendor_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
