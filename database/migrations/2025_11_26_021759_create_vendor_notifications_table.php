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
        Schema::create('vendor_notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('type', 50);
            $table->string('title', 255);
            $table->text('message');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('vendor_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->index(['vendor_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_notifications');
    }
};
