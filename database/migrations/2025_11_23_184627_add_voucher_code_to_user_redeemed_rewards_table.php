<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns don't exist before adding
        if (!Schema::hasColumn('user_redeemed_rewards', 'voucher_code')) {
            Schema::table('user_redeemed_rewards', function (Blueprint $table) {
                $table->string('voucher_code', 20)->nullable()->after('reward_id');
                $table->boolean('is_used')->default(0)->after('voucher_code');
                $table->timestamp('used_at')->nullable()->after('is_used');
            });
        }
        
        // Generate voucher codes for existing records
        $existingRewards = DB::table('user_redeemed_rewards')
            ->where(function($query) {
                $query->whereNull('voucher_code')
                      ->orWhere('voucher_code', '');
            })
            ->get();
        
        foreach ($existingRewards as $reward) {
            $voucherCode = 'FH-' . strtoupper(substr(md5(uniqid() . $reward->redeemed_id), 0, 8));
            DB::table('user_redeemed_rewards')
                ->where('redeemed_id', $reward->redeemed_id)
                ->update(['voucher_code' => $voucherCode]);
        }
        
        // Add unique constraint if not exists
        $indexes = DB::select("SHOW INDEXES FROM user_redeemed_rewards WHERE Key_name = 'user_redeemed_rewards_voucher_code_unique'");
        if (empty($indexes)) {
            Schema::table('user_redeemed_rewards', function (Blueprint $table) {
                $table->unique('voucher_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_redeemed_rewards', function (Blueprint $table) {
            $table->dropUnique(['voucher_code']);
            $table->dropColumn(['voucher_code', 'is_used', 'used_at']);
        });
    }
};
