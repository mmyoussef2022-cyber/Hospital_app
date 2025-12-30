@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('التقارير المالية الشاملة') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('الرئيسية') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('التقارير') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('التقارير المالية') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- فلاتر التقرير -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('فلاتر التقرير') }}</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.financial') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('الفترة الزمنية') }}</label>
                                    <select name="period" class="form-select">
                                        <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>{{ __('يومي') }}</option>
                                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>{{ __('أسبوعي') }}</option>
                                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>{{ __('شهري') }}</option>
                                        <option value="quarterly" {{ $period == 'quarterly' ? 'selected' : '' }}>{{ __('ربع سنوي') }}</option>
                                        <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>{{ __('سنوي') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('من تاريخ') }}</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('إلى تاريخ') }}</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-search me-1"></i>{{ __('تطبيق الفلتر') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- الإحصائيات الرئيسية -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('إجمالي الإيرادات') }}</p>
                            <h4 class="mb-0 text-success">{{ number_format($data['total_revenue'] ?? 0) }} {{ __('ريال') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('المدفوعات النقدية') }}</p>
                            <h4 class="mb-0 text-primary">{{ number_format($data['cash_payments'] ?? 0) }} {{ __('ريال') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-wallet font-size-24"></i>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('مدفوعات البطاقات') }}</p>
                            <h4 class="mb-0 text-info">{{ number_format($data['card_payments'] ?? 0) }} {{ __('ريال') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-info">
                                <span class="avatar-title">
                                    <i class="bx bx-credit-card font-size-24"></i>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('مدفوعات التأمين') }}</p>
                            <h4 class="mb-0 text-warning">{{ number_format($data['insurance_payments'] ?? 0) }} {{ __('ريال') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                                <span class="avatar-title">
                                    <i class="bx bx-shield font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('الإيرادات اليومية') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="dailyRevenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
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

    <!-- تفصيل طرق الدفع -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('تفصيل طرق الدفع') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('طريقة الدفع') }}</th>
                                    <th>{{ __('عدد المعاملات') }}</th>
                                    <th>{{ __('المبلغ الإجمالي') }}</th>
                                    <th>{{ __('النسبة') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['payment_methods']))
                                    @foreach($data['payment_methods'] as $method)
                                    <tr>
                                        <td>
                                            @switch($method->payment_method)
                                                @case('cash')
                                                    <i class="bx bx-wallet text-primary me-1"></i>{{ __('نقدي') }}
                                                    @break
                                                @case('visa')
                                                    <i class="bx bx-credit-card text-info me-1"></i>{{ __('فيزا') }}
                                                    @break
                                                @case('mastercard')
                                                    <i class="bx bx-credit-card text-warning me-1"></i>{{ __('ماستركارد') }}
                                                    @break
                                                @case('insurance')
                                                    <i class="bx bx-shield text-success me-1"></i>{{ __('تأمين') }}
                                                    @break
                                                @default
                                                    {{ $method->payment_method }}
                                            @endswitch
                                        </td>
                                        <td>{{ $method->count }}</td>
                                        <td>{{ number_format($method->total) }} {{ __('ريال') }}</td>
                                        <td>
                                            @php
                                                $percentage = ($data['total_revenue'] > 0) ? ($method->total / $data['total_revenue']) * 100 : 0;
                                            @endphp
                                            {{ number_format($percentage, 1) }}%
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('إيرادات الأقسام') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('القسم') }}</th>
                                    <th>{{ __('الإيرادات') }}</th>
                                    <th>{{ __('النسبة') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['department_revenue']))
                                    @foreach($data['department_revenue'] as $dept)
                                    <tr>
                                        <td>{{ $dept->department ?? __('غير محدد') }}</td>
                                        <td>{{ number_format($dept->total) }} {{ __('ريال') }}</td>
                                        <td>
                                            @php
                                                $percentage = ($data['total_revenue'] > 0) ? ($dept->total / $data['total_revenue']) * 100 : 0;
                                            @endphp
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            {{ number_format($percentage, 1) }}%
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أزرار التصدير -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('تصدير التقرير') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('reports.financial', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
                               class="btn btn-danger btn-block">
                                <i class="bx bx-file-pdf me-1"></i>{{ __('تصدير PDF') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('reports.financial', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                               class="btn btn-success btn-block">
                                <i class="bx bx-file me-1"></i>{{ __('تصدير Excel') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button onclick="window.print()" class="btn btn-primary btn-block">
                                <i class="bx bx-printer me-1"></i>{{ __('طباعة') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// رسم بياني للإيرادات اليومية
const dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
const dailyData = @json($data['daily_breakdown'] ?? []);

new Chart(dailyRevenueCtx, {
    type: 'bar',
    data: {
        labels: dailyData.map(item => item.date),
        datasets: [{
            label: 'الإيرادات (ريال)',
            data: dailyData.map(item => item.total),
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
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
const paymentMethods = @json($data['payment_methods'] ?? []);

new Chart(paymentMethodsCtx, {
    type: 'doughnut',
    data: {
        labels: paymentMethods.map(method => {
            switch(method.payment_method) {
                case 'cash': return 'نقدي';
                case 'visa': return 'فيزا';
                case 'mastercard': return 'ماستركارد';
                case 'insurance': return 'تأمين';
                default: return method.payment_method;
            }
        }),
        datasets: [{
            data: paymentMethods.map(method => method.total),
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