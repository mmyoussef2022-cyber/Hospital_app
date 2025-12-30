@extends('layouts.app')

@section('page-title', 'تعديل العرض')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">
                    <i class="fas fa-edit me-2"></i>
                    تعديل العرض: {{ $offer->title }}
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
                    <form action="{{ route('admin.landing-page.offers.update', $offer) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">عنوان العرض <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title', $offer->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="discount_type" class="form-label">نوع الخصم <span class="text-danger">*</span></label>
                                <select class="form-select @error('discount_type') is-invalid @enderror" 
                                        id="discount_type" name="discount_type" required>
                                    <option value="">اختر نوع الخصم</option>
                                    <option value="percentage" {{ old('discount_type', $offer->discount_type) == 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                                    <option value="fixed" {{ old('discount_type', $offer->discount_type) == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                                    <option value="free" {{ old('discount_type', $offer->discount_type) == 'free' ? 'selected' : '' }}>مجاني</option>
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
                                       id="discount_value" name="discount_value" value="{{ old('discount_value', $offer->discount_value) }}" 
                                       step="0.01" min="0">
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="discount_badge_text" class="form-label">نص شارة الخصم</label>
                                <input type="text" class="form-control @error('discount_badge_text') is-invalid @enderror" 
                                       id="discount_badge_text" name="discount_badge_text" value="{{ old('discount_badge_text', $offer->discount_badge_text) }}" 
                                       placeholder="مثال: خصم 50%">
                                @error('discount_badge_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">وصف العرض <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description', $offer->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="valid_from" class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('valid_from') is-invalid @enderror" 
                                       id="valid_from" name="valid_from" value="{{ old('valid_from', $offer->valid_from?->format('Y-m-d')) }}" required>
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="valid_until" class="form-label">تاريخ الانتهاء <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('valid_until') is-invalid @enderror" 
                                       id="valid_until" name="valid_until" value="{{ old('valid_until', $offer->valid_until?->format('Y-m-d')) }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cta_text" class="form-label">نص زر الإجراء</label>
                                <input type="text" class="form-control @error('cta_text') is-invalid @enderror" 
                                       id="cta_text" name="cta_text" value="{{ old('cta_text', $offer->cta_text) }}" 
                                       placeholder="احجز الآن">
                                @error('cta_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="cta_url" class="form-label">رابط زر الإجراء</label>
                                <input type="url" class="form-control @error('cta_url') is-invalid @enderror" 
                                       id="cta_url" name="cta_url" value="{{ old('cta_url', $offer->cta_url) }}" 
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
                                       id="max_uses" name="max_uses" value="{{ old('max_uses', $offer->max_uses) }}" 
                                       min="1" placeholder="غير محدود">
                                @error('max_uses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">ترتيب العرض</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $offer->sort_order) }}" 
                                       min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">صورة العرض</label>
                            @if($offer->image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($offer->image) }}" alt="صورة العرض" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @endif
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
                                      id="terms_conditions" name="terms_conditions" rows="3">{{ old('terms_conditions', $offer->terms_conditions) }}</textarea>
                            @error('terms_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           {{ old('is_active', $offer->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        تفعيل العرض
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           {{ old('is_featured', $offer->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        عرض مميز
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ التغييرات
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
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات العرض
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $offer->current_uses ?? 0 }}</h4>
                                <small class="text-muted">مرات الاستخدام</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $offer->max_uses ?? '∞' }}</h4>
                            <small class="text-muted">الحد الأقصى</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small">
                        <p class="mb-1"><strong>تاريخ الإنشاء:</strong> {{ $offer->created_at?->format('Y-m-d H:i') }}</p>
                        <p class="mb-1"><strong>آخر تحديث:</strong> {{ $offer->updated_at?->format('Y-m-d H:i') }}</p>
                        <p class="mb-0">
                            <strong>الحالة:</strong> 
                            @if($offer->is_active)
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-secondary">غير نشط</span>
                            @endif
                            
                            @if($offer->is_featured)
                                <span class="badge bg-warning">مميز</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog me-2"></i>
                        إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleOfferStatus({{ $offer->id }})">
                            <i class="fas fa-power-off me-1"></i>
                            {{ $offer->is_active ? 'إلغاء التفعيل' : 'تفعيل العرض' }}
                        </button>
                        
                        <a href="{{ route('admin.landing-page.preview') }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            معاينة في الصفحة
                        </a>
                        
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteOffer({{ $offer->id }})">
                            <i class="fas fa-trash me-1"></i>
                            حذف العرض
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow mt-4">
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
                            تأكد من صحة التواريخ
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            راجع الوصف والشروط
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-check text-success me-1"></i>
                            اختبر الروابط قبل النشر
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

function toggleOfferStatus(offerId) {
    Swal.fire({
        title: 'تأكيد العملية',
        text: 'هل تريد تغيير حالة العرض؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'نعم، غير الحالة',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/landing-page/offers/${offerId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}

function deleteOffer(offerId) {
    Swal.fire({
        title: 'تأكيد الحذف',
        text: 'هل أنت متأكد من حذف هذا العرض؟ لا يمكن التراجع عن هذا الإجراء.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/landing-page/offers/${offerId}`;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(methodInput);
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// تفعيل حالة الخصم عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    const discountType = document.getElementById('discount_type');
    if (discountType.value === 'free') {
        document.getElementById('discount_value').disabled = true;
    }
});
</script>
@endsection