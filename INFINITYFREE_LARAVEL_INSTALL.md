# 🚀 تثبيت Laravel على InfinityFree - دليل شامل
# Installing Laravel on InfinityFree - Complete Guide

## 🎯 نظرة عامة | Overview

InfinityFree يدعم Laravel بشكل كامل! يمكنك تثبيت مشروع Laravel بعدة طرق بسيطة.

InfinityFree fully supports Laravel! You can install Laravel projects in several simple ways.

---

## ✅ متطلبات InfinityFree | InfinityFree Requirements

### المواصفات المدعومة | Supported Specifications
- ✅ **PHP 8.2** - Latest version supported
- ✅ **MySQL 5.7** - Full database support
- ✅ **5GB Storage** - Plenty of space
- ✅ **Unlimited Bandwidth** - No traffic limits
- ✅ **cPanel** - Easy file management
- ✅ **File Manager** - Web-based file upload
- ✅ **SSH Access** - Available on paid plans (not needed)

### Laravel المدعوم | Supported Laravel
- ✅ **Laravel 8, 9, 10, 11** - All recent versions
- ✅ **Composer Dependencies** - Most packages work
- ✅ **Artisan Commands** - Via custom scripts
- ✅ **Migrations & Seeders** - Fully supported
- ✅ **Blade Templates** - Complete support

---

## 🚀 الطريقة الأولى: رفع مشروع موجود (الأسهل)

### الخطوة 1: تحضير المشروع محلياً (10 دقائق)

#### 1.1 تحديث ملف .env للإنتاج
```env
APP_NAME="Hospital Management System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://your-subdomain.epizy.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=sql200.epizy.com
DB_PORT=3306
DB_DATABASE=epiz_xxxxx_hospital_db
DB_USERNAME=epiz_xxxxx_hospital_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=myoussef400@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@hospital.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### 1.2 تشغيل Composer محلياً
```bash
# في مجلد المشروع
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan key:generate
```

#### 1.3 إنشاء ملف .htaccess للـ public
```apache
# public/.htaccess
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### الخطوة 2: إنشاء حساب InfinityFree (5 دقائق)

#### 2.1 التسجيل
1. اذهب إلى: **https://infinityfree.net**
2. انقر **"Create Account"**
3. املأ البيانات وفعل الحساب عبر البريد الإلكتروني

#### 2.2 إنشاء موقع جديد
1. انقر **"Create Account"** (إنشاء موقع)
2. اختر subdomain مجاني:
   ```
   hospital-system.epizy.com
   أو
   hospital-app.rf.gd
   أو
   medical-system.42web.io
   ```

### الخطوة 3: إعداد قاعدة البيانات (5 دقائق)

#### 3.1 الوصول لـ cPanel
1. في لوحة التحكم، انقر **"Control Panel"**
2. ستفتح cPanel

#### 3.2 إنشاء MySQL Database
1. ابحث عن **"MySQL Databases"**
2. في **"Create New Database"**:
   ```
   Database Name: hospital_db
   ```
3. انقر **"Create Database"**

#### 3.3 إنشاء MySQL User
1. في **"MySQL Users"**:
   ```
   Username: hospital_user
   Password: كلمة مرور قوية
   ```
2. انقر **"Create User"**

#### 3.4 ربط User بـ Database
1. في **"Add User to Database"**
2. اختر User و Database
3. اختر **"ALL PRIVILEGES"**
4. انقر **"Make Changes"**

### الخطوة 4: رفع ملفات Laravel (15 دقائق)

#### 4.1 ضغط المشروع
```bash
# ضغط المشروع (استثناء المجلدات غير المطلوبة)
zip -r hospital-project.zip . -x "node_modules/*" ".git/*" "tests/*"
```

#### 4.2 رفع الملفات
1. في cPanel، افتح **"File Manager"**
2. اذهب إلى مجلد **"htdocs"**
3. انقر **"Upload"**
4. ارفع ملف hospital-project.zip
5. انقر بالزر الأيمن على الملف واختر **"Extract"**
6. احذف ملف ZIP بعد الاستخراج

#### 4.3 ترتيب الملفات
تأكد من أن الهيكل كالتالي:
```
htdocs/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
└── composer.json
```

### الخطوة 5: إعداد Laravel (10 دقائق)

#### 5.1 تحديث ملف .env
1. في File Manager، افتح ملف `.env`
2. حدث معلومات قاعدة البيانات:
```env
DB_HOST=sql200.epizy.com
DB_DATABASE=epiz_xxxxx_hospital_db
DB_USERNAME=epiz_xxxxx_hospital_user
DB_PASSWORD=your_database_password
APP_URL=https://your-subdomain.epizy.com
```

#### 5.2 إعداد الصلاحيات
1. انقر بالزر الأيمن على مجلد **storage**
2. اختر **"Change Permissions"**
3. اجعلها **755** أو **777**
4. كرر للمجلدات:
   - `storage/`
   - `storage/logs/`
   - `storage/framework/`
   - `bootstrap/cache/`

#### 5.3 تشغيل Migrations
أنشئ ملف `setup.php` في htdocs:
```php
<?php
// setup.php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "Starting Laravel setup...<br>";

try {
    // Clear cache
    $kernel->call('config:clear');
    $kernel->call('cache:clear');
    $kernel->call('view:clear');
    echo "✅ Cache cleared<br>";
    
    // Run migrations
    $kernel->call('migrate', ['--force' => true]);
    echo "✅ Migrations completed<br>";
    
    // Run seeders
    $kernel->call('db:seed', ['--force' => true]);
    echo "✅ Seeders completed<br>";
    
    echo "<h2>🎉 Setup completed successfully!</h2>";
    echo "<p>You can now visit your website: <a href='./'>Click here</a></p>";
    echo "<p><strong>Don't forget to delete this setup.php file!</strong></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
```

#### 5.4 تشغيل Setup
1. اذهب إلى: `https://your-subdomain.epizy.com/setup.php`
2. انتظر حتى يكتمل التشغيل
3. احذف ملف `setup.php` بعد الانتهاء

---

## 🚀 الطريقة الثانية: تثبيت Laravel جديد (للمتقدمين)

### الخطوة 1: إنشاء مشروع Laravel عبر Terminal

#### 1.1 إذا كان SSH متاح (خطط مدفوعة)
```bash
# الاتصال بـ SSH
ssh username@your-domain.com

# تثبيت Laravel
composer create-project laravel/laravel hospital-system
cd hospital-system
```

#### 1.2 البديل: تثبيت محلي ثم رفع
```bash
# على جهازك المحلي
composer create-project laravel/laravel hospital-system
cd hospital-system

# إعداد المشروع
cp .env.example .env
php artisan key:generate
```

### الخطوة 2: إعداد المشروع الجديد
اتبع نفس خطوات الطريقة الأولى من الخطوة 3 فما بعد.

---

## 🔧 حل المشاكل الشائعة | Troubleshooting

### مشكلة 1: خطأ 500 Internal Server Error
```
الأسباب المحتملة:
❌ صلاحيات المجلدات خاطئة
❌ ملف .htaccess مفقود أو خاطئ
❌ APP_KEY مفقود في .env
❌ مسار خاطئ في config

الحلول:
✅ اجعل صلاحيات storage و bootstrap/cache = 755
✅ تأكد من وجود .htaccess في public
✅ تحقق من APP_KEY في .env
✅ راجع error logs في cPanel
```

### مشكلة 2: خطأ اتصال قاعدة البيانات
```
الأسباب:
❌ معلومات قاعدة البيانات خاطئة في .env
❌ قاعدة البيانات غير منشأة
❌ المستخدم غير مربوط بقاعدة البيانات

الحلول:
✅ تحقق من DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
✅ تأكد من إنشاء قاعدة البيانات في cPanel
✅ تحقق من ربط المستخدم بقاعدة البيانات
```

### مشكلة 3: Composer Dependencies مفقودة
```
الأسباب:
❌ مجلد vendor غير مرفوع
❌ بعض الـ packages غير مدعومة

الحلول:
✅ تأكد من رفع مجلد vendor كاملاً
✅ شغل composer install محلياً قبل الرفع
✅ استخدم --no-dev للإنتاج
```

### مشكلة 4: Routes لا تعمل
```
الأسباب:
❌ ملف .htaccess مفقود
❌ mod_rewrite غير مفعل
❌ مسارات خاطئة

الحلول:
✅ تأكد من وجود .htaccess في public
✅ InfinityFree يدعم mod_rewrite افتراضياً
✅ تحقق من routes/web.php
```

---

## 📊 مقارنة الطرق | Methods Comparison

| الطريقة | الصعوبة | الوقت | المميزات | العيوب |
|---------|---------|-------|----------|--------|
| **رفع مشروع موجود** | ⭐⭐ | 30 دقيقة | سهل، سريع | يحتاج مشروع جاهز |
| **تثبيت جديد** | ⭐⭐⭐ | 45 دقيقة | مرونة أكثر | أكثر تعقيداً |
| **SSH (مدفوع)** | ⭐⭐⭐⭐ | 20 دقيقة | سريع جداً | يحتاج خطة مدفوعة |

---

## 🎯 نصائح للنجاح | Success Tips

### 1. قبل الرفع
- ✅ اختبر المشروع محلياً
- ✅ شغل composer install --no-dev
- ✅ احذف المجلدات غير المطلوبة (node_modules, .git, tests)
- ✅ تأكد من ملف .htaccess

### 2. أثناء الرفع
- ✅ استخدم ملف ZIP للسرعة
- ✅ تحقق من اكتمال الرفع
- ✅ رتب الملفات في htdocs بشكل صحيح

### 3. بعد الرفع
- ✅ اعدل صلاحيات المجلدات
- ✅ حدث ملف .env
- ✅ شغل setup.php للـ migrations
- ✅ احذف ملفات الإعداد المؤقتة

---

## ✅ قائمة التحقق | Checklist

- [ ] ✅ حساب InfinityFree مُنشأ
- [ ] ✅ موقع جديد مُنشأ مع subdomain
- [ ] ✅ قاعدة بيانات MySQL مُنشأة
- [ ] ✅ مستخدم قاعدة البيانات مُنشأ ومربوط
- [ ] ✅ مشروع Laravel محضر محلياً
- [ ] ✅ ملفات Laravel مرفوعة بالكامل
- [ ] ✅ ملف .env محدث بمعلومات الإنتاج
- [ ] ✅ صلاحيات المجلدات مُعدة
- [ ] ✅ Migrations مُشغلة بنجاح
- [ ] ✅ الموقع يعمل بدون أخطاء

---

## 🎉 النتيجة النهائية | Final Result

**🔗 رابط الموقع**: https://your-subdomain.epizy.com  
**🗄️ قاعدة البيانات**: MySQL 5GB مجاناً إلى الأبد  
**🐘 PHP**: 8.2 مع دعم Laravel كامل  
**💰 التكلفة**: مجاني 100% بدون بطاقة دفع  
**⏰ المدة**: إلى الأبد  

**🚀 Laravel يعمل على InfinityFree بنجاح!**

---

**تم إعداد هذا الدليل بواسطة**: المهندس محمد يوسف - مصر  
**للدعم الفني**: myoussef400@gmail.com | +21095754085