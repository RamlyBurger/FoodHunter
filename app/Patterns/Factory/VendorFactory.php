<?php

namespace App\Patterns\Factory;

use App\Models\User;
use App\Models\VendorSetting;
use App\Models\VendorOperatingHour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Factory Pattern for Vendor Management Module
 * 
 * Creates vendor-specific components and initializes vendor entities
 * with all required dependencies (settings, operating hours, etc.)
 */
class VendorFactory
{
    /**
     * Create a new vendor with all components
     *
     * @param array $data
     * @return User
     */
    public function createVendor(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Create vendor user
            $vendor = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'vendor',
                'phone' => $data['phone'] ?? null,
            ]);

            // Create vendor settings
            $this->createVendorSettings($vendor->user_id, $data);

            // Create default operating hours
            $this->createDefaultOperatingHours($vendor->user_id);

            return $vendor;
        });
    }

    /**
     * Create vendor settings component
     *
     * @param int $vendorId
     * @param array $data
     * @return VendorSetting
     */
    public function createVendorSettings(int $vendorId, array $data): VendorSetting
    {
        return VendorSetting::create([
            'vendor_id' => $vendorId,
            'store_name' => $data['store_name'] ?? $data['name'],
            'store_description' => $data['store_description'] ?? null,
            'store_logo' => $data['store_logo'] ?? null,
            'accepts_orders' => $data['accepts_orders'] ?? true,
            'payment_methods' => $data['payment_methods'] ?? json_encode(['cash']),
            'notification_email' => $data['notification_email'] ?? true,
            'notification_sms' => $data['notification_sms'] ?? false,
        ]);
    }

    /**
     * Create default operating hours component
     *
     * @param int $vendorId
     * @return void
     */
    public function createDefaultOperatingHours(int $vendorId): void
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $defaultHours = [
            'monday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
            'tuesday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
            'wednesday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
            'thursday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
            'friday' => ['open' => '08:00', 'close' => '17:00', 'closed' => false],
            'saturday' => ['open' => '09:00', 'close' => '15:00', 'closed' => false],
            'sunday' => ['open' => null, 'close' => null, 'closed' => true],
        ];

        foreach ($days as $day) {
            VendorOperatingHour::create([
                'vendor_id' => $vendorId,
                'day' => $day,
                'opening_time' => $defaultHours[$day]['open'],
                'closing_time' => $defaultHours[$day]['close'],
                'is_closed' => $defaultHours[$day]['closed'],
            ]);
        }
    }

    /**
     * Create vendor menu item component
     *
     * @param int $vendorId
     * @param array $data
     * @return \App\Models\MenuItem
     */
    public function createMenuItem(int $vendorId, array $data): \App\Models\MenuItem
    {
        return \App\Models\MenuItem::create([
            'vendor_id' => $vendorId,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'image_path' => $data['image_path'] ?? null,
            'is_available' => $data['is_available'] ?? true,
        ]);
    }

    /**
     * Update vendor component
     *
     * @param User $vendor
     * @param array $data
     * @return User
     */
    public function updateVendor(User $vendor, array $data): User
    {
        return DB::transaction(function () use ($vendor, $data) {
            // Update vendor user
            $vendor->update([
                'name' => $data['name'] ?? $vendor->name,
                'email' => $data['email'] ?? $vendor->email,
                'phone' => $data['phone'] ?? $vendor->phone,
            ]);

            // Update vendor settings if provided
            if (isset($data['settings'])) {
                $vendor->vendorSetting()->update($data['settings']);
            }

            return $vendor->fresh();
        });
    }
}
