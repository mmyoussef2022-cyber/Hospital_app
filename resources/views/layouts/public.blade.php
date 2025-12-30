<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta-description', 'مركز محمد يوسف لطب الأسنان - خدمات طبية متميزة')">
    <meta name="keywords" content="@yield('meta-keywords', 'طب الأسنان, مستشفى, عيادة, أطباء, حجز موعد')">

    <title>@yield('title', 'مركز محمد يوسف لطب الأسنان')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom Public CSS -->
    <style>
        :root {
            /* Facebook 2025 Color Scheme */
            --facebook-blue: #1877F2;
            --facebook-light-blue: #42A5F5;
            --facebook-dark-blue: #166FE5;
            --facebook-gray: #F0F2F5;
            --facebook-dark-gray: #65676B;
            --facebook-light-gray: #E4E6EA;
            --facebook-green: #42B883;
            --facebook-orange: #FF6B35;
            --facebook-purple: #8B5CF6;
            
            /* Additional Colors */
            --white: #FFFFFF;
            --black: #1C1E21;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            
            /* Gradients */
            --gradient-primary: linear-gradient(135deg, var(--facebook-blue) 0%, var(--facebook-light-blue) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--facebook-purple) 0%, var(--facebook-blue) 100%);
            --gradient-success: linear-gradient(135deg, var(--facebook-green) 0%, var(--success) 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--black);
            background-color: var(--white);
            overflow-x: hidden;
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 1rem;
        }
        
        .display-1 { font-size: 3.5rem; font-weight: 700; }
        .display-2 { font-size: 3rem; font-weight: 700; }
        .display-3 { font-size: 2.5rem; font-weight: 600; }
        .display-4 { font-size: 2rem; font-weight: 600; }
        
        /* Buttons */
        .btn {
            border-radius: 12px;
            font-weight: 500;
            padding: 12px 24px;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-facebook {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3);
        }
        
        .btn-facebook:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(24, 119, 242, 0.4);
            color: white;
        }
        
        .btn-outline-facebook {
            border: 2px solid var(--facebook-blue);
            color: var(--facebook-blue);
            background: transparent;
        }
        
        .btn-outline-facebook:hover {
            background: var(--facebook-blue);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        .btn-lg {
            padding: 16px 32px;
            font-size: 1.1rem;
            border-radius: 16px;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 8px;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card-modern {
            background: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card-gradient {
            background: var(--gradient-primary);
            color: white;
        }
        
        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        /* Navigation */
        .navbar-public {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        
        .navbar-scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.15);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--facebook-blue) !important;
            text-decoration: none;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--black) !important;
            padding: 0.75rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .nav-link:hover {
            background: var(--facebook-gray);
            color: var(--facebook-blue) !important;
        }
        
        .nav-link.active {
            background: var(--facebook-blue);
            color: white !important;
        }
        
        /* Hero Section */
        .hero-section {
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        /* Statistics */
        .stats-section {
            background: var(--facebook-gray);
            padding: 4rem 0;
        }
        
        .stat-card {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--facebook-blue);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--facebook-dark-gray);
            font-weight: 500;
        }
        
        /* Doctor Cards */
        .doctor-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .doctor-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }
        
        .doctor-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: var(--gradient-primary);
        }
        
        .doctor-info {
            padding: 1.5rem;
        }
        
        .doctor-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--black);
            margin-bottom: 0.5rem;
        }
        
        .doctor-specialty {
            color: var(--facebook-blue);
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .doctor-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .rating-stars {
            color: #FFD700;
        }
        
        .rating-text {
            color: var(--facebook-dark-gray);
            font-size: 0.9rem;
        }
        
        /* Forms */
        .form-control {
            border: 2px solid var(--facebook-light-gray);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--facebook-blue);
            box-shadow: 0 0 0 0.2rem rgba(24, 119, 242, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--black);
            margin-bottom: 0.5rem;
        }
        
        .form-select {
            border: 2px solid var(--facebook-light-gray);
            border-radius: 12px;
            padding: 12px 16px;
        }
        
        /* Alerts */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
            border-left: 4px solid var(--info);
        }
        
        /* Footer */
        .footer {
            background: var(--black);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer-section h5 {
            color: var(--facebook-blue);
            margin-bottom: 1rem;
        }
        
        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: var(--facebook-blue);
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--facebook-blue);
            color: white;
            border-radius: 50%;
            text-decoration: none;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--facebook-dark-blue);
            transform: translateY(-2px);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .btn-lg {
                padding: 12px 24px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .display-1 { font-size: 2.5rem; }
            .display-2 { font-size: 2rem; }
            .display-3 { font-size: 1.8rem; }
            .display-4 { font-size: 1.5rem; }
        }
        
        /* RTL Support */
        [dir="rtl"] {
            text-align: right;
        }
        
        [dir="rtl"] .navbar-nav {
            margin-right: auto;
            margin-left: 0;
        }
        
        [dir="rtl"] .social-links a {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50%;
            box-shadow: 0 4px 20px rgba(24, 119, 242, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1020;
        }
        
        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(24, 119, 242, 0.6);
        }
        
        [dir="rtl"] .fab {
            right: auto;
            left: 2rem;
        }
        
        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--facebook-gray);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--facebook-blue);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--facebook-dark-blue);
        }
    </style>
    
    @stack('styles')
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-public" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="{{ route('public.index') }}">
                <i class="fas fa-hospital-alt me-2"></i>
                مركز محمد يوسف لطب الأسنان
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.index') ? 'active' : '' }}" href="{{ route('public.index') }}">
                            <i class="fas fa-home me-1"></i>
                            الرئيسية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.doctors.*') ? 'active' : '' }}" href="{{ route('public.doctors.index') }}">
                            <i class="fas fa-user-md me-1"></i>
                            الأطباء
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.booking.*') ? 'active' : '' }}" href="{{ route('public.booking.form') }}">
                            <i class="fas fa-calendar-plus me-1"></i>
                            حجز موعد
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">
                            <i class="fas fa-stethoscope me-1"></i>
                            الخدمات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">
                            <i class="fas fa-phone me-1"></i>
                            اتصل بنا
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-2">
                    @guest
                        <button class="btn btn-outline-facebook btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            تسجيل الدخول
                        </button>
                        <button class="btn btn-facebook btn-sm" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="fas fa-user-plus me-1"></i>
                            إنشاء حساب
                        </button>
                    @else
                        <div class="dropdown">
                            <button class="btn btn-facebook btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('public.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5>
                            <i class="fas fa-hospital-alt me-2"></i>
                            مركز محمد يوسف لطب الأسنان
                        </h5>
                        <p class="text-light">
                            نقدم أفضل الخدمات الطبية المتخصصة في طب الأسنان مع فريق من أمهر الأطباء والمتخصصين.
                        </p>
                        <div class="social-links">
                            <a href="#" title="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="تويتر"><i class="fab fa-twitter"></i></a>
                            <a href="#" title="إنستغرام"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="واتساب"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>روابط سريعة</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('public.index') }}" class="footer-link">الرئيسية</a></li>
                            <li><a href="{{ route('public.doctors.index') }}" class="footer-link">الأطباء</a></li>
                            <li><a href="{{ route('public.booking.form') }}" class="footer-link">حجز موعد</a></li>
                            <li><a href="#services" class="footer-link">الخدمات</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>معلومات التواصل</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                العنوان: شارع الملك فهد، الرياض
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                الهاتف: 966123456789+
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                البريد: info@dentalcenter.com
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>ساعات العمل</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">السبت - الخميس: 8:00 ص - 10:00 م</li>
                            <li class="mb-2">الجمعة: 2:00 م - 10:00 م</li>
                            <li class="mb-2">الطوارئ: 24/7</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-light">
                        © {{ date('Y') }} مركز محمد يوسف لطب الأسنان. جميع الحقوق محفوظة.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="footer-link me-3">سياسة الخصوصية</a>
                    <a href="#" class="footer-link">شروط الاستخدام</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <button class="fab" onclick="scrollToTop()" title="العودة للأعلى">
        <i class="fas fa-arrow-up"></i>
    </button>

    @guest
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تسجيل الدخول</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">تذكرني</label>
                        </div>
                        <button type="submit" class="btn btn-facebook w-100">تسجيل الدخول</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إنشاء حساب جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_name" class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" id="reg_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reg_email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="reg_email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_phone" class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" id="reg_phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reg_national_id" class="form-label">رقم الهوية الوطنية</label>
                                <input type="text" class="form-control" id="reg_national_id" name="national_id" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_date_of_birth" class="form-label">تاريخ الميلاد</label>
                                <input type="date" class="form-control" id="reg_date_of_birth" name="date_of_birth" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reg_gender" class="form-label">الجنس</label>
                                <select class="form-select" id="reg_gender" name="gender" required>
                                    <option value="">اختر الجنس</option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_password" class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" id="reg_password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reg_password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                                <input type="password" class="form-control" id="reg_password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-facebook w-100">إنشاء الحساب</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endguest

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Smooth scroll to top
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/hide FAB based on scroll position
        window.addEventListener('scroll', function() {
            const fab = document.querySelector('.fab');
            if (window.scrollY > 300) {
                fab.style.display = 'flex';
            } else {
                fab.style.display = 'none';
            }
        });

        // Register form submission
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<span class="loading"></span> جاري الإنشاء...';
            submitBtn.disabled = true;
            
            fetch('{{ route("public.register") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم إنشاء الحساب بنجاح!');
                    window.location.href = data.redirect;
                } else {
                    alert('حدث خطأ: ' + (data.message || 'يرجى المحاولة مرة أخرى'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Search functionality
        function performSearch(query) {
            if (query.length < 2) return;
            
            fetch(`{{ route('public.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    // Handle search results
                    console.log('Search results:', data);
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }

        // Get available slots for booking
        function getAvailableSlots(doctorId, date) {
            if (!doctorId || !date) return;
            
            fetch(`{{ route('public.available-slots') }}?doctor_id=${doctorId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    // Handle available slots
                    console.log('Available slots:', data);
                })
                .catch(error => {
                    console.error('Slots error:', error);
                });
        }
    </script>
    
    @stack('scripts')
</body>
</html>