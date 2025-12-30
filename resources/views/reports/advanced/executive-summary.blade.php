@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">{{ __('التقرير التنفيذي الشامل') }}</h1>
                    <p class="text-muted">{{ __('ملخص شامل لأداء المستشفى - الفترة: ') . ucfirst($period) }}</p>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> {{ __('تصدير التقرير') }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('reports.executive-summary', array_merge(request()->all(), ['export' => 'pdf'])) }}">
                            <i class="fas fa-file-pdf text-danger"></i> {{ __('تصدير PDF') }}
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.executive-summary', array_merge(request()->all(), ['export' => 'excel'])) }}">
                            <i class="fas fa-file-excel text-success"></i> {{ __('تصدير Excel') }}
                        </a></li>
                    </ul>
                </div>
            </div>

            <!-- Period Selection -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.executive-summary') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('الفترة الزمنية') }}</label>
                            <select name="period" class="form-select" onchange="this.form.submit()">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>{{ __('يومي') }}</option>
                                <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>{{ __('أسبوعي') }}</option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>{{ __('شهري') }}</option>
                                <option value="quarterly" {{ $period == 'quarterly' ? 'selected' : '' }}>{{ __('ربع سنوي') }}</option>
                                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>{{ __('سنوي') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('تاريخ التحديث') }}</label>
                            <input type="text" class="form-control" value="{{ now()->format('Y-m-d H:i') }}" readonly>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Key Performance Indicators -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        {{ __('إجمالي الإيرادات') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($data['financial']['total_revenue'], 2) }} {{ __('ريال') }}
                                    </div>
                                    @if($data['financial']['revenue_growth'] > 0)
                                        <div class="text-success small">
                                            <i class="fas fa-arrow-up"></i> {{ number_format($data['financial']['revenue_growth'], 1) }}%
                                        </div>
                                    @elseif($data['financial']['revenue_growth'] < 0)
                                        <div class="text-danger small">
                                            <i class="fas fa-arrow-down"></i> {{ number_format(abs($data['financial']['revenue_growth']), 1) }}%
                                        </div>
                                    @else
                                        <div class="text-muted small">{{ __('لا يوجد تغيير') }}</div>
                                    @endif
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                        {{ __('إجمالي المواعيد') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($data['operational']['total_appointments']) }}
                                    </div>
                                    <div class="text-success small">
                                        {{ __('مكتملة: ') }} {{ number_format($data['operational']['completed_appointments']) }}
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
                                        {{ __('رضا المرضى') }}
                                    </div>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                {{ $data['performance']['patient_satisfaction'] }}/5
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="progress progress-sm mr-2">
                                                <div class="progress-bar bg-info" role="progressbar"
                                                     style="width: {{ ($data['performance']['patient_satisfaction'] / 5) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-smile fa-2x text-gray-300"></i>
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
                                        {{ __('متوسط وقت الانتظار') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $data['performance']['average_wait_time'] }} {{ __('دقيقة') }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ __('الهدف: أقل من 30 دقيقة') }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('الملخص المالي') }}</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                    <a class="dropdown-item" href="{{ route('reports.financial') }}">{{ __('التقرير المفصل') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('إجمالي الإيرادات') }}</div>
                                        <div class="h5 text-success">{{ number_format($data['financial']['total_revenue'], 2) }} {{ __('ريال') }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('المستحقات المعلقة') }}</div>
                                        <div class="h6 text-warning">{{ number_format($data['financial']['outstanding_receivables'], 2) }} {{ __('ريال') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('مطالبات التأمين المعلقة') }}</div>
                                        <div class="h6 text-info">{{ number_format($data['financial']['insurance_pending'], 2) }} {{ __('ريال') }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('نمو الإيرادات') }}</div>
                                        <div class="h6 {{ $data['financial']['revenue_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($data['financial']['revenue_growth'], 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('الملخص التشغيلي') }}</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                    <a class="dropdown-item" href="{{ route('reports.performance') }}">{{ __('التقرير المفصل') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('إجمالي المواعيد') }}</div>
                                        <div class="h5 text-primary">{{ number_format($data['operational']['total_appointments']) }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('المواعيد المكتملة') }}</div>
                                        <div class="h6 text-success">{{ number_format($data['operational']['completed_appointments']) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('المواعيد المُلغاة') }}</div>
                                        <div class="h6 text-danger">{{ number_format($data['operational']['cancelled_appointments']) }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-muted">{{ __('معدل إشغال الأسرة') }}</div>
                                        <div class="h6 text-info">{{ $data['operational']['bed_occupancy_rate'] }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Summary -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('الملخص السريري') }}</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                    <a class="dropdown-item" href="{{ route('reports.patient-statistics') }}">{{ __('التقرير المفصل') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-vial fa-3x text-primary mb-2"></i>
                                        <div class="h4 text-primary">{{ number_format($data['clinical']['lab_tests_ordered']) }}</div>
                                        <div class="small text-muted">{{ __('تحاليل مطلوبة') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-x-ray fa-3x text-success mb-2"></i>
                                        <div class="h4 text-success">{{ number_format($data['clinical']['radiology_studies']) }}</div>
                                        <div class="small text-muted">{{ __('دراسات أشعة') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-prescription-bottle fa-3x text-info mb-2"></i>
                                        <div class="h4 text-info">{{ number_format($data['clinical']['prescriptions_written']) }}</div>
                                        <div class="small text-muted">{{ __('وصفات طبية') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-2"></i>
                                        <div class="h4 text-warning">{{ number_format($data['clinical']['critical_results']) }}</div>
                                        <div class="small text-muted">{{ __('نتائج حرجة') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('مؤشرات الأداء') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="small text-muted mb-1">{{ __('استغلال الموظفين') }}</div>
                                <div class="progress mb-1">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $data['performance']['staff_utilization'] }}%"></div>
                                </div>
                                <div class="small text-right">{{ $data['performance']['staff_utilization'] }}%</div>
                            </div>

                            <div class="mb-4">
                                <div class="small text-muted mb-1">{{ __('رضا المرضى') }}</div>
                                <div class="progress mb-1">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: {{ ($data['performance']['patient_satisfaction'] / 5) * 100 }}%"></div>
                                </div>
                                <div class="small text-right">{{ $data['performance']['patient_satisfaction'] }}/5</div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-muted mb-1">{{ __('معدل إعادة الدخول') }}</div>
                                <div class="progress mb-1">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: {{ $data['performance']['readmission_rate'] }}%"></div>
                                </div>
                                <div class="small text-right">{{ $data['performance']['readmission_rate'] }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('إجراءات سريعة') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('reports.financial') }}" class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-chart-line"></i> {{ __('التقارير المالية') }}
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('reports.performance') }}" class="btn btn-outline-success btn-block">
                                        <i class="fas fa-users"></i> {{ __('تقارير الأداء') }}
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('reports.patient-statistics') }}" class="btn btn-outline-info btn-block">
                                        <i class="fas fa-user-injured"></i> {{ __('إحصائيات المرضى') }}
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('reports.insurance') }}" class="btn btn-outline-warning btn-block">
                                        <i class="fas fa-shield-alt"></i> {{ __('تقارير التأمين') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <p class="text-muted mb-0">
                                {{ __('تم إنشاء هذا التقرير في: ') }} {{ now()->format('Y-m-d H:i:s') }}
                                <br>
                                {{ __('نظام إدارة المستشفى - التقرير التنفيذي الشامل') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.progress-sm {
    height: 0.5rem;
}
.btn-block {
    display: block;
    width: 100%;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
    
    // Add tooltips to performance indicators
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection