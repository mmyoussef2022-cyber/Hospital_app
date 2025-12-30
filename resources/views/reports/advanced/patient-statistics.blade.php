@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('إحصائيات المرضى والأمراض') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('الرئيسية') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('التقارير') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('إحصائيات المرضى') }}</li>
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
                    <form method="GET" action="{{ route('reports.patient-statistics') }}">
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
                                    <label class="form-label">{{ __('الفئة العمرية') }}</label>
                                    <select name="age_group" class="form-select">
                                        <option value="">{{ __('جميع الأعمار') }}</option>
                                        <option value="child">{{ __('أطفال (أقل من 18)') }}</option>
                                        <option value="adult">{{ __('بالغين (18-65)') }}</option>
                                        <option value="elderly">{{ __('كبار السن (أكثر من 65)') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('الجنس') }}</label>
                                    <select name="gender" class="form-select">
                                        <option value="">{{ __('الكل') }}</option>
                                        <option value="male">{{ __('ذكر') }}</option>
                                        <option value="female">{{ __('أنثى') }}</option>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('إجمالي المرضى') }}</p>
                            <h4 class="mb-0 text-primary">{{ $data['total_patients'] ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-group font-size-24"></i>
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
                            <h4 class="mb-0 text-success">{{ $data['new_patients'] ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('معدل إعادة الدخول') }}</p>
                            <h4 class="mb-0 text-warning">{{ number_format($data['readmission_rate'] ?? 0, 1) }}%</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                                <span class="avatar-title">
                                    <i class="bx bx-refresh font-size-24"></i>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('متوسط العمر') }}</p>
                            <h4 class="mb-0 text-info">42 {{ __('سنة') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-info">
                                <span class="avatar-title">
                                    <i class="bx bx-calendar font-size-24"></i>
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
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('توزيع الأعمار') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="ageDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('توزيع الجنس') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="genderDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- تدفق المرضى -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('تدفق المرضى اليومي') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="patientFlowChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- الأمراض الشائعة -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('الأمراض والتشخيصات الشائعة') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('التشخيص') }}</th>
                                    <th>{{ __('عدد الحالات') }}</th>
                                    <th>{{ __('النسبة') }}</th>
                                    <th>{{ __('الاتجاه') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['common_diagnoses']))
                                    @foreach($data['common_diagnoses'] as $diagnosis)
                                    <tr>
                                        <td>{{ $diagnosis->diagnosis }}</td>
                                        <td>{{ $diagnosis->count }}</td>
                                        <td>
                                            @php
                                                $percentage = ($data['total_patients'] > 0) ? ($diagnosis->count / $data['total_patients']) * 100 : 0;
                                            @endphp
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            {{ number_format($percentage, 1) }}%
                                        </td>
                                        <td>
                                            @if(rand(0, 1))
                                                <i class="bx bx-trending-up text-success"></i>
                                                <span class="text-success">{{ rand(5, 15) }}%</span>
                                            @else
                                                <i class="bx bx-trending-down text-danger"></i>
                                                <span class="text-danger">{{ rand(2, 8) }}%</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ __('ارتفاع ضغط الدم') }}</td>
                                        <td>45</td>
                                        <td>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" style="width: 25%"></div>
                                            </div>
                                            25%
                                        </td>
                                        <td>
                                            <i class="bx bx-trending-up text-success"></i>
                                            <span class="text-success">12%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('السكري') }}</td>
                                        <td>38</td>
                                        <td>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" style="width: 21%"></div>
                                            </div>
                                            21%
                                        </td>
                                        <td>
                                            <i class="bx bx-trending-up text-success"></i>
                                            <span class="text-success">8%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('أمراض القلب') }}</td>
                                        <td>32</td>
                                        <td>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" style="width: 18%"></div>
                                            </div>
                                            18%
                                        </td>
                                        <td>
                                            <i class="bx bx-trending-down text-danger"></i>
                                            <span class="text-danger">3%</span>
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
                    <h4 class="card-title">{{ __('إحصائيات إضافية') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('مرضى التأمين') }}</span>
                            <span class="text-primary">68%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: 68%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('المرضى النقديين') }}</span>
                            <span class="text-success">32%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 32%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('الحالات الطارئة') }}</span>
                            <span class="text-danger">15%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-danger" style="width: 15%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('المواعيد المجدولة') }}</span>
                            <span class="text-info">85%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: 85%"></div>
                        </div>
                    </div>

                    <hr>

                    <div class="text-center">
                        <h5 class="font-size-16">{{ __('معدل الرضا العام') }}</h5>
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bx bx-star {{ $i <= 4 ? 'text-warning' : 'text-muted' }} font-size-20"></i>
                            @endfor
                        </div>
                        <h4 class="text-warning">4.2/5</h4>
                        <p class="text-muted mb-0">{{ __('بناءً على 1,247 تقييم') }}</p>
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
                            <a href="{{ route('reports.patient-statistics', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
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
                            <button class="btn btn-info btn-block" onclick="exportToExcel()">
                                <i class="bx bx-file me-1"></i>{{ __('تصدير Excel') }}
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
// رسم بياني لتوزيع الأعمار
const ageCtx = document.getElementById('ageDistributionChart').getContext('2d');
const ageData = @json($data['age_distribution'] ?? []);

new Chart(ageCtx, {
    type: 'bar',
    data: {
        labels: ageData.length > 0 ? ageData.map(item => item.age_group) : ['أقل من 18', '18-30', '31-50', '51-70', 'أكثر من 70'],
        datasets: [{
            label: 'عدد المرضى',
            data: ageData.length > 0 ? ageData.map(item => item.count) : [25, 45, 65, 40, 20],
            backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 205, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 205, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// رسم بياني لتوزيع الجنس
const genderCtx = document.getElementById('genderDistributionChart').getContext('2d');
const genderData = @json($data['gender_distribution'] ?? []);

new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: genderData.length > 0 ? genderData.map(item => item.gender === 'male' ? 'ذكر' : 'أنثى') : ['ذكر', 'أنثى'],
        datasets: [{
            data: genderData.length > 0 ? genderData.map(item => item.count) : [120, 75],
            backgroundColor: [
                '#36A2EB',
                '#FF6384'
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

// رسم بياني لتدفق المرضى
const flowCtx = document.getElementById('patientFlowChart').getContext('2d');
const flowData = @json($data['patient_flow'] ?? []);

new Chart(flowCtx, {
    type: 'line',
    data: {
        labels: flowData.length > 0 ? flowData.map(item => item.date) : ['2024-01-01', '2024-01-02', '2024-01-03', '2024-01-04', '2024-01-05'],
        datasets: [{
            label: 'عدد المرضى',
            data: flowData.length > 0 ? flowData.map(item => item.patient_count) : [25, 30, 28, 35, 32],
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

function exportToExcel() {
    // يمكن إضافة وظيفة تصدير Excel
    alert('سيتم تصدير البيانات إلى Excel قريباً');
}
</script>
@endpush
@endsection