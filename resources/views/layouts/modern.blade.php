<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1877F2">

    <title>@yield('title', config('app.name', 'Hospital Management System'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom Themes CSS -->
    <link href="{{ asset('css/themes.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="theme-facebook">
    <!-- Header -->
    <header class="modern-header">
        <div class="header-content d-flex align-items-center justify-content-between w-100">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle btn btn-link d-md-none p-0" type="button">
                <i class="bi bi-list fs-4"></i>
            </button>
            
            <!-- Logo -->
            <div class="header-logo d-flex align-items-center">
                <i class="bi bi-hospital text-primary-theme fs-3 me-2"></i>
                <h4 class="mb-0 text-primary-theme fw-bold d-none d-sm-block">
                    {{ config('app.name', 'Hospital MS') }}
                </h4>
            </div>
            
            <!-- Header Actions -->
            <div class="header-actions d-flex align-items-center gap-3">
                <!-- Search -->
                <div class="search-box d-none d-lg-block">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-modern" placeholder="{{ app()->getLocale() === 'ar' ? 'بحث...' : 'Search...' }}">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-link position-relative p-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ app()->getLocale() === 'ar' ? 'الإشعارات' : 'Notifications' }}</h6></li>
                        <li><a class="dropdown-item" href="#">{{ app()->getLocale() === 'ar' ? 'موعد جديد' : 'New appointment' }}</a></li>
                        <li><a class="dropdown-item" href="#">{{ app()->getLocale() === 'ar' ? 'تقرير مختبر' : 'Lab report ready' }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">{{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View all' }}</a></li>
                    </ul>
                </div>
                
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link d-flex align-items-center p-0" type="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=1877F2&color=fff" 
                             alt="User" class="rounded-circle me-2" width="40" height="40">
                        <div class="text-start d-none d-sm-block">
                            <div class="fw-semibold">{{ auth()->user()->name ?? 'User' }}</div>
                            <small class="text-muted">{{ auth()->user()->email ?? 'user@example.com' }}</small>
                        </div>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>{{ app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'Profile' }}</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>{{ app()->getLocale() === 'ar' ? 'الإعدادات' : 'Settings' }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Logout' }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="modern-sidebar">
        <!-- Sidebar Toggle -->
        <div class="sidebar-header p-3 d-flex justify-content-between align-items-center">
            <h6 class="text-white mb-0 fw-bold">{{ app()->getLocale() === 'ar' ? 'القائمة' : 'Menu' }}</h6>
            <button class="sidebar-toggle btn btn-link text-white p-0">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="sidebar-nav">
            <!-- Dashboard -->
            <div class="nav-section">
                <div class="nav-section-title">{{ app()->getLocale() === 'ar' ? 'الرئيسية' : 'Main' }}</div>
                <div class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 nav-icon"></i>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</span>
                    </a>
                </div>
            </div>

            <!-- Patient Management -->
            <div class="nav-section">
                <div class="nav-section-title">{{ app()->getLocale() === 'ar' ? 'إدارة المرضى' : 'Patient Management' }}</div>
                <div class="nav-item">
                    <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                        <i class="bi bi-people nav-icon"></i>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'المرضى' : 'Patients' }}</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check nav-icon"></i>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'المواعيد' : 'Appointments' }}</span>
                    </a>
                </div>
            </div>

            <!-- Medical Staff -->
            <div class="nav-section">
                <div class="nav-section-title">{{ app()->getLocale() === 'ar' ? 'الطاقم الطبي' : 'Medical Staff' }}</div>
                <div class="nav-item">
                    <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge nav-icon"></i>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'الأطباء' : 'Doctors' }}</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('reviews.index') }}" class="nav-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}">
                        <i class="bi bi-star nav-icon"></i>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'التقييمات' : 'Reviews' }}</span>
                    </a>
                </div>
            </div>

            <!-- Departments -->
            <div class="nav-section">
                <div class="nav-section-title">{{ app()->getLocale() === 'ar' ? 'الأقسام الطبية' : 'Medical Departments' }}</div>
                <div class="nav-item">
                    <a href="{{ route('dental.treatments.index') }}" class="nav-link {{ request()->routeIs('dental.*') ? 'active' : '' }}">
                        <i class="bi bi-heart-pulse nav-icon"></i>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'قسم الأسنان' : 'Dental Department' }}</span>
                    </a>
                </div>
            </div>

            <!-- Administration -->
            @can('access-admin')
            <div class="nav-section">
                <div class="nav-section-title">{{ app()->getLocale() === 'ar' ? 'الإدارة' : 'Administration' }}</div>
                <div class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="bi bi-gear nav-icon"></i>
                        <span class="nav-text">{{ app()->getLocale() === 'ar' ? 'إدارة المستخدمين' : 'User Management' }}</span>
                    </a>
                </div>
            </div>
            @endcan
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="modern-content">
        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show animate-fade-in" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate-fade-in" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show animate-fade-in" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show animate-fade-in" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        <div class="animate-fade-in">
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Manager -->
    <script src="{{ asset('js/theme-manager.js') }}"></script>
    
    @stack('scripts')
</body>
</html>