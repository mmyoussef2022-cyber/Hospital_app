@extends('layouts.app')

@section('page-title', 'تفاصيل الموعد')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check text-facebook"></i>
                        تفاصيل الموعد
                    </h5>
                    <div class="btn-group">
                        @if($appointment->canBeRescheduled())
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                                تعديل
                            </a>
                        @endif
                        @if($appointment->canBeCancelled())
                            <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" 
                                  class="d-inline" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الموعد؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle"></i>
                                    إلغاء
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Appointment Status -->
                        <div class="col-12 mb-4">
                            <div class="alert alert-{{ $appointment->status_color }} d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    <strong>حالة الموعد:</strong> {{ $appointment->status_display }}
                                    @if($appointment->isPast() && $appointment->status === 'scheduled')
                                        <span class="badge bg-warning ms-2">متأخر</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Patient Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-person-fill text-primary"></i>
                                        معلومات المريض
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>الاسم:</strong></div>
                                        <div class="col-sm-8">{{ $appointment->patient->name }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>الهوية:</strong></div>
                                        <div class="col-sm-8">{{ $appointment->patient->national_id }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>الهاتف:</strong></div>
                                        <div class="col-sm-8">{{ $appointment->patient->phone ?? 'غير محدد' }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>العمر:</strong></div>
                                        <div class="col-sm-8">
                                            @if($appointment->patient->date_of_birth)
                                                {{ $appointment->patient->date_of_birth->age }} سنة
                                            @else
                                                غير محدد
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('patients.show', $appointment->patient) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                            عرض ملف المريض
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Doctor Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-person-badge text-success"></i>
                                        معلومات الطبيب
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>الاسم:</strong></div>
                                        <div class="col-sm-8">{{ $appointment->doctor->name }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>التخصص:</strong></div>
                                        <div class="col-sm-8">{{ $appointment->doctor->job_title ?? 'طبيب' }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>القسم:</strong></div>
                                        <div class="col-sm-8">{{ $appointment->doctor->department->name ?? 'غير محدد' }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>الهاتف:</strong></div>
                                        <div class="col-sm-8">{{ $appointment->doctor->phone ?? 'غير محدد' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Appointment Details -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-calendar-event text-info"></i>
                                        تفاصيل الموعد
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row mb-2">
                                                <div class="col-sm-4"><strong>التاريخ:</strong></div>
                                                <div class="col-sm-8">
                                                    {{ $appointment->appointment_date->format('Y/m/d') }}
                                                    <span class="text-muted">({{ $appointment->appointment_date->translatedFormat('l') }})</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-4"><strong>الوقت:</strong></div>
                                                <div class="col-sm-8">{{ $appointment->appointment_time->format('H:i') }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-4"><strong>المدة:</strong></div>
                                                <div class="col-sm-8">{{ $appointment->duration }} دقيقة</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row mb-2">
                                                <div class="col-sm-4"><strong>النوع:</strong></div>
                                                <div class="col-sm-8">
                                                    <span class="badge bg-info">{{ $appointment->type_display }}</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-4"><strong>الحالة:</strong></div>
                                                <div class="col-sm-8">
                                                    <span class="badge bg-{{ $appointment->status_color }}">
                                                        {{ $appointment->status_display }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-4"><strong>وقت الانتهاء:</strong></div>
                                                <div class="col-sm-8">{{ $appointment->end_time->format('H:i') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($appointment->notes)
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="bi bi-sticky text-warning"></i>
                                            الملاحظات
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $appointment->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Status Update Actions -->
                        @if(in_array($appointment->status, ['scheduled', 'confirmed', 'in_progress']))
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="bi bi-arrow-repeat text-primary"></i>
                                            تحديث حالة الموعد
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="btn-group" role="group">
                                            @if($appointment->status === 'scheduled')
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="updateStatus('confirmed')">
                                                    <i class="bi bi-check-circle"></i>
                                                    تأكيد الموعد
                                                </button>
                                            @endif
                                            
                                            @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                                <button type="button" class="btn btn-warning btn-sm" 
                                                        onclick="updateStatus('in_progress')">
                                                    <i class="bi bi-play-circle"></i>
                                                    بدء الموعد
                                                </button>
                                            @endif
                                            
                                            @if($appointment->status === 'in_progress')
                                                <button type="button" class="btn btn-info btn-sm" 
                                                        onclick="updateStatus('completed')">
                                                    <i class="bi bi-check-square"></i>
                                                    إنهاء الموعد
                                                </button>
                                            @endif
                                            
                                            @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                                <button type="button" class="btn btn-secondary btn-sm" 
                                                        onclick="updateStatus('no_show')">
                                                    <i class="bi bi-person-x"></i>
                                                    لم يحضر
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Metadata -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-info-square text-muted"></i>
                                        معلومات إضافية
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <strong>تاريخ الإنشاء:</strong> {{ $appointment->created_at->format('Y/m/d H:i') }}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <strong>آخر تحديث:</strong> {{ $appointment->updated_at->format('Y/m/d H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                    @if($appointment->reminder_sent_at)
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <small class="text-muted">
                                                    <strong>تم إرسال التذكير:</strong> {{ $appointment->reminder_sent_at->format('Y/m/d H:i') }}
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-4">
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateStatus(newStatus) {
    if (!confirm('هل أنت متأكد من تحديث حالة الموعد؟')) {
        return;
    }

    fetch(`{{ route('appointments.update-status', $appointment) }}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show updated status
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
</script>
@endpush
@endsection