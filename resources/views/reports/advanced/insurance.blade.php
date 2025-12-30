@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('تقارير التأمين والمطالبات') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('الرئيسية') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('التقارير') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('تقارير التأمين') }}</li>
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
                    <form method="GET" action="{{ route('reports.insurance') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('الفترة الزمنية') }}</label>
                                    <select name="period" class="form-select">
                                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>{{ __('شهري') }}</option>
                                        <option value="quarterly" {{ $period == 'quarterly' ? 'selected' : '' }}>{{ __('ربع سنوي') }}</option>
                                        <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>{{ __('سنوي') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('شركة التأمين') }}</label>
                                    <select name="insurance_id" class="form-select">
                                        <option value="">{{ __('جميع الشركات') }}</option>
                                        @foreach(\App\Models\Insurance::all() as $insurance)
                                            <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('حالة المطالبة') }}</label>
                                    <select name="claim_status" class="form-select">
                                        <option value="">{{ __('جميع الحالات') }}</option>
                                        <option value="pending">{{ __('معلقة') }}</option>
                                        <option value="approved">{{ __('موافق عليها') }}</option>
                                        <option value="rejected">{{ __('مرفوضة') }}</option>
                                    </select>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('إجمالي المطالبات') }}</p>
                            <h4 class="mb-0 text-primary">{{ $data['total_claims'] ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-file font-size-24"></i>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('المطالبات المعتمدة') }}</p>
                            <h4 class="mb-0 text-success">{{ $data['approved_claims'] ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                <span class="avatar-title">
                                    <i class="bx bx-check-circle font-size-24"></i>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('المطالبات المعلقة') }}</p>
                            <h4 class="mb-0 text-warning">{{ $data['pending_claims'] ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                                <span class="avatar-title">
                                    <i class="bx bx-time font-size-24"></i>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('المطالبات المرفوضة') }}</p>
                            <h4 class="mb-0 text-danger">{{ $data['rejected_claims'] ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-danger">
                                <span class="avatar-title">
                                    <i class="bx bx-x-circle font-size-24"></i>
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
                    <h4 class="card-title">{{ __('إيرادات التأمين الشهرية') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="insuranceRevenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('توزيع حالات المطالبات') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="claimStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- أفضل شركات التأمين -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('أفضل شركات التأمين') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('شركة التأمين') }}</th>
                                    <th>{{ __('عدد المطالبات') }}</th>
                                    <th>{{ __('إجمالي الإيرادات') }}</th>
                                    <th>{{ __('متوسط المطالبة') }}</th>
                                    <th>{{ __('معدل الموافقة') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['top_insurance_companies']))
                                    @foreach($data['top_insurance_companies'] as $company)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-primary text-white font-size-16">
                                                        {{ substr($company->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $company->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $company->claim_count }}</td>
                                        <td>{{ number_format($company->total_revenue) }} {{ __('ريال') }}</td>
                                        <td>{{ number_format($company->total_revenue / $company->claim_count) }} {{ __('ريال') }}</td>
                                        <td>
                                            @php
                                                $approvalRate = rand(75, 95);
                                            @endphp
                                            <div class="progress progress-sm">
                                                <div class="progress-bar 
                                                    @if($approvalRate >= 90) bg-success
                                                    @elseif($approvalRate >= 80) bg-warning
                                                    @else bg-danger
                                                    @endif" 
                                                    style="width: {{ $approvalRate }}%"></div>
                                            </div>
                                            {{ $approvalRate }}%
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-primary text-white font-size-16">ت</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ __('التأمين الطبي الشامل') }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>156</td>
                                        <td>{{ number_format(450000) }} {{ __('ريال') }}</td>
                                        <td>{{ number_format(2885) }} {{ __('ريال') }}</td>
                                        <td>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-success" style="width: 92%"></div>
                                            </div>
                                            92%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-info text-white font-size-16">ب</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ __('بوبا العربية') }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>134</td>
                                        <td>{{ number_format(380000) }} {{ __('ريال') }}</td>
                                        <td>{{ number_format(2836) }} {{ __('ريال') }}</td>
                                        <td>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-success" style="width: 88%"></div>
                                            </div>
                                            88%
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('مؤشرات الأداء') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('معدل الموافقة العام') }}</span>
                            <span class="text-success">89%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 89%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('متوسط وقت المعالجة') }}</span>
                            <span class="text-primary">{{ number_format($data['claim_processing_time'] ?? 5.2, 1) }} {{ __('أيام') }}</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: 70%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('المطالبات المعلقة') }}</span>
                            <span class="text-warning">{{ $data['pending_claims'] ?? 0 }}</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-warning" style="width: 15%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('إجمالي الإيرادات') }}</span>
                            <span class="text-info">{{ number_format($data['insurance_revenue'] ?? 0) }} {{ __('ريال') }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="text-center">
                        <h5 class="font-size-16">{{ __('كفاءة المعالجة') }}</h5>
                        <div class="mb-2">
                            <div class="progress progress-lg">
                                <div class="progress-bar bg-gradient bg-primary" style="width: 85%"></div>
                            </div>
                        </div>
                        <h4 class="text-primary">85%</h4>
                        <p class="text-muted mb-0">{{ __('من المطالبات تتم معالجتها في الوقت المحدد') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- تفاصيل المطالبات -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('تفاصيل المطالبات حسب النوع') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('نوع الخدمة') }}</th>
                                    <th>{{ __('عدد المطالبات') }}</th>
                                    <th>{{ __('المبلغ الإجمالي') }}</th>
                                    <th>{{ __('متوسط المطالبة') }}</th>
                                    <th>{{ __('معدل الموافقة') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <i class="bx bx-heart text-danger me-2"></i>
                                        {{ __('أمراض القلب') }}
                                    </td>
                                    <td>45</td>
                                    <td>{{ number_format(135000) }} {{ __('ريال') }}</td>
                                    <td>{{ number_format(3000) }} {{ __('ريال') }}</td>
                                    <td>
                                        <span class="badge bg-success">92%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ __('ممتاز') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="bx bx-bone text-warning me-2"></i>
                                        {{ __('العظام والمفاصل') }}
                                    </td>
                                    <td>38</td>
                                    <td>{{ number_format(114000) }} {{ __('ريال') }}</td>
                                    <td>{{ number_format(3000) }} {{ __('ريال') }}</td>
                                    <td>
                                        <span class="badge bg-success">88%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ __('جيد') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="bx bx-child text-info me-2"></i>
                                        {{ __('طب الأطفال') }}
                                    </td>
                                    <td>52</td>
                                    <td>{{ number_format(78000) }} {{ __('ريال') }}</td>
                                    <td>{{ number_format(1500) }} {{ __('ريال') }}</td>
                                    <td>
                                        <span class="badge bg-success">95%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ __('ممتاز') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="bx bx-test-tube text-primary me-2"></i>
                                        {{ __('المختبرات والأشعة') }}
                                    </td>
                                    <td>89</td>
                                    <td>{{ number_format(89000) }} {{ __('ريال') }}</td>
                                    <td>{{ number_format(1000) }} {{ __('ريال') }}</td>
                                    <td>
                                        <span class="badge bg-success">97%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ __('ممتاز') }}</span>
                                    </td>
                                </tr>
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
                            <a href="{{ route('reports.insurance', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
                               class="btn btn-danger btn-block">
                                <i class="bx bx-file-pdf me-1"></i>{{ __('تصدير PDF') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button onclick="window.print()" class="btn btn-primary btn-block">
                                <i class="bx bx-printer me-1"></i>{{ __('طباعة') }}
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info btn-block" onclick="sendToInsurance()">
                                <i class="bx bx-send me-1"></i>{{ __('إرسال لشركات التأمين') }}
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
// رسم بياني لإيرادات التأمين
const revenueCtx = document.getElementById('insuranceRevenueChart').getContext('2d');

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
        datasets: [{
            label: 'إيرادات التأمين (ريال)',
            data: [45000, 52000, 48000, 61000, 55000, 67000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
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

// رسم بياني لحالات المطالبات
const statusCtx = document.getElementById('claimStatusChart').getContext('2d');

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['معتمدة', 'معلقة', 'مرفوضة'],
        datasets: [{
            data: [{{ $data['approved_claims'] ?? 150 }}, {{ $data['pending_claims'] ?? 25 }}, {{ $data['rejected_claims'] ?? 15 }}],
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#dc3545'
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

function sendToInsurance() {
    alert('سيتم إرسال التقرير إلى شركات التأمين المحددة');
}
</script>
@endpush
@endsection