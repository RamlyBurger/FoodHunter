<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id('cart_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->text('special_request')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('menu_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
