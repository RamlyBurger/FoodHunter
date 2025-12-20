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
        Schema::create('vendor_settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('store_name', 255);
            $table->string('phone', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->boolean('accepting_orders')->default(true);
            $table->boolean('notify_new_orders')->default(true);
            $table->boolean('notify_order_updates')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->string('payment_methods')->default('cash,online');
            $table->timestamps();
            
            $table->foreign('vendor_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->unique('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_settings');
    }
};
