<?php
/**
 * =============================================================================
 * Vendor Hours Table Migration - Lee Kin Hang (Vendor Management Module)
 * =============================================================================
 * 
 * @author     Lee Kin Hang
 * @module     Vendor Management Module
 * 
 * Creates the vendor_hours table for store operating hours.
 * Supports daily schedules with open/close times.
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0=Sunday, 6=Saturday
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['vendor_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_hours');
    }
};
