<?php
/**
 * =============================================================================
 * ImageHelper - Shared (All Students)
 * =============================================================================
 * 
 * @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
 * @module     Shared Infrastructure
 * 
 * Provides image URL generation for avatars, logos, and menu item images.
 * Used across all modules for consistent image handling.
 * =============================================================================
 */

namespace App\Helpers;

class ImageHelper
{
    public static function avatar(?string $path, string $name = 'User', ?string $updatedAt = null): string
    {
        if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            // Add cache-busting timestamp
            $cacheBuster = $updatedAt ? '?v=' . strtotime($updatedAt) : '?v=' . time();
            return asset('storage/' . $path) . $cacheBuster;
        }
        
        // Generate UI Avatars URL with user initials
        $initials = self::getInitials($name);
        $bgColor = self::getColorFromName($name);
        return "https://ui-avatars.com/api/?name={$initials}&background={$bgColor}&color=fff&size=200&bold=true";
    }

    public static function vendorLogo(?string $path, string $name = 'Vendor'): string
    {
        if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . $path) . '?v=' . time();
        }
        
        // Generate UI Avatars for vendor
        $initials = self::getInitials($name);
        $bgColor = self::getColorFromName($name);
        return "https://ui-avatars.com/api/?name={$initials}&background={$bgColor}&color=fff&size=200&bold=true&rounded=true";
    }

    public static function menuItem(?string $path): string
    {
        if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . $path);
        }
        
        return asset('images/defaults/food-placeholder.svg');
    }

    public static function fallbackUrl(string $name = 'Image'): string
    {
        $initials = self::getInitials($name);
        $bgColor = self::getColorFromName($name);
        return "https://ui-avatars.com/api/?name={$initials}&background={$bgColor}&color=fff&size=400&bold=true";
    }

    private static function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
            if (strlen($initials) >= 2) break;
        }
        
        return $initials ?: 'U';
    }

    private static function getColorFromName(string $name): string
    {
        $colors = ['e63946', '457b9d', '2a9d8f', 'e76f51', '6d597a', '355070', '7f5539', '1d3557'];
        $hash = crc32($name);
        return $colors[abs($hash) % count($colors)];
    }
}
