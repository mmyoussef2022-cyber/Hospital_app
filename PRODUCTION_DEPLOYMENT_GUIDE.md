# دليل نشر المشروع للإنتاج 🚀
# Production Deployment Guide

## 📋 نظرة عامة
هذا الدليل يوضح كيفية نشر نظام إدارة المستشفى على منصات الاستضافة المجانية مع رفعه على GitHub.

## 🎯 المنصات المقترحة للاستضافة المجانية

### 1. **Railway** (الأفضل للـ Laravel) ⭐⭐⭐⭐⭐
- **المميزات**: 
  - دعم ممتاز لـ Laravel
  - قاعدة بيانات MySQL مجانية
  - نشر تلقائي من GitHub
  - SSL مجاني
  - 500 ساعة مجانية شهرياً
- **الرابط**: https://railway.app

### 2. **Heroku** (كلاسيكي وموثوق) ⭐⭐⭐⭐
- **المميزات**:
  - منصة مجربة ومستقرة
  - دعم جيد لـ PHP/Laravel
  - إضافات مجانية لقاعدة البيانات
  - نشر سهل من Git
- **الرابط**: https://heroku.com

### 3. **Render** (حديث وسريع) ⭐⭐⭐⭐
- **المميزات**:
  - نشر مجاني للتطبيقات الثابتة والديناميكية
  - قاعدة بيانات PostgreSQL مجانية
  - SSL تلقائي
  - نشر من GitHub
- **الرابط**: https://render.com

### 4. **PlanetScale + Vercel** (للمشاريع الحديثة) ⭐⭐⭐
- **المميزات**:
  - قاعدة بيانات MySQL مجانية (PlanetScale)
  - استضافة مجانية (Vercel)
  - أداء عالي
- **الروابط**: 
  - https://planetscale.com
  - https://vercel.com

## 🛠️ خطوات الإعداد

### الخطوة 1: إعداد المشروع لـ Git

#### 1.1 إنشاء ملف .gitignore
```gitignore
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
/nbproject/private/
.buildpath
.project
.settings/
*.log
*.cache
.DS_Store
Thumbs.db

# Production specific
/bootstrap/cache/*.php
/storage/app/*
!/storage/app/.gitkeep
/storage/framework/cache/*
!/storage/framework/cache/.gitkeep
/storage/framework/sessions/*
!/storage/framework/sessions/.gitkeep
/storage/framework/views/*
!/storage/framework/views/.gitkeep
/storage/logs/*
!/storage/logs/.gitkeep

# Test files
*test*.php
*Test*.php
/tests/
comprehensive_*.php
integration_*.php
fix_*.php
check_*.php
setup_*.php
verify_*.php
debug_*.php
simple_*.php
add_*.php
test_*.html
*.md
!README.md
!PRODUCTION_DEPLOYMENT_GUIDE.md
```

#### 1.2 إنشاء ملف .env.example للإنتاج
```env
APP_NAME="Hospital Management System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dental_app
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### 1.3 إنشاء Procfile لـ Heroku
```
web: vendor/bin/heroku-php-apache2 public/
```

#### 1.4 تحديث composer.json للإنتاج
```json
{
    "scripts": {
        "post-install-cmd": [
            "php artisan clear-compiled",
            "php artisan optimize",
            "chmod -R 755 storage",
            "php artisan migrate --force"
        ],
        "post-update-cmd": [
            "php artisan clear-compiled",
            "php artisan optimize"
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi"
        ]
    }
}
```

### الخطوة 2: رفع المشروع على GitHub

#### 2.1 استخدام السكريبت التلقائي (الأسهل)
```bash
# في Windows
git-setup.bat

# أو يدوياً
cd Dental_app
git init
git add .
git commit -m "Initial commit: Complete Hospital Management System v1.0"
git branch -M main
git remote add origin https://github.com/myouseef/Dental_app.git
git push -u origin main
```

#### 2.2 التحقق من الرفع
- اذهب إلى: https://github.com/myouseef/Dental_app
- تأكد من وجود جميع الملفات
- تحقق من أن ملف README.md يظهر بشكل صحيح

### الخطوة 3: النشر على Railway (الأسهل والأفضل) ⭐

#### 3.1 إنشاء حساب على Railway
1. اذهب إلى https://railway.app
2. سجل دخول باستخدام GitHub
3. اربط حسابك بـ GitHub repository

#### 3.2 إعداد المشروع
1. انقر على "New Project"
2. اختر "Deploy from GitHub repo"
3. اختر repository: `myouseef/Dental_app`
4. Railway سيكتشف أنه مشروع PHP تلقائياً

#### 3.3 إعداد قاعدة البيانات
1. في لوحة تحكم Railway، انقر "Add Service"
2. اختر "MySQL"
3. انسخ معلومات الاتصال من Variables tab

#### 3.4 إعداد متغيرات البيئة
في Railway Dashboard > Variables، أضف:
```env
APP_NAME=Hospital Management System
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql
DB_HOST=mysql_host_from_railway
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=password_from_railway

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# إعدادات الأمان
LOG_LEVEL=error
SESSION_LIFETIME=120
```

#### 3.5 النشر والاختبار
1. Railway سينشر المشروع تلقائياً
2. انتظر حتى يكتمل النشر (5-10 دقائق)
3. اختبر الموقع على الرابط المُعطى
4. سجل دخول باستخدام: `admin@hospital.com` / `admin123`

### الخطوة 4: النشر على Heroku (البديل الثاني)

#### 4.1 تثبيت Heroku CLI
```bash
# Windows
winget install Heroku.CLI

# أو تحميل من الموقع
# https://devcenter.heroku.com/articles/heroku-cli
```

#### 4.2 تسجيل الدخول وإنشاء التطبيق
```bash
heroku login
heroku create dental-app-hospital
```

#### 4.3 إضافة قاعدة البيانات
```bash
heroku addons:create cleardb:ignite
```

#### 4.4 إعداد متغيرات البيئة
```bash
heroku config:set APP_NAME="Hospital Management System"
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_KEY=$(php artisan --no-ansi key:generate --show)
```

#### 4.5 النشر
```bash
git push heroku main
heroku run php artisan migrate --force
heroku run php artisan db:seed --force
```

### الخطوة 5: إعداد قاعدة البيانات للإنتاج

#### 5.1 إنشاء Migration للبيانات الأساسية
```php
// database/migrations/2025_01_01_000000_seed_production_data.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        // إنشاء المستخدم الرئيسي
        $admin = User::firstOrCreate([
            'email' => 'admin@hospital.com'
        ], [
            'name' => 'مدير النظام',
            'password' => bcrypt('admin123'),
            'email_verified_at' => now(),
        ]);

        // إنشاء الأدوار الأساسية
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        
        // تعيين الدور للمستخدم
        if (!$admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        // إعطاء جميع الصلاحيات للمدير الرئيسي
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
    }

    public function down()
    {
        // لا نحذف البيانات في الإنتاج
    }
};
```

## 🔧 إعدادات الأمان للإنتاج

### 1. تحديث .htaccess
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Hide Laravel
    RewriteCond %{HTTP_HOST} ^(www\.)?(.*)$ [NC]
    RewriteRule ^(.*)$ /public/$1 [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set Content-Security-Policy "default-src 'self'"
</IfModule>
```

### 2. تحديث config/app.php للإنتاج
```php
'debug' => env('APP_DEBUG', false),
'url' => env('APP_URL', 'https://your-domain.com'),
'asset_url' => env('ASSET_URL', 'https://your-domain.com'),
```

## 📊 مراقبة الأداء

### 1. إعداد Laravel Telescope (اختياري)
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### 2. إعداد Logging
```php
// config/logging.php
'channels' => [
    'production' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
],
```

## 🚀 خطوات النشر السريع (التوصية)

### الطريقة الأسرع - Railway (5 خطوات فقط):
1. ✅ تشغيل `git-setup.bat` لرفع المشروع على GitHub
2. ✅ إنشاء حساب على Railway وربطه بـ GitHub
3. ✅ إنشاء مشروع جديد من repository
4. ✅ إضافة MySQL service وإعداد متغيرات البيئة
5. ✅ انتظار النشر التلقائي (5-10 دقائق)

### بيانات الدخول الافتراضية:
- **البريد الإلكتروني**: `admin@hospital.com`
- **كلمة المرور**: `admin123`
- **أو**: `admin@dental.com` / `password123`

### للنشر على Heroku (البديل):
1. ✅ تثبيت Heroku CLI
2. ✅ إنشاء تطبيق Heroku
3. ✅ إضافة ClearDB MySQL
4. ✅ إعداد متغيرات البيئة
5. ✅ Push إلى Heroku

## 🔗 روابط مفيدة

- **Railway Documentation**: https://docs.railway.app
- **Heroku PHP Support**: https://devcenter.heroku.com/articles/php-support
- **Laravel Deployment**: https://laravel.com/docs/deployment
- **GitHub Repository**: https://github.com/myouseef/Dental_app

## 📞 الدعم الفني

في حالة مواجهة أي مشاكل:
1. تحقق من logs التطبيق
2. راجع متغيرات البيئة
3. تأكد من إعدادات قاعدة البيانات
4. اتصل بدعم المنصة المستخدمة

---

**ملاحظة**: هذا الدليل يغطي النشر المجاني. للاستخدام التجاري الكثيف، يُنصح بالترقية للخطط المدفوعة.