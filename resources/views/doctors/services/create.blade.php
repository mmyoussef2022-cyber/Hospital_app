@extends('layouts.app')

@section('title', 'إضافة خدمة جديدة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-plus-circle"></i>
                        إضافة خدمة جديدة
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctors.show', $doctor) }}">{{ $doctor->user->name }}</a></li>
                            <li class="breadcrumb-item active">إضافة خدمة</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                        العودة لملف الطبيب
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctor Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    @if($doctor->photo)
                        <img src="{{ asset('storage/' . $doctor->photo) }}" 
                             alt="{{ $doctor->user->name }}" 
                             class="img-fluid rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="bg-facebook text-white rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ substr($doctor->user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="col-md-10">
                    <h5 class="mb-1">{{ $doctor->user->name }}</h5>
                    <p class="text-muted mb-1">{{ $doctor->specialization }}</p>
                    <div class="d-flex gap-3">
                        <span class="badge bg-info">{{ $doctor->services->count() }} خدمة</span>
                        <span class="badge bg-{{ $doctor->is_available ? 'success' : 'warning' }}">
                            {{ $doctor->is_available ? 'متاح' : 'غير متاح' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Form -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-gear-wide-connected"></i>
                بيانات الخدمة الجديدة
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('doctors.services.store', $doctor) }}" method="POST" id="serviceForm">
                @csrf
                
                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-facebook border-bottom pb-2 mb-3">المعلومات الأساسية</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="service_name" class="form-label">اسم الخدمة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('service_name') is-invalid @enderror" 
                               id="service_name" name="service_name" value="{{ old('service_name') }}" 
                               placeholder="مثال: استشارة طبية عامة" required>
                        @error('service_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="service_name_en" class="form-label">اسم الخدمة بالإنجليزية</label>
                        <input type="text" class="form-control @error('service_name_en') is-invalid @enderror" 
                               id="service_name_en" name="service_name_en" value="{{ old('service_name_en') }}" 
                               placeholder="General Medical Consultation">
                        @error('service_name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">وصف الخدمة</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="وصف مفصل للخدمة وما تشمله...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Service Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-facebook border-bottom pb-2 mb-3">تفاصيل الخدمة</h6>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="category" class="form-label">فئة الخدمة <span class="text-danger">*</span></label>
                        <select class="form-select @error('category') is-invalid @enderror" 
                                id="category" name="category" required>
                            <option value="">اختر الفئة</option>
                            @foreach($categories as $key => $value)
                                <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">سعر الخدمة (ريال) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" value="{{ old('price') }}" 
                                   min="0" step="0.01" placeholder="0.00" required>
                            <span class="input-group-text">ريال</span>
                        </div>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="duration_minutes" class="form-label">مدة الخدمة (دقيقة) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                   id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" 
                                   min="1" max="1440" placeholder="30" required>
                            <span class="input-group-text">دقيقة</span>
                        </div>
                        @error('duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">المدة المتوقعة لتقديم الخدمة (1-1440 دقيقة)</div>
                    </div>
                </div>

                <!-- Requirements and Instructions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-facebook border-bottom pb-2 mb-3">المتطلبات والتعليمات</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="requirements_list" class="form-label">متطلبات الخدمة</label>
                        <textarea class="form-control @error('requirements_list') is-invalid @enderror" 
                                  id="requirements_list" name="requirements_list" rows="5" 
                                  placeholder="اكتب كل متطلب في سطر منفصل...">{{ old('requirements_list') }}</textarea>
                        @error('requirements_list')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">اكتب كل متطلب في سطر منفصل</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="preparation_list" class="form-label">تعليمات التحضير</label>
                        <textarea class="form-control @error('preparation_list') is-invalid @enderror" 
                                  id="preparation_list" name="preparation_list" rows="5" 
                                  placeholder="اكتب كل تعليمة في سطر منفصل...">{{ old('preparation_list') }}</textarea>
                        @error('preparation_list')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">تعليمات للمريض قبل الخدمة</div>
                    </div>
                </div>

                <!-- Service Settings -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-facebook border-bottom pb-2 mb-3">إعدادات الخدمة</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="requires_appointment" 
                                   name="requires_appointment" value="1" 
                                   {{ old('requires_appointment', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_appointment">
                                يتطلب موعد مسبق
                            </label>
                        </div>
                        <div class="form-text">هل تحتاج هذه الخدمة إلى حجز موعد مسبق؟</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" 
                                   name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                خدمة نشطة
                            </label>
                        </div>
                        <div class="form-text">هل هذه الخدمة متاحة للحجز؟</div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="row mb-4" id="servicePreview" style="display: none;">
                    <div class="col-12">
                        <h6 class="text-facebook border-bottom pb-2 mb-3">معاينة الخدمة</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 id="previewName">اسم الخدمة</h6>
                                        <p class="text-muted mb-2" id="previewDescription">وصف الخدمة</p>
                                        <div class="d-flex gap-2 mb-2">
                                            <span class="badge bg-secondary" id="previewCategory">الفئة</span>
                                            <span class="badge bg-info" id="previewAppointment">يحتاج موعد</span>
                                            <span class="badge bg-success" id="previewStatus">نشط</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="h5 text-success mb-1" id="previewPrice">0 ريال</div>
                                        <div class="text-muted" id="previewDuration">0 دقيقة</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-info" onclick="togglePreview()">
                                    <i class="bi bi-eye"></i>
                                    معاينة الخدمة
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-x-circle"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-facebook">
                                    <i class="bi bi-check-circle"></i>
                                    حفظ الخدمة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const categories = @json($categories);

// Preview functionality
function togglePreview() {
    const preview = document.getElementById('servicePreview');
    const isVisible = preview.style.display !== 'none';
    
    if (isVisible) {
        preview.style.display = 'none';
    } else {
        updatePreview();
        preview.style.display = 'block';
    }
}

function updatePreview() {
    const serviceName = document.getElementById('service_name').value || 'اسم الخدمة';
    const description = document.getElementById('description').value || 'وصف الخدمة';
    const category = document.getElementById('category').value;
    const price = document.getElementById('price').value || '0';
    const duration = document.getElementById('duration_minutes').value || '0';
    const requiresAppointment = document.getElementById('requires_appointment').checked;
    const isActive = document.getElementById('is_active').checked;

    document.getElementById('previewName').textContent = serviceName;
    document.getElementById('previewDescription').textContent = description;
    document.getElementById('previewCategory').textContent = categories[category] || 'غير محدد';
    document.getElementById('previewPrice').textContent = parseFloat(price).toFixed(2) + ' ريال';
    document.getElementById('previewDuration').textContent = duration + ' دقيقة';
    
    const appointmentBadge = document.getElementById('previewAppointment');
    appointmentBadge.textContent = requiresAppointment ? 'يحتاج موعد' : 'بدون موعد';
    appointmentBadge.className = requiresAppointment ? 'badge bg-info' : 'badge bg-warning';
    
    const statusBadge = document.getElementById('previewStatus');
    statusBadge.textContent = isActive ? 'نشط' : 'غير نشط';
    statusBadge.className = isActive ? 'badge bg-success' : 'badge bg-secondary';
}

// Auto-update preview when fields change
document.addEventListener('DOMContentLoaded', function() {
    const fields = ['service_name', 'description', 'category', 'price', 'duration_minutes', 'requires_appointment', 'is_active'];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                if (document.getElementById('servicePreview').style.display !== 'none') {
                    updatePreview();
                }
            });
            
            if (field.type === 'checkbox') {
                field.addEventListener('change', function() {
                    if (document.getElementById('servicePreview').style.display !== 'none') {
                        updatePreview();
                    }
                });
            }
        }
    });
});

// Form validation
document.getElementById('serviceForm').addEventListener('submit', function(e) {
    const serviceName = document.getElementById('service_name').value.trim();
    const category = document.getElementById('category').value;
    const price = parseFloat(document.getElementById('price').value);
    const duration = parseInt(document.getElementById('duration_minutes').value);

    if (!serviceName) {
        e.preventDefault();
        alert('يرجى إدخال اسم الخدمة');
        document.getElementById('service_name').focus();
        return;
    }

    if (!category) {
        e.preventDefault();
        alert('يرجى اختيار فئة الخدمة');
        document.getElementById('category').focus();
        return;
    }

    if (isNaN(price) || price < 0) {
        e.preventDefault();
        alert('يرجى إدخال سعر صحيح للخدمة');
        document.getElementById('price').focus();
        return;
    }

    if (isNaN(duration) || duration < 1 || duration > 1440) {
        e.preventDefault();
        alert('يرجى إدخال مدة صحيحة للخدمة (1-1440 دقيقة)');
        document.getElementById('duration_minutes').focus();
        return;
    }
});

// Category-based suggestions
document.getElementById('category').addEventListener('change', function() {
    const category = this.value;
    const durationField = document.getElementById('duration_minutes');
    const priceField = document.getElementById('price');
    
    // Suggest duration based on category
    const durationSuggestions = {
        'consultation': 30,
        'surgery': 120,
        'procedure': 60,
        'examination': 20,
        'treatment': 45,
        'follow_up': 15,
        'emergency': 30,
        'other': 30
    };
    
    if (durationSuggestions[category] && !durationField.value) {
        durationField.value = durationSuggestions[category];
    }
    
    // Update preview if visible
    if (document.getElementById('servicePreview').style.display !== 'none') {
        updatePreview();
    }
});
</script>
@endpush