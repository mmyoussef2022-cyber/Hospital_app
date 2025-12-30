@extends('layouts.app')

@section('title', 'تقييمات الطبيب - ' . $doctor->user->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-star-fill"></i>
                        تقييمات الطبيب
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctors.index') }}">الأطباء</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctors.show', $doctor) }}">{{ $doctor->user->name }}</a></li>
                            <li class="breadcrumb-item active">التقييمات</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-facebook me-2">
                        <i class="bi bi-person-badge"></i>
                        ملف الطبيب
                    </a>
                    <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list"></i>
                        جميع التقييمات
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Doctor Info & Statistics -->
        <div class="col-md-4 mb-4">
            <!-- Doctor Card -->
            <div class="card">
                <div class="card-body text-center">
                    @if($doctor->photo)
                        <img src="{{ asset('storage/' . $doctor->photo) }}" 
                             alt="{{ $doctor->user->name }}" 
                             class="rounded-circle mb-3" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="bg-facebook text-white rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                             style="width: 120px; height: 120px; font-size: 3rem;">
                            <i class="bi bi-person"></i>
                        </div>
                    @endif
                    
                    <h4 class="mb-1">{{ $doctor->user->name }}</h4>
                    <p class="text-muted mb-3">{{ $doctor->specialization }}</p>
                    
                    @if($doctor->average_rating > 0)
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <span class="fs-4 fw-bold text-facebook me-2">{{ number_format($doctor->average_rating, 1) }}</span>
                            <div class="me-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $doctor->average_rating ? '-fill text-warning' : ' text-muted' }}" style="font-size: 1.2rem;"></i>
                                @endfor
                            </div>
                            <span class="text-muted">({{ $doctor->reviews_count }} تقييم)</span>
                        </div>
                    @else
                        <p class="text-muted">لا توجد تقييمات بعد</p>
                    @endif
                </div>
            </div>

            <!-- Rating Distribution -->
            @if(count($ratingDistribution) > 0 && array_sum($ratingDistribution) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-bar-chart"></i>
                            توزيع التقييمات
                        </h6>
                    </div>
                    <div class="card-body">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $count = $ratingDistribution[$i] ?? 0;
                                $total = array_sum($ratingDistribution);
                                $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                            @endphp
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2" style="width: 20px;">{{ $i }}</span>
                                <i class="bi bi-star-fill text-warning me-2"></i>
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    <div class="progress-bar bg-facebook" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-muted small" style="width: 30px;">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif

            <!-- Aspect Ratings -->
            @if(count($aspectRatings) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up"></i>
                            تقييم الجوانب
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $aspectNames = [
                                'professionalism' => 'الاحترافية',
                                'communication' => 'التواصل',
                                'punctuality' => 'الالتزام بالمواعيد',
                                'cleanliness' => 'النظافة',
                                'effectiveness' => 'فعالية العلاج',
                                'staff_behavior' => 'تعامل الطاقم',
                                'waiting_time' => 'وقت الانتظار',
                                'value_for_money' => 'القيمة مقابل المال'
                            ];
                        @endphp
                        
                        @foreach($aspectRatings as $aspect => $rating)
                            @if(isset($aspectNames[$aspect]))
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small">{{ $aspectNames[$aspect] }}</span>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 small fw-bold">{{ number_format($rating, 1) }}</span>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $rating ? '-fill text-warning' : ' text-muted' }}" style="font-size: 0.8rem;"></i>
                                        @endfor
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i>
                        معلومات إضافية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
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
        </div>

        <!-- Reviews List -->
        <div class="col-md-8">
            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel"></i>
                        تصفية التقييمات
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('doctors.reviews', $doctor) }}">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="rating" class="form-label">التقييم</label>
                                <select class="form-select" id="rating" name="rating" onchange="this.form.submit()">
                                    <option value="">جميع التقييمات</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                            {{ $i }} نجوم
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <a href="{{ route('doctors.reviews', $doctor) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reviews -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-chat-quote"></i>
                        التقييمات ({{ $reviews->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($reviews->count() > 0)
                        @foreach($reviews as $review)
                            <div class="review-item border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if(!$review->is_anonymous && $review->patient->user->avatar)
                                                <img src="{{ asset('storage/' . $review->patient->user->avatar) }}" 
                                                     alt="{{ $review->patient_name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $review->patient_name }}</h6>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $review->rating }}</span>
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill text-warning' : ' text-muted' }}"></i>
                                                @endfor
                                                @if($review->is_featured)
                                                    <span class="badge bg-warning ms-2">مميز</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $review->time_ago }}</small>
                                </div>

                                @if($review->review_text)
                                    <p class="mb-2">{{ $review->review_text }}</p>
                                @endif

                                @if($review->rating_aspects && count($review->rating_aspects_display) > 0)
                                    <div class="row">
                                        @foreach($review->rating_aspects_display as $aspectName => $rating)
                                            <div class="col-md-6 mb-1">
                                                <small class="text-muted">
                                                    <strong>{{ $aspectName }}:</strong>
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="bi bi-star{{ $i <= $rating ? '-fill text-warning' : ' text-muted' }}" style="font-size: 0.7rem;"></i>
                                                    @endfor
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @can('reviews.view')
                                    <div class="mt-2">
                                        <a href="{{ route('reviews.show', $review) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                            عرض التفاصيل
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $reviews->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-chat-quote text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">لا توجد تقييمات</h5>
                            @if(request('rating'))
                                <p class="text-muted">لا توجد تقييمات بـ {{ request('rating') }} نجوم لهذا الطبيب</p>
                                <a href="{{ route('doctors.reviews', $doctor) }}" class="btn btn-outline-facebook">
                                    عرض جميع التقييمات
                                </a>
                            @else
                                <p class="text-muted">لم يتم تقييم هذا الطبيب بعد</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.review-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
</style>
@endsection