@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-speedometer2 text-facebook"></i>
                    لوحة التحكم
                </h1>
                <div class="text-muted">
                    <i class="bi bi-calendar3"></i>
                    {{ now()->format('Y/m/d') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-facebook text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-1">مرحباً، {{ Auth::user()->name }}</h4>
                            <p class="mb-0">
                                <i class="bi bi-briefcase"></i>
                                {{ Auth::user()->job_title }}
                                @if(Auth::user()->department)
                                    - {{ Auth::user()->department->name }}
                                @endif
                            </p>
                            <small class="opacity-75">
                                آخر تسجيل دخول: {{ Auth::user()->last_login_at?->diffForHumans() ?? 'المرة الأولى' }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-circle" style="font-size: 4rem; opacity: 0.7;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        @can('patients.view')
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">المرضى</h5>
                    <h3 class="text-primary">{{ \App\Models\Patient::count() }}</h3>
                    <small class="text-muted">إجمالي المرضى المسجلين</small>
                </div>
            </div>
        </div>
        @endcan

        @can('doctors.view')
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-person-heart text-danger" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">الأطباء</h5>
                    <h3 class="text-danger">{{ \App\Models\Doctor::count() }}</h3>
                    <small class="text-muted">الأطباء المسجلين</small>
                </div>
            </div>
        </div>
        @endcan

        @can('users.view')
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-person-badge text-success" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">الموظفين</h5>
                    <h3 class="text-success">{{ \App\Models\User::where('is_active', true)->count() }}</h3>
                    <small class="text-muted">الموظفين النشطين</small>
                </div>
            </div>
        </div>
        @endcan

        @can('appointments.view')
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-calendar-check text-warning" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">المواعيد</h5>
                    <h3 class="text-warning">{{ \App\Models\Appointment::whereDate('appointment_date', today())->count() }}</h3>
                    <small class="text-muted">مواعيد اليوم</small>
                </div>
            </div>
        </div>
        @endcan
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning"></i>
                        الإجراءات السريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @can('patients.create')
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('patients.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center">
                                <i class="bi bi-person-plus" style="font-size: 2rem;"></i>
                                <span class="mt-2">تسجيل مريض جديد</span>
                            </a>
                        </div>
                        @endcan

                        @can('doctors.view')
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('doctors.index') }}" class="btn btn-outline-danger w-100 h-100 d-flex flex-column justify-content-center">
                                <i class="bi bi-person-heart" style="font-size: 2rem;"></i>
                                <span class="mt-2">إدارة الأطباء</span>
                            </a>
                        </div>
                        @endcan

                        @can('appointments.create')
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('appointments.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column justify-content-center">
                                <i class="bi bi-calendar-plus" style="font-size: 2rem;"></i>
                                <span class="mt-2">حجز موعد</span>
                            </a>
                        </div>
                        @endcan

                        @can('users.create')
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center">
                                <i class="bi bi-person-badge" style="font-size: 2rem;"></i>
                                <span class="mt-2">إضافة موظف</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Roles and Permissions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check"></i>
                        أدوارك وصلاحياتك
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>الأدوار:</strong>
                        <div class="mt-2">
                            @forelse(Auth::user()->roles as $role)
                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">لا توجد أدوار مخصصة</span>
                            @endforelse
                        </div>
                    </div>
                    
                    <div>
                        <strong>عدد الصلاحيات:</strong>
                        <span class="badge bg-success">{{ Auth::user()->getAllPermissions()->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i>
                        معلومات النظام
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-code-square text-primary"></i>
                            <strong>إصدار Laravel:</strong> {{ app()->version() }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-server text-success"></i>
                            <strong>إصدار PHP:</strong> {{ PHP_VERSION }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-database text-info"></i>
                            <strong>قاعدة البيانات:</strong> MySQL
                        </li>
                        <li>
                            <i class="bi bi-clock text-warning"></i>
                            <strong>المنطقة الزمنية:</strong> {{ config('app.timezone') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection