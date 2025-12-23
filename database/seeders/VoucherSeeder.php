<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();

        if ($vendors->isEmpty()) {
            $this->command->info('No vendors found. Skipping voucher seeding.');
            return;
        }

        foreach ($vendors as $vendor) {
            // Fixed discount voucher
            Voucher::create([
                'vendor_id' => $vendor->id,
                'code' => strtoupper(substr(str_replace(' ', '', $vendor->store_name), 0, 4)) . 'SAVE5',
                'name' => 'RM5 Off',
                'description' => 'Get RM5 off your order at ' . $vendor->store_name,
                'type' => 'fixed',
                'value' => 5.00,
                'min_order' => 15.00,
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ]);

            // Percentage discount voucher
            Voucher::create([
                'vendor_id' => $vendor->id,
                'code' => strtoupper(substr(str_replace(' ', '', $vendor->store_name), 0, 4)) . '10OFF',
                'name' => '10% Discount',
                'description' => 'Enjoy 10% off your order at ' . $vendor->store_name,
                'type' => 'percentage',
                'value' => 10.00,
                'min_order' => 20.00,
                'max_discount' => 15.00,
                'usage_limit' => 50,
                'per_user_limit' => 2,
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
            ]);

            // Welcome voucher
            Voucher::create([
                'vendor_id' => $vendor->id,
                'code' => strtoupper(substr(str_replace(' ', '', $vendor->store_name), 0, 4)) . 'WELCOME',
                'name' => 'Welcome Discount',
                'description' => 'New customer welcome discount',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order' => 25.00,
                'max_discount' => 20.00,
                'usage_limit' => 200,
                'per_user_limit' => 1,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]);
        }

        $this->command->info('Vouchers seeded successfully for ' . $vendors->count() . ' vendors.');
    }
}
