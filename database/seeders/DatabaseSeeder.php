<?php
/**
 * =============================================================================
 * Database Seeder - Shared (All Students)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
 * @module     Shared Infrastructure
 * 
 * Main database seeder that calls all other seeders in order.
 * Populates the database with initial test data.
 * =============================================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            VendorSeeder::class,
            MenuItemSeeder::class,
            UserSeeder::class,
            NotificationSeeder::class,
            VoucherSeeder::class,
        ]);
    }
}
