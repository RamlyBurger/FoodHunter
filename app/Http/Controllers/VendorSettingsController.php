<?php

namespace App\Http\Controllers;

use App\Models\VendorSetting;
use App\Models\VendorOperatingHour;
use App\Models\VendorNotification;
use App\Models\User;
use App\Services\FileValidationService;
use App\Services\SecurityLoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VendorSettingsController extends Controller
{
    private FileValidationService $fileValidator;
    private SecurityLoggingService $securityLogger;

    public function __construct(
        FileValidationService $fileValidator,
        SecurityLoggingService $securityLogger
    ) {
        $this->fileValidator = $fileValidator;
        $this->securityLogger = $securityLogger;
    }
    
    /**
     * Display vendor settings page
     */
    public function index()
    {
        $vendor = Auth::user();
        
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
        
        // Get operating hours (create default if not exists)
        $operatingHours = $this->getOrCreateOperatingHours($vendor->user_id);
        
        // Get recent notifications
        $notifications = VendorNotification::where('vendor_id', $vendor->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $unreadCount = VendorNotification::where('vendor_id', $vendor->user_id)
            ->where('is_read', false)
            ->count();
        
        return view('vendor.settings', compact('settings', 'operatingHours', 'notifications', 'unreadCount'));
    }
    
    /**
     * Update store information
     * Implements: [183] File header validation, [107] Generic error messages
     */
    public function updateStoreInfo(Request $request)
    {
        try {
            /** @var \App\Models\User $vendor */
            $vendor = Auth::user();
            
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'store_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            
            if ($validator->fails()) {
                // [121] Log all input validation failures
                $this->securityLogger->logValidationFailure('vendor_store_info', $validator->errors()->toArray());
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please fix the validation errors.');
            }
            
            $settings = VendorSetting::where('vendor_id', $vendor->user_id)->first();
            
            $data = [
                'store_name' => $request->store_name,
                'phone' => $request->phone,
                'description' => $request->description,
            ];
            
            // Handle logo upload with file header validation
            // [183] Validate uploaded files by checking file headers
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                
                // Validate file using magic bytes checking
                $validation = $this->fileValidator->validateImage($logo, 'vendor_logo');
                
                if (!$validation['valid']) {
                    // Log failed upload attempt
                    $this->securityLogger->logFileUpload('vendor_logo', false, [
                        'reason' => $validation['error'],
                        'filename' => $logo->getClientOriginalName(),
                    ]);
                    
                    // [107] Generic error message - no sensitive information
                    return back()
                        ->withInput()
                        ->with('error', $validation['error']);
                }
                
                // Delete old logo if exists
                if ($settings && $settings->logo_path) {
                    Storage::disk('public')->delete($settings->logo_path);
                }
                
                // File is valid, proceed with upload
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $logo->getClientOriginalName());
                
                // Get file size BEFORE storing the file
                $fileSize = $logo->getSize();
                
                $logoPath = $logo->storeAs('vendor_logos', $filename, 'public');
                $data['logo_path'] = $logoPath;
                
                // Log successful upload
                $this->securityLogger->logFileUpload('vendor_logo', true, [
                    'filename' => $filename,
                    'mime_type' => $validation['mime_type'],
                    'size' => $fileSize,
                ]);
            }
            
            $settings->fill($data);
            $settings->save();
            
            // Also update user's name using DB facade to avoid IDE issues
            DB::table('users')
                ->where('user_id', $vendor->user_id)
                ->update(['name' => $request->store_name]);
            
            // [127] Log administrative action
            $this->securityLogger->logAdministrativeAction('vendor_store_info_updated', [
                'store_name' => $request->store_name,
            ]);
            
            return redirect()->back()->with('success', 'Store information updated successfully!');
            
        } catch (\Exception $e) {
            // [126] Log all system exceptions
            // [107] Generic error response - no sensitive information
            $this->securityLogger->logException($e, 'vendor_store_info_update', [
                'vendor_id' => Auth::id(),
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update store information. Please try again.');
        }
    }
    
    /**
     * Update operating hours
     */
    public function updateOperatingHours(Request $request)
    {
        $vendor = Auth::user();
        
        $request->validate([
            'hours' => 'required|array',
            'hours.*.day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'hours.*.opening_time' => 'nullable|date_format:H:i',
            'hours.*.closing_time' => 'nullable|date_format:H:i',
            'hours.*.is_open' => 'required|boolean',
        ]);
        
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
        
        return redirect()->back()->with('success', 'Operating hours updated successfully!');
    }
    
    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $vendor = Auth::user();
        
        $request->validate([
            'notify_new_orders' => 'boolean',
            'notify_order_updates' => 'boolean',
            'notify_email' => 'boolean',
        ]);
        
        VendorSetting::where('vendor_id', $vendor->user_id)->update([
            'notify_new_orders' => $request->has('notify_new_orders'),
            'notify_order_updates' => $request->has('notify_order_updates'),
            'notify_email' => $request->has('notify_email'),
        ]);
        
        return redirect()->back()->with('success', 'Notification settings updated successfully!');
    }
    
    /**
     * Update payment methods
     */
    public function updatePaymentMethods(Request $request)
    {
        $vendor = Auth::user();
        
        $request->validate([
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'in:cash,online,card',
        ]);
        
        VendorSetting::where('vendor_id', $vendor->user_id)->update([
            'payment_methods' => implode(',', $request->payment_methods),
        ]);
        
        return redirect()->back()->with('success', 'Payment methods updated successfully!');
    }
    
    /**
     * Toggle store status (accepting orders)
     */
    public function toggleStoreStatus(Request $request)
    {
        $vendor = Auth::user();
        
        $request->validate([
            'accepting_orders' => 'required|boolean',
        ]);
        
        VendorSetting::where('vendor_id', $vendor->user_id)->update([
            'accepting_orders' => $request->accepting_orders,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $request->accepting_orders 
                ? 'Store is now accepting orders' 
                : 'Store is no longer accepting orders',
            'accepting_orders' => $request->accepting_orders,
        ]);
    }
    
    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();
        
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        // Check current password
        if (!Hash::check($request->current_password, $vendor->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        // Update password using DB facade
        DB::table('users')
            ->where('user_id', $vendor->user_id)
            ->update(['password' => Hash::make($request->new_password)]);
        
        return redirect()->back()->with('success', 'Password updated successfully!');
    }
    
    /**
     * Update profile (email)
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();
        
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $vendor->user_id . ',user_id',
        ]);
        
        // Update email using DB facade
        DB::table('users')
            ->where('user_id', $vendor->user_id)
            ->update(['email' => $request->email]);
        
        return redirect()->back()->with('success', 'Profile updated successfully!');
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
                    'is_open' => $day !== 'sunday', // Closed on Sunday by default
                ]
            );
        }
        
        return $hours;
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationRead($id)
    {
        $vendor = Auth::user();
        
        $notification = VendorNotification::where('notification_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->first();
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead()
    {
        $vendor = Auth::user();
        
        VendorNotification::where('vendor_id', $vendor->user_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        
        return response()->json(['success' => true]);
    }
}

