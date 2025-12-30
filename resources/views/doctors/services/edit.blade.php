@extends('layouts.app')

@section('title', 'تعديل الخدمة - ' . $service->service_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-pencil"></i>
                        تعديل الخدمة
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctors.show', $doctor) }}">{{ $doctor->user->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctors.services.show', [$doctor, $service]) }}">{{ $service->service_name }}</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('doctors.services.show', [$doctor, $service]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                        العودة للخدمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Service Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $service->service_name }}</h5>
                    <p class="text-muted mb-1">{{ $service->category_display }} - {{ $service->price_formatted }} - {{ $service->duration_formatted }}</p>
                    <div class="d-flex gap-2">
                        {!! $service->status_badge !!}
                        {!! $service->appointment_badge !!}
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">آخر تحديث: {{ $service->updated_at->format('Y-m-d H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-gear-wide-connected"></i>
                تعديل بيانات الخدمة
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('doctors.services.update', [$doctor, $service]) }}" method="POST" id="serviceForm">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-facebook border-bottom pb-2 mb-3">المعلومات الأساسية</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="service_name" class="form-label">اسم الخدمة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('service_name') is-invalid @enderror" 
                               id="service_name" name="service_name" 
                               value="{{ old('service_name', $service->service_name) }}" 
                               placeholder="مثال: استشارة طبية عامة" required>
                        @error('service_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="service_name_en" class="form-label">اسم الخدمة بالإنجليزية</label>
                        <input type="text" class="form-control @error('service_name_en') is-invalid @enderror" 
                               id="service_name_en" name="service_name_en" 
                               value="{{ old('service_name_en', $service->service_name_en) }}" 
                               placeholder="General Medical Consultation">
                        @error('service_name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">وصف الخدمة</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="وصف مفصل للخدمة وما تشمله...">{{ old('description', $service->description) }}</textarea>
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
                                <option value="{{ $key }}" {{ old('category', $service->category) == $key ? 'selected' : '' }}>
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
                                   id="price" name="price" value="{{ old('price', $service->price) }}" 
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
                                   id="duration_minutes" name="duration_minutes" 
                                   value="{{ old('duration_minutes', $service->duration_minutes) }}" 
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
                                  placeholder="اكتب كل متطلب في سطر منفصل...">{{ old('requirements_list', $service->requirements ? implode("\n", $service->requirements) : '') }}</textarea>
                        @error('requirements_list')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">اكتب كل متطلب في سطر منفصل</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="preparation_list" class="form-label">تعليمات التحضير</label>
                        <textarea class="form-control @error('preparation_list') is-invalid @enderror" 
                                  id="preparation_list" name="preparation_list" rows="5" 
                                  placeholder="اكتب كل تعليمة في سطر منفصل...">{{ old('preparation_list', $service->preparation_instructions ? implode("\n", $service->preparation_instructions) : '') }}</textarea>
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
                                   {{ old('requires_appointment', $service->requires_appointment) ? 'checked' : '' }}>
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
                                   {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                خدمة نشطة
                            </label>
                        </div>
                        <div class="form-text">هل هذه الخدمة متاحة للحجز؟</div>
                    </div>
                </div>

                <!-- Changes Preview -->
                <div class="row mb-4" id="changesPreview" style="display: none;">
                    <div class="col-12">
                        <h6 class="text-facebook border-bottom pb-2 mb-3">معاينة التغييرات</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">القيم الحالية:</h6>
                                        <div id="currentValues">
                                            <div><strong>الاسم:</strong> {{ $service->service_name }}</div>
                                            <div><strong>الفئة:</strong> {{ $service->category_display }}</div>
                                            <div><strong>السعر:</strong> {{ $service->price_formatted }}</div>
                                            <div><strong>المدة:</strong> {{ $service->duration_formatted }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-success">القيم الجديدة:</h6>
                                        <div id="newValues">
                                            <!-- Will be populated by JavaScript -->
                                        </div>
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
                                <button type="button" class="btn btn-outline-info" onclick="toggleChangesPreview()">
                                    <i class="bi bi-eye"></i>
                                    معاينة التغييرات
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    إعادة تعيين
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('doctors.services.show', [$doctor, $service]) }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-x-circle"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-facebook">
                                    <i class="bi bi-check-circle"></i>
                                    حفظ التغييرات
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
const originalValues = {
    service_name: '{{ $service->service_name }}',
    category: '{{ $service->category }}',
    price: {{ $service->price }},
    duration_minutes: {{ $service->duration_minutes }}
};

// Changes preview functionality
function toggleChangesPreview() {
    const preview = document.getElementById('changesPreview');
    const isVisible = preview.style.display !== 'none';
    
    if (isVisible) {
        preview.style.display = 'none';
    } else {
        updateChangesPreview();
        preview.style.display = 'block';
    }
}

function updateChangesPreview() {
    const serviceName = document.getElementById('service_name').value || 'غير محدد';
    const category = document.getElementById('category').value;
    const price = document.getElementById('price').value || '0';
    const duration = document.getElementById('duration_minutes').value || '0';

    const newValuesDiv = document.getElementById('newValues');
    newValuesDiv.innerHTML = `
        <div><strong>الاسم:</strong> ${serviceName}</div>
        <div><strong>الفئة:</strong> ${categories[category] || 'غير محدد'}</div>
        <div><strong>السعر:</strong> ${parseFloat(price).toFixed(2)} ريال</div>
        <div><strong>المدة:</strong> ${duration} دقيقة</div>
    `;
}

// Reset form to original values
function resetForm() {
    if (confirm('هل تريد إعادة تعيين النموذج للقيم الأصلية؟')) {
        document.getElementById('service_name').value = originalValues.service_name;
        document.getElementById('category').value = originalValues.category;
        document.getElementById('price').value = originalValues.price;
        document.getElementById('duration_minutes').value = originalValues.duration_minutes;
        
        // Reset other fields to their original values
        document.getElementById('service_name_en').value = '{{ $service->service_name_en }}';
        document.getElementById('description').value = '{{ $service->description }}';
        document.getElementById('requirements_list').value = '{{ $service->requirements ? implode("\n", $service->requirements) : "" }}';
        document.getElementById('preparation_list').value = '{{ $service->preparation_instructions ? implode("\n", $service->preparation_instructions) : "" }}';
        document.getElementById('requires_appointment').checked = {{ $service->requires_appointment ? 'true' : 'false' }};
        document.getElementById('is_active').checked = {{ $service->is_active ? 'true' : 'false' }};
    }
}

// Auto-update preview when fields change
document.addEventListener('DOMContentLoaded', function() {
    const fields = ['service_name', 'category', 'price', 'duration_minutes'];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                if (document.getElementById('changesPreview').style.display !== 'none') {
                    updateChangesPreview();
                }
            });
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

// Detect changes and show warning
let formChanged = false;
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('serviceForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'لديك تغييرات غير محفوظة. هل تريد المغادرة؟';
        }
    });
    
    // Don't show warning when form is submitted
    form.addEventListener('submit', function() {
        formChanged = false;
    });
});
</script>
@endpush