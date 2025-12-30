@extends('layouts.app')

@section('title', 'لوحة التحكم الرئيسية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-speedometer2 text-primary me-2"></i>
                        مرحباً، {{ auth()->user()->name }}
                    </h1>
                    <p class="text-muted mb-0">لوحة التحكم الرئيسية - نظام إدارة المستشفى</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-clock me-1"></i>
                        {{ now()->format('H:i') }}
                    </span>
                    <br>
                    <small class="text-muted">{{ now()->format('Y-m-d') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                المرضى المسجلين
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Patient::count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                مواعيد اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Appointment::whereDate('appointment_date', today())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                الأطباء النشطين
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Doctor::where('is_active', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                الفواتير المعلقة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Invoice::where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Systems -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                الأنظمة الرئيسية
            </h4>
        </div>
    </div>

    <div class="row">
        @can('reception.view')
        <!-- Reception System -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-hospital fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-0">لوحة الاستقبال الشاملة</h5>
                            <small>إدارة جميع عمليات المستشفى</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-0 text-primary">{{ \App\Models\Appointment::whereDate('appointment_date', today())->count() }}</div>
                                    <small class="text-muted">مواعيد اليوم</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-0 text-success">{{ \App\Models\Patient::whereDate('created_at', today())->count() }}</div>
                                    <small class="text-muted">مرضى جدد</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0 text-warning">0</div>
                                <small class="text-muted">في الانتظار</small>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('reception.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-right me-2"></i>
                            دخول لوحة الاستقبال
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success">مكتمل</span>
                        <small class="text-muted">جاهز للاستخدام</small>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        @can('cashier.view')
        <!-- Cashier System -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-success text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-credit-card-2-front fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-0">لوحة الخزينة المتقدمة</h5>
                            <small>إدارة المدفوعات والتأمين</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-0 text-success">0</div>
                                    <small class="text-muted">مدفوعات اليوم</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-0 text-info">{{ \App\Models\Invoice::where('status', 'pending')->count() }}</div>
                                    <small class="text-muted">فواتير معلقة</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0 text-warning">0</div>
                                <small class="text-muted">متأخرات</small>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('cashier.dashboard') }}" class="btn btn-success">
                            <i class="bi bi-arrow-right me-2"></i>
                            دخول لوحة الخزينة
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success">مكتمل</span>
                        <small class="text-muted">جاهز للاستخدام</small>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        @can('doctor.integrated.view')
        <!-- Doctor Integrated System -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-info text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-stethoscope fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-0">لوحة الطبيب المتكاملة</h5>
                            <small>الكشف والوصفات والتحاليل</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>قيد التطوير</strong>
                            <br>
                            <small>المهمة التالية في خطة التطوير</small>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-info" onclick="alert('سيتم تطوير هذا النظام قريباً - المهمة التالية في خطة التطوير')">
                            <i class="bi bi-tools me-2"></i>
                            قيد التطوير
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-warning">قيد التطوير</span>
                        <small class="text-muted">المهمة التالية</small>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>

    <!-- Existing Systems -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="bi bi-check-circle text-success me-2"></i>
                الأنظمة الموجودة
            </h4>
        </div>
    </div>

    <div class="row">
        <!-- Patients -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-people-fill text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">إدارة المرضى</h6>
                            <small class="text-muted">{{ \App\Models\Patient::count() }} مريض</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-check text-success fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">إدارة المواعيد</h6>
                            <small class="text-muted">{{ \App\Models\Appointment::count() }} موعد</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctors -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-md text-info fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">إدارة الأطباء</h6>
                            <small class="text-muted">{{ \App\Models\Doctor::count() }} طبيب</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cash-stack text-warning fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">الأنظمة المالية</h6>
                            <small class="text-muted">{{ \App\Models\Invoice::count() }} فاتورة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Progress -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        تقدم التطوير - الأنظمة الأساسية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">نظام المستخدمين والصلاحيات</span>
                                <span class="text-sm text-success">100%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">لوحة الاستقبال الشاملة</span>
                                <span class="text-sm text-success">100%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">لوحة الخزينة المتقدمة</span>
                                <span class="text-sm text-success">100%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">لوحة الطبيب المتكاملة</span>
                                <span class="text-sm text-warning">0%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 5%"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">نظام ملف المريض المتقدم</span>
                                <span class="text-sm text-secondary">0%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-secondary" style="width: 2%"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm font-weight-bold">الأنظمة المتقدمة الأخرى</span>
                                <span class="text-sm text-secondary">0%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-secondary" style="width: 1%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>المرحلة الحالية:</strong> تم إكمال 3 من 12 مهمة رئيسية (25%)
                            <br>
                            <strong>المهمة التالية:</strong> تطوير لوحة تحكم الطبيب المتكاملة
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحديث الوقت كل دقيقة
    setInterval(function() {
        const timeElement = document.querySelector('.badge.bg-success');
        if (timeElement) {
            const now = new Date();
            const timeString = now.toLocaleTimeString('ar-SA', {
                hour: '2-digit',
                minute: '2-digit'
            });
            timeElement.innerHTML = '<i class="bi bi-clock me-1"></i>' + timeString;
        }
    }, 60000);
});
</script>
@endpush