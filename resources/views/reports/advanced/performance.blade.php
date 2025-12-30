@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('تقارير الأداء والجودة') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('الرئيسية') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('التقارير') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('تقارير الأداء') }}</li>
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
                    <form method="GET" action="{{ route('reports.performance') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('الفترة الزمنية') }}</label>
                                    <select name="period" class="form-select">
                                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>{{ __('أسبوعي') }}</option>
                                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>{{ __('شهري') }}</option>
                                        <option value="quarterly" {{ $period == 'quarterly' ? 'selected' : '' }}>{{ __('ربع سنوي') }}</option>
                                        <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>{{ __('سنوي') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('الطبيب') }}</label>
                                    <select name="doctor_id" class="form-select">
                                        <option value="">{{ __('جميع الأطباء') }}</option>
                                        @foreach(\App\Models\User::where('role', 'doctor')->get() as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('القسم') }}</label>
                                    <select name="department_id" class="form-select">
                                        <option value="">{{ __('جميع الأقسام') }}</option>
                                        <option value="cardiology">{{ __('القلب') }}</option>
                                        <option value="orthopedics">{{ __('العظام') }}</option>
                                        <option value="pediatrics">{{ __('الأطفال') }}</option>
                                        <option value="general">{{ __('الباطنة') }}</option>
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

    <!-- مؤشرات الأداء الرئيسية -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('إجمالي المواعيد') }}</p>
                            <h4 class="mb-0 text-primary">{{ $data['total_appointments'] ?? 0 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('المواعيد المكتملة') }}</p>
                            <h4 class="mb-0 text-success">{{ $data['completed_appointments'] ?? 0 }}</h4>
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
                            <p class="text-truncate font-size-14 mb-2">{{ __('المواعيد الملغية') }}</p>
                            <h4 class="mb-0 text-danger">{{ $data['cancelled_appointments'] ?? 0 }}</h4>
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

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('متوسط وقت الانتظار') }}</p>
                            <h4 class="mb-0 text-warning">{{ number_format($data['average_wait_time'] ?? 0) }} {{ __('دقيقة') }}</h4>
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
    </div>

    <!-- أداء الأطباء -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('أداء الأطباء') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('اسم الطبيب') }}</th>
                                    <th>{{ __('إجمالي المواعيد') }}</th>
                                    <th>{{ __('المواعيد المكتملة') }}</th>
                                    <th>{{ __('معدل الإنجاز') }}</th>
                                    <th>{{ __('متوسط مدة الموعد') }}</th>
                                    <th>{{ __('التقييم') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['doctor_performance']))
                                    @foreach($data['doctor_performance'] as $doctor)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-3">
                                                    <span class="avatar-title rounded-circle bg-primary text-white font-size-16">
                                                        {{ substr($doctor->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $doctor->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $doctor->total_appointments }}</td>
                                        <td>{{ $doctor->completed }}</td>
                                        <td>
                                            @php
                                                $completionRate = $doctor->total_appointments > 0 ? ($doctor->completed / $doctor->total_appointments) * 100 : 0;
                                            @endphp
                                            <div class="progress progress-sm">
                                                <div class="progress-bar 
                                                    @if($completionRate >= 90) bg-success
                                                    @elseif($completionRate >= 70) bg-warning
                                                    @else bg-danger
                                                    @endif" 
                                                    style="width: {{ $completionRate }}%"></div>
                                            </div>
                                            {{ number_format($completionRate, 1) }}%
                                        </td>
                                        <td>{{ number_format($doctor->avg_duration ?? 0) }} {{ __('دقيقة') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bx bx-star {{ $i <= 4 ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                                <span class="ms-2">4.0</span>
                                            </div>
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

    <!-- أداء الأقسام -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('أداء الأقسام') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="departmentPerformanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('مؤشرات الجودة') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('رضا المرضى') }}</span>
                            <span class="text-success">{{ $data['patient_satisfaction']['average_rating'] ?? 4.2 }}/5</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 84%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('الالتزام بالمواعيد') }}</span>
                            <span class="text-primary">92%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: 92%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('معدل إعادة الزيارة') }}</span>
                            <span class="text-warning">15%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-warning" style="width: 15%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('كفاءة الموارد') }}</span>
                            <span class="text-info">88%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: 88%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- تفصيل الأقسام -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('تفصيل أداء الأقسام') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('القسم') }}</th>
                                    <th>{{ __('إجمالي المواعيد') }}</th>
                                    <th>{{ __('المواعيد المكتملة') }}</th>
                                    <th>{{ __('معدل الإنجاز') }}</th>
                                    <th>{{ __('متوسط وقت الانتظار') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['department_performance']))
                                    @foreach($data['department_performance'] as $dept)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-hospital me-2 text-primary"></i>
                                                {{ $dept->department ?? __('غير محدد') }}
                                            </div>
                                        </td>
                                        <td>{{ $dept->total_appointments }}</td>
                                        <td>{{ $dept->completed }}</td>
                                        <td>
                                            @php
                                                $rate = $dept->total_appointments > 0 ? ($dept->completed / $dept->total_appointments) * 100 : 0;
                                            @endphp
                                            {{ number_format($rate, 1) }}%
                                        </td>
                                        <td>{{ rand(15, 35) }} {{ __('دقيقة') }}</td>
                                        <td>
                                            @if($rate >= 90)
                                                <span class="badge bg-success">{{ __('ممتاز') }}</span>
                                            @elseif($rate >= 70)
                                                <span class="badge bg-warning">{{ __('جيد') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('يحتاج تحسين') }}</span>
                                            @endif
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
                            <a href="{{ route('reports.performance', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
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
                            <button class="btn btn-info btn-block" onclick="generateDetailedReport()">
                                <i class="bx bx-detail me-1"></i>{{ __('تقرير مفصل') }}
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
// رسم بياني لأداء الأقسام
const departmentCtx = document.getElementById('departmentPerformanceChart').getContext('2d');
const departmentData = @json($data['department_performance'] ?? []);

new Chart(departmentCtx, {
    type: 'bar',
    data: {
        labels: departmentData.map(dept => dept.department || 'غير محدد'),
        datasets: [{
            label: 'إجمالي المواعيد',
            data: departmentData.map(dept => dept.total_appointments),
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'المواعيد المكتملة',
            data: departmentData.map(dept => dept.completed),
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
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

function generateDetailedReport() {
    // يمكن إضافة وظيفة لتوليد تقرير مفصل
    alert('سيتم توليد التقرير المفصل قريباً');
}
</script>
@endpush
@endsection