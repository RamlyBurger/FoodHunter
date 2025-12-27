<?php
/**
 * =============================================================================
 * Notification Seeder - Lee Song Yan (Cart, Checkout & Notifications Module)
 * =============================================================================
 * 
 * @author     Lee Song Yan
 * @module     Cart, Checkout & Notifications Module
 * 
 * Seeds the notifications table with sample notifications for testing.
 * Creates welcome and promotional notifications for test users.
 * =============================================================================
 */

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->limit(5)->get();

        $notifications = [
            [
                'type' => 'promo',
                'title' => 'New Promo Available!',
                'message' => 'Use code WELCOME10 to get 10% off your next order!',
            ],
            [
                'type' => 'system',
                'title' => 'Welcome to FoodHunter!',
                'message' => 'Thank you for joining us. Explore our menu and start ordering!',
            ],
            [
                'type' => 'reward',
                'title' => 'Points Earned!',
                'message' => 'You earned 50 points from your last order. Keep ordering to earn more!',
            ],
            [
                'type' => 'order',
                'title' => 'Order Completed',
                'message' => 'Your order has been completed. Don\'t forget to leave a review!',
            ],
            [
                'type' => 'promo',
                'title' => 'Weekend Special!',
                'message' => 'Get 20% off all orders this weekend with code WEEKEND20!',
            ],
        ];

        foreach ($users as $user) {
            foreach ($notifications as $index => $notif) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $notif['type'],
                    'title' => $notif['title'],
                    'message' => $notif['message'],
                    'is_read' => rand(0, 1),
                    'created_at' => now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                ]);
            }
        }
    }
}
