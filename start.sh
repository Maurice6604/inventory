#!/bin/bash
set -e

echo "=========================================="
echo " InventoryMS - Starting up"
echo "=========================================="

# Run database migrations
echo "[1/4] Running database migrations..."
php artisan migrate --force

# Seed demo data (DemoSeeder is idempotent - skips if already seeded)
echo "[2/4] Seeding demo data..."
php artisan db:seed --class=DemoSeeder --force

# Cache config, routes and views for production performance
echo "[3/4] Caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
echo "[4/4] Starting Apache web server..."
exec apache2-foreground
