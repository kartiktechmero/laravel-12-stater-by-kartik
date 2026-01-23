#!/bin/bash
# =========================================
# 🚀 Laravel Deployment Script
# Author: Kartik Patel
# =========================================

set -e
set -o pipefail

echo "========================================="
echo "🚀 Starting Deployment on $(date)"
echo "========================================="

# 1. Enter maintenance
#echo "➡️  Putting application into maintenance mode..."
#php artisan down || true

# 2. Git update
echo "➡️  Stashing local changes..."
git stash || true

echo "➡️  Pulling latest code..."
git pull --rebase

echo "➡️  Restoring stashed changes (if any)..."
git stash pop || true

# 3. Dependencies
echo "➡️  Installing composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Database migrations
echo "➡️  Running database migrations..."
php artisan migrate --force

# 5. Cache + optimize
echo "➡️  Clearing application caches..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

echo "➡️  Optimizing framework..."
php artisan optimize

echo "➡️  NPM install & build..."
npm install --legacy-peer-deps
npm run build


# 6. Exit maintenance
#echo "➡️  Bringing application back online..."
#php artisan up

echo "========================================="
echo "✅ Deployment Finished Successfully!"
echo "========================================="
