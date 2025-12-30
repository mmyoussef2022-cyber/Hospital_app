# Hospital Management System (HMS)

نظام إدارة المستشفيات الشامل - A Comprehensive Hospital Management System

## 🏥 نظرة عامة | Overview

نظام إدارة المستشفيات هو حل شامل مصمم لإدارة جميع جوانب عمليات المستشفى متعدد الأقسام. يخدم النظام أدوار مستخدمين مختلفة ويوفر وصولاً آمناً للبيانات الطبية وجدولة المواعيد ونتائج المختبرات والإدارة المالية.

The Hospital Management System is a comprehensive solution designed to manage all aspects of multi-department hospital operations, serving various user roles with secure access to medical data, appointment scheduling, laboratory results, and financial management.

## ✨ الميزات الرئيسية | Key Features

### 🔐 إدارة المستخدمين والأمان | User Management & Security
- نظام أدوار وصلاحيات متقدم
- مصادقة متعددة العوامل (كلمة مرور، بصمة، Google OAuth)
- تشفير الرقم القومي وحماية البيانات الحساسة
- سجلات تدقيق شاملة

### 👥 إدارة المرضى والعائلات | Patient & Family Management
- نظام العائلات مع كود موحد
- السجلات الطبية الشاملة
- إدارة التأمين الصحي
- البحث المتقدم والفلترة

### 📅 نظام المواعيد | Appointment System
- جدولة ذكية مع منع التضارب
- تكامل مع جداول الأطباء
- إشعارات تلقائية عبر الواتساب والرسائل النصية
- حجز أونلاين من الموقع العام

### 🏥 القسم الداخلي | Inpatient Management
- إدارة الغرف والأسرة في الوقت الفعلي
- تنسيق العمليات الجراحية
- وحدات الرعاية المتخصصة
- تتبع المعدات والموارد

### 💰 النظام المالي المتقدم | Advanced Financial System
- فواتير نقدية وآجلة
- نظام أقساط خاص بعلاجات الأسنان
- إدارة شركات التأمين ومعالجة المطالبات
- تقارير مالية شاملة

### 🔔 نظام الإشعارات المتقدم | Advanced Notification System
- إشعارات متعددة القنوات (واتساب، SMS، بريد إلكتروني)
- تصنيف الأولويات والتصعيد التلقائي
- تتبع حالة التسليم
- قوالب رسائل قابلة للتخصيص

### 📱 الموقع العام والتطبيق المحمول | Public Website & Mobile App
- موقع عام بتصميم عصري (ألوان الفيسبوك 2025)
- حجز المواعيد أونلاين
- ملفات الأطباء التفصيلية
- تطبيق Flutter للأندرويد و iOS

## 🛠️ التقنيات المستخدمة | Technology Stack

### Backend
- **Laravel 10+** - PHP Framework
- **MySQL** - Database
- **Laravel Sanctum** - API Authentication
- **Spatie Laravel Permission** - Role & Permission System
- **Laravel Livewire** - Real-time UI Components

### Frontend
- **Laravel Blade** - Template Engine
- **Livewire** - Dynamic Components
- **Alpine.js** - Client-side Interactivity
- **Bootstrap** - UI Framework
- **Chart.js** - Analytics and Reports

### Mobile
- **Flutter** - Cross-platform Mobile Development
- **Android + iOS** Support

### External Integrations
- **WhatsApp Business API** - Messaging
- **SMS Gateway** - Text Notifications
- **Google OAuth 2.0** - Authentication
- **Barcode Scanning** - Patient & Inventory Management

## 📋 متطلبات النظام | System Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js & NPM
- Laravel 10+

## 🚀 التثبيت والإعداد | Installation & Setup

### 1. استنساخ المشروع | Clone Project
```bash
git clone <repository-url>
cd hospital-management-system
```

### 2. تثبيت التبعيات | Install Dependencies
```bash
composer install
npm install
```

### 3. إعداد البيئة | Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. إعداد قاعدة البيانات | Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 5. تشغيل التطبيق | Run Application
```bash
php artisan serve
npm run dev
```

## 📚 الوثائق | Documentation

- [متطلبات النظام](docs/requirements-arabic.md)
- [دليل التصميم](docs/design.md)
- [قائمة المهام](docs/tasks.md)
- [دليل المطور](docs/developer-guide.md)

## 🔒 الأمان | Security

- تشفير البيانات الحساسة
- مصادقة متعددة العوامل
- نظام صلاحيات متقدم
- سجلات تدقيق شاملة
- حماية من CSRF و XSS

## 🌐 الدعم متعدد اللغات | Multi-language Support

- العربية (الافتراضية)
- الإنجليزية
- واجهة RTL للعربية
- محتوى قابل للترجمة

## 📞 الدعم والمساعدة | Support & Help

للحصول على المساعدة أو الإبلاغ عن مشاكل:
- البريد الإلكتروني: support@hospital-hms.com
- الهاتف: +966112345678

## 📄 الترخيص | License

هذا المشروع مرخص تحت رخصة MIT - انظر ملف [LICENSE](LICENSE) للتفاصيل.

---

تم تطوير هذا النظام بواسطة فريق تطوير متخصص لخدمة القطاع الصحي في المملكة العربية السعودية.

Developed by a specialized development team to serve the healthcare sector in Saudi Arabia.