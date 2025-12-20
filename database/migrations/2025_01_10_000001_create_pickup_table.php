<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id('pickup_id');
            $table->unsignedBigInteger('order_id');
            $table->integer('queue_number');
            $table->string('qr_code', 255)->nullable();
            $table->enum('status', ['waiting', 'ready', 'picked_up'])->default('waiting');
            $table->timestamps();

            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};
