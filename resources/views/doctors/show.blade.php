@extends('layouts.app')

@section('page-title', 'ملف الطبيب - ' . $doctor->full_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-facebook"></i>
                        ملف الطبيب
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i>
                            تعديل
                        </a>
                        <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Doctor Photo and Basic Info -->
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <img src="{{ $doctor->profile_photo_url }}" 
                                     alt="{{ $doctor->user->name }}" 
                                     class="img-fluid rounded-circle mb-3" 
                                     style="width: 200px; height: 200px; object-fit: cover;">
                                
                                <h4 class="text-facebook">{{ $doctor->full_name }}</h4>
                                <p class="text-muted mb-2">{{ $doctor->doctor_number }}</p>
                                
                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <span class="badge bg-{{ $doctor->is_active ? 'success' : 'secondary' }} fs-6">
                                        {{ $doctor->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                    <span class="badge bg-{{ $doctor->is_available ? 'primary' : 'warning' }} fs-6">
                                        {{ $doctor->is_available ? 'متاح' : 'غير متاح' }}
                                    </span>
                                </div>
                                
                                @if($doctor->rating > 0)
                                    <div class="mb-3">
                                        <div class="text-warning fs-5">
                                            {{ $doctor->rating_display }}
                                        </div>
                                        <small class="text-muted">({{ $doctor->total_reviews }} تقييم)</small>
                                    </div>
                                @endif
                                
                                <div class="d-grid gap-2">
                                    <button type="button" 
                                            class="btn btn-{{ $doctor->is_available ? 'outline-secondary' : 'outline-success' }}" 
                                            onclick="toggleAvailability({{ $doctor->id }})">
                                        <i class="bi bi-{{ $doctor->is_available ? 'pause' : 'play' }}-circle"></i>
                                        {{ $doctor->is_available ? 'جعل غير متاح' : 'جعل متاح' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Doctor Details -->
                        <div class="col-md-8">
                            <!-- Personal Information -->
                            <div class="mb-4">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-circle"></i>
                                    المعلومات الشخصية
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <strong>البريد الإلكتروني:</strong>
                                        <span class="text-muted">{{ $doctor->user->email }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>الرقم القومي:</strong>
                                        <span class="text-muted">{{ $doctor->national_id }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>الجنس:</strong>
                                        <span class="text-muted">{{ $doctor->user->gender == 'male' ? 'ذكر' : 'أنثى' }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>تاريخ الميلاد:</strong>
                                        <span class="text-muted">
                                            {{ $doctor->user->date_of_birth ? $doctor->user->date_of_birth->format('Y-m-d') : 'غير محدد' }}
                                        </span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>رقم الهاتف:</strong>
                                        <span class="text-muted">{{ $doctor->user->phone ?? 'غير محدد' }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>رقم الجوال:</strong>
                                        <span class="text-muted">{{ $doctor->user->mobile ?? 'غير محدد' }}</span>
                                    </div>
                                    @if($doctor->user->address)
                                        <div class="col-12 mb-2">
                                            <strong>العنوان:</strong>
                                            <span class="text-muted">{{ $doctor->user->address }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Professional Information -->
                            <div class="mb-4">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-award"></i>
                                    المعلومات المهنية
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <strong>القسم:</strong>
                                        <span class="text-muted">{{ $doctor->user->department->name ?? 'غير محدد' }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>المسمى الوظيفي:</strong>
                                        <span class="text-muted">{{ $doctor->user->job_title ?? 'طبيب' }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>رقم الترخيص:</strong>
                                        <span class="text-muted">{{ $doctor->license_number }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>التخصص:</strong>
                                        <span class="badge bg-info">
                                            {{ \App\Models\Doctor::getSpecializations()[$doctor->specialization] ?? $doctor->specialization }}
                                        </span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>الدرجة العلمية:</strong>
                                        <span class="text-muted">
                                            {{ \App\Models\Doctor::getDegrees()[$doctor->degree] ?? $doctor->degree }}
                                        </span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>الجامعة:</strong>
                                        <span class="text-muted">{{ $doctor->university ?? 'غير محدد' }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>سنوات الخبرة:</strong>
                                        <span class="text-muted">{{ $doctor->experience_display }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>اللغات:</strong>
                                        <span class="text-muted">{{ $doctor->languages_display }}</span>
                                    </div>
                                    @if($doctor->sub_specializations)
                                        <div class="col-12 mb-2">
                                            <strong>التخصصات الفرعية:</strong>
                                            <div class="mt-1">
                                                @foreach($doctor->sub_specializations as $sub)
                                                    <span class="badge bg-light text-dark me-1">{{ $sub }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if($doctor->biography)
                                        <div class="col-12 mb-2">
                                            <strong>السيرة الذاتية:</strong>
                                            <p class="text-muted mt-1">{{ $doctor->biography }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Contact & Fees -->
                            <div class="mb-4">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-telephone"></i>
                                    معلومات الاتصال والرسوم
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <strong>رسوم الاستشارة:</strong>
                                        <span class="text-success fw-bold">{{ number_format($doctor->consultation_fee, 2) }} ريال</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>رسوم المتابعة:</strong>
                                        <span class="text-success fw-bold">{{ number_format($doctor->follow_up_fee, 2) }} ريال</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>رقم الغرفة:</strong>
                                        <span class="text-muted">{{ $doctor->room_number ?? 'غير محدد' }}</span>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>هاتف الطبيب:</strong>
                                        <span class="text-muted">{{ $doctor->phone ?? 'غير محدد' }}</span>
                                    </div>
                                    @if($doctor->email)
                                        <div class="col-12 mb-2">
                                            <strong>إيميل الطبيب:</strong>
                                            <span class="text-muted">{{ $doctor->email }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Working Hours -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-facebook border-bottom pb-2 mb-3">
                                <i class="bi bi-clock"></i>
                                ساعات العمل
                            </h6>
                            @if($doctor->working_hours)
                                <div class="row">
                                    @php
                                        $dayNames = [
                                            'sunday' => 'الأحد',
                                            'monday' => 'الاثنين',
                                            'tuesday' => 'الثلاثاء',
                                            'wednesday' => 'الأربعاء',
                                            'thursday' => 'الخميس',
                                            'friday' => 'الجمعة',
                                            'saturday' => 'السبت'
                                        ];
                                    @endphp
                                    @foreach($doctor->working_hours as $day => $hours)
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                                <strong>{{ $dayNames[$day] ?? $day }}:</strong>
                                                @if($hours['is_working'])
                                                    <span class="text-success">
                                                        {{ $hours['start'] }} - {{ $hours['end'] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">إجازة</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">لم يتم تحديد ساعات العمل</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Certificates Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-facebook border-bottom pb-2 mb-0">
                                    <i class="bi bi-award-fill"></i>
                                    الشهادات والمؤهلات ({{ $doctor->certificates->count() }})
                                </h6>
                                <a href="{{ route('doctors.certificates.create', $doctor) }}" class="btn btn-sm btn-outline-facebook">
                                    <i class="bi bi-plus-circle"></i>
                                    إضافة شهادة
                                </a>
                            </div>
                            
                            @if($doctor->certificates->count() > 0)
                                <div class="row">
                                    @foreach($doctor->certificates as $certificate)
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">{{ $certificate->title }}</h6>
                                                        {!! $certificate->status_display !!}
                                                    </div>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            <i class="bi bi-building"></i>
                                                            {{ $certificate->institution }}
                                                            @if($certificate->country)
                                                                - {{ $certificate->country }}
                                                            @endif
                                                        </small>
                                                    </p>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar"></i>
                                                            {{ $certificate->issue_date->format('Y-m-d') }}
                                                            @if($certificate->expiry_date)
                                                                - {{ $certificate->expiry_date->format('Y-m-d') }}
                                                            @endif
                                                        </small>
                                                    </p>
                                                    @if($certificate->expiry_date)
                                                        <div class="mb-2">
                                                            {!! $certificate->expiry_status !!}
                                                        </div>
                                                    @endif
                                                    @if($certificate->file_path)
                                                        <a href="{{ route('doctor-certificates.download', $certificate) }}" 
                                                           class="btn btn-sm btn-outline-primary me-2">
                                                            <i class="bi bi-download"></i>
                                                            تحميل
                                                        </a>
                                                        <a href="{{ route('doctors.certificates.show', [$doctor, $certificate]) }}" 
                                                           class="btn btn-sm btn-outline-info">
                                                            <i class="bi bi-eye"></i>
                                                            عرض
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-award text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">لا توجد شهادات مضافة</h6>
                                    <p class="text-muted">يمكن إضافة الشهادات والمؤهلات من هنا</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Services Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-facebook border-bottom pb-2 mb-0">
                                    <i class="bi bi-gear-fill"></i>
                                    الخدمات المقدمة ({{ $doctor->services->count() }})
                                </h6>
                                <a href="{{ route('doctors.services.create', $doctor) }}" class="btn btn-sm btn-outline-facebook">
                                    <i class="bi bi-plus-circle"></i>
                                    إضافة خدمة
                                </a>
                            </div>
                            
                            @if($doctor->services->count() > 0)
                                <div class="row">
                                    @foreach($doctor->services as $service)
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">{{ $service->service_name }}</h6>
                                                        {!! $service->status_badge !!}
                                                    </div>
                                                    <p class="card-text">
                                                        <span class="badge bg-secondary">{{ $service->category_display }}</span>
                                                        {!! $service->appointment_badge !!}
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="text-success fw-bold">{{ $service->price_formatted }}</span>
                                                        <small class="text-muted">{{ $service->duration_formatted }}</small>
                                                    </div>
                                                    @if($service->description)
                                                        <p class="card-text mt-2">
                                                            <small class="text-muted">{{ Str::limit($service->description, 100) }}</small>
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-gear text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">لا توجد خدمات مضافة</h6>
                                    <p class="text-muted">يمكن إضافة الخدمات الطبية من هنا</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Today's Appointments -->
                    @if($todayAppointments->count() > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-calendar-check"></i>
                                    مواعيد اليوم ({{ $todayAppointments->count() }})
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>الوقت</th>
                                                <th>المريض</th>
                                                <th>نوع الموعد</th>
                                                <th>الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($todayAppointments as $appointment)
                                                <tr>
                                                    <td>{{ $appointment->appointment_time }}</td>
                                                    <td>{{ $appointment->patient->user->name }}</td>
                                                    <td>{{ $appointment->appointment_type }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : 'warning' }}">
                                                            {{ $appointment->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
</script>
@endpush
@endsection