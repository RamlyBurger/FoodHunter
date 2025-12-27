<?php
/**
 * =============================================================================
 * Pickups Table Migration - Low Nam Lee (Order & Pickup Module)
 * =============================================================================
 * 
 * @author     Low Nam Lee
 * @module     Order & Pickup Module
 * 
 * Creates the pickups table for QR-based order pickup verification.
 * Stores queue numbers and digitally signed QR codes.
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->integer('queue_number');
            $table->string('qr_code', 100)->unique();
            $table->enum('status', ['waiting', 'ready', 'collected'])->default('waiting');
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};
