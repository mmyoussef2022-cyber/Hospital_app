@extends('layouts.app')

@section('title', 'تعديل فحص الأشعة - ' . $radiologyStudy->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل فحص الأشعة
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('radiology-studies.show', $radiologyStudy) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            عرض
                        </a>
                        <a href="{{ route('radiology-studies.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('radiology-studies.update', $radiologyStudy) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">كود الفحص <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $radiologyStudy->code) }}" 
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">الفئة <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" 
                                            id="category" 
                                            name="category" 
                                            required>
                                        <option value="">اختر الفئة</option>
                                        @foreach($categories as $key => $category)
                                            <option value="{{ $key }}" {{ old('category', $radiologyStudy->category) == $key ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">اسم الفحص <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $radiologyStudy->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name_en" class="form-label">الاسم بالإنجليزية</label>
                                    <input type="text" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" 
                                           name="name_en" 
                                           value="{{ old('name_en', $radiologyStudy->name_en) }}">
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="body_part" class="form-label">جزء الجسم <span class="text-danger">*</span></label>
                                    <select class="form-select @error('body_part') is-invalid @enderror" 
                                            id="body_part" 
                                            name="body_part" 
                                            required>
                                        <option value="">اختر جزء الجسم</option>
                                        @foreach($bodyParts as $key => $bodyPart)
                                            <option value="{{ $key }}" {{ old('body_part', $radiologyStudy->body_part) == $key ? 'selected' : '' }}>
                                                {{ $bodyPart }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('body_part')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">السعر (ر.س) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('price') is-invalid @enderror" 
                                           id="price" 
                                           name="price" 
                                           value="{{ old('price', $radiologyStudy->price) }}" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">المدة (دقيقة) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('duration_minutes') is-invalid @enderror" 
                                           id="duration_minutes" 
                                           name="duration_minutes" 
                                           value="{{ old('duration_minutes', $radiologyStudy->duration_minutes) }}" 
                                           min="1" 
                                           max="480" 
                                           required>
                                    @error('duration_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3">{{ old('description', $radiologyStudy->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Requirements -->
                            <div class="col-md-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    المتطلبات
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="requires_contrast" 
                                               name="requires_contrast" 
                                               value="1" 
                                               {{ old('requires_contrast', $radiologyStudy->requires_contrast) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_contrast">
                                            يتطلب صبغة
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="requires_fasting" 
                                               name="requires_fasting" 
                                               value="1" 
                                               {{ old('requires_fasting', $radiologyStudy->requires_fasting) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_fasting">
                                            يتطلب صيام
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_urgent_capable" 
                                               name="is_urgent_capable" 
                                               value="1" 
                                               {{ old('is_urgent_capable', $radiologyStudy->is_urgent_capable) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_urgent_capable">
                                            قابل للعجل
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preparation_instructions" class="form-label">تعليمات التحضير</label>
                                    <textarea class="form-control @error('preparation_instructions') is-invalid @enderror" 
                                              id="preparation_instructions" 
                                              name="preparation_instructions" 
                                              rows="4" 
                                              placeholder="مثال: صيام 6 ساعات قبل الفحص">{{ old('preparation_instructions', $radiologyStudy->preparation_instructions) }}</textarea>
                                    @error('preparation_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contrast_instructions" class="form-label">تعليمات الصبغة</label>
                                    <textarea class="form-control @error('contrast_instructions') is-invalid @enderror" 
                                              id="contrast_instructions" 
                                              name="contrast_instructions" 
                                              rows="4" 
                                              placeholder="مثال: شرب كمية كافية من الماء قبل الفحص">{{ old('contrast_instructions', $radiologyStudy->contrast_instructions) }}</textarea>
                                    @error('contrast_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', $radiologyStudy->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            فحص نشط
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('radiology-studies.show', $radiologyStudy) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        حفظ التغييرات
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.btn {
    border-radius: 0.375rem;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-label {
    font-weight: 500;
}

h5 {
    color: #495057;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate code based on category and name
    $('#category, #name').on('input', function() {
        const category = $('#category option:selected').text();
        const name = $('#name').val();
        
        if (category && name && category !== 'اختر الفئة') {
            // Create a simple code based on category and name
            const categoryCode = category.substring(0, 2).toUpperCase();
            const nameCode = name.substring(0, 3).toUpperCase();
            const randomNum = Math.floor(Math.random() * 100).toString().padStart(2, '0');
            
            const suggestedCode = `${categoryCode}${nameCode}${randomNum}`;
            
            // Only suggest if code field is empty
            if (!$('#code').val()) {
                $('#code').val(suggestedCode);
            }
        }
    });

    // Show/hide contrast instructions based on requires_contrast checkbox
    $('#requires_contrast').change(function() {
        const contrastInstructions = $('#contrast_instructions').closest('.col-md-6');
        if ($(this).is(':checked')) {
            contrastInstructions.show();
            $('#contrast_instructions').attr('placeholder', 'أدخل تعليمات الصبغة المطلوبة');
        } else {
            contrastInstructions.hide();
            $('#contrast_instructions').val('');
        }
    });

    // Show/hide preparation instructions based on requires_fasting checkbox
    $('#requires_fasting').change(function() {
        if ($(this).is(':checked')) {
            $('#preparation_instructions').attr('placeholder', 'أدخل تعليمات الصيام والتحضير');
        } else {
            $('#preparation_instructions').attr('placeholder', 'مثال: صيام 6 ساعات قبل الفحص');
        }
    });

    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Validate required fields
        $('input[required], select[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            
            // Show error message
            const alert = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    يرجى تصحيح الأخطاء في النموذج قبل المتابعة.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            $('.card-body').prepend(alert);
            
            // Scroll to first error
            const firstError = $('.is-invalid').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }
    });

    // Initialize contrast instructions visibility
    $('#requires_contrast').trigger('change');
});
</script>
@endpush