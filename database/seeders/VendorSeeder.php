<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'user' => ['name' => 'Mak Cik Kitchen', 'email' => 'lownl-jm22@student.tarc.edu.my'],
                'vendor' => [
                    'store_name' => 'Mak Cik Kitchen', 
                    'slug' => 'mak-cik-kitchen', 
                    'description' => 'Authentic Malay cuisine made with traditional recipes passed down through generations.', 
                    'avg_prep_time' => 15,
                    'logo' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=400&h=400&q=80' // Traditional cook/home kitchen style
                ],
            ],
            [
                'user' => ['name' => 'Western Delight', 'email' => 'leekh-jm22@student.tarc.edu.my'],
                'vendor' => [
                    'store_name' => 'Western Delight', 
                    'slug' => 'western-delight', 
                    'description' => 'Premium burgers, steaks, and fries cooked by professional chefs.', 
                    'avg_prep_time' => 12,
                    'logo' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=400&h=400&q=80' // Professional chef in white uniform
                ],
            ],
            [
                'user' => ['name' => 'Bubble Tea Corner', 'email' => 'leesy-jm22@student.tarc.edu.my'],
                'vendor' => [
                    'store_name' => 'Bubble Tea Corner', 
                    'slug' => 'bubble-tea-corner', 
                    'description' => 'Fresh bubble tea, fruit teas, and handcrafted beverages.', 
                    'avg_prep_time' => 5,
                    'logo' => 'https://images.unsplash.com/photo-1605881528191-68f38c78e3d3?auto=format&fit=crop&w=400&h=400&q=80' // Modern barista/beverage specialist
                ],
            ],
        ];

        foreach ($vendors as $data) {
            $user = User::create([
                'name' => $data['user']['name'],
                'email' => $data['user']['email'],
                'password' => Hash::make('password123'),
                'role' => 'vendor',
            ]);

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $data['vendor']['store_name'],
                'slug' => $data['vendor']['slug'],
                'description' => $data['vendor']['description'],
                'avg_prep_time' => $data['vendor']['avg_prep_time'],
                'logo' => $data['vendor']['logo'], // Ensure your 'vendors' table has a 'logo' column
                'is_open' => true,
                'is_active' => true,
            ]);

            // Add operating hours (Mon-Sat 8am-8pm)
            for ($day = 1; $day <= 6; $day++) {
                VendorHour::create([
                    'vendor_id' => $vendor->id,
                    'day_of_week' => $day,
                    'open_time' => '08:00:00',
                    'close_time' => '20:00:00',
                    'is_closed' => false,
                ]);
            }

            // Sunday closed
            VendorHour::create([
                'vendor_id' => $vendor->id,
                'day_of_week' => 0,
                'open_time' => '08:00:00',
                'close_time' => '20:00:00',
                'is_closed' => true,
            ]);
        }
    }
}