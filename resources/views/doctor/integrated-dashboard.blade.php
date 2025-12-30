@extends('layouts.app')

@section('title', 'لوحة تحكم الطبيب المتكاملة')

@section('content')
<div class="container-fluid" dir="rtl">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">مرحباً د. {{ $doctor->user->name ?? $doctor->name }}</h2>
                            <p class="mb-0">{{ $doctor->specialization ?? 'طبيب عام' }} - {{ now()->locale('ar')->translatedFormat('l، j F Y') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-light btn-sm" onclick="refreshDashboard()">
                                    <i class="fas fa-sync-alt"></i> تحديث
                                </button>
                                <button type="button" class="btn btn-light btn-sm" onclick="openEmergencyPanel()">
                                    <i class="fas fa-exclamation-triangle text-danger"></i> طوارئ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-right-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">مواعيد اليوم</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayStats['total_appointments'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-right-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">مؤكدة</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayStats['confirmed_appointments'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-right-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">مكتملة</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayStats['completed_appointments'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-right-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">روشتات</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayStats['prescriptions_written'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-prescription fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-right-secondary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">تحاليل</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayStats['lab_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flask fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-right-dark">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">أشعة</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayStats['radiology_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-x-ray fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Today's Appointments -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-day me-2"></i>
                        مواعيد اليوم ({{ $todayAppointments->count() }})
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-filter="all">الكل</button>
                        <button class="btn btn-outline-success" data-filter="confirmed">مؤكدة</button>
                        <button class="btn btn-outline-warning" data-filter="scheduled">مجدولة</button>
                        <button class="btn btn-outline-info" data-filter="completed">مكتملة</button>
                    </div>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @forelse($todayAppointments as $appointment)
                    <div class="appointment-item border-bottom py-3" data-status="{{ $appointment->status }}">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title bg-light text-primary rounded-circle">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $appointment->patient->name }}</h6>
                                        <small class="text-muted">{{ $appointment->patient->phone }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="h5 mb-0 text-primary">{{ $appointment->appointment_time->format('H:i') }}</div>
                                    <small class="text-muted">{{ $appointment->type ?? 'استشارة' }}</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    @if($appointment->patient->insurancePolicy)
                                        <span class="badge bg-success">مؤمن</span>
                                        <small class="d-block text-muted">{{ $appointment->patient->insurancePolicy->company->name ?? 'شركة التأمين' }}</small>
                                    @else
                                        <span class="badge bg-warning">نقدي</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : ($appointment->status === 'completed' ? 'info' : 'warning') }}">
                                        {{ $appointment->status === 'confirmed' ? 'مؤكد' : ($appointment->status === 'completed' ? 'مكتمل' : 'مجدول') }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="btn-group btn-group-sm w-100">
                                    @if($appointment->status === 'confirmed')
                                        <button class="btn btn-primary" onclick="startExamination({{ $appointment->id }})">
                                            <i class="fas fa-stethoscope"></i> بدء الكشف
                                        </button>
                                    @elseif($appointment->status === 'completed')
                                        <button class="btn btn-info" onclick="viewMedicalRecord({{ $appointment->id }})">
                                            <i class="fas fa-file-medical"></i> عرض التقرير
                                        </button>
                                    @else
                                        <button class="btn btn-success btn-sm" onclick="confirmAppointment({{ $appointment->id }})">
                                            <i class="fas fa-check"></i> تأكيد
                                        </button>
                                    @endif
                                    <button class="btn btn-outline-secondary" onclick="viewPatientDetails({{ $appointment->patient_id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @if($appointment->notes)
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted"><i class="fas fa-sticky-note me-1"></i>{{ $appointment->notes }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد مواعيد لهذا اليوم</h5>
                        <p class="text-muted">يمكنك الاستفادة من هذا الوقت لمراجعة النتائج المعلقة</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Pending Results -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-clock me-2"></i>
                        النتائج المعلقة ({{ count($pendingResults['lab_results']) + count($pendingResults['radiology_results']) }})
                    </h6>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if(count($pendingResults['lab_results']) > 0)
                        <h6 class="text-primary mb-2"><i class="fas fa-flask me-1"></i>نتائج التحاليل</h6>
                        @foreach($pendingResults['lab_results'] as $result)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="fw-bold">{{ $result->patient->name }}</div>
                                <small class="text-muted">{{ $result->completed_at->diffForHumans() }}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="reviewLabResult({{ $result->id }})">
                                مراجعة
                            </button>
                        </div>
                        @endforeach
                    @endif

                    @if(count($pendingResults['radiology_results']) > 0)
                        <h6 class="text-info mb-2 mt-3"><i class="fas fa-x-ray me-1"></i>نتائج الأشعة</h6>
                        @foreach($pendingResults['radiology_results'] as $result)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="fw-bold">{{ $result->patient->name }}</div>
                                <small class="text-muted">{{ $result->completed_at->diffForHumans() }}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-info" onclick="reviewRadiologyResult({{ $result->id }})">
                                مراجعة
                            </button>
                        </div>
                        @endforeach
                    @endif

                    @if(count($pendingResults['lab_results']) === 0 && count($pendingResults['radiology_results']) === 0)
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">لا توجد نتائج معلقة</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Medical Alerts -->
            @if(count($medicalAlerts) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        التنبيهات الطبية ({{ count($medicalAlerts) }})
                    </h6>
                </div>
                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                    @foreach($medicalAlerts as $alert)
                    <div class="alert alert-{{ $alert['priority'] === 'critical' ? 'danger' : ($alert['priority'] === 'high' ? 'warning' : 'info') }} alert-dismissible fade show">
                        <strong>{{ $alert['message'] }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-bolt me-2"></i>
                        إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="openNewPrescription()">
                            <i class="fas fa-prescription me-2"></i>روشتة جديدة
                        </button>
                        <button class="btn btn-outline-success" onclick="orderLabTests()">
                            <i class="fas fa-flask me-2"></i>طلب تحاليل
                        </button>
                        <button class="btn btn-outline-info" onclick="orderRadiology()">
                            <i class="fas fa-x-ray me-2"></i>طلب أشعة
                        </button>
                        <button class="btn btn-outline-warning" onclick="openPatientSearch()">
                            <i class="fas fa-search me-2"></i>البحث عن مريض
                        </button>
                        <button class="btn btn-outline-secondary" onclick="viewMySchedule()">
                            <i class="fas fa-calendar me-2"></i>جدولي الأسبوعي
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('doctor.modals.patient-search')
@include('doctor.modals.examination')
@include('doctor.modals.prescription')
@include('doctor.modals.lab-order')
@include('doctor.modals.radiology-order')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تصفية المواعيد
    $('[data-filter]').click(function() {
        const filter = $(this).data('filter');
        $('[data-filter]').removeClass('active');
        $(this).addClass('active');
        
        if (filter === 'all') {
            $('.appointment-item').show();
        } else {
            $('.appointment-item').hide();
            $(`.appointment-item[data-status="${filter}"]`).show();
        }
    });

    // تحديث تلقائي كل 30 ثانية
    setInterval(function() {
        refreshDashboard();
    }, 30000);
});

// بدء الكشف الطبي
function startExamination(appointmentId) {
    $.get(`/doctor/examination/start/${appointmentId}`, function(data) {
        $('#examinationModal .modal-body').html(data);
        $('#examinationModal').modal('show');
    });
}

// تأكيد الموعد
function confirmAppointment(appointmentId) {
    if (confirm('هل تريد تأكيد هذا الموعد؟')) {
        $.post(`/doctor/appointments/${appointmentId}/confirm`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                showAlert('success', 'تم تأكيد الموعد بنجاح');
                location.reload();
            }
        });
    }
}

// عرض تفاصيل المريض
function viewPatientDetails(patientId) {
    window.open(`/patients/${patientId}`, '_blank');
}

// عرض السجل الطبي
function viewMedicalRecord(appointmentId) {
    window.open(`/medical-records/appointment/${appointmentId}`, '_blank');
}

// مراجعة نتائج التحاليل
function reviewLabResult(resultId) {
    window.open(`/lab-results/${resultId}/review`, '_blank');
}

// مراجعة نتائج الأشعة
function reviewRadiologyResult(resultId) {
    window.open(`/radiology-results/${resultId}/review`, '_blank');
}

// تحديث لوحة التحكم
function refreshDashboard() {
    location.reload();
}

// فتح لوحة الطوارئ
function openEmergencyPanel() {
    alert('سيتم تطوير لوحة الطوارئ قريباً');
}

// إجراءات سريعة
function openNewPrescription() {
    window.open('/doctor/prescriptions/create', '_blank');
}

function orderLabTests() {
    window.open('/doctor/lab-orders/create', '_blank');
}

function orderRadiology() {
    window.open('/doctor/radiology-orders/create', '_blank');
}

function openPatientSearch() {
    window.open('/patients/search', '_blank');
}

function viewMySchedule() {
    window.open('/doctor/schedule', '_blank');
}

// عرض التنبيهات
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush