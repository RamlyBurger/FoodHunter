## 3. Design Pattern

### 3.1 Description of Design Pattern

The Strategy Pattern is a behavioral design pattern that enables selecting an algorithm's behavior at runtime. Instead of implementing a single algorithm directly, the code receives run-time instructions specifying which algorithm from a family of algorithms to use. This pattern was introduced by the Gang of Four (GoF) in their seminal book "Design Patterns: Elements of Reusable Object-Oriented Software" (1994).

In the FoodHunter User & Authentication Module, the Strategy Pattern is used to implement different authentication methods. The system supports multiple ways for users to authenticate:

1. **Password Authentication**: Traditional email and password login
2. **Token Authentication**: API token-based authentication for programmatic access

The Strategy Pattern is ideal for this use case because:

- **Flexibility**: New authentication methods (e.g., OAuth, biometric) can be added without modifying existing code
- **Single Responsibility**: Each authentication strategy is encapsulated in its own class
- **Open/Closed Principle**: The system is open for extension but closed for modification
- **Runtime Selection**: The authentication method can be switched dynamically based on the context (web login vs API access)

The pattern consists of three main components:

- **Strategy Interface (`AuthStrategyInterface`)**: Defines the common interface for all authentication strategies
- **Concrete Strategies (`PasswordAuthStrategy`, `TokenAuthStrategy`)**: Implement specific authentication algorithms
- **Context (`AuthContext`)**: Maintains a reference to a Strategy object and delegates the authentication work to it

### 3.2 Implementation of Design Pattern

The Strategy Pattern is implemented in the `app/Patterns/Strategy` directory with the following classes:

**File: `app/Patterns/Strategy/AuthStrategyInterface.php`**
```php
<?php

namespace App\Patterns\Strategy;

use App\Models\User;

/**
 * Strategy Pattern - Authentication Strategy Interface
 * Student 1: User & Authentication Module
 * 
 * This interface defines the contract for different authentication strategies.
 * Each strategy implements a different way to authenticate users.
 */
interface AuthStrategyInterface
{
    /**
     * Authenticate user with given credentials
     * 
     * @param array $credentials
     * @return User|null
     */
    public function authenticate(array $credentials): ?User;

    /**
     * Get the strategy name for logging/debugging
     * 
     * @return string
     */
    public function getStrategyName(): string;
}
```

**File: `app/Patterns/Strategy/PasswordAuthStrategy.php`**
```php
<?php

namespace App\Patterns\Strategy;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Strategy Pattern - Password Authentication Strategy
 * Student 1: User & Authentication Module
 * 
 * Authenticates users using email and password credentials.
 */
class PasswordAuthStrategy implements AuthStrategyInterface
{
    public function authenticate(array $credentials): ?User
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return null;
        }

        $user = User::where('email', strtolower($email))->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function getStrategyName(): string
    {
        return 'password';
    }
}
```

**File: `app/Patterns/Strategy/TokenAuthStrategy.php`**
```php
<?php

namespace App\Patterns\Strategy;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Strategy Pattern - Token Authentication Strategy
 * Student 1: User & Authentication Module
 * 
 * Authenticates users using API tokens (Sanctum).
 */
class TokenAuthStrategy implements AuthStrategyInterface
{
    public function authenticate(array $credentials): ?User
    {
        $token = $credentials['token'] ?? null;

        if (!$token) {
            return null;
        }

        // Remove 'Bearer ' prefix if present
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return null;
        }

        // Check if token is expired (optional: tokens can have expiration)
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return null;
        }

        return $accessToken->tokenable;
    }

    public function getStrategyName(): string
    {
        return 'token';
    }
}
```

**File: `app/Patterns/Strategy/AuthContext.php`**
```php
<?php

namespace App\Patterns\Strategy;

use App\Models\User;

/**
 * Strategy Pattern - Auth Context
 * Student 1: User & Authentication Module
 * 
 * The Context maintains a reference to one of the Strategy objects.
 * The Context does not know the concrete class of a strategy.
 */
class AuthContext
{
    private AuthStrategyInterface $strategy;

    public function __construct(AuthStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(AuthStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function authenticate(array $credentials): ?User
    {
        return $this->strategy->authenticate($credentials);
    }

    public function getStrategyName(): string
    {
        return $this->strategy->getStrategyName();
    }
}
```

**Usage in `app/Services/AuthService.php`:**
```php
<?php

namespace App\Services;

use App\Models\User;
use App\Patterns\Strategy\AuthContext;
use App\Patterns\Strategy\PasswordAuthStrategy;
use App\Patterns\Strategy\TokenAuthStrategy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private AuthContext $authContext;

    public function __construct()
    {
        // Default to password authentication strategy
        $this->authContext = new AuthContext(new PasswordAuthStrategy());
    }

    public function attemptLogin(string $email, string $password, string $ipAddress): array
    {
        // ... rate limiting checks ...

        // Uses Strategy Pattern for authentication
        $user = $this->authContext->authenticate([
            'email' => $email,
            'password' => $password,
        ]);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username and/or password.',
            ];
        }

        // ... rest of login logic (token creation, session management) ...
    }

    public function validateToken(string $token): ?User
    {
        // Switch to token strategy at runtime
        $this->authContext->setStrategy(new TokenAuthStrategy());
        return $this->authContext->authenticate(['token' => $token]);
    }
}
```

### 3.3 Class Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           Strategy Pattern                                   │
│                     User & Authentication Module                             │
└─────────────────────────────────────────────────────────────────────────────┘

                          ┌──────────────────────┐
                          │     AuthContext      │
                          ├──────────────────────┤
                          │ - strategy           │
                          ├──────────────────────┤
                          │ + setStrategy()      │
                          │ + authenticate()     │
                          │ + getStrategyName()  │
                          └──────────┬───────────┘
                                     │ uses
                                     ▼
                    ┌────────────────────────────────────┐
                    │    <<interface>>                   │
                    │    AuthStrategyInterface           │
                    ├────────────────────────────────────┤
                    │ + authenticate(credentials): User  │
                    │ + getStrategyName(): string        │
                    └────────────────────────────────────┘
                                     △
                                     │ implements
                    ┌────────────────┴────────────────┐
                    │                                 │
        ┌───────────▼───────────┐       ┌────────────▼────────────┐
        │ PasswordAuthStrategy  │       │   TokenAuthStrategy     │
        ├───────────────────────┤       ├─────────────────────────┤
        │ + authenticate()      │       │ + authenticate()        │
        │ + getStrategyName()   │       │ + getStrategyName()     │
        └───────────────────────┘       └─────────────────────────┘
                    │                                 │
                    │ uses                            │ uses
                    ▼                                 ▼
        ┌───────────────────────┐       ┌─────────────────────────┐
        │        User           │       │   PersonalAccessToken   │
        ├───────────────────────┤       ├─────────────────────────┤
        │ - email               │       │ - token                 │
        │ - password            │       │ - tokenable             │
        └───────────────────────┘       │ - expires_at            │
                                        └─────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              Client Usage                                    │
└─────────────────────────────────────────────────────────────────────────────┘

        ┌───────────────────────┐       ┌─────────────────────────┐
        │      AuthService      │──────▶│       AuthContext       │
        ├───────────────────────┤       └─────────────────────────┘
        │ - authContext         │
        │ + attemptLogin()      │
        │ + validateToken()     │
        └───────────────────────┘
                    │
                    │ used by
                    ▼
        ┌───────────────────────┐       ┌─────────────────────────┐
        │  Web\AuthController   │       │   Api\AuthController    │
        └───────────────────────┘       └─────────────────────────┘
```

### 3.4 Justification for Using Strategy Pattern

The Strategy Pattern was chosen for the User & Authentication Module for the following reasons:

1. **Multiple Authentication Methods**: The system requires different authentication mechanisms for web sessions (password-based) and API access (token-based). The Strategy Pattern allows these to coexist without code duplication.

2. **Extensibility**: Future authentication methods such as OAuth (Google, Facebook), two-factor authentication, or biometric login can be added by simply creating new strategy classes without modifying existing code.

3. **Testability**: Each authentication strategy can be unit tested independently, improving code quality and maintainability.

4. **Separation of Concerns**: The authentication logic is separated from the controller logic, making the codebase cleaner and easier to understand.

5. **Runtime Flexibility**: The AuthContext can switch between strategies at runtime, allowing the AuthService to use password authentication for login and token authentication for API validation within the same request lifecycle.