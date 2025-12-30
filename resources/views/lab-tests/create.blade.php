@extends('layouts.app')

@section('title', 'إضافة فحص مخبري جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>
                        إضافة فحص مخبري جديد
                    </h3>
                    <a href="{{ route('lab-tests.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للقائمة
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('lab-tests.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">كود الفحص <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code') }}" 
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
                                        <option value="كيمياء الدم" {{ old('category') == 'كيمياء الدم' ? 'selected' : '' }}>كيمياء الدم</option>
                                        <option value="أمراض الدم" {{ old('category') == 'أمراض الدم' ? 'selected' : '' }}>أمراض الدم</option>
                                        <option value="المناعة" {{ old('category') == 'المناعة' ? 'selected' : '' }}>المناعة</option>
                                        <option value="الهرمونات" {{ old('category') == 'الهرمونات' ? 'selected' : '' }}>الهرمونات</option>
                                        <option value="الميكروبيولوجي" {{ old('category') == 'الميكروبيولوجي' ? 'selected' : '' }}>الميكروبيولوجي</option>
                                        <option value="علم الطفيليات" {{ old('category') == 'علم الطفيليات' ? 'selected' : '' }}>علم الطفيليات</option>
                                        <option value="البول" {{ old('category') == 'البول' ? 'selected' : '' }}>البول</option>
                                        <option value="البراز" {{ old('category') == 'البراز' ? 'selected' : '' }}>البراز</option>
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
                                           value="{{ old('name') }}" 
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
                                           value="{{ old('name_en') }}">
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Test Details -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">السعر (ر.س) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('price') is-invalid @enderror" 
                                           id="price" 
                                           name="price" 
                                           value="{{ old('price') }}" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">المدة (دقيقة) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('duration_minutes') is-invalid @enderror" 
                                           id="duration_minutes" 
                                           name="duration_minutes" 
                                           value="{{ old('duration_minutes') }}" 
                                           min="1" 
                                           max="1440" 
                                           required>
                                    @error('duration_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="specimen_type" class="form-label">نوع العينة <span class="text-danger">*</span></label>
                                    <select class="form-select @error('specimen_type') is-invalid @enderror" 
                                            id="specimen_type" 
                                            name="specimen_type" 
                                            required>
                                        <option value="">اختر نوع العينة</option>
                                        <option value="دم" {{ old('specimen_type') == 'دم' ? 'selected' : '' }}>دم</option>
                                        <option value="بول" {{ old('specimen_type') == 'بول' ? 'selected' : '' }}>بول</option>
                                        <option value="براز" {{ old('specimen_type') == 'براز' ? 'selected' : '' }}>براز</option>
                                        <option value="لعاب" {{ old('specimen_type') == 'لعاب' ? 'selected' : '' }}>لعاب</option>
                                        <option value="مسحة" {{ old('specimen_type') == 'مسحة' ? 'selected' : '' }}>مسحة</option>
                                        <option value="سائل شوكي" {{ old('specimen_type') == 'سائل شوكي' ? 'selected' : '' }}>سائل شوكي</option>
                                        <option value="أخرى" {{ old('specimen_type') == 'أخرى' ? 'selected' : '' }}>أخرى</option>
                                    </select>
                                    @error('specimen_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="preparation_instructions" class="form-label">تعليمات التحضير</label>
                                    <textarea class="form-control @error('preparation_instructions') is-invalid @enderror" 
                                              id="preparation_instructions" 
                                              name="preparation_instructions" 
                                              rows="3" 
                                              placeholder="مثال: صيام 12 ساعة قبل الفحص">{{ old('preparation_instructions') }}</textarea>
                                    @error('preparation_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Normal Ranges -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="normal_ranges" class="form-label">المعدلات الطبيعية (JSON)</label>
                                    <textarea class="form-control @error('normal_ranges') is-invalid @enderror" 
                                              id="normal_ranges" 
                                              name="normal_ranges" 
                                              rows="4" 
                                              placeholder='{"male": {"min": 0, "max": 100, "unit": "mg/dl"}, "female": {"min": 0, "max": 90, "unit": "mg/dl"}}'>{{ old('normal_ranges') }}</textarea>
                                    @error('normal_ranges')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        أدخل المعدلات الطبيعية بصيغة JSON
                                    </small>
                                </div>
                            </div>

                            <!-- Critical Values -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="critical_values" class="form-label">القيم الحرجة (JSON)</label>
                                    <textarea class="form-control @error('critical_values') is-invalid @enderror" 
                                              id="critical_values" 
                                              name="critical_values" 
                                              rows="4" 
                                              placeholder='{"high": 200, "low": 50, "unit": "mg/dl"}'>{{ old('critical_values') }}</textarea>
                                    @error('critical_values')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        أدخل القيم الحرجة بصيغة JSON
                                    </small>
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
                                               {{ old('is_active', true) ? 'checked' : '' }}>
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
                                    <a href="{{ route('lab-tests.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        حفظ الفحص
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate code based on category and name
    $('#category, #name').on('input', function() {
        const category = $('#category').val();
        const name = $('#name').val();
        
        if (category && name) {
            // Create a simple code based on category and name
            const categoryCode = category.substring(0, 3).toUpperCase();
            const nameCode = name.substring(0, 3).toUpperCase();
            const randomNum = Math.floor(Math.random() * 100).toString().padStart(2, '0');
            
            const suggestedCode = `${categoryCode}${nameCode}${randomNum}`;
            
            if (!$('#code').val()) {
                $('#code').val(suggestedCode);
            }
        }
    });

    // Validate JSON fields
    $('#normal_ranges, #critical_values').on('blur', function() {
        const value = $(this).val().trim();
        
        if (value) {
            try {
                JSON.parse(value);
                $(this).removeClass('is-invalid').addClass('is-valid');
            } catch (e) {
                $(this).removeClass('is-valid').addClass('is-invalid');
                
                // Show error message
                let feedback = $(this).siblings('.invalid-feedback');
                if (feedback.length === 0) {
                    feedback = $('<div class="invalid-feedback"></div>');
                    $(this).after(feedback);
                }
                feedback.text('صيغة JSON غير صحيحة');
            }
        } else {
            $(this).removeClass('is-invalid is-valid');
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

        // Validate JSON fields
        $('#normal_ranges, #critical_values').each(function() {
            const value = $(this).val().trim();
            if (value) {
                try {
                    JSON.parse(value);
                } catch (e) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                }
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
});
</script>
@endpush