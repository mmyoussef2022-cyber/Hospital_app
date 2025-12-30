@extends('layouts.app')

@section('page-title', 'إضافة عرض جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">
                    <i class="fas fa-plus me-2"></i>
                    إضافة عرض جديد
                </h2>
                <a href="{{ route('admin.landing-page.offers') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>
                    العودة للقائمة
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات العرض
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.landing-page.offers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">عنوان العرض <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="discount_type" class="form-label">نوع الخصم <span class="text-danger">*</span></label>
                                <select class="form-select @error('discount_type') is-invalid @enderror" 
                                        id="discount_type" name="discount_type" required>
                                    <option value="">اختر نوع الخصم</option>
                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                                    <option value="free" {{ old('discount_type') == 'free' ? 'selected' : '' }}>مجاني</option>
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="discount_value" class="form-label">قيمة الخصم</label>
                                <input type="number" class="form-control @error('discount_value') is-invalid @enderror" 
                                       id="discount_value" name="discount_value" value="{{ old('discount_value') }}" 
                                       step="0.01" min="0">
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="discount_badge_text" class="form-label">نص شارة الخصم</label>
                                <input type="text" class="form-control @error('discount_badge_text') is-invalid @enderror" 
                                       id="discount_badge_text" name="discount_badge_text" value="{{ old('discount_badge_text') }}" 
                                       placeholder="مثال: خصم 50%">
                                @error('discount_badge_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">وصف العرض <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="valid_from" class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('valid_from') is-invalid @enderror" 
                                       id="valid_from" name="valid_from" value="{{ old('valid_from') }}" required>
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="valid_until" class="form-label">تاريخ الانتهاء <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('valid_until') is-invalid @enderror" 
                                       id="valid_until" name="valid_until" value="{{ old('valid_until') }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cta_text" class="form-label">نص زر الإجراء</label>
                                <input type="text" class="form-control @error('cta_text') is-invalid @enderror" 
                                       id="cta_text" name="cta_text" value="{{ old('cta_text') }}" 
                                       placeholder="احجز الآن">
                                @error('cta_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="cta_url" class="form-label">رابط زر الإجراء</label>
                                <input type="url" class="form-control @error('cta_url') is-invalid @enderror" 
                                       id="cta_url" name="cta_url" value="{{ old('cta_url') }}" 
                                       placeholder="https://example.com/booking">
                                @error('cta_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_uses" class="form-label">الحد الأقصى للاستخدام</label>
                                <input type="number" class="form-control @error('max_uses') is-invalid @enderror" 
                                       id="max_uses" name="max_uses" value="{{ old('max_uses') }}" 
                                       min="1" placeholder="غير محدود">
                                @error('max_uses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">ترتيب العرض</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                                       min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">صورة العرض</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">الحد الأقصى: 2MB، الأنواع المدعومة: JPG, PNG, GIF</div>
                        </div>

                        <div class="mb-3">
                            <label for="terms_conditions" class="form-label">الشروط والأحكام</label>
                            <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                                      id="terms_conditions" name="terms_conditions" rows="3">{{ old('terms_conditions') }}</textarea>
                            @error('terms_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           {{ old('is_active') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        تفعيل العرض
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        عرض مميز
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ العرض
                            </button>
                            <a href="{{ route('admin.landing-page.offers') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb me-2"></i>
                        نصائح مفيدة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            استخدم عناوين جذابة وواضحة
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            أضف وصف مفصل للعرض
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            حدد تواريخ صحيحة للعرض
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            استخدم صور عالية الجودة
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-check text-success me-1"></i>
                            اكتب شروط وأحكام واضحة
                        </p>
                    </div>
                </div>
            </div>

            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات إضافية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="mb-2">
                            <strong>نوع الخصم:</strong><br>
                            - نسبة مئوية: خصم بالنسبة المئوية<br>
                            - مبلغ ثابت: خصم بمبلغ محدد<br>
                            - مجاني: خدمة مجانية
                        </p>
                        <p class="mb-2">
                            <strong>ترتيب العرض:</strong><br>
                            يحدد ترتيب ظهور العرض في الصفحة
                        </p>
                        <p class="mb-0">
                            <strong>العرض المميز:</strong><br>
                            يظهر في قسم العروض المميزة
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('discount_type').addEventListener('change', function() {
    const discountValue = document.getElementById('discount_value');
    const discountType = this.value;
    
    if (discountType === 'free') {
        discountValue.disabled = true;
        discountValue.value = '';
        discountValue.required = false;
    } else {
        discountValue.disabled = false;
        discountValue.required = true;
    }
});

// تحديد الحد الأدنى لتاريخ الانتهاء
document.getElementById('valid_from').addEventListener('change', function() {
    const validUntil = document.getElementById('valid_until');
    validUntil.min = this.value;
});
</script>
@endsection