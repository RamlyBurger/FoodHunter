<?php
/**
 * =============================================================================
 * Home Controller - Shared (All Students)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
 * @module     Shared Infrastructure
 * 
 * Renders the main home page with categories, featured items, and vendors.
 * Uses data from Haerine Deepak Singh's Menu module and displays for all users.
 * =============================================================================
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Vendor;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::active()->orderBy('sort_order')->limit(6)->get();
        $featured = MenuItem::available()->featured()->with('vendor')->limit(8)->get();
        $vendors = Vendor::active()->limit(6)->get();

        return view('home', compact('categories', 'featured', 'vendors'));
    }
}
