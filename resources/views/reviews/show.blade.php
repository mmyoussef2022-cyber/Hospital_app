@extends('layouts.app')

@section('title', 'عرض التقييم')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-star-fill"></i>
                        عرض التقييم #{{ $review->id }}
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reviews.index') }}">تقييمات المرضى</a></li>
                            <li class="breadcrumb-item active">عرض التقييم</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    @can('reviews.edit')
                        @if($review->canBeEditedBy(auth()->user()))
                            <a href="{{ route('reviews.edit', $review) }}" class="btn btn-outline-facebook me-2">
                                <i class="bi bi-pencil"></i>
                                تعديل
                            </a>
                        @endif
                    @endcan
                    <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Review Details -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-star"></i>
                            تفاصيل التقييم
                        </h6>
                        <div>
                            {!! $review->status_badge !!}
                            @if($review->is_featured)
                                <span class="badge bg-warning ms-1">مميز</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Overall Rating -->
                    <div class="mb-4">
                        <h5 class="mb-2">التقييم العام</h5>
                        <div class="d-flex align-items-center mb-2">
                            <span class="fs-3 fw-bold text-facebook me-3">{{ $review->rating }}</span>
                            <div class="me-3">
                                {!! $review->rating_stars !!}
                            </div>
                            <span class="text-muted">من 5 نجوم</span>
                        </div>
                    </div>

                    <!-- Detailed Aspects -->
                    @if($review->rating_aspects && count($review->rating_aspects_display) > 0)
                        <div class="mb-4">
                            <h6 class="mb-3">التقييم التفصيلي</h6>
                            <div class="row">
                                @foreach($review->rating_aspects_display as $aspectName => $rating)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $aspectName }}</span>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $rating }}</span>
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $rating ? '-fill text-warning' : ' text-muted' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Review Text -->
                    @if($review->review_text)
                        <div class="mb-4">
                            <h6 class="mb-2">التعليق</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $review->review_text }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Review Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-2">معلومات التقييم</h6>
                            <ul class="list-unstyled">
                                <li><strong>المريض:</strong> {{ $review->patient_name }}</li>
                                <li><strong>تاريخ التقييم:</strong> {{ $review->created_at->format('Y-m-d H:i') }}</li>
                                <li><strong>منذ:</strong> {{ $review->time_ago }}</li>
                                @if($review->appointment)
                                    <li><strong>الموعد:</strong> #{{ $review->appointment->id }}</li>
                                @endif
                                <li><strong>تقييم مجهول:</strong> {{ $review->is_anonymous ? 'نعم' : 'لا' }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            @if($review->is_approved)
                                <h6 class="mb-2">معلومات الاعتماد</h6>
                                <ul class="list-unstyled">
                                    <li><strong>تاريخ الاعتماد:</strong> {{ $review->approved_at?->format('Y-m-d H:i') }}</li>
                                    @if($review->approvedBy)
                                        <li><strong>معتمد بواسطة:</strong> {{ $review->approvedBy->name }}</li>
                                    @endif
                                </ul>
                            @endif
                            
                            @if($review->admin_notes)
                                <h6 class="mb-2">ملاحظات الإدارة</h6>
                                <div class="bg-warning bg-opacity-10 p-2 rounded">
                                    <small>{{ $review->admin_notes }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Moderation Actions -->
            @can('reviews.moderate')
                @if($review->status === 'pending')
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-shield-check"></i>
                                إجراءات المراجعة
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="approveReview()">
                                    <i class="bi bi-check-circle"></i>
                                    اعتماد التقييم
                                </button>
                                <button type="button" class="btn btn-danger" onclick="rejectReview()">
                                    <i class="bi bi-x-circle"></i>
                                    رفض التقييم
                                </button>
                                <button type="button" class="btn btn-warning" onclick="hideReview()">
                                    <i class="bi bi-eye-slash"></i>
                                    إخفاء التقييم
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if($review->is_approved)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-star"></i>
                                إجراءات إضافية
                            </h6>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-{{ $review->is_featured ? 'outline-warning' : 'warning' }}" 
                                    onclick="toggleFeature()">
                                <i class="bi bi-star{{ $review->is_featured ? '' : '-fill' }}"></i>
                                {{ $review->is_featured ? 'إلغاء التمييز' : 'تمييز التقييم' }}
                            </button>
                        </div>
                    </div>
                @endif
            @endcan
        </div>

        <!-- Doctor Information -->
        <div class="col-md-4">
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
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <span class="me-2">{{ number_format($review->doctor->average_rating, 1) }}</span>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $review->doctor->average_rating ? '-fill text-warning' : ' text-muted' }}"></i>
                            @endfor
                            <span class="ms-2 text-muted">({{ $review->doctor->reviews_count }} تقييم)</span>
                        </div>
                    @endif
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('doctors.show', $review->doctor) }}" class="btn btn-outline-facebook">
                            <i class="bi bi-eye"></i>
                            عرض ملف الطبيب
                        </a>
                        <a href="{{ route('doctors.reviews', $review->doctor) }}" class="btn btn-outline-info">
                            <i class="bi bi-star"></i>
                            جميع تقييمات الطبيب
                        </a>
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
                        <div class="mb-3">
                            <strong>الحالة:</strong> {!! $review->appointment->status_badge !!}
                        </div>
                        <a href="{{ route('appointments.show', $review->appointment) }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye"></i>
                            عرض تفاصيل الموعد
                        </a>
                    </div>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart"></i>
                        إحصائيات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
@can('reviews.moderate')
<script>
// Approve review
function approveReview() {
    if (confirm('هل أنت متأكد من اعتماد هذا التقييم؟')) {
        fetch(`{{ route('reviews.approve', $review) }}`, {
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
                alert('حدث خطأ في اعتماد التقييم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في اعتماد التقييم');
        });
    }
}

// Reject review
function rejectReview() {
    const notes = prompt('سبب الرفض (اختياري):');
    if (notes !== null) {
        fetch(`{{ route('reviews.reject', $review) }}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ admin_notes: notes })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ في رفض التقييم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في رفض التقييم');
        });
    }
}

// Hide review
function hideReview() {
    const notes = prompt('سبب الإخفاء (اختياري):');
    if (notes !== null) {
        fetch(`{{ route('reviews.hide', $review) }}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ admin_notes: notes })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ في إخفاء التقييم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في إخفاء التقييم');
        });
    }
}

// Toggle feature
function toggleFeature() {
    const action = {{ $review->is_featured ? 'false' : 'true' }};
    const message = action ? 'تمييز هذا التقييم' : 'إلغاء تمييز هذا التقييم';
    
    if (confirm(`هل أنت متأكد من ${message}؟`)) {
        fetch(`{{ route('reviews.toggle-feature', $review) }}`, {
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
                alert('حدث خطأ في تغيير حالة التمييز');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تغيير حالة التمييز');
        });
    }
}
</script>
@endcan
@endpush