<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            [
                'reward_name' => 'RM 5 Voucher',
                'description' => 'Get RM 5 off your next order (minimum spend RM 20)',
                'terms_conditions' => 'Valid for 30 days from redemption. Minimum spend RM 20 required. Cannot be combined with other vouchers. One voucher per order.',
                'points_required' => 50,
                'reward_value' => 5.00,
                'reward_type' => 'voucher',
                'min_spend' => 20.00,
                'max_discount' => null,
                'stock' => null, // Unlimited
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reward_name' => 'RM 10 Voucher',
                'description' => 'Get RM 10 off your next order (minimum spend RM 40)',
                'terms_conditions' => 'Valid for 30 days from redemption. Minimum spend RM 40 required. Cannot be combined with other vouchers. One voucher per order.',
                'points_required' => 100,
                'reward_value' => 10.00,
                'reward_type' => 'voucher',
                'min_spend' => 40.00,
                'max_discount' => null,
                'stock' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reward_name' => 'RM 20 Voucher',
                'description' => 'Get RM 20 off your next order (minimum spend RM 80)',
                'terms_conditions' => 'Valid for 30 days from redemption. Minimum spend RM 80 required. Cannot be combined with other vouchers. One voucher per order.',
                'points_required' => 200,
                'reward_value' => 20.00,
                'reward_type' => 'voucher',
                'min_spend' => 80.00,
                'max_discount' => null,
                'stock' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reward_name' => 'Free Drink',
                'description' => 'Get a free drink (worth up to RM 5) with any order',
                'terms_conditions' => 'Valid for 30 days from redemption. Present voucher code at vendor. Drink selection subject to availability. Cannot be exchanged for cash.',
                'points_required' => 30,
                'reward_value' => 5.00,
                'reward_type' => 'free_item',
                'min_spend' => null,
                'max_discount' => null,
                'stock' => 100,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reward_name' => 'Free Side Dish',
                'description' => 'Get a free side dish (worth up to RM 8) with any order',
                'terms_conditions' => 'Valid for 30 days from redemption. Present voucher code at vendor. Side dish selection subject to availability. Cannot be exchanged for cash.',
                'points_required' => 60,
                'reward_value' => 8.00,
                'reward_type' => 'free_item',
                'min_spend' => null,
                'max_discount' => null,
                'stock' => 50,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reward_name' => '15% Off Coupon',
                'description' => 'Get 15% off your entire order (maximum discount RM 30)',
                'terms_conditions' => 'Valid for 30 days from redemption. Maximum discount capped at RM 30. Cannot be combined with other vouchers. Applicable to all menu items.',
                'points_required' => 150,
                'reward_value' => 15.00,
                'reward_type' => 'percentage',
                'min_spend' => null,
                'max_discount' => 30.00,
                'stock' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rewards')->insert($rewards);
    }
}
