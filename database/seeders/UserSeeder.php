<?php
/**
 * =============================================================================
 * User Seeder - Ng Wayne Xiang (User & Authentication Module)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang
 * @module     User & Authentication Module
 * 
 * Seeds the users table with test customer accounts.
 * Creates sample users for testing authentication and orders.
 * =============================================================================
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test customers
        $customers = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '0123456789'],
            ['name' => 'Jane Smith', 'email' => 'haerineds-jm22@student.tarc.edu.my', 'phone' => '0198765432'],
            ['name' => 'Ali Ahmad', 'email' => 'ngwx-jm22@student.tarc.edu.my', 'phone' => '0112345678'],
        ];

        foreach ($customers as $customer) {
            User::firstOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'customer',
                    'phone' => $customer['phone'],
                ]
            );
        }
    }
}
