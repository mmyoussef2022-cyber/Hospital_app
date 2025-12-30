#!/bin/bash

# Hospital Management System - Production Deployment Script
# نص نشر نظام إدارة المستشفى للإنتاج

echo "🏥 Hospital Management System - Production Deployment"
echo "=================================================="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

echo "📋 Step 1: Preparing for deployment..."

# Create .env from .env.example if it doesn't exist
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created. Please update it with your production settings."
else
    echo "✅ .env file already exists."
fi

echo "📋 Step 2: Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "📋 Step 3: Generating application key..."
php artisan key:generate --force

echo "📋 Step 4: Setting up storage permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "📋 Step 5: Running database migrations..."
php artisan migrate --force

echo "📋 Step 6: Seeding production data..."
php artisan db:seed --class=DatabaseSeeder --force

echo "📋 Step 7: Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "📋 Step 8: Creating symbolic link for storage..."
php artisan storage:link

echo "🎉 Deployment completed successfully!"
echo "=================================================="
echo "📝 Next steps:"
echo "1. Update your .env file with production database credentials"
echo "2. Set APP_ENV=production and APP_DEBUG=false"
echo "3. Configure your web server to point to the public/ directory"
echo "4. Set up SSL certificate"
echo "5. Configure backup and monitoring"
echo ""
echo "🔐 Default admin credentials:"
echo "Email: admin@hospital.com"
echo "Password: admin123"
echo ""
echo "⚠️  Remember to change the default password after first login!"
echo "=================================================="