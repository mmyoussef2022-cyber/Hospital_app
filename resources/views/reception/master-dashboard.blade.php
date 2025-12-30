@extends('layouts.app')

@section('title', 'لوحة الاستقبال الشاملة')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-hospital-user text-primary me-2"></i>
                    لوحة الاستقبال الشاملة
                </h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newPatientModal">
                        <i class="fas fa-user-plus me-1"></i>
                        تسجيل مريض جديد
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#emergencyModal">
                        <i class="fas fa-ambulance me-1"></i>
                        حالة طوارئ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['total_visits'] ?? 0 }}</h4>
                            <p class="mb-0">إجمالي الزيارات اليوم</p>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['waiting_visits'] ?? 0 }}</h4>
                            <p class="mb-0">في الانتظار</p>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['emergency_visits'] ?? 0 }}</h4>
                            <p class="mb-0">حالات الطوارئ</p>
                        </div>
                        <i class="fas fa-ambulance fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['completed_visits'] ?? 0 }}</h4>
                            <p class="mb-0">مكتملة</p>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="row">
        <!-- Today's Appointments -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day text-primary me-2"></i>
                        مواعيد اليوم
                    </h5>
                    <span class="badge bg-primary">{{ count($todayAppointments ?? []) }}</span>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($todayAppointments ?? [] as $appointment)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <h6 class="mb-1">{{ $appointment->patient->name }}</h6>
                            <small class="text-muted">
                                د. {{ $appointment->doctor->name ?? 'غير محدد' }} - 
                                {{ $appointment->appointment_time->format('H:i') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $appointment->status_color }}">
                                {{ $appointment->status_display }}
                            </span>
                            @if($appointment->status === 'scheduled')
                            <button class="btn btn-sm btn-outline-primary ms-1" 
                                    onclick="checkInPatient({{ $appointment->id }})">
                                تسجيل وصول
                            </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>لا توجد مواعيد لهذا اليوم</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Queue Management -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol text-warning me-2"></i>
                        إدارة الطوابير
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshQueues()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($departmentQueues ?? [] as $department => $queue)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <h6 class="card-title">{{ $queue['name'] ?? $department }}</h6>
                                    <div class="d-flex justify-content-between">
                                        <small>في الانتظار: <strong>{{ $queue['waiting'] ?? 0 }}</strong></small>
                                        <small>المتوسط: <strong>{{ $queue['avg_wait'] ?? 0 }}د</strong></small>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar" style="width: {{ min(($queue['waiting'] ?? 0) * 10, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Alerts & Current Patients -->
    <div class="row">
        <!-- Emergency Alerts -->
        <div class="col-lg-4 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        تنبيهات الطوارئ
                    </h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @forelse($emergencyAlerts ?? [] as $alert)
                    <div class="alert alert-{{ $alert['level'] === 'critical' ? 'danger' : 'warning' }} alert-dismissible fade show">
                        <strong>{{ $alert['patient_name'] }}</strong><br>
                        <small>{{ $alert['message'] }}</small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-shield-alt fa-2x mb-2"></i>
                        <p>لا توجد تنبيهات طوارئ</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Current Patients in Hospital -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-injured text-info me-2"></i>
                        المرضى الحاليون في المستشفى
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-filter="all">الكل</button>
                        <button class="btn btn-outline-warning" data-filter="waiting">انتظار</button>
                        <button class="btn btn-outline-success" data-filter="consultation">كشف</button>
                        <button class="btn btn-outline-danger" data-filter="emergency">طوارئ</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>الطبيب</th>
                                    <th>القسم</th>
                                    <th>الحالة</th>
                                    <th>وقت الانتظار</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($currentPatients ?? [] as $visit)
                                <tr data-status="{{ $visit->visit_status }}" 
                                    class="{{ $visit->is_emergency ? 'table-danger' : '' }}">
                                    <td>
                                        <div>
                                            <strong>{{ $visit->patient->name }}</strong>
                                            @if($visit->is_emergency)
                                            <span class="badge bg-danger ms-1">طوارئ</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $visit->patient->patient_number }}</small>
                                    </td>
                                    <td>{{ $visit->doctor->name ?? 'غير محدد' }}</td>
                                    <td>{{ $visit->department ?? 'غير محدد' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $visit->visit_status === 'waiting' ? 'warning' : ($visit->visit_status === 'in_consultation' ? 'primary' : 'success') }}">
                                            {{ $visit->visit_status_display }}
                                        </span>
                                    </td>
                                    <td>{{ $visit->total_waiting_time }}د</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($visit->visit_status === 'waiting')
                                            <button class="btn btn-outline-primary" 
                                                    onclick="callPatient({{ $visit->id }})">
                                                استدعاء
                                            </button>
                                            @endif
                                            <button class="btn btn-outline-info" 
                                                    onclick="viewPatientDetails({{ $visit->patient_id }})">
                                                تفاصيل
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        لا توجد مرضى حالياً في المستشفى
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Patient Registration Modal -->
<div class="modal fade" id="newPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تسجيل مريض جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newPatientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم الكامل *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم الهوية *</label>
                            <input type="text" class="form-control" name="national_id" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم الجوال *</label>
                            <input type="text" class="form-control" name="mobile" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ الميلاد</label>
                            <input type="date" class="form-control" name="date_of_birth">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الجنس</label>
                            <select class="form-select" name="gender">
                                <option value="male">ذكر</option>
                                <option value="female">أنثى</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نوع المريض</label>
                            <select class="form-select" name="patient_type" id="patientType">
                                <option value="cash">نقدي</option>
                                <option value="insurance">تأمين</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3" id="insuranceSection" style="display: none;">
                            <label class="form-label">معلومات التأمين</label>
                            <textarea class="form-control" name="insurance_info" rows="2" 
                                      placeholder="اسم شركة التأمين، رقم البوليصة، نسبة التغطية"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">الشكوى الرئيسية</label>
                            <textarea class="form-control" name="chief_complaint" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تسجيل المريض</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Emergency Modal -->
<div class="modal fade" id="emergencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ambulance me-2"></i>
                    تسجيل حالة طوارئ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="emergencyForm">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        سيتم إرسال تنبيه فوري لجميع الأطباء والممرضين
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البحث عن المريض</label>
                        <input type="text" class="form-control" id="emergencyPatientSearch" 
                               placeholder="اسم المريض أو رقم الهوية">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مستوى الطوارئ *</label>
                        <select class="form-select" name="emergency_level" required>
                            <option value="level_1">مستوى 1 - حرج جداً (إنعاش فوري)</option>
                            <option value="level_2">مستوى 2 - حرج (خلال 15 دقيقة)</option>
                            <option value="level_3">مستوى 3 - عاجل (خلال 30 دقيقة)</option>
                            <option value="level_4">مستوى 4 - أقل عجلة (خلال ساعة)</option>
                            <option value="level_5">مستوى 5 - غير عاجل (خلال ساعتين)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وصف الحالة *</label>
                        <textarea class="form-control" name="emergency_description" rows="4" 
                                  placeholder="وصف مفصل للحالة الطارئة" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ambulance me-1"></i>
                        تسجيل طوارئ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Patient type change handler
    $('#patientType').change(function() {
        if ($(this).val() === 'insurance') {
            $('#insuranceSection').show();
        } else {
            $('#insuranceSection').hide();
        }
    });

    // New patient form submission
    $('#newPatientForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("reception.register-patient") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#newPatientModal').modal('hide');
                    showAlert('success', 'تم تسجيل المريض بنجاح');
                    location.reload();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                showAlert('error', 'حدث خطأ أثناء تسجيل المريض');
            }
        });
    });

    // Emergency form submission
    $('#emergencyForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("reception.register-emergency") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#emergencyModal').modal('hide');
                    showAlert('success', 'تم تسجيل حالة الطوارئ وإرسال التنبيهات');
                    location.reload();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                showAlert('error', 'حدث خطأ أثناء تسجيل حالة الطوارئ');
            }
        });
    });

    // Auto-refresh dashboard every 30 seconds
    setInterval(function() {
        refreshDashboard();
    }, 30000);
});

// Check in patient
function checkInPatient(appointmentId) {
    $.ajax({
        url: '{{ route("reception.check-in") }}',
        method: 'POST',
        data: {
            appointment_id: appointmentId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', 'تم تسجيل وصول المريض بنجاح');
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            showAlert('error', 'حدث خطأ أثناء تسجيل الوصول');
        }
    });
}

// Call patient
function callPatient(visitId) {
    $.ajax({
        url: '{{ route("reception.call-patient") }}',
        method: 'POST',
        data: {
            visit_id: visitId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', 'تم استدعاء المريض بنجاح');
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            showAlert('error', 'حدث خطأ أثناء استدعاء المريض');
        }
    });
}

// View patient details
function viewPatientDetails(patientId) {
    window.open('{{ route("patients.show", ":id") }}'.replace(':id', patientId), '_blank');
}

// Refresh queues
function refreshQueues() {
    location.reload();
}

// Refresh dashboard
function refreshDashboard() {
    // This would use AJAX to refresh specific sections without full page reload
    // Implementation depends on your real-time update strategy (WebSockets, Server-Sent Events, etc.)
}

// Show alert
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
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

// Filter patients
$('[data-filter]').click(function() {
    const filter = $(this).data('filter');
    $('[data-filter]').removeClass('active');
    $(this).addClass('active');
    
    if (filter === 'all') {
        $('tbody tr').show();
    } else {
        $('tbody tr').hide();
        $(`tbody tr[data-status="${filter}"]`).show();
    }
});
</script>
@endpush