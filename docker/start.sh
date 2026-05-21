#!/bin/sh
set -e

echo "🚀 Starting Finance API deployment..."

# Generate key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations
echo "📦 Running migrations..."
php artisan migrate --force

# Seed if requested
if [ "$SEED_DB" = "true" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
fi

# Clear and cache config for production
echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo "✅ Deployment setup complete!"
