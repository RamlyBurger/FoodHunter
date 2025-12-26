## 2. Module Description

### 2.1 User & Authentication Module

The User & Authentication Module serves as the security foundation of the FoodHunter Food Ordering System. This module is responsible for managing all user-related operations including registration, authentication, session management, and profile maintenance. It ensures that only authorized users can access the system and that all authentication processes are secure against common attacks. As the gateway to the entire application, this module plays a critical role in protecting user data and ensuring a seamless user experience across all devices and platforms.

#### 2.1.1 Module Architecture Overview

The module follows a layered architecture that separates concerns into distinct components:

- **Controllers Layer**: Handles HTTP requests and responses, delegating business logic to services
- **Services Layer**: Contains the `AuthService` class that implements all authentication logic using the Strategy Pattern
- **Repository Layer**: Interacts with the database through Eloquent ORM for user data persistence
- **Patterns Layer**: Houses the Strategy Pattern implementation for flexible authentication methods

This separation ensures that each layer has a single responsibility, making the codebase maintainable, testable, and extensible for future enhancements.

#### 2.1.2 User Registration Process

When a user first visits the FoodHunter platform, they have the option to register a new account or log in with existing credentials. The registration process is designed to be both user-friendly and secure, collecting essential information while protecting against common vulnerabilities.

The registration flow follows these steps:

1. **Data Collection**: The registration form collects name, email address, phone number (optional), and password. All fields are validated on both client-side (JavaScript) and server-side (Laravel Form Request validation) to ensure data integrity.

2. **Password Validation**: The system enforces password complexity requirements including minimum 8 characters, preventing weak passwords that could be easily compromised through dictionary attacks or brute force attempts.

3. **Email Uniqueness Check**: Before proceeding, the system verifies that the email address is not already registered, providing immediate feedback to the user.

4. **Temporary Storage**: Registration data is stored in the PHP session temporarily while awaiting email verification, preventing incomplete registrations from cluttering the database.

5. **OTP Generation and Delivery**: A One-Time Password is generated using Supabase Auth's secure random generation and sent to the user's email address. The OTP expires after 15 minutes for security.

6. **Email Verification**: Users must enter the correct OTP to complete registration. Upon successful verification, the user record is created in the database with a hashed password using BCrypt algorithm.

7. **Welcome Notification**: After successful registration, the system automatically sends a welcome notification to the user using Student 4's Notification API, demonstrating web service integration.

#### 2.1.3 Authentication Mechanisms

The login functionality implements multiple authentication strategies through the Strategy Pattern, providing flexibility for different authentication contexts. This design allows the system to easily add new authentication methods in the future without modifying existing code.

**Password-Based Authentication**: Users can authenticate using their email and password credentials. The system retrieves the user by email, then uses PHP's `password_verify()` function (via Laravel's `Hash::check()`) to compare the provided password against the stored BCrypt hash. This approach ensures that passwords are never stored in plain text and are resistant to rainbow table attacks.

**Token-Based Authentication**: For API access and stateless authentication, the system uses Laravel Sanctum to generate Personal Access Tokens. When a user logs in via the API, a unique token is generated and returned to the client. This token must be included in the `Authorization` header of subsequent requests as a Bearer token.

**Remember Me Functionality**: Users can opt to stay logged in by checking the "Remember Me" checkbox. This creates a longer-lived session cookie that persists even after the browser is closed, providing convenience for trusted devices.

**Google OAuth Integration**: The system supports social login through Google OAuth 2.0, allowing users to authenticate with their existing Google accounts. This reduces friction in the registration process and leverages Google's robust security infrastructure.

#### 2.1.4 Single-Device Login Enforcement

A critical security feature of this module is single-device login enforcement. When a user logs in from a new device or browser, all existing sessions and API tokens are automatically invalidated through the following process:

1. Upon successful credential verification, the system checks for existing tokens associated with the user
2. If tokens exist, they are deleted from the `personal_access_tokens` table
3. A new token is generated for the current login session
4. The security log records the invalidation event with details about the previous token count and reason

This ensures that users can only be logged in from one device at a time, preventing unauthorized access from compromised sessions. If a user's credentials are stolen and used from another location, the legitimate user will be automatically logged out, alerting them to potential account compromise.

#### 2.1.5 Rate Limiting and Brute Force Protection

The module implements comprehensive rate limiting to protect against brute force attacks, following OWASP security best practices:

- **Attempt Tracking**: Failed login attempts are tracked using Laravel's Cache system with a unique key combining the email address and IP address
- **Threshold Configuration**: After 5 failed attempts within 15 minutes, the account is temporarily locked for that IP address
- **Generic Error Messages**: To prevent account enumeration, the system returns the same error message regardless of whether the email exists or the password is incorrect
- **Security Logging**: All failed attempts are logged with timestamps, IP addresses, and user agents for security auditing and forensic analysis

The rate limiting mechanism uses Redis or file-based caching to store attempt counts, ensuring persistence across requests while maintaining high performance.

#### 2.1.6 Profile Management

Profile management allows authenticated users to view and update their personal information through a comprehensive dashboard interface. Users can perform the following actions:

- **View Profile**: Display current user information including name, email, phone, avatar, and account statistics
- **Update Basic Information**: Change name and phone number with immediate effect
- **Avatar Upload**: Upload profile pictures with file type validation and automatic resizing
- **Email Change**: Update email address with mandatory re-verification via OTP to prevent account hijacking
- **Password Change**: Update password by providing current password (for verification) and new password (with confirmation)
- **View User Statistics**: Access order history statistics through Student 1's User Stats API, showing total orders, spending, and loyalty points

**Sub-Modules Implemented:**

**User Registration:** Handles new user account creation with email verification via OTP. Validates input data, enforces password complexity, and stores user credentials securely using BCrypt hashing. Registration data is temporarily stored in session until email verification is complete.

**User Login:** Authenticates users using the Strategy Pattern via AuthService. Implements rate limiting to prevent brute force attacks, session regeneration to prevent session fixation, and single-device enforcement to limit concurrent logins.

**Session Management:** Manages user sessions for web access and API tokens for programmatic access. Sessions are stored in the database with IP address and user agent information. The module supports secure session cookies with HTTP-only and SameSite flags.

**Profile Management:** Allows users to view and update their personal information. Supports avatar upload, email change with verification, and secure password change functionality.

**Password Reset:** Enables users to recover their accounts through email-based password reset. A secure token is generated and sent to the user's email, allowing them to set a new password.

**Google OAuth Integration:** Provides social login functionality through Google OAuth 2.0. Users can quickly sign in or register using their Google account, with automatic account linking for existing email addresses.

**Email Verification:** Sends One-Time Passwords via Supabase Auth for email verification during registration and email change. Supabase handles OTP generation, sending, and expiration (15 minutes) securely.

**Logout:** Invalidates the current session and all API tokens, ensuring complete sign-out across all platforms.