## 2. Module Description

### 2.1 User & Authentication Module

The User & Authentication Module serves as the security foundation of the FoodHunter Food Ordering System. This module is responsible for managing all user-related operations including registration, authentication, session management, and profile maintenance. It ensures that only authorized users can access the system and that all authentication processes are secure against common attacks.

When a user first visits the FoodHunter platform, they have the option to register a new account or log in with existing credentials. The registration process collects essential information including name, email address, phone number (optional), and password. The system enforces password complexity requirements to ensure account security. Upon registration, users receive a One-Time Password (OTP) via email for verification, ensuring the validity of provided email addresses.

The login functionality implements multiple authentication strategies through the Strategy Pattern. Users can authenticate using email and password credentials, and the system supports "Remember Me" functionality for convenience. For API access, token-based authentication is used via Laravel Sanctum, allowing secure communication between the frontend and backend services.

A critical security feature of this module is single-device login enforcement. When a user logs in from a new device or browser, all existing sessions and API tokens are automatically invalidated. This ensures that users can only be logged in from one device at a time, preventing unauthorized access from compromised sessions.

The module also implements comprehensive rate limiting to protect against brute force attacks. After 5 failed login attempts within 15 minutes, the account is temporarily locked. Failed login attempts are logged with IP addresses and timestamps for security auditing purposes.

Profile management allows authenticated users to view and update their personal information. Users can change their name, phone number, avatar, and even update their email address (with verification). Password changes require the current password for security.

**Sub-Modules Implemented:**

**User Registration:** Handles new user account creation with email verification via OTP. Validates input data, enforces password complexity, and stores user credentials securely using BCrypt hashing. Registration data is temporarily stored in session until email verification is complete.

**User Login:** Authenticates users using the Strategy Pattern via AuthService. Implements rate limiting to prevent brute force attacks, session regeneration to prevent session fixation, and single-device enforcement to limit concurrent logins.

**Session Management:** Manages user sessions for web access and API tokens for programmatic access. Sessions are stored in the database with IP address and user agent information. The module supports secure session cookies with HTTP-only and SameSite flags.

**Profile Management:** Allows users to view and update their personal information. Supports avatar upload, email change with verification, and secure password change functionality.

**Password Reset:** Enables users to recover their accounts through email-based password reset. A secure token is generated and sent to the user's email, allowing them to set a new password.

**Google OAuth Integration:** Provides social login functionality through Google OAuth 2.0. Users can quickly sign in or register using their Google account, with automatic account linking for existing email addresses.

**Email Verification:** Sends One-Time Passwords via Supabase Auth for email verification during registration and email change. Supabase handles OTP generation, sending, and expiration (15 minutes) securely.

**Logout:** Invalidates the current session and all API tokens, ensuring complete sign-out across all platforms.