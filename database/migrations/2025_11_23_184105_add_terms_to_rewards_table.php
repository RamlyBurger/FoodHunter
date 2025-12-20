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
        Schema::table('rewards', function (Blueprint $table) {
            $table->text('terms_conditions')->nullable()->after('description');
            $table->decimal('min_spend', 10, 2)->nullable()->after('reward_value');
            $table->decimal('max_discount', 10, 2)->nullable()->after('min_spend');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn(['terms_conditions', 'min_spend', 'max_discount']);
        });
    }
};
