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
            // Rename title to reward_name
            $table->renameColumn('title', 'reward_name');
            
            // Add new columns
            $table->decimal('reward_value', 10, 2)->after('points_required');
            $table->string('reward_type', 50)->after('reward_value'); // voucher, free_item, percentage
            $table->integer('stock')->nullable()->after('reward_type');
            $table->boolean('is_active')->default(1)->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->renameColumn('reward_name', 'title');
            $table->dropColumn(['reward_value', 'reward_type', 'stock', 'is_active']);
        });
    }
};
