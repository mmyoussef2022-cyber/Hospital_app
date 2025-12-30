@extends('layouts.app')

@section('page-title', 'تفاصيل الشهادة - ' . $certificate->title)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-award text-facebook"></i>
                        تفاصيل الشهادة
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('doctors.certificates.edit', [$doctor, $certificate]) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i>
                            تعديل
                        </a>
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-info">
                            <i class="bi bi-person"></i>
                            ملف الطبيب
                        </a>
                        <a href="{{ route('doctor-certificates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right"></i>
                            قائمة الشهادات
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Doctor Info -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $doctor->profile_photo_url }}" 
                                         alt="{{ $doctor->user->name }}" 
                                         class="rounded-circle me-3" 
                                         width="60" height="60"
                                         style="object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1">{{ $doctor->full_name }}</h6>
                                        <small class="text-muted">
                                            {{ $doctor->doctor_number }} - 
                                            {{ \App\Models\Doctor::getSpecializations()[$doctor->specialization] ?? $doctor->specialization }}
                                        </small>
                                        <br>
                                        <small class="text-muted">{{ $doctor->user->department->name ?? 'غير محدد' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-facebook border-bottom pb-2 mb-3">
                                <i class="bi bi-info-circle"></i>
                                معلومات الشهادة
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>عنوان الشهادة:</strong>
                            <div class="text-muted">{{ $certificate->title }}</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>نوع الشهادة:</strong>
                            <div>
                                <span class="badge bg-info">{{ $certificate->type_display }}</span>
                            </div>
                        </div>
                        
                        <div class="col-md-8 mb-3">
                            <strong>المؤسسة المانحة:</strong>
                            <div class="text-muted">{{ $certificate->institution }}</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>البلد:</strong>
                            <div class="text-muted">{{ $certificate->country ?? 'غير محدد' }}</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>تاريخ الإصدار:</strong>
                            <div class="text-muted">
                                <i class="bi bi-calendar"></i>
                                {{ $certificate->issue_date->format('Y-m-d') }}
                                <small>({{ $certificate->issue_date->diffForHumans() }})</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>تاريخ الانتهاء:</strong>
                            <div class="text-muted">
                                @if($certificate->expiry_date)
                                    <i class="bi bi-calendar-x"></i>
                                    {{ $certificate->expiry_date->format('Y-m-d') }}
                                    <small>({{ $certificate->expiry_date->diffForHumans() }})</small>
                                    <br>
                                    {!! $certificate->expiry_status !!}
                                @else
                                    <span class="badge bg-secondary">لا تنتهي</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($certificate->certificate_number)
                            <div class="col-md-6 mb-3">
                                <strong>رقم الشهادة:</strong>
                                <div class="text-muted">{{ $certificate->certificate_number }}</div>
                            </div>
                        @endif
                        
                        @if($certificate->description)
                            <div class="col-12 mb-3">
                                <strong>الوصف:</strong>
                                <div class="text-muted">{{ $certificate->description }}</div>
                            </div>
                        @endif
                    </div>

                    <!-- File Information -->
                    @if($certificate->file_path)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-file-earmark"></i>
                                    ملف الشهادة
                                </h6>
                            </div>
                            
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-{{ $certificate->file_type == 'application/pdf' ? 'pdf' : 'image' }} text-facebook me-3" style="font-size: 2rem;"></i>
                                                <div>
                                                    <div class="fw-bold">{{ basename($certificate->file_path) }}</div>
                                                    <small class="text-muted">
                                                        النوع: {{ $certificate->file_type }} | 
                                                        الحجم: {{ $certificate->file_size_formatted }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="btn-group">
                                                <a href="{{ $certificate->file_url }}" target="_blank" class="btn btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                    عرض
                                                </a>
                                                <a href="{{ route('doctor-certificates.download', $certificate) }}" class="btn btn-primary">
                                                    <i class="bi bi-download"></i>
                                                    تحميل
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Image Preview -->
                                        @if(str_starts_with($certificate->file_type, 'image/'))
                                            <div class="mt-3 text-center">
                                                <img src="{{ $certificate->file_url }}" 
                                                     alt="{{ $certificate->title }}" 
                                                     class="img-fluid rounded" 
                                                     style="max-height: 400px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Verification Status -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-facebook border-bottom pb-2 mb-3">
                                <i class="bi bi-shield-check"></i>
                                حالة التحقق
                            </h6>
                        </div>
                        
                        <div class="col-12">
                            <div class="card {{ $certificate->is_verified ? 'border-success' : 'border-warning' }}">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            {!! $certificate->status_display !!}
                                            @if($certificate->is_verified)
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-person-check"></i>
                                                        تم التحقق بواسطة: {{ $certificate->verifiedBy->name ?? 'غير محدد' }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar-check"></i>
                                                        تاريخ التحقق: {{ $certificate->verified_at ? $certificate->verified_at->format('Y-m-d H:i') : 'غير محدد' }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            @if(!$certificate->is_verified)
                                                <button type="button" 
                                                        class="btn btn-success" 
                                                        onclick="verifyCertificate({{ $certificate->id }})"
                                                        id="verifyBtn">
                                                    <i class="bi bi-check-circle"></i>
                                                    تحقق من الشهادة
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-outline-warning" 
                                                        onclick="unverifyCertificate({{ $certificate->id }})"
                                                        id="unverifyBtn">
                                                    <i class="bi bi-x-circle"></i>
                                                    إلغاء التحقق
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('doctors.certificates.edit', [$doctor, $certificate]) }}" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i>
                                    تعديل الشهادة
                                </a>
                                <form method="POST" action="{{ route('doctors.certificates.destroy', [$doctor, $certificate]) }}" 
                                      class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الشهادة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash"></i>
                                        حذف الشهادة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
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
                        @if($certificate->file_path)
                            <a href="{{ $certificate->file_url }}" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i>
                                عرض الملف
                            </a>
                            <a href="{{ route('doctor-certificates.download', $certificate) }}" class="btn btn-outline-success">
                                <i class="bi bi-download"></i>
                                تحميل الملف
                            </a>
                        @endif
                        <a href="{{ route('doctors.certificates.create', $doctor) }}" class="btn btn-outline-facebook">
                            <i class="bi bi-plus-circle"></i>
                            إضافة شهادة أخرى
                        </a>
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-info">
                            <i class="bi bi-person"></i>
                            عرض ملف الطبيب
                        </a>
                    </div>
                </div>
            </div>

            <!-- Certificate Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle text-facebook"></i>
                        معلومات إضافية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>تاريخ الإضافة:</span>
                            <small class="text-muted">{{ $certificate->created_at->format('Y-m-d') }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>آخر تحديث:</span>
                            <small class="text-muted">{{ $certificate->updated_at->format('Y-m-d') }}</small>
                        </div>
                    </div>
                    @if($certificate->expiry_date)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>الأيام المتبقية:</span>
                                <small class="text-muted">
                                    @if($certificate->expiry_date->isFuture())
                                        {{ $certificate->expiry_date->diffInDays(now()) }} يوم
                                    @else
                                        <span class="text-danger">منتهية</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Certificates -->
            @if($doctor->certificates->where('id', '!=', $certificate->id)->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-collection text-facebook"></i>
                            شهادات أخرى للطبيب
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($doctor->certificates->where('id', '!=', $certificate->id)->take(5) as $otherCert)
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-award text-facebook me-2"></i>
                                <div class="flex-grow-1">
                                    <a href="{{ route('doctors.certificates.show', [$doctor, $otherCert]) }}" 
                                       class="text-decoration-none">
                                        <small>{{ Str::limit($otherCert->title, 30) }}</small>
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $otherCert->type_display }}</small>
                                </div>
                                {!! $otherCert->status_display !!}
                            </div>
                            @if(!$loop->last)
                                <hr class="my-2">
                            @endif
                        @endforeach
                        
                        @if($doctor->certificates->where('id', '!=', $certificate->id)->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('doctors.show', $doctor) }}#certificates" class="btn btn-sm btn-outline-facebook">
                                    عرض جميع الشهادات ({{ $doctor->certificates->count() - 1 }} أخرى)
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Verify certificate
function verifyCertificate(certificateId) {
    if (!confirm('هل أنت متأكد من التحقق من هذه الشهادة؟')) {
        return;
    }
    
    const verifyBtn = document.getElementById('verifyBtn');
    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري التحقق...';
    
    fetch(`/doctor-certificates/${certificateId}/verify`, {
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
            alert('حدث خطأ في التحقق من الشهادة');
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = '<i class="bi bi-check-circle"></i> تحقق من الشهادة';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في التحقق من الشهادة');
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = '<i class="bi bi-check-circle"></i> تحقق من الشهادة';
    });
}

// Unverify certificate
function unverifyCertificate(certificateId) {
    if (!confirm('هل أنت متأكد من إلغاء التحقق من هذه الشهادة؟')) {
        return;
    }
    
    const unverifyBtn = document.getElementById('unverifyBtn');
    unverifyBtn.disabled = true;
    unverifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الإلغاء...';
    
    fetch(`/doctor-certificates/${certificateId}/unverify`, {
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
            alert('حدث خطأ في إلغاء التحقق من الشهادة');
            unverifyBtn.disabled = false;
            unverifyBtn.innerHTML = '<i class="bi bi-x-circle"></i> إلغاء التحقق';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إلغاء التحقق من الشهادة');
        unverifyBtn.disabled = false;
        unverifyBtn.innerHTML = '<i class="bi bi-x-circle"></i> إلغاء التحقق';
    });
}
</script>
@endpush
@endsection