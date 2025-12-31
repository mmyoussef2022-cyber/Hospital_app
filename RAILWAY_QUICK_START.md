# 🚀 دليل البدء السريع - Railway
# Railway Quick Start Guide

## ✅ تم الانتهاء من | Completed
- [x] رفع المشروع على GitHub: https://github.com/myouseef/Hospital_app.git
- [x] إعداد ملفات Railway (nixpacks.toml, railway.json)
- [x] تحديث إعدادات الإنتاج

## 🎯 الخطوات التالية (30 دقيقة) | Next Steps (30 minutes)

### 1️⃣ إنشاء حساب Railway (5 دقائق)
```
🌐 اذهب إلى: https://railway.app
🔐 سجل دخول بـ GitHub
✅ وافق على الصلاحيات
```

### 2️⃣ نشر المشروع (10 دقائق)
```
➕ New Project
📁 Deploy from GitHub repo
🔍 ابحث عن: myouseef/Hospital_app
🚀 Deploy Now
```

### 3️⃣ إضافة MySQL (5 دقائق)
```
➕ + New Service
🗄️ Database > Add MySQL
⏳ انتظر الإنشاء (2-3 دقائق)
```

### 4️⃣ إعداد المتغيرات (10 دقائق)
انقر Laravel service > Variables:

```env
APP_NAME=Hospital Management System
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=${{RAILWAY_STATIC_URL}}

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

### 5️⃣ توليد APP_KEY
```bash
# في مجلد المشروع
php artisan key:generate --show
# انسخ النتيجة وضعها في APP_KEY
```

### 6️⃣ الحصول على الرابط
```
⚙️ Laravel service > Settings
🌐 Domains > Generate Domain
🔗 ستحصل على: https://your-app.railway.app
```

## 🎉 النتيجة | Result
- ✅ موقع يعمل على الإنترنت
- ✅ قاعدة بيانات مجانية
- ✅ SSL مجاني
- ✅ 500 ساعة مجانية شهرياً

## 🔑 بيانات الدخول | Login Credentials
```
📧 البريد: admin@hospital.com
🔒 كلمة المرور: admin123
```

## 📞 الدعم | Support
```
📧 Email: myoussef400@gmail.com
📱 Phone: +21095754085
📚 الدليل المفصل: RAILWAY_DEPLOYMENT_GUIDE.md
```

---
**🚀 ابدأ الآن من الخطوة 1!**