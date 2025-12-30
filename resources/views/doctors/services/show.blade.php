@extends('layouts.app')

@section('title', 'عرض الخدمة - ' . $service->service_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-gear-wide-connected"></i>
                        {{ $service->service_name }}
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctors.show', $doctor) }}">{{ $doctor->user->name }}</a></li>
                            <li class="breadcrumb-item active">{{ $service->service_name }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <div class="btn-group">
                        <a href="{{ route('doctors.services.edit', [$doctor, $service]) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i>
                            تعديل
                        </a>
                        <button type="button" class="btn btn-{{ $service->is_active ? 'outline-warning' : 'outline-success' }}" 
                                onclick="toggleServiceStatus({{ $service->id }})">
                            <i class="bi bi-{{ $service->is_active ? 'pause' : 'play' }}-circle"></i>
                            {{ $service->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}
                        </button>
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i>
                            العودة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Service Details -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i>
                        معلومات الخدمة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-facebook mb-2">{{ $service->service_name }}</h4>
                            @if($service->service_name_en)
                                <h6 class="text-muted mb-3">{{ $service->service_name_en }}</h6>
                            @endif
                            
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-secondary fs-6">{{ $service->category_display }}</span>
                                {!! $service->status_badge !!}
                                {!! $service->appointment_badge !!}
                            </div>
                            
                            @if($service->description)
                                <div class="mb-3">
                                    <h6 class="text-facebook">وصف الخدمة:</h6>
                                    <p class="text-muted">{{ $service->description }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="mb-3">
                                <div class="h3 text-success mb-1">{{ $service->price_formatted }}</div>
                                <div class="text-muted">سعر الخدمة</div>
                            </div>
                            <div class="mb-3">
                                <div class="h5 text-info mb-1">{{ $service->duration_formatted }}</div>
                                <div class="text-muted">مدة الخدمة</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requirements and Instructions -->
            @if($service->requirements || $service->preparation_instructions)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-list-check"></i>
                            المتطلبات والتعليمات
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($service->requirements && count($service->requirements) > 0)
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-facebook mb-2">
                                        <i class="bi bi-check-square"></i>
                                        متطلبات الخدمة:
                                    </h6>
                                    <ul class="list-unstyled">
                                        @foreach($service->requirements as $requirement)
                                            <li class="mb-1">
                                                <i class="bi bi-arrow-right text-facebook"></i>
                                                {{ $requirement }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            @if($service->preparation_instructions && count($service->preparation_instructions) > 0)
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-facebook mb-2">
                                        <i class="bi bi-clipboard-check"></i>
                                        تعليمات التحضير:
                                    </h6>
                                    <ul class="list-unstyled">
                                        @foreach($service->preparation_instructions as $instruction)
                                            <li class="mb-1">
                                                <i class="bi bi-arrow-right text-info"></i>
                                                {{ $instruction }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Service Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart"></i>
                        إحصائيات الخدمة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <div class="h4 text-primary mb-1">0</div>
                                <div class="text-muted small">إجمالي الحجوزات</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <div class="h4 text-success mb-1">0</div>
                                <div class="text-muted small">الحجوزات المكتملة</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <div class="h4 text-warning mb-1">0</div>
                                <div class="text-muted small">الحجوزات المعلقة</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <div class="h4 text-info mb-1">0 ريال</div>
                                <div class="text-muted small">إجمالي الإيرادات</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Doctor Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person-badge"></i>
                        معلومات الطبيب
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($doctor->photo)
                            <img src="{{ asset('storage/' . $doctor->photo) }}" 
                                 alt="{{ $doctor->user->name }}" 
                                 class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                            <div class="bg-facebook text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 60px; height: 60px; font-size: 1.5rem;">
                                {{ substr($doctor->user->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-1">{{ $doctor->user->name }}</h6>
                            <p class="text-muted mb-0">{{ $doctor->specialization }}</p>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="border-end">
                                <div class="fw-bold text-facebook">{{ $doctor->experience_years }}</div>
                                <small class="text-muted">سنوات الخبرة</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="fw-bold text-success">{{ $doctor->services->count() }}</div>
                            <small class="text-muted">الخدمات</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-facebook btn-sm">
                            <i class="bi bi-person"></i>
                            عرض ملف الطبيب
                        </a>
                        <a href="{{ route('doctors.services.create', $doctor) }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-plus-circle"></i>
                            إضافة خدمة جديدة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-charge"></i>
                        إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('doctors.services.edit', [$doctor, $service]) }}" class="btn btn-outline-warning">
                            <i class="bi bi-pencil"></i>
                            تعديل الخدمة
                        </a>
                        <button type="button" class="btn btn-outline-info" onclick="duplicateService({{ $service->id }})">
                            <i class="bi bi-files"></i>
                            نسخ الخدمة
                        </button>
                        <button type="button" class="btn btn-outline-{{ $service->is_active ? 'warning' : 'success' }}" 
                                onclick="toggleServiceStatus({{ $service->id }})">
                            <i class="bi bi-{{ $service->is_active ? 'pause' : 'play' }}-circle"></i>
                            {{ $service->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}
                        </button>
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="deleteService({{ $service->id }}, '{{ $service->service_name }}')">
                            <i class="bi bi-trash"></i>
                            حذف الخدمة
                        </button>
                    </div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-square"></i>
                        تفاصيل إضافية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">تاريخ الإنشاء:</span>
                            <span>{{ $service->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">آخر تحديث:</span>
                            <span>{{ $service->updated_at->format('Y-m-d') }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">ترتيب الخدمة:</span>
                            <span class="badge bg-info">{{ $service->sort_order }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">يمكن حجزها:</span>
                            <span class="badge bg-{{ $service->canBeBooked() ? 'success' : 'warning' }}">
                                {{ $service->canBeBooked() ? 'نعم' : 'لا' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle service status
function toggleServiceStatus(serviceId) {
    fetch(`/doctor-services/${serviceId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تغيير حالة الخدمة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تغيير حالة الخدمة');
    });
}

// Duplicate service
function duplicateService(serviceId) {
    if (confirm('هل تريد إنشاء نسخة من هذه الخدمة؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/doctors/{{ $doctor->id }}/services/${serviceId}/duplicate`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Delete service
function deleteService(serviceId, serviceName) {
    if (confirm(`هل أنت متأكد من حذف الخدمة "${serviceName}"؟\nهذا الإجراء لا يمكن التراجع عنه.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/doctors/{{ $doctor->id }}/services/${serviceId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush