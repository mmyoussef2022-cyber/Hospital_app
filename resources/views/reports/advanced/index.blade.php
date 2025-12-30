@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('التقارير والتحليلات المتقدمة') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('الرئيسية') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('التقارير المتقدمة') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('إجمالي الإيرادات اليوم') }}</p>
                            <h4 class="mb-0">{{ number_format($todayRevenue ?? 0) }} {{ __('ريال') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-money font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('المواعيد اليوم') }}</p>
                            <h4 class="mb-0">{{ $todayAppointments ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                <span class="avatar-title">
                                    <i class="bx bx-calendar font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('المرضى الجدد') }}</p>
                            <h4 class="mb-0">{{ $newPatients ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-info">
                                <span class="avatar-title">
                                    <i class="bx bx-user-plus font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('معدل الرضا') }}</p>
                            <h4 class="mb-0">4.2/5</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                                <span class="avatar-title">
                                    <i class="bx bx-star font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أنواع التقارير -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('أنواع التقارير المتاحة') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- التقارير المالية -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border shadow-none">
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="bx bx-line-chart display-4 text-primary"></i>
                                    </div>
                                    <h5 class="font-size-16 mb-3">{{ __('التقارير المالية') }}</h5>
                                    <p class="text-muted mb-4">{{ __('تقارير شاملة للإيرادات والمصروفات وطرق الدفع') }}</p>
                                    <a href="{{ route('reports.financial') }}" class="btn btn-primary btn-sm">
                                        {{ __('عرض التقارير') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- تقارير الأداء -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border shadow-none">
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="bx bx-trending-up display-4 text-success"></i>
                                    </div>
                                    <h5 class="font-size-16 mb-3">{{ __('تقارير الأداء') }}</h5>
                                    <p class="text-muted mb-4">{{ __('أداء الأطباء والأقسام ومؤشرات الجودة') }}</p>
                                    <a href="{{ route('reports.performance') }}" class="btn btn-success btn-sm">
                                        {{ __('عرض التقارير') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- إحصائيات المرضى -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border shadow-none">
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="bx bx-group display-4 text-info"></i>
                                    </div>
                                    <h5 class="font-size-16 mb-3">{{ __('إحصائيات المرضى') }}</h5>
                                    <p class="text-muted mb-4">{{ __('توزيع المرضى والأمراض الشائعة') }}</p>
                                    <a href="{{ route('reports.patient-statistics') }}" class="btn btn-info btn-sm">
                                        {{ __('عرض التقارير') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- تقارير التأمين -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border shadow-none">
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="bx bx-shield display-4 text-warning"></i>
                                    </div>
                                    <h5 class="font-size-16 mb-3">{{ __('تقارير التأمين') }}</h5>
                                    <p class="text-muted mb-4">{{ __('مطالبات التأمين وشركات التأمين') }}</p>
                                    <a href="{{ route('reports.insurance') }}" class="btn btn-warning btn-sm">
                                        {{ __('عرض التقارير') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- تقارير المخزون -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border shadow-none">
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="bx bx-package display-4 text-danger"></i>
                                    </div>
                                    <h5 class="font-size-16 mb-3">{{ __('تقارير المخزون') }}</h5>
                                    <p class="text-muted mb-4">{{ __('الأدوية والمستلزمات الطبية') }}</p>
                                    <a href="{{ route('reports.inventory') }}" class="btn btn-danger btn-sm">
                                        {{ __('عرض التقارير') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- التقرير التنفيذي -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border shadow-none">
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="bx bx-briefcase display-4 text-dark"></i>
                                    </div>
                                    <h5 class="font-size-16 mb-3">{{ __('التقرير التنفيذي') }}</h5>
                                    <p class="text-muted mb-4">{{ __('ملخص شامل للإدارة العليا') }}</p>
                                    <a href="{{ route('reports.executive-summary') }}" class="btn btn-dark btn-sm">
                                        {{ __('عرض التقرير') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- التقارير السريعة -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('التقارير السريعة') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('reports.financial', ['period' => 'daily', 'export' => 'pdf']) }}" 
                               class="btn btn-outline-primary btn-block mb-2">
                                <i class="bx bx-download me-1"></i>
                                {{ __('التقرير المالي اليومي') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.performance', ['period' => 'weekly', 'export' => 'pdf']) }}" 
                               class="btn btn-outline-success btn-block mb-2">
                                <i class="bx bx-download me-1"></i>
                                {{ __('تقرير الأداء الأسبوعي') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.patient-statistics', ['period' => 'monthly', 'export' => 'pdf']) }}" 
                               class="btn btn-outline-info btn-block mb-2">
                                <i class="bx bx-download me-1"></i>
                                {{ __('إحصائيات المرضى الشهرية') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.executive-summary', ['period' => 'monthly', 'export' => 'pdf']) }}" 
                               class="btn btn-outline-dark btn-block mb-2">
                                <i class="bx bx-download me-1"></i>
                                {{ __('التقرير التنفيذي الشهري') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية السريعة -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('الإيرادات الأسبوعية') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="weeklyRevenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('توزيع طرق الدفع') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// رسم بياني للإيرادات الأسبوعية
const weeklyRevenueCtx = document.getElementById('weeklyRevenueChart').getContext('2d');
new Chart(weeklyRevenueCtx, {
    type: 'line',
    data: {
        labels: ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'],
        datasets: [{
            label: 'الإيرادات (ريال)',
            data: [12000, 15000, 18000, 14000, 16000, 13000, 11000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// رسم بياني لطرق الدفع
const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
new Chart(paymentMethodsCtx, {
    type: 'doughnut',
    data: {
        labels: ['نقدي', 'فيزا', 'ماستركارد', 'تأمين', 'تحويل بنكي'],
        datasets: [{
            data: [35, 25, 20, 15, 5],
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection