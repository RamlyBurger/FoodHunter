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
        Schema::create('vendor_operating_hours', function (Blueprint $table) {
            $table->id('hour_id');
            $table->unsignedBigInteger('vendor_id');
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
            
            $table->foreign('vendor_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->unique(['vendor_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_operating_hours');
    }
};
