@extends('layouts.app')

@section('title', 'لوحة تحكم الفوترة المتقدمة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">لوحة تحكم الفوترة المتقدمة</h1>
            <p class="mb-0 text-muted">إدارة شاملة للفوترة النقدية والآجلة مع تتبع المدفوعات</p>
        </div>
        <div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> فاتورة جديدة
            </a>
            <a href="{{ route('advanced-billing.overdue-management') }}" class="btn btn-warning">
                <i class="fas fa-exclamation-triangle"></i> إدارة المتأخرات
            </a>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                إجمالي الإيرادات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_revenue'], 2) }} ريال
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                المبالغ المعلقة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending_revenue'], 2) }} ريال
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                المبالغ المتأخرة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['overdue_revenue'], 2) }} ريال
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                معدل التحصيل
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['collection_rate'], 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                الإيرادات النقدية
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['cash_revenue'], 2) }} ريال
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                الإيرادات الآجلة
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['credit_revenue'], 2) }} ريال
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                متوسط وقت الدفع
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['average_payment_time'] }} يوم
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cash Flow Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">التدفق النقدي - آخر 12 شهر</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="{{ route('advanced-billing.financial-reports', ['type' => 'cash_flow']) }}">تقرير مفصل</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="cashFlowChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Analysis -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">تحليل المتأخرات</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="overdueChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> 0-30 يوم
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> 31-60 يوم
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> +60 يوم
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Payment Trends -->
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إجراءات سريعة</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('advanced-billing.cash-invoices') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-money-bill-wave"></i><br>
                                <small>الفواتير النقدية</small>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('advanced-billing.credit-invoices') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-credit-card"></i><br>
                                <small>الفواتير الآجلة</small>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('advanced-billing.payment-tracking') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-chart-line"></i><br>
                                <small>تتبع المدفوعات</small>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('advanced-billing.financial-reports') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-file-alt"></i><br>
                                <small>التقارير المالية</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Trends -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">اتجاهات طرق الدفع - آخر 30 يوم</h6>
                </div>
                <div class="card-body">
                    @if($paymentTrends->count() > 0)
                        @foreach($paymentTrends as $trend)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    @switch($trend->payment_method)
                                        @case('cash')
                                            <i class="fas fa-money-bill-wave text-success"></i>
                                            @break
                                        @case('card')
                                            <i class="fas fa-credit-card text-primary"></i>
                                            @break
                                        @case('bank_transfer')
                                            <i class="fas fa-university text-info"></i>
                                            @break
                                        @default
                                            <i class="fas fa-payment text-secondary"></i>
                                    @endswitch
                                </div>
                                <div>
                                    <div class="font-weight-bold">
                                        @switch($trend->payment_method)
                                            @case('cash')
                                                نقدي
                                                @break
                                            @case('card')
                                                بطاقة ائتمان
                                                @break
                                            @case('bank_transfer')
                                                تحويل بنكي
                                                @break
                                            @default
                                                {{ $trend->payment_method }}
                                        @endswitch
                                    </div>
                                    <small class="text-muted">{{ $trend->count }} معاملة</small>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-weight-bold">{{ number_format($trend->total, 0) }} ريال</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>لا توجد بيانات للعرض</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Cash Flow Chart
const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
const cashFlowChart = new Chart(cashFlowCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($cashFlowData->pluck('month_name')) !!},
        datasets: [{
            label: 'الفواتير المصدرة',
            data: {!! json_encode($cashFlowData->pluck('invoiced')) !!},
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1
        }, {
            label: 'المبالغ المحصلة',
            data: {!! json_encode($cashFlowData->pluck('collected')) !!},
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
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' ريال';
                    }
                }
            }
        }
    }
});

// Overdue Analysis Chart
const overdueCtx = document.getElementById('overdueChart').getContext('2d');
const overdueChart = new Chart(overdueCtx, {
    type: 'doughnut',
    data: {
        labels: ['0-30 يوم', '31-60 يوم', '61-90 يوم', '+90 يوم'],
        datasets: [{
            data: [
                {{ $overdueAnalysis['0_30_days'] }},
                {{ $overdueAnalysis['31_60_days'] }},
                {{ $overdueAnalysis['61_90_days'] }},
                {{ $overdueAnalysis['over_90_days'] }}
            ],
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#fd7e14',
                '#dc3545'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
@endpush
@endsection