<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Hospital Management System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Themes CSS -->
    <link href="{{ asset('css/themes.css') }}" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --facebook-blue: #1877F2;
            --facebook-light-blue: #42A5F5;
            --facebook-dark-blue: #166FE5;
            --facebook-gray: #F0F2F5;
            --facebook-dark-gray: #65676B;
            --sidebar-width: 280px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--facebook-gray);
            overflow-x: hidden;
        }
        
        .btn-facebook {
            background-color: var(--facebook-blue);
            border-color: var(--facebook-blue);
            color: white;
        }
        
        .btn-facebook:hover {
            background-color: var(--facebook-dark-blue);
            border-color: var(--facebook-dark-blue);
            color: white;
        }
        
        .text-facebook {
            color: var(--facebook-blue);
        }
        
        .bg-facebook {
            background-color: var(--facebook-blue);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--facebook-blue) !important;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .form-control:focus {
            border-color: var(--facebook-light-blue);
            box-shadow: 0 0 0 0.2rem rgba(24, 119, 242, 0.25);
        }
        
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        .btn {
            border-radius: 6px;
            font-weight: 500;
        }
        
        .rtl {
            direction: rtl;
            text-align: right;
        }
        
        .ltr {
            direction: ltr;
            text-align: left;
        }

        /* Mini Sidebar Styles */
        .mini-sidebar {
            position: fixed;
            top: 80px; /* بعد الهيدر */
            width: 70px;
            height: calc(100vh - 80px);
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            z-index: 1050;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            overflow: hidden;
        }

        /* RTL Support for Mini Sidebar */
        [dir="rtl"] .mini-sidebar {
            right: 0;
            left: auto;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        }

        [dir="ltr"] .mini-sidebar {
            left: 0;
            right: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .mini-sidebar.expanded {
            width: 280px;
        }

        .mini-sidebar-content {
            padding: 1rem 0;
            height: 100%;
            overflow-y: auto;
        }

        .mini-nav-item {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .mini-nav-link {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            white-space: nowrap;
        }

        .mini-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .mini-nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .mini-nav-link.active::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 4px;
            background: white;
        }

        [dir="rtl"] .mini-nav-link.active::before {
            right: 0;
            left: auto;
        }

        [dir="ltr"] .mini-nav-link.active::before {
            left: 0;
            right: auto;
        }

        .mini-nav-icon {
            font-size: 1.4rem;
            width: 38px;
            text-align: center;
            flex-shrink: 0;
        }

        .mini-nav-text {
            font-weight: 500;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
        }

        [dir="rtl"] .mini-nav-text {
            margin-right: 1rem;
            margin-left: 0;
        }

        [dir="ltr"] .mini-nav-text {
            margin-left: 1rem;
            margin-right: 0;
        }

        .mini-sidebar.expanded .mini-nav-text {
            opacity: 1;
            transform: translateX(0);
        }

        /* Tooltip for collapsed state */
        .mini-nav-tooltip {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1060;
            pointer-events: none;
        }

        [dir="rtl"] .mini-nav-tooltip {
            right: 80px;
            left: auto;
        }

        [dir="ltr"] .mini-nav-tooltip {
            left: 80px;
            right: auto;
        }

        .mini-nav-tooltip::after {
            content: '';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
        }

        [dir="rtl"] .mini-nav-tooltip::after {
            left: -5px;
            right: auto;
            border-right-color: rgba(0, 0, 0, 0.8);
            border-left-color: transparent;
        }

        [dir="ltr"] .mini-nav-tooltip::after {
            right: -5px;
            left: auto;
            border-left-color: rgba(0, 0, 0, 0.8);
            border-right-color: transparent;
        }

        .mini-nav-item:hover .mini-nav-tooltip {
            opacity: 1;
            visibility: visible;
        }

        .mini-sidebar.expanded .mini-nav-tooltip {
            display: none;
        }

        /* Submenu styles */
        .mini-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0, 0, 0, 0.2);
        }

        .mini-submenu.expanded {
            max-height: 300px;
        }

        .mini-submenu-link {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        [dir="rtl"] .mini-submenu-link {
            padding: 0.75rem 4rem 0.75rem 1rem;
        }

        [dir="ltr"] .mini-submenu-link {
            padding: 0.75rem 1rem 0.75rem 4rem;
        }

        .mini-submenu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .mini-submenu-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        /* Expand/Collapse button */
        .mini-sidebar-toggle {
            position: absolute;
            top: 1rem;
            width: 30px;
            height: 30px;
            background: var(--primary);
            border: 2px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1051;
        }

        [dir="rtl"] .mini-sidebar-toggle {
            left: -15px;
            right: auto;
        }

        [dir="ltr"] .mini-sidebar-toggle {
            right: -15px;
            left: auto;
        }

        .mini-sidebar-toggle:hover {
            background: var(--dark);
            transform: scale(1.1);
        }

        .mini-sidebar-toggle i {
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }

        .mini-sidebar.expanded .mini-sidebar-toggle i {
            transform: rotate(180deg);
        }

        /* Section dividers */
        .mini-nav-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            margin: 1rem 0.5rem;
        }

        .mini-nav-section {
            padding: 0.5rem 1rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mini-sidebar.expanded .mini-nav-section {
            opacity: 1;
        }

        /* Hide old sidebar styles */
        .sidebar {
            display: none !important;
        }

        .sidebar-overlay {
            display: none !important;
        }

        /* Main content adjustment for mini sidebar */
        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
            transition: margin 0.3s ease;
        }

        [dir="rtl"] .main-content {
            margin-right: 70px; /* Space for mini sidebar */
            margin-left: 0;
        }

        [dir="ltr"] .main-content {
            margin-left: 70px; /* Space for mini sidebar */
            margin-right: 0;
        }

        [dir="rtl"] .mini-sidebar.expanded ~ .main-content {
            margin-right: 280px;
            margin-left: 0;
        }

        [dir="ltr"] .mini-sidebar.expanded ~ .main-content {
            margin-left: 280px;
            margin-right: 0;
        }

        /* Top Navigation */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
            display: none; /* Hide menu toggle for mini sidebar */
        }

        .menu-toggle:hover {
            background: var(--gray);
            transform: scale(1.05);
        }

        .menu-toggle:active {
            transform: scale(0.95);
        }

        .menu-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(var(--primary), 0.3);
        }

        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .mini-sidebar {
                transition: transform 0.3s ease;
            }
            
            [dir="rtl"] .mini-sidebar {
                transform: translateX(100%);
            }
            
            [dir="ltr"] .mini-sidebar {
                transform: translateX(-100%);
            }
            
            .mini-sidebar.mobile-show {
                transform: translateX(0);
            }
            
            .menu-toggle {
                display: block !important; /* Show menu toggle on mobile */
            }
            
            .main-content {
                margin-left: 0;
                margin-right: 0;
                padding: 1rem 0;
            }
            
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        /* Remove old RTL styles - now handled by CSS variables and proper RTL support */
        
        /* Language Toggle in Header */
        .language-toggle {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(var(--primary), 0.1);
            border-radius: 20px;
            padding: 0.25rem;
        }

        .language-option {
            padding: 0.5rem 1rem;
            border-radius: 16px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .language-option.active {
            background: var(--primary);
            color: white;
        }

        .language-option:hover:not(.active) {
            background: rgba(var(--primary), 0.1);
            color: var(--primary);
        }
    </style>
    
    @stack('styles')
</head>
<body class="theme-facebook {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div id="app">
        @auth
        <!-- Mini Sidebar -->
        <div class="mini-sidebar" id="miniSidebar">
            <div class="mini-sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-chevron-left"></i>
            </div>
            
            <div class="mini-sidebar-content">
                <!-- الرئيسية -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('home') || request()->routeIs('dashboard') || request()->routeIs('doctor.dashboard') ? 'active' : '' }}" 
                       href="{{ auth()->user()->hasRole('doctor') ? route('doctor.dashboard') : route('home') }}">
                        <i class="mini-nav-icon bi bi-speedometer2"></i>
                        <span class="mini-nav-text">{{ __('app.dashboard') }}</span>
                        <div class="mini-nav-tooltip">{{ __('app.dashboard') }}</div>
                    </a>
                </div>

                <div class="mini-nav-divider"></div>

                @can('users.view')
                <!-- نظام المستخدمين والصلاحيات المتقدم -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('usersSystemSubmenu', event)">
                        <i class="mini-nav-icon bi bi-people-fill"></i>
                        <span class="mini-nav-text">نظام المستخدمين والصلاحيات</span>
                        <span class="badge bg-success ms-2">جديد</span>
                        <div class="mini-nav-tooltip">إدارة المستخدمين والأدوار والصلاحيات</div>
                    </a>
                    <div class="mini-submenu" id="usersSystemSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('advanced-users.*') ? 'active' : '' }}" 
                           href="{{ route('advanced-users.index') }}">
                            <span>إدارة المستخدمين</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" 
                           href="{{ route('roles.index') }}">
                            <span>إدارة الأدوار</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}" 
                           href="{{ route('permissions.index') }}">
                            <span>إدارة الصلاحيات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}" 
                           href="{{ route('activity-logs.index') }}">
                            <span>سجل العمليات</span>
                        </a>
                    </div>
                </div>
                @endcan

                @can('reception.view')
                <!-- لوحة الاستقبال الشاملة -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('reception.*') ? 'active' : '' }}" 
                       href="{{ route('reception.dashboard') }}">
                        <i class="mini-nav-icon bi bi-hospital"></i>
                        <span class="mini-nav-text">لوحة الاستقبال الشاملة</span>
                        <span class="badge bg-success ms-2">جاهز</span>
                        <div class="mini-nav-tooltip">إدارة جميع عمليات المستشفى</div>
                    </a>
                </div>
                @endcan

                @can('cashier.view')
                <!-- لوحة الخزينة المتقدمة -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('cashier.*') ? 'active' : '' }}" 
                       href="{{ route('cashier.dashboard') }}">
                        <i class="mini-nav-icon bi bi-credit-card-2-front"></i>
                        <span class="mini-nav-text">لوحة الخزينة المتقدمة</span>
                        <span class="badge bg-success ms-2">جاهز</span>
                        <div class="mini-nav-tooltip">إدارة المدفوعات والتأمين</div>
                    </a>
                </div>
                @endcan

                @can('doctor.integrated.view')
                <!-- لوحة تحكم الطبيب المتكاملة -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('doctor.integrated.*') ? 'active' : '' }}" 
                       href="{{ route('doctor.integrated.dashboard') }}">
                        <i class="mini-nav-icon fas fa-stethoscope"></i>
                        <span class="mini-nav-text">لوحة الطبيب المتكاملة</span>
                        <div class="mini-nav-tooltip">الكشف والوصفات والتحاليل</div>
                    </a>
                </div>
                @endcan

                <div class="mini-nav-divider"></div>

                @can('patients.view')
                <!-- المرضى -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('patientsSubmenu', event)">
                        <i class="mini-nav-icon bi bi-people-fill"></i>
                        <span class="mini-nav-text">{{ __('app.patients') }}</span>
                        <div class="mini-nav-tooltip">{{ __('app.patient_management') }}</div>
                    </a>
                    <div class="mini-submenu" id="patientsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('patients.index') ? 'active' : '' }}" 
                           href="{{ route('patients.index') }}">
                            <span>{{ __('app.patient_list') }}</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('patients.create') ? 'active' : '' }}" 
                           href="{{ route('patients.create') }}">
                            <span>{{ __('app.add_new_patient') }}</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('patients.families') ? 'active' : '' }}" 
                           href="{{ route('patients.families') }}">
                            <span>إدارة العائلات</span>
                        </a>
                    </div>
                </div>
                @endcan

                <!-- المواعيد -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('appointmentsSubmenu', event)">
                        <i class="mini-nav-icon bi bi-calendar-check"></i>
                        <span class="mini-nav-text">{{ __('app.appointments') }}</span>
                        <div class="mini-nav-tooltip">{{ __('app.appointments') }}</div>
                    </a>
                    <div class="mini-submenu" id="appointmentsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}" 
                           href="{{ route('appointments.index') }}">
                            <span>{{ __('app.appointment_list') }}</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('appointments.doctor-calendar') ? 'active' : '' }}" 
                           href="{{ route('appointments.doctor-calendar') }}">
                            <span>{{ __('app.doctor_calendar') }}</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('appointments.create') ? 'active' : '' }}" 
                           href="{{ route('appointments.create') }}">
                            <span>{{ __('app.book_appointment') }}</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('appointments.today') ? 'active' : '' }}" 
                           href="{{ route('appointments.today') }}">
                            <span>{{ __('app.today_appointments') }}</span>
                        </a>
                    </div>
                </div>

                <!-- إدارة الأطباء -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('doctors.*') || request()->routeIs('doctor-certificates.*') || request()->routeIs('doctor-services.*') || request()->routeIs('reviews.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('doctorsSubmenu', event)">
                        <i class="mini-nav-icon fas fa-user-md"></i>
                        <span class="mini-nav-text">إدارة الأطباء</span>
                        <div class="mini-nav-tooltip">إدارة الأطباء</div>
                    </a>
                    <div class="mini-submenu" id="doctorsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('doctors.index') ? 'active' : '' }}" 
                           href="{{ route('doctors.index') }}">
                            <span>قائمة الأطباء</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('doctors.create') ? 'active' : '' }}" 
                           href="{{ route('doctors.create') }}">
                            <span>إضافة طبيب جديد</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('doctor-certificates.index') ? 'active' : '' }}" 
                           href="{{ route('doctor-certificates.index') }}">
                            <span>شهادات الأطباء</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('doctor-services.index') ? 'active' : '' }}" 
                           href="{{ route('doctor-services.index') }}">
                            <span>خدمات الأطباء</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('reviews.index') ? 'active' : '' }}" 
                           href="{{ route('reviews.index') }}">
                            <span>تقييمات المرضى</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('financial.index') ? 'active' : '' }}" 
                           href="{{ route('financial.index') }}">
                            <span>الإدارة المالية للأطباء</span>
                        </a>
                    </div>
                </div>

                <!-- السجلات الطبية والوصفات -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('medical-records.*') || request()->routeIs('prescriptions.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('medicalRecordsSubmenu', event)">
                        <i class="mini-nav-icon fas fa-file-medical"></i>
                        <span class="mini-nav-text">السجلات الطبية</span>
                        <div class="mini-nav-tooltip">السجلات الطبية والوصفات</div>
                    </a>
                    <div class="mini-submenu" id="medicalRecordsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('medical-records.index') ? 'active' : '' }}" 
                           href="{{ route('medical-records.index') }}">
                            <span>السجلات الطبية</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('prescriptions.index') ? 'active' : '' }}" 
                           href="{{ route('prescriptions.index') }}">
                            <span>الوصفات الطبية</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('prescriptions.create') ? 'active' : '' }}" 
                           href="{{ route('prescriptions.create') }}">
                            <span>إنشاء وصفة جديدة</span>
                        </a>
                    </div>
                </div>

                <!-- قسم الأسنان -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('dental.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('dentalSubmenu', event)">
                        <i class="mini-nav-icon fas fa-tooth"></i>
                        <span class="mini-nav-text">قسم الأسنان</span>
                        <div class="mini-nav-tooltip">قسم الأسنان</div>
                    </a>
                    <div class="mini-submenu" id="dentalSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('dental.treatments.index') ? 'active' : '' }}" 
                           href="{{ route('dental.treatments.index') }}">
                            <span>خطط العلاج</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('dental.sessions.index') ? 'active' : '' }}" 
                           href="{{ route('dental.sessions.index') }}">
                            <span>جلسات العلاج</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('dental.installments.index') ? 'active' : '' }}" 
                           href="{{ route('dental.installments.index') }}">
                            <span>الأقساط</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('dental.sessions.calendar') ? 'active' : '' }}" 
                           href="{{ route('dental.sessions.calendar') }}">
                            <span>تقويم الجلسات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('dental.installments.overdue-list') ? 'active' : '' }}" 
                           href="{{ route('dental.installments.overdue-list') }}">
                            <span>الأقساط المتأخرة</span>
                        </a>
                    </div>
                </div>

                <!-- المختبرات والأشعة -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('lab.*') || request()->routeIs('radiology.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('labsSubmenu', event)">
                        <i class="mini-nav-icon bi bi-clipboard-pulse"></i>
                        <span class="mini-nav-text">المختبرات والأشعة</span>
                        <div class="mini-nav-tooltip">المختبرات والأشعة</div>
                    </a>
                    <div class="mini-submenu" id="labsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('lab.index') ? 'active' : '' }}" 
                           href="{{ route('lab.index') }}">
                            <span>طلبات المختبر</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('lab.today') ? 'active' : '' }}" 
                           href="{{ route('lab.today') }}">
                            <span>مختبرات اليوم</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('lab-tests.*') ? 'active' : '' }}" 
                           href="{{ route('lab-tests.index') }}">
                            <span>إدارة الفحوصات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('radiology.index') ? 'active' : '' }}" 
                           href="{{ route('radiology.index') }}">
                            <span>طلبات الأشعة</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('radiology.today') ? 'active' : '' }}" 
                           href="{{ route('radiology.today') }}">
                            <span>أشعة اليوم</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('radiology-studies.*') ? 'active' : '' }}" 
                           href="{{ route('radiology-studies.index') }}">
                            <span>إدارة فحوصات الأشعة</span>
                        </a>
                        <div class="mini-nav-divider"></div>
                        @can('lab.view')
                        <a class="mini-submenu-link {{ request()->routeIs('lab-specialized.dashboard') ? 'active' : '' }}" 
                           href="{{ route('lab-specialized.dashboard') }}">
                            <span>لوحة تحكم المختبر</span>
                            <span class="badge bg-success ms-2">جديد</span>
                        </a>
                        @endcan
                        @can('radiology.view')
                        <a class="mini-submenu-link {{ request()->routeIs('radiology-specialized.dashboard') ? 'active' : '' }}" 
                           href="{{ route('radiology-specialized.dashboard') }}">
                            <span>لوحة تحكم الأشعة</span>
                            <span class="badge bg-success ms-2">جديد</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <!-- إدارة الغرف والأسرة -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('rooms.*') || request()->routeIs('beds.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('roomsSubmenu', event)">
                        <i class="mini-nav-icon bi bi-house-door"></i>
                        <span class="mini-nav-text">إدارة الغرف والأسرة</span>
                        <div class="mini-nav-tooltip">إدارة الغرف والأسرة</div>
                    </a>
                    <div class="mini-submenu" id="roomsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('rooms.dashboard') ? 'active' : '' }}" 
                           href="{{ route('rooms.dashboard') }}">
                            <span>لوحة تحكم الغرف</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('rooms.index') ? 'active' : '' }}" 
                           href="{{ route('rooms.index') }}">
                            <span>إدارة الغرف</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('beds.index') ? 'active' : '' }}" 
                           href="{{ route('beds.index') }}">
                            <span>إدارة الأسرة</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('rooms.create') ? 'active' : '' }}" 
                           href="{{ route('rooms.create') }}">
                            <span>إضافة غرفة جديدة</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('beds.create') ? 'active' : '' }}" 
                           href="{{ route('beds.create') }}">
                            <span>إضافة سرير جديد</span>
                        </a>
                    </div>
                </div>

                <!-- إدارة الجراحة -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('surgeries.*') || request()->routeIs('surgical-procedures.*') || request()->routeIs('operating-rooms.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('surgerySubmenu', event)">
                        <i class="mini-nav-icon fas fa-procedures"></i>
                        <span class="mini-nav-text">إدارة الجراحة</span>
                        <div class="mini-nav-tooltip">إدارة الجراحة</div>
                    </a>
                    <div class="mini-submenu" id="surgerySubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('surgeries.dashboard') ? 'active' : '' }}" 
                           href="{{ route('surgeries.dashboard') }}">
                            <span>لوحة تحكم الجراحة</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('surgeries.today') ? 'active' : '' }}" 
                           href="{{ route('surgeries.today') }}">
                            <span>عمليات اليوم</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('surgeries.index') ? 'active' : '' }}" 
                           href="{{ route('surgeries.index') }}">
                            <span>إدارة العمليات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('surgeries.create') ? 'active' : '' }}" 
                           href="{{ route('surgeries.create') }}">
                            <span>جدولة عملية جديدة</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('surgical-procedures.index') ? 'active' : '' }}" 
                           href="{{ route('surgical-procedures.index') }}">
                            <span>الإجراءات الجراحية</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('operating-rooms.dashboard') ? 'active' : '' }}" 
                           href="{{ route('operating-rooms.dashboard') }}">
                            <span>غرف العمليات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('operating-rooms.index') ? 'active' : '' }}" 
                           href="{{ route('operating-rooms.index') }}">
                            <span>إدارة غرف العمليات</span>
                        </a>
                    </div>
                </div>

                <div class="mini-nav-divider"></div>

                <!-- نظام التقارير والتحليلات المتقدم -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('reportsSubmenu', event)">
                        <i class="mini-nav-icon fas fa-chart-line"></i>
                        <span class="mini-nav-text">التقارير والتحليلات المتقدم</span>
                        <span class="badge bg-success ms-2">جديد</span>
                        <div class="mini-nav-tooltip">نظام التقارير والتحليلات المتقدم</div>
                    </a>
                    <div class="mini-submenu" id="reportsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('reports.executive-summary') ? 'active' : '' }}" 
                           href="{{ route('reports.executive-summary') }}">
                            <span>الملخص التنفيذي</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('reports.financial') ? 'active' : '' }}" 
                           href="{{ route('reports.financial') }}">
                            <span>التقارير المالية</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('reports.performance') ? 'active' : '' }}" 
                           href="{{ route('reports.performance') }}">
                            <span>تقارير الأداء</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('reports.patient-statistics') ? 'active' : '' }}" 
                           href="{{ route('reports.patient-statistics') }}">
                            <span>إحصائيات المرضى</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('reports.insurance') ? 'active' : '' }}" 
                           href="{{ route('reports.insurance') }}">
                            <span>تقارير التأمين</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}" 
                           href="{{ route('reports.inventory') }}">
                            <span>تقارير المخزون</span>
                        </a>
                    </div>
                </div>

                <!-- إدارة صفحة الهبوط -->
                @can('landing-page.manage')
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('admin.landing-page.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('landingPageSubmenu', event)">
                        <i class="mini-nav-icon fas fa-globe"></i>
                        <span class="mini-nav-text">إدارة صفحة الهبوط</span>
                        <span class="badge bg-info ms-2">جديد</span>
                        <div class="mini-nav-tooltip">إدارة صفحة الهبوط والعروض</div>
                    </a>
                    <div class="mini-submenu" id="landingPageSubmenu">
                        @can('landing-page.manage')
                        <a class="mini-submenu-link {{ request()->routeIs('admin.landing-page.dashboard') ? 'active' : '' }}" 
                           href="{{ route('admin.landing-page.dashboard') }}">
                            <span>لوحة التحكم</span>
                        </a>
                        @endcan
                        
                        @can('landing-page.settings')
                        <a class="mini-submenu-link {{ request()->routeIs('admin.landing-page.settings') ? 'active' : '' }}" 
                           href="{{ route('admin.landing-page.settings') }}">
                            <span>إعدادات الصفحة</span>
                        </a>
                        @endcan
                        
                        @can('landing-page.offers')
                        <a class="mini-submenu-link {{ request()->routeIs('admin.landing-page.offers*') ? 'active' : '' }}" 
                           href="{{ route('admin.landing-page.offers') }}">
                            <span>إدارة العروض</span>
                        </a>
                        @endcan
                        
                        @can('landing-page.offers')
                        <a class="mini-submenu-link {{ request()->routeIs('admin.landing-page.offers.create') ? 'active' : '' }}" 
                           href="{{ route('admin.landing-page.offers.create') }}">
                            <span>إضافة عرض جديد</span>
                        </a>
                        @endcan
                        
                        @can('landing-page.analytics')
                        <a class="mini-submenu-link {{ request()->routeIs('admin.landing-page.analytics') ? 'active' : '' }}" 
                           href="{{ route('admin.landing-page.analytics') }}">
                            <span>التحليلات</span>
                        </a>
                        @endcan
                        
                        <div class="mini-nav-divider"></div>
                        
                        @can('landing-page.view')
                        <a class="mini-submenu-link" href="{{ route('public.landing') }}" target="_blank">
                            <span>عرض صفحة الهبوط</span>
                        </a>
                        @endcan
                        
                        @can('landing-page.manage')
                        <a class="mini-submenu-link {{ request()->routeIs('admin.landing-page.preview') ? 'active' : '' }}" 
                           href="{{ route('admin.landing-page.preview') }}" target="_blank">
                            <span>معاينة الصفحة</span>
                        </a>
                        @endcan
                        
                        @can('landing-page.manage')
                        <a class="mini-submenu-link" href="#" onclick="clearLandingPageCache()">
                            <span>مسح الذاكرة المؤقتة</span>
                        </a>
                        @endcan
                    </div>
                </div>
                @endcan

                <!-- نظام الأمان والمراقبة المتقدم -->
                @can('security.view')
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('security.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('securitySubmenu', event)">
                        <i class="mini-nav-icon fas fa-shield-alt"></i>
                        <span class="mini-nav-text">الأمان والمراقبة</span>
                        <span class="badge bg-danger ms-2">جديد</span>
                        <div class="mini-nav-tooltip">نظام الأمان والمراقبة المتقدم</div>
                    </a>
                    <div class="mini-submenu" id="securitySubmenu">
                        @can('security.view')
                        <a class="mini-submenu-link {{ request()->routeIs('security.dashboard') ? 'active' : '' }}" 
                           href="{{ route('security.dashboard') }}">
                            <span>لوحة تحكم الأمان</span>
                        </a>
                        @endcan
                        
                        @can('security.logs')
                        <a class="mini-submenu-link {{ request()->routeIs('security.logs') ? 'active' : '' }}" 
                           href="{{ route('security.logs') }}">
                            <span>سجلات الأمان</span>
                        </a>
                        @endcan
                        
                        @can('security.view')
                        <a class="mini-submenu-link {{ request()->routeIs('security.login-attempts') ? 'active' : '' }}" 
                           href="{{ route('security.login-attempts') }}">
                            <span>محاولات تسجيل الدخول</span>
                        </a>
                        @endcan
                        
                        @can('security.manage')
                        <a class="mini-submenu-link" href="#" onclick="performHealthCheck()">
                            <span>فحص صحة النظام</span>
                        </a>
                        @endcan
                        
                        @can('security.backup')
                        <a class="mini-submenu-link" href="#" onclick="createSecurityBackup()">
                            <span>نسخة احتياطية أمنية</span>
                        </a>
                        @endcan
                    </div>
                </div>
                @endcan

                <!-- نظام اختبار التكامل الشامل -->
                @can('integration.test')
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('integration.*') ? 'active' : '' }}" 
                       href="#" onclick="toggleSubmenu('integrationSubmenu', event)">
                        <i class="mini-nav-icon fas fa-cogs"></i>
                        <span class="mini-nav-text">اختبار التكامل الشامل</span>
                        <span class="badge bg-info ms-2">مكتمل</span>
                        <div class="mini-nav-tooltip">نظام اختبار التكامل الشامل للأنظمة</div>
                    </a>
                    <div class="mini-submenu" id="integrationSubmenu">
                        @can('integration.test')
                        <a class="mini-submenu-link" href="#" onclick="runIntegrationTest('comprehensive')">
                            <span>الاختبار الشامل الأساسي</span>
                        </a>
                        @endcan
                        
                        @can('integration.test')
                        <a class="mini-submenu-link" href="#" onclick="runIntegrationTest('data-flow')">
                            <span>اختبار تدفق البيانات</span>
                        </a>
                        @endcan
                        
                        @can('integration.test')
                        <a class="mini-submenu-link" href="#" onclick="runIntegrationTest('permissions')">
                            <span>اختبار نظام الصلاحيات</span>
                        </a>
                        @endcan
                        
                        @can('integration.test')
                        <a class="mini-submenu-link" href="#" onclick="runIntegrationTest('payment-insurance')">
                            <span>اختبار الدفع والتأمين</span>
                        </a>
                        @endcan
                        
                        @can('integration.test')
                        <a class="mini-submenu-link" href="#" onclick="runIntegrationTest('medical-procedures')">
                            <span>اختبار الإجراءات الطبية</span>
                        </a>
                        @endcan
                        
                        @can('integration.test')
                        <a class="mini-submenu-link" href="#" onclick="runIntegrationTest('notifications')">
                            <span>اختبار نظام الإشعارات</span>
                        </a>
                        @endcan
                        
                        <div class="mini-nav-divider"></div>
                        
                        @can('integration.manage')
                        <a class="mini-submenu-link" href="#" onclick="generateSystemReport()">
                            <span>تقرير حالة النظام</span>
                        </a>
                        @endcan
                        
                        @can('integration.manage')
                        <a class="mini-submenu-link" href="#" onclick="runAllTests()">
                            <span>تشغيل جميع الاختبارات</span>
                        </a>
                        @endcan
                    </div>
                </div>
                @endcan

                <!-- المالية -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('invoices.*') || request()->routeIs('payments.*') || request()->routeIs('insurance-companies.*') || request()->routeIs('financial.*') || request()->routeIs('advanced-billing.*') ? 'active' : '' }}" href="#" onclick="toggleSubmenu('financeSubmenu', event)">
                        <i class="mini-nav-icon bi bi-cash-stack"></i>
                        <span class="mini-nav-text">{{ __('app.finance') }}</span>
                        <div class="mini-nav-tooltip">{{ __('app.finance') }} & {{ __('app.insurance_companies') }}</div>
                    </a>
                    <div class="mini-submenu" id="financeSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('advanced-billing.dashboard') ? 'active' : '' }}" href="{{ route('advanced-billing.dashboard') }}">
                            <span>لوحة الفوترة المتقدمة</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                            <span>إدارة الفواتير</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('invoices.dashboard') ? 'active' : '' }}" href="{{ route('invoices.dashboard') }}">
                            <span>لوحة تحكم الفواتير</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('advanced-billing.cash-invoices') ? 'active' : '' }}" href="{{ route('advanced-billing.cash-invoices') }}">
                            <span>الفواتير النقدية</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('advanced-billing.credit-invoices') ? 'active' : '' }}" href="{{ route('advanced-billing.credit-invoices') }}">
                            <span>الفواتير الآجلة</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('advanced-billing.overdue-management') ? 'active' : '' }}" href="{{ route('advanced-billing.overdue-management') }}">
                            <span>إدارة المتأخرات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                            <span>إدارة المدفوعات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('payments.dashboard') ? 'active' : '' }}" href="{{ route('payments.dashboard') }}">
                            <span>لوحة تحكم المدفوعات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('insurance-companies.*') ? 'active' : '' }}" href="{{ route('insurance-companies.index') }}">
                            <span>شركات التأمين</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('insurance-companies.dashboard') ? 'active' : '' }}" href="{{ route('insurance-companies.dashboard') }}">
                            <span>لوحة تحكم التأمين</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('insurance-policies.*') ? 'active' : '' }}" href="{{ route('insurance-policies.index') }}">
                            <span>بوالص التأمين</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('insurance-claims.*') ? 'active' : '' }}" href="{{ route('insurance-claims.index') }}">
                            <span>المطالبات التأمينية</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('insurance-claims.dashboard') ? 'active' : '' }}" href="{{ route('insurance-claims.dashboard') }}">
                            <span>لوحة تحكم المطالبات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('financial.*') ? 'active' : '' }}" href="{{ route('financial.index') }}">
                            <span>الإدارة المالية للأطباء</span>
                        </a>
                    </div>
                </div>

                <!-- إدارة الورديات -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('shifts.*') || request()->routeIs('cash-registers.*') || request()->routeIs('shift-reports.*') || request()->routeIs('shift-handovers.*') || request()->routeIs('staff-productivity.*') ? 'active' : '' }}" href="#" onclick="toggleSubmenu('shiftsSubmenu', event)">
                        <i class="mini-nav-icon fas fa-clock"></i>
                        <span class="mini-nav-text">إدارة الورديات</span>
                        <div class="mini-nav-tooltip">إدارة الورديات والصناديق</div>
                    </a>
                    <div class="mini-submenu" id="shiftsSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('shifts.dashboard') ? 'active' : '' }}" href="{{ route('shifts.dashboard') }}">
                            <span>لوحة تحكم الورديات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('shifts.index') ? 'active' : '' }}" href="{{ route('shifts.index') }}">
                            <span>إدارة الورديات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('shifts.calendar') ? 'active' : '' }}" href="{{ route('shifts.calendar') }}">
                            <span>تقويم الورديات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('cash-registers.dashboard') ? 'active' : '' }}" href="{{ route('cash-registers.dashboard') }}">
                            <span>لوحة تحكم الصناديق</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('cash-registers.index') ? 'active' : '' }}" href="{{ route('cash-registers.index') }}">
                            <span>إدارة الصناديق</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('cash-registers.reconciliation-report') ? 'active' : '' }}" href="{{ route('cash-registers.reconciliation-report') }}">
                            <span>تقرير التسوية</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('shift-reports.*') ? 'active' : '' }}" href="{{ route('shift-reports.index') }}">
                            <span>تقارير الورديات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('shift-handovers.*') ? 'active' : '' }}" href="{{ route('shift-handovers.index') }}">
                            <span>تسليم الورديات</span>
                        </a>
                        <a class="mini-submenu-link {{ request()->routeIs('staff-productivity.*') ? 'active' : '' }}" href="{{ route('staff-productivity.index') }}">
                            <span>إنتاجية الموظفين</span>
                        </a>
                    </div>
                </div>

                <div class="mini-nav-divider"></div>

                @can('users.view')
                <!-- الإدارة -->
                <div class="mini-nav-item">
                    <a class="mini-nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="#" onclick="toggleSubmenu('adminSubmenu', event)">
                        <i class="mini-nav-icon bi bi-gear"></i>
                        <span class="mini-nav-text">{{ __('app.administration') }}</span>
                        <div class="mini-nav-tooltip">{{ __('app.administration') }} {{ __('app.reports') }}</div>
                    </a>
                    <div class="mini-submenu" id="adminSubmenu">
                        <a class="mini-submenu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                           href="{{ route('admin.users.index') }}">
                            <span>{{ __('app.user_management') }}</span>
                        </a>
                        <a class="mini-submenu-link" href="#">
                            <span>{{ __('app.system_settings') }}</span>
                        </a>
                        <a class="mini-submenu-link" href="#">
                            <span>{{ __('app.financial_reports') }}</span>
                        </a>
                        <a class="mini-submenu-link" href="#">
                            <span>{{ __('app.general_reports') }}</span>
                        </a>
                        <a class="mini-submenu-link" href="#">
                            <span>إحصائيات النظام</span>
                        </a>
                        <a class="mini-submenu-link" href="#">
                            <span>النسخ الاحتياطي</span>
                        </a>
                    </div>
                </div>
                @endcan
            </div>
        </div>

        <!-- Old Sidebar (Hidden) -->
        <div class="sidebar" id="sidebar" style="display: none;">
            <div class="sidebar-header">
                <a href="{{ route('home') }}" class="sidebar-brand">
                    <i class="bi bi-hospital"></i>
                    {{ __('app.hospital_management_system') }}
                </a>
            </div>
            
            <nav class="sidebar-nav"></nav>
            
            <div class="sidebar-user">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-details">
                        <h6>{{ Auth::user()->name }}</h6>
                        <small>{{ Auth::user()->job_title ?? 'موظف' }}</small>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i>
                        {{ __('app.logout') }}
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Sidebar Overlay (Hidden) -->
        <div class="sidebar-overlay" id="sidebarOverlay" style="display: none;"></div>
        
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="menu-toggle" id="menuToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <h5 class="mb-0 ms-3 text-primary-theme">
                            @yield('page-title', __('app.hospital_management_system'))
                        </h5>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- Language Toggle -->
                        <div class="language-toggle d-none d-md-flex">
                            <div class="language-option {{ app()->getLocale() === 'ar' ? 'active' : '' }}" onclick="switchLanguage('ar')">
                                {{ __('app.arabic') }}
                            </div>
                            <div class="language-option {{ app()->getLocale() === 'en' ? 'active' : '' }}" onclick="switchLanguage('en')">
                                {{ __('app.english') }}
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell"></i>
                                <span class="badge bg-danger">3</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">{{ __('app.notifications') }}</h6></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-info-circle text-info"></i> {{ __('app.new_appointment') }}</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-exclamation-triangle text-warning"></i> {{ __('app.lab_report_ready') }}</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="#">{{ __('app.view_all') }}</a></li>
                            </ul>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-primary-modern btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> {{ __('app.profile') }}</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> {{ __('app.settings') }}</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right"></i> {{ __('app.logout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        <main class="main-content">
            @if (session('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Manager -->
    <script src="{{ asset('js/theme-manager.js') }}"></script>
    
    <!-- Mini Sidebar JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const miniSidebar = document.getElementById('miniSidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const menuToggle = document.getElementById('menuToggle');
            
            // Auto-expand sidebar on page load to show the new systems
            if (miniSidebar) {
                miniSidebar.classList.add('expanded');
            }
            
            // Toggle sidebar expansion
            function toggleSidebar() {
                miniSidebar.classList.toggle('expanded');
                
                // Close all submenus when collapsing
                if (!miniSidebar.classList.contains('expanded')) {
                    document.querySelectorAll('.mini-submenu.expanded').forEach(submenu => {
                        submenu.classList.remove('expanded');
                    });
                }
            }
            
            // Toggle mobile sidebar
            function toggleMobileSidebar() {
                miniSidebar.classList.toggle('mobile-show');
            }
            
            // Event listeners
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleMobileSidebar();
                });
            }
            
            // Auto-expand sidebar on hover (desktop only)
            if (window.innerWidth > 768) {
                miniSidebar.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('expanded')) {
                        this.classList.add('expanded');
                    }
                });
                
                // Optional: Auto-collapse on mouse leave (uncomment if desired)
                // miniSidebar.addEventListener('mouseleave', function() {
                //     if (this.classList.contains('expanded')) {
                //         this.classList.remove('expanded');
                //     }
                // });
            }
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    miniSidebar.classList.remove('mobile-show');
                }
            });
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && 
                    miniSidebar.classList.contains('mobile-show') &&
                    !miniSidebar.contains(e.target) &&
                    !menuToggle.contains(e.target)) {
                    miniSidebar.classList.remove('mobile-show');
                }
            });
            
            // Handle escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (window.innerWidth <= 768 && miniSidebar.classList.contains('mobile-show')) {
                        miniSidebar.classList.remove('mobile-show');
                    }
                }
            });
            
            // Auto-expand active submenu
            document.querySelectorAll('.mini-submenu .mini-submenu-link.active').forEach(activeLink => {
                const submenu = activeLink.closest('.mini-submenu');
                if (submenu) {
                    submenu.classList.add('expanded');
                    // Also expand the sidebar if there's an active submenu item
                    miniSidebar.classList.add('expanded');
                }
            });
        });
        
        // Toggle submenu function
        function toggleSubmenu(submenuId, event) {
            event.preventDefault();
            
            const submenu = document.getElementById(submenuId);
            const miniSidebar = document.getElementById('miniSidebar');
            
            // Expand sidebar if not already expanded
            if (!miniSidebar.classList.contains('expanded')) {
                miniSidebar.classList.add('expanded');
            }
            
            // Close other submenus
            document.querySelectorAll('.mini-submenu').forEach(otherSubmenu => {
                if (otherSubmenu !== submenu) {
                    otherSubmenu.classList.remove('expanded');
                }
            });
            
            // Toggle current submenu
            submenu.classList.toggle('expanded');
        }
        
        // Language switching function
        function switchLanguage(language) {
            if (window.themeManager) {
                window.themeManager.switchLanguage(language);
            } else {
                // Fallback - redirect to Laravel language route
                window.location.href = `/lang/${language}`;
            }
        }
    </script>

    <!-- Integration Testing System JavaScript Functions -->
    <script>
        // Run specific integration test
        function runIntegrationTest(testType) {
            const testNames = {
                'comprehensive': 'الاختبار الشامل الأساسي',
                'data-flow': 'اختبار تدفق البيانات',
                'permissions': 'اختبار نظام الصلاحيات',
                'payment-insurance': 'اختبار الدفع والتأمين',
                'medical-procedures': 'اختبار الإجراءات الطبية',
                'notifications': 'اختبار نظام الإشعارات'
            };

            Swal.fire({
                title: `تشغيل ${testNames[testType]}`,
                text: 'هل تريد تشغيل هذا الاختبار؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'نعم، تشغيل',
                cancelButtonText: 'إلغاء',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`/integration/test/${testType}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`خطأ في الطلب: ${error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'اكتمل الاختبار بنجاح',
                            html: `
                                <div class="text-start">
                                    <h6>نتائج ${testNames[testType]}:</h6>
                                    <div class="mt-3">
                                        ${data.results ? data.results.map(result => 
                                            `<div class="d-flex align-items-center mb-2">
                                                <i class="bi ${result.status === 'success' ? 'bi-check-circle text-success' : 'bi-x-circle text-danger'} me-2"></i>
                                                <span>${result.message}</span>
                                            </div>`
                                        ).join('') : data.message}
                                    </div>
                                </div>
                            `,
                            width: '600px'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل الاختبار',
                            text: data.message || 'حدث خطأ أثناء تشغيل الاختبار'
                        });
                    }
                }
            });
        }

        // Generate system status report
        function generateSystemReport() {
            Swal.fire({
                title: 'إنشاء تقرير حالة النظام',
                text: 'هل تريد إنشاء تقرير شامل عن حالة النظام؟',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'نعم، إنشاء التقرير',
                cancelButtonText: 'إلغاء',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('/integration/system-report', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .catch(error => {
                        Swal.showValidationMessage(`خطأ في الطلب: ${error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم إنشاء التقرير بنجاح',
                            html: `
                                <div class="text-start">
                                    <h6>ملخص حالة النظام:</h6>
                                    <div class="mt-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>التكامل:</strong> ${data.integration_status}%
                                            </div>
                                            <div class="col-6">
                                                <strong>الأمان:</strong> ${data.security_status}%
                                            </div>
                                            <div class="col-6">
                                                <strong>قاعدة البيانات:</strong> ${data.database_status}%
                                            </div>
                                            <div class="col-6">
                                                <strong>الصلاحيات:</strong> ${data.permissions_status}%
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">تم حفظ التقرير الكامل في: ${data.report_path}</small>
                                        </div>
                                    </div>
                                </div>
                            `,
                            width: '500px'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل إنشاء التقرير',
                            text: data.message || 'حدث خطأ أثناء إنشاء التقرير'
                        });
                    }
                }
            });
        }

        // Run all integration tests
        function runAllTests() {
            Swal.fire({
                title: 'تشغيل جميع اختبارات التكامل',
                text: 'هذا سيستغرق عدة دقائق. هل تريد المتابعة؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، تشغيل جميع الاختبارات',
                cancelButtonText: 'إلغاء',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('/integration/run-all-tests', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .catch(error => {
                        Swal.showValidationMessage(`خطأ في الطلب: ${error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'اكتملت جميع الاختبارات',
                            html: `
                                <div class="text-start">
                                    <h6>نتائج الاختبارات الشاملة:</h6>
                                    <div class="mt-3">
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-success" style="width: ${data.success_rate}%">
                                                ${data.success_rate}% نجح
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>الاختبارات المنجزة:</strong> ${data.total_tests}
                                            </div>
                                            <div class="col-6">
                                                <strong>الاختبارات الناجحة:</strong> ${data.passed_tests}
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">تم حفظ التقرير الكامل في: ${data.report_path}</small>
                                        </div>
                                    </div>
                                </div>
                            `,
                            width: '600px'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشلت بعض الاختبارات',
                            html: `
                                <div class="text-start">
                                    <p>${data.message}</p>
                                    <div class="mt-3">
                                        <small class="text-muted">راجع السجلات للحصول على تفاصيل أكثر</small>
                                    </div>
                                </div>
                            `
                        });
                    }
                }
            });
        }
    </script>

    <!-- Security System JavaScript Functions -->
    <script>
        // Security Health Check Function
        function performHealthCheck() {
            fetch('/security/health-check', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'healthy') {
                    Swal.fire({
                        icon: 'success',
                        title: 'النظام سليم',
                        text: 'لم يتم العثور على مشاكل أمنية'
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'تم العثور على مشاكل',
                        html: data.issues.join('<br>')
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'فشل في فحص صحة النظام'
                });
            });
        }

        // Security Backup Function
        function createSecurityBackup() {
            Swal.fire({
                title: 'إنشاء نسخة احتياطية أمنية',
                text: 'هل تريد إنشاء نسخة احتياطية من بيانات الأمان؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'نعم، إنشاء',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/security/create-backup', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم بنجاح',
                                text: data.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: data.message
                            });
                        }
                    });
                }
            });
        }

        // Landing Page Cache Clear Function
        function clearLandingPageCache() {
            Swal.fire({
                title: 'مسح ذاكرة صفحة الهبوط',
                text: 'هل تريد مسح الذاكرة المؤقتة لصفحة الهبوط؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'نعم، مسح',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/admin/landing-page/clear-cache', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم بنجاح',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: 'حدث خطأ أثناء مسح الذاكرة المؤقتة'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'حدث خطأ أثناء مسح الذاكرة المؤقتة'
                        });
                    });
                }
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>