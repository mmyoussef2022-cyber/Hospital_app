@extends('layouts.app')

@section('page-title', 'لوحة تحكم الطبيب - ' . $doctor->full_name)

@section('content')
<div class="container">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-facebook text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">مرحباً، {{ $doctor->full_name }}</h4>
                            <p class="mb-0">{{ $doctor->specialization ? \App\Models\Doctor::getSpecializations()[$doctor->specialization] : 'طبيب' }}</p>
                            <small class="opacity-75">{{ $doctor->user->department->name ?? 'غير محدد' }}</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <img src="{{ $doctor->profile_photo_url }}" 
                                 alt="{{ $doctor->user->name }}" 
                                 class="rounded-circle" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">مواعيد اليوم</h6>
                            <h3 class="mb-0">{{ $stats['today_appointments'] }}</h3>
                        </div>
                        <i class="bi bi-calendar-check-fill" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">المواعيد القادمة</h6>
                            <h3 class="mb-0">{{ $stats['upcoming_appointments'] }}</h3>
                        </div>
                        <i class="bi bi-calendar-plus-fill" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">الخدمات النشطة</h6>
                            <h3 class="mb-0">{{ $stats['total_services'] }}</h3>
                        </div>
                        <i class="bi bi-gear-fill" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">الشهادات المعتمدة</h6>
                            <h3 class="mb-0">{{ $stats['certificates'] }}</h3>
                        </div>
                        <i class="bi bi-award-fill" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Appointments -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-check text-facebook"></i>
                        مواعيد اليوم ({{ $todayAppointments->count() }})
                    </h6>
                    <span class="badge bg-facebook">{{ now()->format('Y-m-d') }}</span>
                </div>
                <div class="card-body">
                    @if($todayAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>الوقت</th>
                                        <th>المريض</th>
                                        <th>نوع الموعد</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayAppointments as $appointment)
                                        <tr>
                                            <td>
                                                <strong class="text-facebook">{{ $appointment->appointment_time }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $appointment->patient->user->name }}</div>
                                                    <small class="text-muted">{{ $appointment->patient->patient_number }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $appointment->appointment_type }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'scheduled' => 'warning',
                                                        'confirmed' => 'success',
                                                        'completed' => 'primary',
                                                        'cancelled' => 'danger',
                                                        'no_show' => 'secondary'
                                                    ];
                                                    $statusNames = [
                                                        'scheduled' => 'مجدول',
                                                        'confirmed' => 'مؤكد',
                                                        'completed' => 'مكتمل',
                                                        'cancelled' => 'ملغي',
                                                        'no_show' => 'لم يحضر'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                                    {{ $statusNames[$appointment->status] ?? $appointment->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                                       class="btn btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                                        <button type="button" 
                                                                class="btn btn-outline-success" 
                                                                onclick="updateAppointmentStatus({{ $appointment->id }}, 'completed')"
                                                                title="تم الانتهاء">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <h6 class="text-muted mt-2">لا توجد مواعيد اليوم</h6>
                            <p class="text-muted">استمتع بيوم هادئ!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Info -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-charge text-facebook"></i>
                        إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-outline-facebook">
                            <i class="bi bi-pencil"></i>
                            تعديل الملف الشخصي
                        </a>
                        <button type="button" 
                                class="btn btn-outline-{{ $doctor->is_available ? 'warning' : 'success' }}"
                                onclick="toggleAvailability({{ $doctor->id }})">
                            <i class="bi bi-{{ $doctor->is_available ? 'pause' : 'play' }}-circle"></i>
                            {{ $doctor->is_available ? 'جعل غير متاح' : 'جعل متاح' }}
                        </button>
                        <a href="{{ route('doctors.certificates.create', $doctor) }}" class="btn btn-outline-info">
                            <i class="bi bi-plus-circle"></i>
                            إضافة شهادة جديدة
                        </a>
                        <a href="{{ route('doctor-certificates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-award"></i>
                            إدارة الشهادات
                        </a>
                        <a href="{{ route('doctor-services.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-gear-wide-connected"></i>
                            إدارة الخدمات
                        </a>
                    </div>
                </div>
            </div>

            <!-- Doctor Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle text-facebook"></i>
                        حالة الطبيب
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>الحالة النشطة:</span>
                            <span class="badge bg-{{ $doctor->is_active ? 'success' : 'secondary' }} fs-6">
                                {{ $doctor->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>الإتاحة:</span>
                            <span class="badge bg-{{ $doctor->is_available ? 'primary' : 'warning' }} fs-6">
                                {{ $doctor->is_available ? 'متاح' : 'غير متاح' }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>العمل اليوم:</span>
                            <span class="badge bg-{{ $doctor->isWorkingToday() ? 'success' : 'secondary' }} fs-6">
                                {{ $doctor->isWorkingToday() ? 'يوم عمل' : 'إجازة' }}
                            </span>
                        </div>
                    </div>
                    @if($doctor->isWorkingToday())
                        @php $todayHours = $doctor->getTodayWorkingHours(); @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>ساعات العمل:</span>
                                <small class="text-muted">
                                    {{ $todayHours['start'] }} - {{ $todayHours['end'] }}
                                </small>
                            </div>
                        </div>
                    @endif
                    @if($doctor->rating > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>التقييم:</span>
                                <div class="text-warning">
                                    {{ $doctor->rating_display }}
                                    <small class="text-muted">({{ $doctor->total_reviews }})</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Working Hours Today -->
            @if($doctor->isWorkingToday())
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-clock text-facebook"></i>
                            ساعات العمل اليوم
                        </h6>
                    </div>
                    <div class="card-body">
                        @php 
                            $todayHours = $doctor->getTodayWorkingHours();
                            $now = now();
                            $startTime = \Carbon\Carbon::parse($todayHours['start']);
                            $endTime = \Carbon\Carbon::parse($todayHours['end']);
                            $currentTime = \Carbon\Carbon::parse($now->format('H:i'));
                        @endphp
                        
                        <div class="text-center mb-3">
                            <div class="fs-4 text-facebook">
                                {{ $todayHours['start'] }} - {{ $todayHours['end'] }}
                            </div>
                        </div>
                        
                        <div class="progress mb-2" style="height: 10px;">
                            @php
                                $totalMinutes = $startTime->diffInMinutes($endTime);
                                $elapsedMinutes = $currentTime->between($startTime, $endTime) 
                                    ? $startTime->diffInMinutes($currentTime) 
                                    : ($currentTime->lt($startTime) ? 0 : $totalMinutes);
                                $percentage = $totalMinutes > 0 ? ($elapsedMinutes / $totalMinutes) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-facebook" 
                                 style="width: {{ min(100, $percentage) }}%"></div>
                        </div>
                        
                        <div class="text-center">
                            @if($currentTime->lt($startTime))
                                <small class="text-muted">
                                    يبدأ العمل خلال {{ $currentTime->diffForHumans($startTime, true) }}
                                </small>
                            @elseif($currentTime->between($startTime, $endTime))
                                <small class="text-success">
                                    <i class="bi bi-circle-fill"></i>
                                    في وقت العمل الآن
                                </small>
                            @else
                                <small class="text-muted">
                                    انتهى وقت العمل
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Appointments -->
    @if($upcomingAppointments->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-plus text-facebook"></i>
                            المواعيد القادمة ({{ $upcomingAppointments->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>الوقت</th>
                                        <th>المريض</th>
                                        <th>نوع الموعد</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingAppointments as $appointment)
                                        <tr>
                                            <td>
                                                <strong>{{ $appointment->appointment_date->format('Y-m-d') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $appointment->appointment_date->format('l') }}</small>
                                            </td>
                                            <td>
                                                <strong class="text-facebook">{{ $appointment->appointment_time }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $appointment->patient->user->name }}</div>
                                                    <small class="text-muted">{{ $appointment->patient->patient_number }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $appointment->appointment_type }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                                    {{ $statusNames[$appointment->status] ?? $appointment->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                                       class="btn btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($appointment->status == 'scheduled')
                                                        <button type="button" 
                                                                class="btn btn-outline-success" 
                                                                onclick="updateAppointmentStatus({{ $appointment->id }}, 'confirmed')"
                                                                title="تأكيد">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function toggleAvailability(doctorId) {
    fetch(`/doctors/${doctorId}/toggle-availability`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تحديث حالة الطبيب');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث حالة الطبيب');
    });
}

function updateAppointmentStatus(appointmentId, status) {
    if (!confirm('هل أنت متأكد من تحديث حالة الموعد؟')) {
        return;
    }
    
    fetch(`/appointments/${appointmentId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تحديث حالة الموعد');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث حالة الموعد');
    });
}

// Auto-refresh every 5 minutes to keep appointments updated
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush
@endsection