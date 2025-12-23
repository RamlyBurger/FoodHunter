## 5. Software Security

### 5.1 Potential Threats and Attacks

#### Threat 1: Brute Force Attack

Brute force attacks occur when an attacker systematically attempts multiple password combinations to gain unauthorized access to user accounts. Without proper protection, attackers could continuously submit login requests with different passwords until they find the correct one.

**Attack Scenario:**

```
Attempt 1: student@tarumt.edu.my / password123 → Failed
Attempt 2: student@tarumt.edu.my / 123456789 → Failed
Attempt 3: student@tarumt.edu.my / tarumt2024 → Failed
... (continues thousands of times)
Attempt 5847: student@tarumt.edu.my / MyP@ssw0rd! → SUCCESS (account compromised)
```

#### Threat 2: Session Hijacking

Session hijacking occurs when an attacker steals a valid session token to impersonate a legitimate user. Attackers can obtain tokens through network sniffing on public WiFi, XSS attacks, or malware.

**Attack Scenario:**

1. User logs into FoodHunter and receives token: `1|abc123xyz789...`
2. Attacker intercepts this token via public WiFi
3. Attacker uses the stolen token to access user's account
4. Without proper session management, attacker maintains access indefinitely

#### Threat 3: Weak Password

Weak passwords are easy targets for dictionary attacks and credential stuffing. Without enforcing password complexity, users may choose simple passwords like "password123" or "123456" which are quickly compromised.

**Attack Scenario:**

1. Attacker obtains a list of common passwords (e.g., "123456", "password", "qwerty")
2. Attacker uses dictionary attack against FoodHunter login
3. Users with weak passwords are compromised within minutes
4. Attacker gains access to multiple accounts using common password lists

---

### 5.2 Security Practices Implemented

#### Practice 1: Rate Limiting (Brute Force Prevention)

**OWASP Reference:** [41] Account lockout after failed attempts, [94] Transaction rate limiting

The system implements rate limiting that tracks failed login attempts per email/IP combination. After **5 failed attempts within 15 minutes**, the account is temporarily locked.

**Implementation in `app/Services/AuthService.php`:**

```php
class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    private AuthContext $authContext;

    public function attemptLogin(string $email, string $password, string $ipAddress): array
    {
        // OWASP [41, 94]: Rate Limiting - prevent brute force attacks
        if ($this->isLockedOut($email, $ipAddress)) {
            SecurityLogService::logRateLimitExceeded($email, '/api/auth/login');
            SecurityLogService::logAuthAttempt($email, false, $ipAddress, 'rate_limited');
            
            return [
                'success' => false,
                // OWASP [33]: Generic error message - don't reveal lockout details
                'message' => 'Invalid username and/or password.',
                'locked_out' => true,
            ];
        }

        $user = $this->authContext->authenticate([
            'email' => $email,
            'password' => $password,
        ]);

        if (!$user) {
            $this->recordFailedAttempt($email, $ipAddress);
            
            // OWASP [122]: Log failed authentication attempt
            SecurityLogService::logAuthAttempt($email, false, $ipAddress, 'invalid_credentials');
            
            return [
                'success' => false,
                // OWASP [33]: Generic error message - don't reveal which part was wrong
                'message' => 'Invalid username and/or password.',
            ];
        }

        // Clear failed attempts on successful login
        $this->clearFailedAttempts($email, $ipAddress);
        // ... rest of login logic
    }

    // Rate Limiting Methods
    private function getCacheKey(string $email, string $ip): string
    {
        return 'login_attempts:' . md5($email . $ip);
    }

    private function getFailedAttempts(string $email, string $ip): int
    {
        return (int) Cache::get($this->getCacheKey($email, $ip), 0);
    }

    private function recordFailedAttempt(string $email, string $ip): void
    {
        $key = $this->getCacheKey($email, $ip);
        $attempts = $this->getFailedAttempts($email, $ip) + 1;
        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));
    }

    private function clearFailedAttempts(string $email, string $ip): void
    {
        Cache::forget($this->getCacheKey($email, $ip));
    }

    private function isLockedOut(string $email, string $ip): bool
    {
        return $this->getFailedAttempts($email, $ip) >= self::MAX_ATTEMPTS;
    }
}
```

---

#### Practice 2: Session Regeneration (Session Hijacking Prevention)

**OWASP Reference:** [66-67] Session regeneration on authentication

The system regenerates sessions and revokes all existing tokens upon each successful login, invalidating any potentially compromised tokens.

**Implementation in `app/Http/Controllers/Web/AuthController.php`:**

```php
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Use Strategy Pattern via AuthService for authentication
    $result = $this->authService->attemptLogin(
        $request->email,
        $request->password,
        $request->ip()
    );

    if (!$result['success']) {
        return back()->withErrors([
            'email' => $result['message'],
        ]);
    }

    // Log in the user for web session
    Auth::login($result['user'], $request->boolean('remember'));
    
    // OWASP [66-67]: Single-device login - invalidate all other sessions
    $currentSessionId = session()->getId();
    $deletedSessions = DB::table('sessions')
        ->where('user_id', $result['user']->id)
        ->where('id', '!=', $currentSessionId)
        ->delete();
    
    if ($deletedSessions > 0) {
        SecurityLogService::logSessionRevoked(
            $result['user']->id,
            $result['user']->email,
            $deletedSessions,
            'new_login_from_another_device',
            $request->ip()
        );
    }
    
    // OWASP [66-67]: Session regeneration to prevent session fixation
    $request->session()->regenerate();
    
    return redirect()->intended('/');
}
```

**Single-Device Login Enforcement:**
- When user logs in from a new device, all existing sessions are revoked
- Stolen tokens become invalid immediately when user logs in again

---

#### Practice 3: Password Complexity Validation (Weak Password Prevention)

**OWASP Reference:** [38-39] Password complexity requirements

The system enforces OWASP-compliant password complexity rules during registration, password change, and password reset. This prevents users from choosing weak, easily-guessable passwords.

**Implementation in `app/Http/Controllers/Web/AuthController.php`:**

```php
public function register(Request $request)
{
    $request->validate([
        'name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-\.]+$/u'],
        'email' => ['required', 'email:rfc', 'unique:users,email', 'max:100'],
        'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
        // OWASP [38-39]: Password complexity - min 8 chars, mixed case, numbers, symbols
        'password' => [
            'required',
            'confirmed',
            \Illuminate\Validation\Rules\Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ],
    ], [
        'name.regex' => 'Name can only contain letters, spaces, hyphens, and periods.',
        'phone.regex' => 'Phone number format is invalid.',
    ]);

    // Store registration data in session
    $request->session()->put('registration_data', [
        'name' => $request->name,
        'email' => strtolower($request->email),
        'phone' => $request->phone,
        'password' => $request->password,
    ]);

    // Supabase will handle OTP sending via frontend JS
    return redirect()->route('verify.show');
}
```

**Password Requirements:**
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one symbol (!@#$%^&*, etc.)