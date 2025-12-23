## 5. Software Security

### 5.1 Potential Threats and Attacks

#### Threat 1: Malicious File Upload

Malicious file upload attacks occur when an attacker uploads a file with a dangerous extension (e.g., .php, .exe) disguised as a legitimate image by changing its extension. If the server only validates file extensions, the attacker could upload a PHP script named "image.jpg.php" or embed malicious code in an image file header.

**Attack Scenario:**

```
Attacker creates malicious.php containing: <?php system($_GET['cmd']); ?>
Attacker renames to malicious.jpg (but keeps PHP content)
Attacker uploads via "Add Menu Item" form
Without magic byte validation: File is stored as image
Attacker accesses: /storage/menu-items/malicious.jpg.php?cmd=whoami
Result: Remote code execution on server!
```

#### Threat 2: Information Disclosure via Error Messages

Information disclosure occurs when detailed error messages reveal internal system details to attackers. Stack traces, database errors, file paths, and configuration values can help attackers understand the system architecture and plan targeted attacks.

**Attack Scenario:**

```
Attacker sends malformed request to /vendor/menu/999999
Without generic errors: "SQLSTATE[42S02]: Table 'foodhunter.menu_items_backup' doesn't exist"
Attacker now knows: Database name, table naming convention, MySQL version
This information aids SQL injection and other attacks!
```

#### Threat 3: Brute Force Authentication Attacks

Brute force attacks occur when an attacker systematically tries many password combinations to gain unauthorized access. Without detection and logging, attackers can run automated scripts attempting thousands of passwords against vendor accounts.

**Attack Scenario:**

```
Attacker targets vendor@foodhunter.com
Attacker runs script trying common passwords
Without logging: No evidence of attack, no alerts
Attacker eventually guesses correct password
Result: Full access to vendor account, orders, revenue!
```

---

### 5.2 Security Practices Implemented

#### Practice 1: File Header (Magic Byte) Validation

**OWASP Reference:** [104] Validate file headers, [143] Do not store uploaded content in web-accessible directories without validation

The system validates file types by checking the actual file content (magic bytes) rather than just the file extension. This prevents attackers from uploading malicious files disguised as images.

**Implementation in `app/Http/Controllers/Web/VendorController.php`:**

```php
public function menuStore(Request $request)
{
    // ... validation ...

    if ($request->hasFile('image')) {
        // Security: File Header (Magic Byte) Validation [OWASP 104, 143]
        $file = $request->file('image');
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $detectedMime = mime_content_type($file->getRealPath());
        
        if (!in_array($detectedMime, $allowedMimes)) {
            throw new \Exception('Invalid file type detected. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }
        
        $validated['image'] = $file->store('menu-items', 'public');
    }
}
```

**How It Works:**
1. File is uploaded via the Add Menu Item form
2. `mime_content_type()` reads the file's magic bytes (first few bytes identifying file type)
3. Magic bytes are compared against allowed MIME types
4. If mismatch detected (e.g., PHP script with .jpg extension), upload is rejected
5. Only genuinely valid images are stored

---

#### Practice 2: Generic Error Messages with Server-Side Logging

**OWASP Reference:** [107-130] Return generic error messages to users while logging detailed errors server-side

The system displays user-friendly error messages without exposing internal details, while comprehensive logs are maintained server-side for debugging and security analysis.

**Implementation in `app/Http/Controllers/Web/VendorController.php`:**

```php
public function menuStore(Request $request)
{
    try {
        // ... business logic ...
        
        MenuItem::create($validated);
        return response()->json(['success' => true, 'message' => 'Menu item created successfully.']);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Return validation errors (safe to show)
        return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        
    } catch (\Exception $e) {
        // Security: Generic error message [OWASP 107-130]
        // Don't expose internal details - log them instead
        \Log::error('Menu item creation failed', [
            'vendor_id' => $vendor->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false, 
            'message' => 'An error occurred while creating the item.'
        ], 500);
    }
}
```

---

#### Practice 3: Authentication Failure Logging and Detection

**OWASP Reference:** [119] Log authentication failures with user identification

The system logs all failed authentication attempts with timestamps, IP addresses, and user identifiers. This enables detection of brute force attacks and provides audit trails for security investigations.

**Implementation in `app/Services/SecurityLogService.php`:**

```php
class SecurityLogService
{
    /**
     * Log authentication attempt
     * Security: Authentication Failure Logging [OWASP 119]
     */
    public static function logAuthAttempt(
        string $email, 
        bool $success, 
        string $ip, 
        ?string $reason = null
    ): void {
        $logData = [
            'event' => $success ? 'auth.success' : 'auth.failure',
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ];

        if (!$success && $reason) {
            $logData['reason'] = $reason;
        }

        if ($success) {
            Log::channel('security')->info('Authentication successful', $logData);
        } else {
            Log::channel('security')->warning('Authentication failed', $logData);
            
            // Check for brute force pattern
            self::checkBruteForcePattern($email, $ip);
        }
    }

    private static function checkBruteForcePattern(string $email, string $ip): void
    {
        // Alert if too many failures from same IP/email
        $cacheKey = "auth_failures:{$ip}:{$email}";
        $failures = cache()->increment($cacheKey);
        cache()->put($cacheKey, $failures, now()->addMinutes(15));

        if ($failures >= 5) {
            Log::channel('security')->alert('Potential brute force attack detected', [
                'email' => $email,
                'ip' => $ip,
                'failures' => $failures,
            ]);
        }
    }
}
```

**How to Test:**
1. Go to vendor login page
2. Enter wrong password multiple times
3. Check `storage/logs/security.log` for failure entries
4. After 5 failures, a brute force alert is logged
