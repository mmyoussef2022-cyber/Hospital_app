@extends('layouts.app')

@section('title', 'تعديل التقييم')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-pencil-square"></i>
                        تعديل التقييم #{{ $review->id }}
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reviews.index') }}">تقييمات المرضى</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reviews.show', $review) }}">عرض التقييم</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('reviews.show', $review) }}" class="btn btn-outline-info me-2">
                        <i class="bi bi-eye"></i>
                        عرض التقييم
                    </a>
                    <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($review->is_approved)
        <div class="alert alert-warning mb-4">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>تنبيه:</strong> هذا التقييم معتمد بالفعل. أي تعديل سيتطلب مراجعة إدارية جديدة قبل النشر.
        </div>
    @endif

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
                    @if($review->doctor->photo)
                        <img src="{{ asset('storage/' . $review->doctor->photo) }}" 
                             alt="{{ $review->doctor->user->name }}" 
                             class="rounded-circle mb-3" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                        <div class="bg-facebook text-white rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                             style="width: 100px; height: 100px; font-size: 2rem;">
                            <i class="bi bi-person"></i>
                        </div>
                    @endif
                    
                    <h5 class="mb-1">{{ $review->doctor->user->name }}</h5>
                    <p class="text-muted mb-2">{{ $review->doctor->specialization }}</p>
                    
                    @if($review->doctor->average_rating > 0)
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <span class="me-2">{{ number_format($review->doctor->average_rating, 1) }}</span>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $review->doctor->average_rating ? '-fill text-warning' : ' text-muted' }}"></i>
                            @endfor
                            <span class="ms-2 text-muted">({{ $review->doctor->reviews_count }} تقييم)</span>
                        </div>
                    @endif
                    
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="fw-bold text-facebook">{{ $review->doctor->experience_years }}</div>
                                <small class="text-muted">سنوات الخبرة</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-facebook">{{ number_format($review->doctor->consultation_fee) }}</div>
                            <small class="text-muted">رسوم الكشف</small>
                        </div>
                    </div>
                </div>
            </div>

            @if($review->appointment)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-check"></i>
                            معلومات الموعد
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>رقم الموعد:</strong> #{{ $review->appointment->id }}
                        </div>
                        <div class="mb-2">
                            <strong>التاريخ:</strong> {{ $review->appointment->appointment_date->format('Y-m-d') }}
                        </div>
                        <div class="mb-2">
                            <strong>الوقت:</strong> {{ $review->appointment->appointment_time->format('H:i') }}
                        </div>
                        <div class="mb-2">
                            <strong>النوع:</strong> {{ $review->appointment->type_display }}
                        </div>
                        <div>
                            <strong>الحالة:</strong> {!! $review->appointment->status_badge !!}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Current Review Info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i>
                        معلومات التقييم الحالي
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>تاريخ الإنشاء:</strong> {{ $review->created_at->format('Y-m-d H:i') }}
                    </div>
                    <div class="mb-2">
                        <strong>آخر تحديث:</strong> {{ $review->updated_at->format('Y-m-d H:i') }}
                    </div>
                    <div class="mb-2">
                        <strong>الحالة:</strong> {!! $review->status_badge !!}
                    </div>
                    @if($review->is_featured)
                        <div class="mb-2">
                            <span class="badge bg-warning">تقييم مميز</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-pencil"></i>
                        تعديل التقييم
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('reviews.update', $review) }}" method="POST" id="editReviewForm">
                        @csrf
                        @method('PUT')
                        
                        <input type="hidden" name="doctor_id" value="{{ $review->doctor_id }}">
                        @if($review->appointment_id)
                            <input type="hidden" name="appointment_id" value="{{ $review->appointment_id }}">
                        @endif

                        <!-- Overall Rating -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">التقييم العام <span class="text-danger">*</span></label>
                            <div class="rating-input mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <input type="radio" name="rating" value="{{ $i }}" id="rating{{ $i }}" 
                                           class="d-none" {{ old('rating', $review->rating) == $i ? 'checked' : '' }}>
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
                                @php
                                    $aspects = [
                                        'professionalism' => 'الاحترافية',
                                        'communication' => 'التواصل',
                                        'punctuality' => 'الالتزام بالمواعيد',
                                        'cleanliness' => 'النظافة',
                                        'effectiveness' => 'فعالية العلاج',
                                        'staff_behavior' => 'تعامل الطاقم',
                                        'waiting_time' => 'وقت الانتظار',
                                        'value_for_money' => 'القيمة مقابل المال'
                                    ];
                                    $currentAspects = old('rating_aspects', $review->rating_aspects ?? []);
                                @endphp
                                
                                @foreach($aspects as $aspectKey => $aspectName)
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ $aspectName }}</label>
                                        <div class="aspect-rating" data-aspect="{{ $aspectKey }}">
                                            @for($i = 1; $i <= 5; $i++)
                                                <input type="radio" name="rating_aspects[{{ $aspectKey }}]" value="{{ $i }}" 
                                                       id="{{ $aspectKey }}{{ $i }}" class="d-none"
                                                       {{ isset($currentAspects[$aspectKey]) && $currentAspects[$aspectKey] == $i ? 'checked' : '' }}>
                                                <label for="{{ $aspectKey }}{{ $i }}" class="star-label-small">
                                                    <i class="bi bi-star-fill"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Review Text -->
                        <div class="mb-4">
                            <label for="review_text" class="form-label fw-bold">تعليقك على التجربة</label>
                            <textarea class="form-control" id="review_text" name="review_text" rows="5" 
                                      placeholder="شاركنا تجربتك مع الطبيب... (اختياري)">{{ old('review_text', $review->review_text) }}</textarea>
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
                                       value="1" {{ old('is_anonymous', $review->is_anonymous) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_anonymous">
                                    تقييم مجهول (لن يظهر اسمك مع التقييم)
                                </label>
                            </div>
                        </div>

                        <!-- Changes Detection -->
                        <div class="alert alert-info" id="changesAlert" style="display: none;">
                            <i class="bi bi-info-circle"></i>
                            <strong>تم اكتشاف تغييرات:</strong> سيتم إعادة تعيين حالة التقييم إلى "في انتظار المراجعة" بعد الحفظ.
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('reviews.show', $review) }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-x-circle"></i>
                                    إلغاء
                                </a>
                                <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    إعادة تعيين
                                </button>
                            </div>
                            <button type="submit" class="btn btn-facebook">
                                <i class="bi bi-check-circle"></i>
                                حفظ التغييرات
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
    // Store original values for change detection
    const originalValues = {
        rating: {{ $review->rating }},
        review_text: @json($review->review_text ?? ''),
        rating_aspects: @json($review->rating_aspects ?? {}),
        is_anonymous: {{ $review->is_anonymous ? 'true' : 'false' }}
    };

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
            checkForChanges();
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

    // Initialize main rating
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
                checkForChanges();
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

        // Initialize aspect rating
        const checkedAspectInput = aspectDiv.querySelector('input[type="radio"]:checked');
        if (checkedAspectInput) {
            updateAspectRating(parseInt(checkedAspectInput.value));
        }
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

    reviewText.addEventListener('input', function() {
        updateCharCount();
        checkForChanges();
    });
    updateCharCount(); // Initialize

    // Anonymous checkbox change detection
    document.getElementById('is_anonymous').addEventListener('change', checkForChanges);

    // Change detection
    function checkForChanges() {
        const currentRating = document.querySelector('input[name="rating"]:checked')?.value;
        const currentReviewText = reviewText.value;
        const currentIsAnonymous = document.getElementById('is_anonymous').checked;
        
        // Get current aspect ratings
        const currentAspects = {};
        document.querySelectorAll('.aspect-rating').forEach(aspectDiv => {
            const aspect = aspectDiv.dataset.aspect;
            const checkedInput = aspectDiv.querySelector('input[type="radio"]:checked');
            if (checkedInput) {
                currentAspects[aspect] = parseInt(checkedInput.value);
            }
        });

        // Check for changes
        const hasChanges = (
            currentRating != originalValues.rating ||
            currentReviewText !== originalValues.review_text ||
            currentIsAnonymous !== originalValues.is_anonymous ||
            JSON.stringify(currentAspects) !== JSON.stringify(originalValues.rating_aspects)
        );

        const changesAlert = document.getElementById('changesAlert');
        if (hasChanges) {
            changesAlert.style.display = 'block';
        } else {
            changesAlert.style.display = 'none';
        }
    }

    // Reset form function
    window.resetForm = function() {
        if (confirm('هل أنت متأكد من إعادة تعيين النموذج؟ ستفقد جميع التغييرات غير المحفوظة.')) {
            // Reset main rating
            const originalRatingInput = document.querySelector(`input[name="rating"][value="${originalValues.rating}"]`);
            if (originalRatingInput) {
                originalRatingInput.checked = true;
                updateMainRating(originalValues.rating);
            }

            // Reset review text
            reviewText.value = originalValues.review_text;
            updateCharCount();

            // Reset anonymous checkbox
            document.getElementById('is_anonymous').checked = originalValues.is_anonymous;

            // Reset aspect ratings
            document.querySelectorAll('.aspect-rating').forEach(aspectDiv => {
                const aspect = aspectDiv.dataset.aspect;
                const inputs = aspectDiv.querySelectorAll('input[type="radio"]');
                
                // Uncheck all inputs first
                inputs.forEach(input => input.checked = false);
                
                // Check the original value if exists
                if (originalValues.rating_aspects[aspect]) {
                    const originalInput = aspectDiv.querySelector(`input[value="${originalValues.rating_aspects[aspect]}"]`);
                    if (originalInput) {
                        originalInput.checked = true;
                        const labels = aspectDiv.querySelectorAll('.star-label-small');
                        labels.forEach((label, index) => {
                            if (index < originalValues.rating_aspects[aspect]) {
                                label.classList.add('active');
                            } else {
                                label.classList.remove('active');
                            }
                        });
                    }
                } else {
                    // Reset visual state
                    const labels = aspectDiv.querySelectorAll('.star-label-small');
                    labels.forEach(label => label.classList.remove('active'));
                }
            });

            checkForChanges();
        }
    };

    // Form validation
    document.getElementById('editReviewForm').addEventListener('submit', function(e) {
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

    // Initial change check
    checkForChanges();
});
</script>
@endpush