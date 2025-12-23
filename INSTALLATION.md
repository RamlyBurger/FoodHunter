# FoodHunter - Installation Guide

## Quick Start Installation

This guide will help you set up the FoodHunter University Canteen Food Ordering System from scratch.

## System Requirements

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **MySQL**: 8.0+ or MariaDB 10.3+
- **Node.js**: 18+ (for frontend assets)
- **Web Server**: Apache or Nginx (optional, Laravel's built-in server works for development)

## Step-by-Step Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd FoodHunter
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Database Setup

#### Option A: Using the Setup Script (Recommended)

```bash
# Run the database setup script
php setup_database.php
```

This script will:
- Create the `university_canteen` database
- Import all tables and sample data from `university_canteen.sql`

#### Option B: Manual Database Setup

1. Create database manually:
```sql
CREATE DATABASE university_canteen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=university_canteen
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. Run migrations:
```bash
php artisan migrate
```

### 6. Configure Application URL

Update the `APP_URL` in your `.env` file:
```env
APP_URL=http://localhost:8000
```

### 7. Build Frontend Assets

```bash
npm run build
```

### 8. Start the Development Server

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

## Alternative: One-Command Setup

If you prefer a streamlined setup, use the composer script:

```bash
composer run setup
```

This command will:
- Install PHP dependencies
- Copy and configure `.env` file
- Generate application key
- Run migrations
- Install Node dependencies
- Build frontend assets

## Development Mode

For development with hot-reloading and multiple services:

```bash
composer run dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer
- Vite frontend dev server

## Test Credentials

After installation, you can use these accounts to test the system:

| Role | Email | Password |
|------|-------|----------|
| Customer | john@example.com | password123 |
| Customer | jane@example.com | password123 |
| Vendor | makcik@foodhunter.com | password123 |
| Vendor | western@foodhunter.com | password123 |
| Admin | admin@foodhunter.com | admin123 |

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify MySQL/MariaDB is running
   - Check database credentials in `.env`
   - Ensure database exists

2. **Composer Dependencies Issues**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Permission Issues**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

4. **Node Modules Issues**
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   ```

5. **Application Key Issues**
   ```bash
   php artisan key:generate
   php artisan config:clear
   ```

### Database Reset

If you need to reset the database:

```bash
php artisan migrate:fresh
# Or with the SQL file:
php setup_database.php
```

### Cache Clear

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Production Deployment

For production deployment:

1. Set environment variables:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize the application:
```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Set up proper file permissions:
```bash
chmod -R 755 storage bootstrap/cache
```

4. Configure your web server to point to the `public/` directory.

## Additional Configuration

### Email Configuration (Optional)

Update mail settings in `.env` for email notifications:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS="${APP_NAME}"
```

### Queue Configuration (Optional)

For background job processing:
```env
QUEUE_CONNECTION=database
```

Start queue worker:
```bash
php artisan queue:work
```

## Support

If you encounter any issues during installation:

1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify all system requirements are met
3. Ensure all steps in this guide were followed correctly
4. Check the main README.md for additional documentation

## Next Steps

After successful installation:

1. Visit `http://localhost:8000` to access the application
2. Use the test credentials to explore different user roles
3. Review the main README.md for API documentation and system overview
4. Check the `docs/` directory for detailed module documentation
