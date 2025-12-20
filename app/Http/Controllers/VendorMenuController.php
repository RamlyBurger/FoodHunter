<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Category;
use App\Models\OrderItem;
use App\Services\FileValidationService;
use App\Services\SecurityLoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * VendorMenuController - Manages vendor menu items
 * 
 * Features:
 * - CRUD operations for menu items
 * - Pagination
 * - Search and filter
 * - Image upload with file header validation [183]
 * - Availability toggle
 * - Enhanced error handling and logging [107, 122]
 */
class VendorMenuController extends Controller
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
     * Display vendor's menu items with pagination, search, and filter
     */
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Get search and filter parameters
        $search = $request->input('search');
        $category = $request->input('category');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);
        
        // Build query
        $query = MenuItem::where('vendor_id', $vendor->user_id)
            ->with('category');
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($category) {
            $query->where('category_id', $category);
        }
        
        // Apply status filter
        if ($status !== null && $status !== '') {
            $query->where('is_available', $status);
        }
        
        // Order by latest
        $query->orderBy('created_at', 'desc');
        
        // Paginate results
        $menuItems = $query->paginate($perPage)->appends($request->except('page'));
        
        // Get sales data for each menu item (completed orders only)
        $menuItemIds = $menuItems->pluck('item_id')->toArray();
        $salesData = OrderItem::select(
                'order_items.item_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.status', 'completed')
            ->whereIn('order_items.item_id', $menuItemIds)
            ->groupBy('order_items.item_id')
            ->get()
            ->keyBy('item_id');
        
        // Attach sales data to menu items
        foreach ($menuItems as $item) {
            $item->total_sold = $salesData->has($item->item_id) ? $salesData[$item->item_id]->total_sold : 0;
            $item->total_revenue = $salesData->has($item->item_id) ? $salesData[$item->item_id]->total_revenue : 0;
        }
        
        // Get all categories for filter dropdown
        $categories = Category::all();
        
        // Get statistics
        $stats = [
            'total' => MenuItem::where('vendor_id', $vendor->user_id)->count(),
            'available' => MenuItem::where('vendor_id', $vendor->user_id)->where('is_available', 1)->count(),
            'unavailable' => MenuItem::where('vendor_id', $vendor->user_id)->where('is_available', 0)->count(),
            'categories' => MenuItem::where('vendor_id', $vendor->user_id)->distinct('category_id')->count('category_id'),
        ];
        
        return view('vendor.menu', compact('menuItems', 'categories', 'stats', 'search', 'category', 'status'));
    }
    
    /**
     * Store a new menu item
     */
    public function store(Request $request)
    {
        try {
            $vendor = Auth::user();
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:100'],
                'category_id' => ['required', 'exists:categories,category_id'],
                'description' => ['nullable', 'string', 'max:500'],
                'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'is_available' => ['boolean'],
            ]);
            
            if ($validator->fails()) {
                // [121] Log all input validation failures
                $this->securityLogger->logValidationFailure('vendor_menu_store', $validator->errors()->toArray());
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please fix the validation errors.');
            }
            
            // Handle image upload with file header validation
            // [183] Validate uploaded files by checking file headers
            $imagePath = '/images/menu/default.jpg';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Validate file using magic bytes checking
                $validation = $this->fileValidator->validateImage($image, 'menu_item');
                
                if (!$validation['valid']) {
                    // Log failed upload attempt
                    $this->securityLogger->logFileUpload('menu_item', false, [
                        'reason' => $validation['error'],
                        'filename' => $image->getClientOriginalName(),
                    ]);
                    
                    // [107] Generic error message - no sensitive information
                    return back()
                        ->withInput()
                        ->with('error', $validation['error']);
                }
                
                // File is valid, proceed with upload
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image->getClientOriginalName());
                
                // Get file size BEFORE moving the file
                $fileSize = $image->getSize();
                
                $image->move(public_path('images/menu'), $filename);
                $imagePath = '/images/menu/' . $filename;
                
                // Log successful upload
                $this->securityLogger->logFileUpload('menu_item', true, [
                    'filename' => $filename,
                    'mime_type' => $validation['mime_type'],
                    'size' => $fileSize,
                ]);
            }
            
            // Create menu item
            MenuItem::create([
                'vendor_id' => $vendor->user_id,
                'category_id' => $request->category_id,
                'name' => strip_tags($request->name),
                'description' => strip_tags($request->description),
                'price' => $request->price,
                'image_path' => $imagePath,
                'is_available' => $request->is_available ?? 1,
            ]);
            
            Log::info('Menu item created', [
                'vendor_id' => $vendor->user_id,
                'item_name' => $request->name,
            ]);
            
            return redirect()->route('vendor.menu')
                ->with('success', 'Menu item added successfully!');
                
        } catch (\Exception $e) {
            // [126] Log all system exceptions with context
            // [107] Do not disclose sensitive information in error responses
            $this->securityLogger->logException($e, 'vendor_menu_store', [
                'vendor_id' => Auth::id(),
            ]);
            
            // Generic error message - no technical details exposed
            return back()
                ->withInput()
                ->with('error', 'Failed to add menu item. Please try again.');
        }
    }
    
    /**
     * Update an existing menu item
     */
    public function update(Request $request, $id)
    {
        try {
            $vendor = Auth::user();
            
            // Find menu item and verify ownership
            $menuItem = MenuItem::where('item_id', $id)
                ->where('vendor_id', $vendor->user_id)
                ->firstOrFail();
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:100'],
                'category_id' => ['required', 'exists:categories,category_id'],
                'description' => ['nullable', 'string', 'max:500'],
                'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'is_available' => ['boolean'],
            ]);
            
            if ($validator->fails()) {
                // [121] Log all input validation failures
                $this->securityLogger->logValidationFailure('vendor_menu_update', $validator->errors()->toArray());
                return back()
                    ->withErrors($validator)
                    ->with('error', 'Please fix the validation errors.');
            }
            
            // Handle image upload with file header validation
            // [183] Validate uploaded files by checking file headers
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Validate file using magic bytes checking
                $validation = $this->fileValidator->validateImage($image, 'menu_item_update');
                
                if (!$validation['valid']) {
                    // Log failed upload attempt
                    $this->securityLogger->logFileUpload('menu_item_update', false, [
                        'reason' => $validation['error'],
                        'filename' => $image->getClientOriginalName(),
                        'item_id' => $id,
                    ]);
                    
                    // [107] Generic error message - no sensitive information
                    return back()
                        ->with('error', $validation['error']);
                }
                
                // Delete old image if not default
                if ($menuItem->image_path && $menuItem->image_path !== '/images/menu/default.jpg') {
                    $oldImagePath = public_path($menuItem->image_path);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                // File is valid, proceed with upload
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image->getClientOriginalName());
                
                // Get file size BEFORE moving the file
                $fileSize = $image->getSize();
                
                $image->move(public_path('images/menu'), $filename);
                $menuItem->image_path = '/images/menu/' . $filename;
                
                // Log successful upload
                $this->securityLogger->logFileUpload('menu_item_update', true, [
                    'filename' => $filename,
                    'mime_type' => $validation['mime_type'],
                    'size' => $fileSize,
                    'item_id' => $id,
                ]);
            }
            
            // Update menu item
            $menuItem->update([
                'category_id' => $request->category_id,
                'name' => strip_tags($request->name),
                'description' => strip_tags($request->description),
                'price' => $request->price,
                'image_path' => $menuItem->image_path,
                'is_available' => $request->is_available ?? $menuItem->is_available,
            ]);
            
            Log::info('Menu item updated', [
                'item_id' => $id,
                'vendor_id' => $vendor->user_id,
            ]);
            
            return redirect()->route('vendor.menu')
                ->with('success', 'Menu item updated successfully!');
                
        } catch (\Exception $e) {
            // [126] Log all system exceptions
            // [107] Generic error response
            $this->securityLogger->logException($e, 'vendor_menu_update', [
                'item_id' => $id,
                'vendor_id' => Auth::id(),
            ]);
            
            return back()
                ->with('error', 'Failed to update menu item. Please try again.');
        }
    }
    
    /**
     * Toggle menu item availability
     */
    public function toggleAvailability($id)
    {
        try {
            $vendor = Auth::user();
            
            $menuItem = MenuItem::where('item_id', $id)
                ->where('vendor_id', $vendor->user_id)
                ->firstOrFail();
            
            $menuItem->is_available = !$menuItem->is_available;
            $menuItem->save();
            
            return response()->json([
                'success' => true,
                'is_available' => $menuItem->is_available,
                'message' => 'Availability updated successfully!',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update availability.',
            ], 500);
        }
    }
    
    /**
     * Delete a menu item
     */
    public function destroy($id)
    {
        try {
            $vendor = Auth::user();
            
            $menuItem = MenuItem::where('item_id', $id)
                ->where('vendor_id', $vendor->user_id)
                ->firstOrFail();
            
            // Delete image if not default
            if ($menuItem->image_path && $menuItem->image_path !== '/images/menu/default.jpg') {
                $imagePath = public_path($menuItem->image_path);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $menuItem->delete();
            
            Log::info('Menu item deleted', [
                'item_id' => $id,
                'vendor_id' => $vendor->user_id,
            ]);
            
            return redirect()->route('vendor.menu')
                ->with('success', 'Menu item deleted successfully!');
                
        } catch (\Exception $e) {
            // [126] Log all system exceptions
            // [107] Generic error response
            $this->securityLogger->logException($e, 'vendor_menu_delete', [
                'item_id' => $id,
                'vendor_id' => Auth::id(),
            ]);
            
            return back()
                ->with('error', 'Failed to delete menu item. Please try again.');
        }
    }
}
