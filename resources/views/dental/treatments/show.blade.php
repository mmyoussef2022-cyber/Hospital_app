@extends('layouts.app')

@section('title', 'تفاصيل خطة العلاج - ' . $treatment->title)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-file-earmark-medical text-primary"></i>
                        تفاصيل خطة العلاج
                    </h1>
                    <p class="text-muted mb-0">{{ $treatment->title }}</p>
                </div>
                <div>
                    <a href="{{ route('dental.treatments.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-right"></i>
                        العودة للقائمة
                    </a>
                    @can('update', $treatment)
                        <a href="{{ route('dental.treatments.edit', $treatment) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i>
                            تعديل
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="row mb-4">
        <div class="col-12">
            @php
                $statusColors = [
                    'planned' => 'info',
                    'in_progress' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    'on_hold' => 'secondary'
                ];
                $statusNames = [
                    'planned' => 'مخطط',
                    'in_progress' => 'قيد التنفيذ',
                    'completed' => 'مكتمل',
                    'cancelled' => 'ملغي',
                    'on_hold' => 'معلق'
                ];
            @endphp
            <div class="alert alert-{{ $statusColors[$treatment->status] }}">
                <i class="bi bi-info-circle"></i>
                <strong>حالة العلاج:</strong> {{ $statusNames[$treatment->status] }}
                @if($treatment->status === 'in_progress')
                    - تقدم العلاج: {{ $treatment->progress_percentage }}%
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Treatment Information -->
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i>
                        المعلومات الأساسية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>المريض:</strong><br>
                            <a href="{{ route('patients.show', $treatment->patient) }}" class="text-decoration-none">
                                {{ $treatment->patient->name }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $treatment->patient->phone }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>الطبيب المعالج:</strong><br>
                            <a href="{{ route('doctors.show', $treatment->doctor) }}" class="text-decoration-none">
                                {{ $treatment->doctor->name }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $treatment->doctor->doctor?->specialization ?? 'طبيب أسنان' }}</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>نوع العلاج:</strong><br>
                            {{ $treatment->treatment_type_name }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>الأولوية:</strong><br>
                            @php
                                $priorityColors = [
                                    'low' => 'success',
                                    'normal' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$treatment->priority] }}">
                                {{ $treatment->priority_name }}
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <strong>وصف العلاج:</strong><br>
                            <p class="mb-0">{{ $treatment->description }}</p>
                        </div>
                    </div>
                    @if($treatment->notes)
                        <div class="row">
                            <div class="col-12">
                                <strong>ملاحظات:</strong><br>
                                <p class="mb-0">{{ $treatment->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Treatment Plan -->
            @if($treatment->treatment_plan)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-check"></i>
                            خطة العلاج التفصيلية
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="treatment-plan">
                            {!! nl2br(e($treatment->treatment_plan)) !!}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Affected Teeth -->
            @if($treatment->teeth_involved && count($treatment->teeth_involved) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-grid-3x3"></i>
                            الأسنان المتضررة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="teeth-display">
                            <div class="mb-3">
                                <h6>الأسنان العلوية</h6>
                                <div class="row">
                                    @for($i = 1; $i <= 16; $i++)
                                        <div class="col-1 mb-2">
                                            <div class="tooth-display {{ in_array($i, $treatment->teeth_involved) ? 'affected' : '' }}">
                                                {{ $i }}
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <div class="mb-3">
                                <h6>الأسنان السفلية</h6>
                                <div class="row">
                                    @for($i = 17; $i <= 32; $i++)
                                        <div class="col-1 mb-2">
                                            <div class="tooth-display {{ in_array($i, $treatment->teeth_involved) ? 'affected' : '' }}">
                                                {{ $i }}
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <strong>الأسنان المتضررة:</strong>
                            @foreach($treatment->teeth_involved as $tooth)
                                <span class="badge bg-danger me-1">{{ $tooth }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Treatment Sessions -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check"></i>
                        جلسات العلاج
                    </h5>
                    @can('create', App\Models\DentalSession::class)
                        <a href="{{ route('dental.sessions.create', ['treatment' => $treatment->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i>
                            إضافة جلسة
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($treatment->sessions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>رقم الجلسة</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات المنفذة</th>
                                        <th>التكلفة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($treatment->sessions->sortBy('session_date') as $session)
                                        <tr>
                                            <td>{{ $session->session_number }}</td>
                                            <td>{{ $session->session_date->format('Y-m-d') }}</td>
                                            <td>
                                                @php
                                                    $sessionStatusColors = [
                                                        'scheduled' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        'no_show' => 'warning'
                                                    ];
                                                    $sessionStatusNames = [
                                                        'scheduled' => 'مجدولة',
                                                        'completed' => 'مكتملة',
                                                        'cancelled' => 'ملغية',
                                                        'no_show' => 'لم يحضر'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $sessionStatusColors[$session->status] }}">
                                                    {{ $sessionStatusNames[$session->status] }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($session->procedures_performed, 50) }}</td>
                                            <td>{{ number_format($session->session_cost, 2) }} ر.س</td>
                                            <td>
                                                <a href="{{ route('dental.sessions.show', $session) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">لا توجد جلسات مسجلة بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Treatment Progress -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up"></i>
                        تقدم العلاج
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>التقدم الإجمالي</span>
                            <span>{{ $treatment->progress_percentage }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $treatment->progress_percentage }}%">
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h6 class="text-muted mb-1">الجلسات المكتملة</h6>
                                <h4 class="mb-0">{{ $treatment->completed_sessions_count }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted mb-1">إجمالي الجلسات</h6>
                            <h4 class="mb-0">{{ $treatment->total_sessions }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-range"></i>
                        الجدولة الزمنية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>تاريخ البداية:</strong><br>
                        <span class="text-muted">{{ $treatment->start_date->format('Y-m-d') }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>تاريخ الانتهاء المتوقع:</strong><br>
                        <span class="text-muted">{{ $treatment->expected_end_date->format('Y-m-d') }}</span>
                    </div>
                    @if($treatment->actual_end_date)
                        <div class="mb-3">
                            <strong>تاريخ الانتهاء الفعلي:</strong><br>
                            <span class="text-success">{{ $treatment->actual_end_date->format('Y-m-d') }}</span>
                        </div>
                    @endif
                    <div class="mb-3">
                        <strong>المدة المتوقعة:</strong><br>
                        <span class="text-muted">{{ $treatment->duration_in_days }} يوم</span>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-currency-dollar"></i>
                        المعلومات المالية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>التكلفة الإجمالية:</strong><br>
                        <h5 class="text-primary mb-0">{{ number_format($treatment->total_cost, 2) }} ر.س</h5>
                    </div>
                    <div class="mb-3">
                        <strong>المبلغ المدفوع:</strong><br>
                        <span class="text-success">{{ number_format($treatment->paid_amount, 2) }} ر.س</span>
                    </div>
                    <div class="mb-3">
                        <strong>المبلغ المتبقي:</strong><br>
                        <span class="text-danger">{{ number_format($treatment->remaining_amount, 2) }} ر.س</span>
                    </div>
                    <div class="mb-3">
                        <strong>نوع الدفع:</strong><br>
                        @php
                            $paymentTypes = [
                                'cash' => 'نقدي',
                                'installments' => 'أقساط',
                                'insurance' => 'تأمين'
                            ];
                        @endphp
                        <span class="badge bg-info">{{ $paymentTypes[$treatment->payment_type] }}</span>
                    </div>
                    @if($treatment->payment_type === 'installments' && $treatment->installments->count() > 0)
                        <div class="mb-3">
                            <strong>الأقساط:</strong><br>
                            <small class="text-muted">
                                {{ $treatment->installments->where('status', 'paid')->count() }} من {{ $treatment->installments->count() }} مدفوع
                            </small>
                            <div class="progress mt-1">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ ($treatment->installments->where('status', 'paid')->count() / $treatment->installments->count()) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning"></i>
                        إجراءات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('create', App\Models\DentalSession::class)
                            <a href="{{ route('dental.sessions.create', ['treatment' => $treatment->id]) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i>
                                إضافة جلسة جديدة
                            </a>
                        @endcan
                        
                        @if($treatment->payment_type === 'installments')
                            <a href="{{ route('dental.installments.index', ['treatment' => $treatment->id]) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-credit-card"></i>
                                إدارة الأقساط
                            </a>
                        @endif
                        
                        @can('update', $treatment)
                            <a href="{{ route('dental.treatments.edit', $treatment) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                                تعديل العلاج
                            </a>
                        @endcan
                        
                        <button type="button" class="btn btn-success btn-sm" onclick="printTreatment()">
                            <i class="bi bi-printer"></i>
                            طباعة التقرير
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printTreatment() {
    window.print();
}
</script>

<style>
.tooth-display {
    width: 30px;
    height: 30px;
    border: 2px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
    background-color: #f8f9fa;
    margin: 0 auto;
}

.tooth-display.affected {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

.treatment-plan {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 4px solid #0d6efd;
}

@media print {
    .btn, .card-header, nav, .sidebar {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container {
        max-width: 100% !important;
    }
}

@media (max-width: 768px) {
    .tooth-display {
        width: 25px;
        height: 25px;
        font-size: 0.7rem;
    }
}
</style>
@endsection