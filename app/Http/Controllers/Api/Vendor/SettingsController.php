<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorSetting;
use App\Models\VendorOperatingHour;
use App\Models\VendorNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * Get vendor settings
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $vendor = $request->user();

        // Get or create vendor settings
        $settings = VendorSetting::firstOrCreate(
            ['vendor_id' => $vendor->user_id],
            [
                'store_name' => $vendor->name,
                'phone' => null,
                'description' => null,
                'accepting_orders' => true,
            ]
        );

        // Get operating hours
        $operatingHours = $this->getOrCreateOperatingHours($vendor->user_id);

        // Get unread notifications count
        $unreadCount = VendorNotification::where('vendor_id', $vendor->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => [
                    'user_id' => $vendor->user_id,
                    'name' => $vendor->name,
                    'email' => $vendor->email,
                ],
                'settings' => [
                    'store_name' => $settings->store_name,
                    'phone' => $settings->phone,
                    'description' => $settings->description,
                    'logo_url' => $settings->logo_path ? asset('storage/' . $settings->logo_path) : null,
                    'accepting_orders' => (bool) $settings->accepting_orders,
                    'notify_new_orders' => (bool) $settings->notify_new_orders,
                    'notify_order_updates' => (bool) $settings->notify_order_updates,
                    'notify_email' => (bool) $settings->notify_email,
                    'payment_methods' => $settings->payment_methods ? explode(',', $settings->payment_methods) : ['cash'],
                ],
                'operating_hours' => collect($operatingHours)->map(function($hour) {
                    return [
                        'day' => $hour->day,
                        'is_open' => (bool) $hour->is_open,
                        'opening_time' => $hour->opening_time,
                        'closing_time' => $hour->closing_time,
                    ];
                }),
                'unread_notifications' => $unreadCount,
            ]
        ]);
    }

    /**
     * Update store information
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStoreInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();
        $settings = VendorSetting::where('vendor_id', $vendor->user_id)->first();

        $data = [
            'store_name' => $request->store_name,
            'phone' => $request->phone,
            'description' => $request->description,
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings && $settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $logoPath = $request->file('logo')->store('vendor_logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        $settings->fill($data);
        $settings->save();

        // Update vendor name
        DB::table('users')
            ->where('user_id', $vendor->user_id)
            ->update(['name' => $request->store_name]);

        return response()->json([
            'success' => true,
            'message' => 'Store information updated successfully',
            'data' => [
                'store_name' => $settings->store_name,
                'phone' => $settings->phone,
                'description' => $settings->description,
                'logo_url' => $settings->logo_path ? asset('storage/' . $settings->logo_path) : null,
            ]
        ]);
    }

    /**
     * Update operating hours
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOperatingHours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hours' => 'required|array',
            'hours.*.day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'hours.*.opening_time' => 'nullable|date_format:H:i',
            'hours.*.closing_time' => 'nullable|date_format:H:i',
            'hours.*.is_open' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();

        foreach ($request->hours as $hourData) {
            VendorOperatingHour::updateOrCreate(
                [
                    'vendor_id' => $vendor->user_id,
                    'day' => $hourData['day'],
                ],
                [
                    'opening_time' => $hourData['is_open'] ? $hourData['opening_time'] : null,
                    'closing_time' => $hourData['is_open'] ? $hourData['closing_time'] : null,
                    'is_open' => $hourData['is_open'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Operating hours updated successfully'
        ]);
    }

    /**
     * Update notification settings
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notify_new_orders' => 'boolean',
            'notify_order_updates' => 'boolean',
            'notify_email' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();

        VendorSetting::where('vendor_id', $vendor->user_id)->update([
            'notify_new_orders' => $request->input('notify_new_orders', false),
            'notify_order_updates' => $request->input('notify_order_updates', false),
            'notify_email' => $request->input('notify_email', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully'
        ]);
    }

    /**
     * Update payment methods
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePaymentMethods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'in:cash,online,card',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();

        VendorSetting::where('vendor_id', $vendor->user_id)->update([
            'payment_methods' => implode(',', $request->payment_methods),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment methods updated successfully',
            'data' => [
                'payment_methods' => $request->payment_methods,
            ]
        ]);
    }

    /**
     * Toggle store status (accepting orders)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStoreStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accepting_orders' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();

        VendorSetting::where('vendor_id', $vendor->user_id)->update([
            'accepting_orders' => $request->accepting_orders,
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->accepting_orders
                ? 'Store is now accepting orders'
                : 'Store is no longer accepting orders',
            'data' => [
                'accepting_orders' => $request->accepting_orders,
            ]
        ]);
    }

    /**
     * Update password
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();

        if (!Hash::check($request->current_password, $vendor->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        DB::table('users')
            ->where('user_id', $vendor->user_id)
            ->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Update profile (email)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $vendor = $request->user();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $vendor->user_id . ',user_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::table('users')
            ->where('user_id', $vendor->user_id)
            ->update(['email' => $request->email]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'email' => $request->email,
            ]
        ]);
    }

    /**
     * Get or create default operating hours
     */
    private function getOrCreateOperatingHours($vendorId)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            $hours[$day] = VendorOperatingHour::firstOrCreate(
                [
                    'vendor_id' => $vendorId,
                    'day' => $day,
                ],
                [
                    'opening_time' => '08:00',
                    'closing_time' => '17:00',
                    'is_open' => $day !== 'sunday',
                ]
            );
        }

        return $hours;
    }
}
