<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_redeemed_rewards', function (Blueprint $table) {
            $table->id('redeemed_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reward_id');
            $table->timestamp('redeemed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('reward_id')->references('reward_id')->on('rewards')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_redeemed_rewards');
    }
};
