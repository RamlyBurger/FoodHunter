# FoodHunter Development Commands

## Quick Start
```powershell
# Start the development server
php artisan serve

# Start on specific port
php artisan serve --port=3008
```

## Database Commands
```powershell
# Reset database (drop all tables, migrate, seed)
php artisan migrate:fresh --seed

# Just run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Seed database only
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

## Cache & Config
```powershell
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache (production)
php artisan optimize
```

## Routes
```powershell
# List all routes
php artisan route:list

# List routes by path
php artisan route:list --path=api
php artisan route:list --path=vendor
```

## Debugging
```powershell
# Show application info
php artisan about

# Check for issues
php artisan config:show app
```

## Test Accounts (after seeding)

| Role | Email | Password |
|------|-------|----------|
| Customer | customer@test.com | password |
| Vendor | vendor@test.com | password |
| Admin | admin@test.com | password |

## Common URLs
- Home: http://localhost:3008
- Login: http://localhost:3008/login
- Menu: http://localhost:3008/menu
- Cart: http://localhost:3008/cart
- Orders: http://localhost:3008/orders
- Wishlist: http://localhost:3008/wishlist
- Rewards: http://localhost:3008/rewards
- Contact: http://localhost:3008/contact
- Vendor Dashboard: http://localhost:3008/vendor/dashboard
- Vendor Reports: http://localhost:3008/vendor/reports

## One-Liner Reset & Start
```powershell
php artisan migrate:fresh --seed; php artisan serve
```
