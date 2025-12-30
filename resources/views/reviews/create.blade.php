@extends('layouts.app')

@section('title', 'إضافة تقييم جديد')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-star-fill"></i>
                        إضافة تقييم جديد
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reviews.index') }}">تقييمات المرضى</a></li>
                            <li class="breadcrumb-item active">إضافة تقييم</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('reviews.index') }}" class="btn btn-outline-facebook">
                    <i class="bi bi-arrow-right"></i>
                    العودة للقائمة
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Doctor Information -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person-badge"></i>
                        معلومات الطبيب
                    </h6>
                </div>
                <div class="card-body text-center">
                    @if($doctor->photo)
                        <img src="{{ asset('storage/' . $doctor->photo) }}" 
                             alt="{{ $doctor->user->name }}" 
                             class="rounded-circle mb-3" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                        <div class="bg-facebook text-white rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                             style="width: 100px; height: 100px; font-size: 2rem;">
                            <i class="bi bi-person"></i>
                        </div>
                    @endif
                    
                    <h5 class="mb-1">{{ $doctor->user->name }}</h5>
                    <p class="text-muted mb-2">{{ $doctor->specialization }}</p>
                    
                    @if($doctor->average_rating > 0)
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <span class="me-2">{{ number_format($doctor->average_rating, 1) }}</span>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $doctor->average_rating ? '-fill text-warning' : ' text-muted' }}"></i>
                            @endfor
                            <span class="ms-2 text-muted">({{ $doctor->reviews_count }} تقييم)</span>
                        </div>
                    @endif
                    
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="fw-bold text-facebook">{{ $doctor->experience_years }}</div>
                                <small class="text-muted">سنوات الخبرة</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-facebook">{{ number_format($doctor->consultation_fee) }}</div>
                            <small class="text-muted">رسوم الكشف</small>
                        </div>
                    </div>
                </div>
            </div>

            @if($appointment)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-check"></i>
                            معلومات الموعد
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>رقم الموعد:</strong> #{{ $appointment->id }}
                        </div>
                        <div class="mb-2">
                            <strong>التاريخ:</strong> {{ $appointment->appointment_date->format('Y-m-d') }}
                        </div>
                        <div class="mb-2">
                            <strong>الوقت:</strong> {{ $appointment->appointment_time->format('H:i') }}
                        </div>
                        <div class="mb-2">
                            <strong>النوع:</strong> {{ $appointment->type_display }}
                        </div>
                        <div>
                            <strong>الحالة:</strong> {!! $appointment->status_badge !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Review Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-star"></i>
                        تقييم الطبيب
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('reviews.store') }}" method="POST" id="reviewForm">
                        @csrf
                        
                        <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
                        @if($appointment)
                            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        @endif

                        <!-- Overall Rating -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">التقييم العام <span class="text-danger">*</span></label>
                            <div class="rating-input mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <input type="radio" name="rating" value="{{ $i }}" id="rating{{ $i }}" 
                                           class="d-none" {{ old('rating') == $i ? 'checked' : '' }}>
                                    <label for="rating{{ $i }}" class="star-label" data-rating="{{ $i }}">
                                        <i class="bi bi-star-fill"></i>
                                    </label>
                                @endfor
                            </div>
                            <div class="rating-text text-muted small" id="ratingText"></div>
                            @error('rating')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Detailed Aspects Rating -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">تقييم تفصيلي (اختياري)</label>
                            <p class="text-muted small">قيم جوانب مختلفة من تجربتك مع الطبيب</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الاحترافية</label>
                                    <div class="aspect-rating" data-aspect="professionalism">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[professionalism]" value="{{ $i }}" 
                                                   id="professionalism{{ $i }}" class="d-none">
                                            <label for="professionalism{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">التواصل</label>
                                    <div class="aspect-rating" data-aspect="communication">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[communication]" value="{{ $i }}" 
                                                   id="communication{{ $i }}" class="d-none">
                                            <label for="communication{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الالتزام بالمواعيد</label>
                                    <div class="aspect-rating" data-aspect="punctuality">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[punctuality]" value="{{ $i }}" 
                                                   id="punctuality{{ $i }}" class="d-none">
                                            <label for="punctuality{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">النظافة</label>
                                    <div class="aspect-rating" data-aspect="cleanliness">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[cleanliness]" value="{{ $i }}" 
                                                   id="cleanliness{{ $i }}" class="d-none">
                                            <label for="cleanliness{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">فعالية العلاج</label>
                                    <div class="aspect-rating" data-aspect="effectiveness">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[effectiveness]" value="{{ $i }}" 
                                                   id="effectiveness{{ $i }}" class="d-none">
                                            <label for="effectiveness{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">تعامل الطاقم</label>
                                    <div class="aspect-rating" data-aspect="staff_behavior">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[staff_behavior]" value="{{ $i }}" 
                                                   id="staff_behavior{{ $i }}" class="d-none">
                                            <label for="staff_behavior{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">وقت الانتظار</label>
                                    <div class="aspect-rating" data-aspect="waiting_time">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[waiting_time]" value="{{ $i }}" 
                                                   id="waiting_time{{ $i }}" class="d-none">
                                            <label for="waiting_time{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">القيمة مقابل المال</label>
                                    <div class="aspect-rating" data-aspect="value_for_money">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating_aspects[value_for_money]" value="{{ $i }}" 
                                                   id="value_for_money{{ $i }}" class="d-none">
                                            <label for="value_for_money{{ $i }}" class="star-label-small">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <div class="mb-4">
                            <label for="review_text" class="form-label fw-bold">تعليقك على التجربة</label>
                            <textarea class="form-control" id="review_text" name="review_text" rows="5" 
                                      placeholder="شاركنا تجربتك مع الطبيب... (اختياري)">{{ old('review_text') }}</textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/2000 حرف
                            </div>
                            @error('review_text')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Anonymous Option -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" 
                                       value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_anonymous">
                                    تقييم مجهول (لن يظهر اسمك مع التقييم)
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-facebook">
                                <i class="bi bi-check-circle"></i>
                                إرسال التقييم
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    gap: 5px;
    font-size: 2rem;
}

.star-label {
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.star-label:hover,
.star-label.active {
    color: #ffc107;
}

.aspect-rating {
    display: flex;
    gap: 3px;
    font-size: 1.2rem;
}

.star-label-small {
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.star-label-small:hover,
.star-label-small.active {
    color: #ffc107;
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rating texts
    const ratingTexts = {
        1: 'سيء جداً',
        2: 'سيء',
        3: 'متوسط',
        4: 'جيد',
        5: 'ممتاز'
    };

    // Main rating functionality
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const ratingLabels = document.querySelectorAll('.star-label');
    const ratingText = document.getElementById('ratingText');

    function updateMainRating(rating) {
        ratingLabels.forEach((label, index) => {
            if (index < rating) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        });
        ratingText.textContent = ratingTexts[rating] || '';
    }

    ratingLabels.forEach((label, index) => {
        label.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInputs[rating - 1].checked = true;
            updateMainRating(rating);
        });

        label.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateMainRating(rating);
        });
    });

    document.querySelector('.rating-input').addEventListener('mouseleave', function() {
        const checkedRating = document.querySelector('input[name="rating"]:checked');
        if (checkedRating) {
            updateMainRating(parseInt(checkedRating.value));
        } else {
            updateMainRating(0);
        }
    });

    // Initialize main rating if already selected
    const checkedRating = document.querySelector('input[name="rating"]:checked');
    if (checkedRating) {
        updateMainRating(parseInt(checkedRating.value));
    }

    // Aspect ratings functionality
    document.querySelectorAll('.aspect-rating').forEach(aspectDiv => {
        const aspect = aspectDiv.dataset.aspect;
        const labels = aspectDiv.querySelectorAll('.star-label-small');
        const inputs = aspectDiv.querySelectorAll('input[type="radio"]');

        function updateAspectRating(rating) {
            labels.forEach((label, index) => {
                if (index < rating) {
                    label.classList.add('active');
                } else {
                    label.classList.remove('active');
                }
            });
        }

        labels.forEach((label, index) => {
            label.addEventListener('click', function() {
                inputs[index].checked = true;
                updateAspectRating(index + 1);
            });

            label.addEventListener('mouseenter', function() {
                updateAspectRating(index + 1);
            });
        });

        aspectDiv.addEventListener('mouseleave', function() {
            const checkedInput = aspectDiv.querySelector('input[type="radio"]:checked');
            if (checkedInput) {
                updateAspectRating(parseInt(checkedInput.value));
            } else {
                updateAspectRating(0);
            }
        });
    });

    // Character counter
    const reviewText = document.getElementById('review_text');
    const charCount = document.getElementById('charCount');

    function updateCharCount() {
        const count = reviewText.value.length;
        charCount.textContent = count;
        
        if (count > 2000) {
            charCount.classList.add('text-danger');
        } else if (count > 1800) {
            charCount.classList.add('text-warning');
            charCount.classList.remove('text-danger');
        } else {
            charCount.classList.remove('text-danger', 'text-warning');
        }
    }

    reviewText.addEventListener('input', updateCharCount);
    updateCharCount(); // Initialize

    // Form validation
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        const rating = document.querySelector('input[name="rating"]:checked');
        
        if (!rating) {
            e.preventDefault();
            alert('يرجى اختيار تقييم عام للطبيب');
            return;
        }

        // Check if low rating has comment
        const ratingValue = parseInt(rating.value);
        const reviewTextValue = reviewText.value.trim();
        
        if (ratingValue <= 2 && !reviewTextValue) {
            e.preventDefault();
            alert('يرجى كتابة تعليق عند إعطاء تقييم منخفض لمساعدتنا على التحسين');
            reviewText.focus();
            return;
        }

        // Check character limit
        if (reviewTextValue.length > 2000) {
            e.preventDefault();
            alert('تعليق التقييم لا يمكن أن يتجاوز 2000 حرف');
            reviewText.focus();
            return;
        }
    });
});
</script>
@endpush