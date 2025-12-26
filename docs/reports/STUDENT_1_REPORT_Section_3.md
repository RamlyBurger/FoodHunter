## 3. Design Pattern

### 3.1 Description of Design Pattern

The Strategy Pattern is a behavioral design pattern that enables selecting an algorithm's behavior at runtime. Instead of implementing a single algorithm directly, the code receives run-time instructions specifying which algorithm from a family of algorithms to use. This pattern was introduced by the Gang of Four (GoF) in their seminal book "Design Patterns: Elements of Reusable Object-Oriented Software" (1994), and has since become one of the most widely used patterns in enterprise software development.

#### 3.1.1 Pattern Overview and History

The Strategy Pattern belongs to the behavioral pattern category, which focuses on how objects interact and distribute responsibility. The pattern's core insight is that algorithms can be encapsulated in separate classes and made interchangeable. This is particularly valuable when:

- Multiple algorithms exist for the same task (e.g., different sorting algorithms, different authentication methods)
- The algorithm selection needs to happen at runtime based on context
- The algorithms need to be easily testable in isolation
- New algorithms may need to be added in the future without disrupting existing code

In traditional procedural programming, different algorithms would be implemented using conditional statements (if-else or switch-case). This approach leads to code that is difficult to maintain, test, and extend. The Strategy Pattern solves these problems by defining a family of algorithms, encapsulating each one, and making them interchangeable.

#### 3.1.2 Application in FoodHunter Authentication

In the FoodHunter User & Authentication Module, the Strategy Pattern is used to implement different authentication methods. The system supports multiple ways for users to authenticate, each with its own unique requirements and security considerations:

1. **Password Authentication**: Traditional email and password login used by web browsers and mobile apps. This strategy validates credentials against the database using BCrypt hash comparison. It is the primary authentication method for most users.

2. **Token Authentication**: API token-based authentication for programmatic access, mobile applications, and single-page applications. This strategy validates Bearer tokens issued by Laravel Sanctum, checking token existence, validity, and expiration.

3. **Future Extensibility**: The pattern allows for easy addition of new strategies such as OAuth strategies (Google, Facebook), biometric authentication, two-factor authentication (TOTP, SMS), SSO integration, and LDAP/Active Directory authentication.

#### 3.1.3 Why Strategy Pattern is Ideal for Authentication

The Strategy Pattern is ideal for this use case because:

- **Flexibility**: New authentication methods (e.g., OAuth, biometric) can be added without modifying existing code. Simply create a new class implementing `AuthStrategyInterface` and the system can use it immediately.

- **Single Responsibility Principle (SRP)**: Each authentication strategy is encapsulated in its own class, handling only one specific authentication method. This makes the code easier to understand, test, and maintain.

- **Open/Closed Principle (OCP)**: The system is open for extension but closed for modification. Adding a new authentication method doesn't require changes to `AuthService`, `AuthContext`, or any existing strategy classes.

- **Runtime Selection**: The authentication method can be switched dynamically based on the context (web login vs API access). The `AuthService` uses password authentication by default but switches to token authentication when validating API tokens.

- **Testability**: Each strategy can be unit tested independently with mock data, without requiring the entire authentication system to be set up. This improves test coverage and reduces test complexity.

- **Dependency Injection**: The pattern naturally supports dependency injection, allowing strategies to be injected into the context, making the code more flexible and testable.

#### 3.1.4 Pattern Components

The pattern consists of three main components:

- **Strategy Interface (`AuthStrategyInterface`)**: Defines the common interface for all authentication strategies. This interface declares the `authenticate()` method that all concrete strategies must implement, ensuring polymorphic behavior.

- **Concrete Strategies (`PasswordAuthStrategy`, `TokenAuthStrategy`)**: Implement specific authentication algorithms. Each strategy encapsulates the logic for one authentication method, including credential validation, error handling, and user retrieval.

- **Context (`AuthContext`)**: Maintains a reference to a Strategy object and delegates the authentication work to it. The context doesn't know which concrete strategy it's using, only that it implements the strategy interface. This decoupling is the key to the pattern's flexibility.

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