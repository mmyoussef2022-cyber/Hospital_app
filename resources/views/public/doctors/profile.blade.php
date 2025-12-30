@extends('layouts.public')

@section('title', 'د. ' . $doctor->name . ' - مركز محمد يوسف لطب الأسنان')
@section('meta-description', 'تعرف على د. ' . $doctor->name . ' - ' . $doctor->specialization . ' في مركز محمد يوسف لطب الأسنان واحجز موعدك الآن')

@section('content')
<!-- Doctor Profile Header -->
<section class="py-5 bg-gradient-primary text-white" style="margin-top: 80px; background: var(--gradient-primary);">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('public.index') }}" class="text-white-50">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.doctors.index') }}" class="text-white-50">الأطباء</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">د. {{ $doctor->name }}</li>
            </ol>
        </nav>
        
        <div class="row align-items-center">
            <div class="col-lg-3 text-center mb-4 mb-lg-0" data-aos="fade-right">
                <div class="doctor-profile-image">
                    @if($doctor->photo)
                        <img src="{{ asset('storage/' . $doctor->photo) }}" alt="د. {{ $doctor->name }}" 
                             class="rounded-circle border border-white border-4 shadow-lg" 
                             style="width: 200px; height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center border border-white border-4 shadow-lg mx-auto" 
                             style="width: 200px; height: 200px;">
                            <i class="fas fa-user-md text-white" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-up">
                <h1 class="display-5 mb-2">د. {{ $doctor->name }}</h1>
                <h4 class="mb-3 text-white-75">{{ $doctor->specialization }}</h4>
                
                @if($doctor->department)
                <p class="mb-3">
                    <i class="fas fa-hospital me-2"></i>
                    {{ $doctor->department->name }}
                </p>
                @endif
                
                <div class="row mb-3">
                    <div class="col-sm-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-graduation-cap me-2"></i>
                            <span>{{ $doctor->years_of_experience }} سنة خبرة</span>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-star me-2 text-warning"></i>
                            <span>{{ number_format($doctorStats['average_rating'], 1) }} ({{ $doctorStats['total_reviews'] }} تقييم)</span>
                        </div>
                    </div>
                </div>
                
                @if($doctor->bio)
                <p class="lead mb-4">{{ $doctor->bio }}</p>
                @endif
            </div>
            
            <div class="col-lg-3 text-center" data-aos="fade-left">
                <div class="bg-white bg-opacity-10 p-4 rounded-3 backdrop-blur">
                    <h5 class="mb-3">احجز موعدك الآن</h5>
                    <a href="{{ route('public.booking.form', $doctor->id) }}" class="btn btn-success btn-lg w-100 mb-3">
                        <i class="fas fa-calendar-plus"></i>
                        حجز موعد
                    </a>
                    <p class="small mb-0">
                        <i class="fas fa-clock me-1"></i>
                        متاح للحجز الفوري
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctor Statistics -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-6 mb-3 mb-lg-0" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-white p-3 rounded-3 shadow-sm">
                    <h4 class="text-primary mb-1">{{ $doctorStats['total_appointments'] }}</h4>
                    <small class="text-muted">إجمالي المواعيد</small>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3 mb-lg-0" data-aos="fade-up" data-aos-delay="200">
                <div class="bg-white p-3 rounded-3 shadow-sm">
                    <h4 class="text-success mb-1">{{ $doctorStats['completed_appointments'] }}</h4>
                    <small class="text-muted">مواعيد مكتملة</small>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3 mb-lg-0" data-aos="fade-up" data-aos-delay="300">
                <div class="bg-white p-3 rounded-3 shadow-sm">
                    <h4 class="text-warning mb-1">{{ number_format($doctorStats['average_rating'], 1) }}</h4>
                    <small class="text-muted">متوسط التقييم</small>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3 mb-lg-0" data-aos="fade-up" data-aos-delay="400">
                <div class="bg-white p-3 rounded-3 shadow-sm">
                    <h4 class="text-info mb-1">{{ $doctor->services->count() }}</h4>
                    <small class="text-muted">الخدمات المتاحة</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Left Column - Doctor Info -->
            <div class="col-lg-8">
                <!-- About Doctor -->
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-md me-2 text-primary"></i>
                            نبذة عن الطبيب
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($doctor->bio)
                            <p class="mb-3">{{ $doctor->bio }}</p>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">التخصص</h6>
                                <p class="mb-0">{{ $doctor->specialization }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">سنوات الخبرة</h6>
                                <p class="mb-0">{{ $doctor->years_of_experience }} سنة</p>
                            </div>
                            @if($doctor->license_number)
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">رقم الترخيص</h6>
                                <p class="mb-0">{{ $doctor->license_number }}</p>
                            </div>
                            @endif
                            @if($doctor->phone)
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">الهاتف</h6>
                                <p class="mb-0">{{ $doctor->phone }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Certificates -->
                @if($doctor->certificates->count() > 0)
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-certificate me-2 text-primary"></i>
                            الشهادات والمؤهلات
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($doctor->certificates as $certificate)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-2">{{ $certificate->name }}</h6>
                                    <p class="text-muted small mb-2">{{ $certificate->issuing_organization }}</p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $certificate->issue_date ? \Carbon\Carbon::parse($certificate->issue_date)->format('Y') : 'غير محدد' }}
                                    </p>
                                    @if($certificate->is_verified)
                                        <span class="badge bg-success mt-2">
                                            <i class="fas fa-check-circle"></i> معتمدة
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Services -->
                @if($doctor->services->count() > 0)
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-stethoscope me-2 text-primary"></i>
                            الخدمات المتاحة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($doctor->services as $service)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="text-primary mb-0">{{ $service->name }}</h6>
                                        @if($service->price)
                                            <span class="badge bg-success">{{ number_format($service->price) }} ريال</span>
                                        @endif
                                    </div>
                                    @if($service->description)
                                        <p class="text-muted small mb-2">{{ Str::limit($service->description, 100) }}</p>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $service->duration ?? 30 }} دقيقة
                                        </small>
                                        <a href="{{ route('public.booking.form', $doctor->id) }}" class="btn btn-outline-primary btn-sm">
                                            احجز الآن
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Reviews -->
                @if($doctor->reviews->count() > 0)
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2 text-primary"></i>
                            تقييمات المرضى ({{ $doctor->reviews->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($doctor->reviews->take(5) as $review)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $review->patient->name ?? 'مريض' }}</h6>
                                    <div class="rating-stars text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                            @if($review->comment)
                                <p class="mb-0 text-muted">{{ $review->comment }}</p>
                            @endif
                        </div>
                        @endforeach
                        
                        @if($doctor->reviews->count() > 5)
                        <div class="text-center">
                            <button class="btn btn-outline-primary" onclick="loadMoreReviews()">
                                عرض المزيد من التقييمات
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Booking & Schedule -->
            <div class="col-lg-4">
                <!-- Quick Booking -->
                <div class="card mb-4 sticky-top" style="top: 100px;" data-aos="fade-left">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-plus me-2"></i>
                            حجز موعد سريع
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-check" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="mt-2 mb-0">متاح للحجز الفوري</h6>
                        </div>
                        
                        <a href="{{ route('public.booking.form', $doctor->id) }}" class="btn btn-success btn-lg w-100 mb-3">
                            <i class="fas fa-calendar-plus"></i>
                            احجز موعدك الآن
                        </a>
                        
                        <div class="text-center">
                            <p class="small text-muted mb-2">أو اتصل مباشرة</p>
                            <a href="tel:+966123456789" class="btn btn-outline-primary w-100">
                                <i class="fas fa-phone"></i>
                                966123456789+
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Available Times -->
                @if(count($availableSlots) > 0)
                <div class="card mb-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            المواعيد المتاحة
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach(array_slice($availableSlots, 0, 3) as $date => $daySlots)
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">{{ \Carbon\Carbon::parse($date)->format('l, d M') }}</h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($daySlots['slots'] as $period => $times)
                                    @foreach(array_slice($times, 0, 3) as $time)
                                        <span class="badge bg-light text-dark border">{{ $time }}</span>
                                    @endforeach
                                @endforeach
                                @if(count($daySlots['slots']['morning'] ?? []) + count($daySlots['slots']['evening'] ?? []) > 3)
                                    <span class="badge bg-secondary">+{{ count($daySlots['slots']['morning'] ?? []) + count($daySlots['slots']['evening'] ?? []) - 3 }}</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        
                        <div class="text-center">
                            <a href="{{ route('public.booking.form', $doctor->id) }}" class="btn btn-outline-primary btn-sm">
                                عرض جميع المواعيد
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Contact Info -->
                <div class="card" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            معلومات إضافية
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">العيادة</h6>
                            <p class="mb-0 small">مركز محمد يوسف لطب الأسنان</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">الموقع</h6>
                            <p class="mb-0 small">شارع الملك فهد، الرياض</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">ساعات العمل</h6>
                            <p class="mb-0 small">
                                السبت - الخميس: 8:00 ص - 10:00 م<br>
                                الجمعة: 2:00 م - 10:00 م
                            </p>
                        </div>
                        
                        <div class="d-grid">
                            <a href="#" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-map-marker-alt"></i>
                                عرض على الخريطة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Doctors -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h3>أطباء آخرون في نفس التخصص</h3>
            <p class="text-muted">قد تكون مهتماً بهؤلاء الأطباء أيضاً</p>
        </div>
        
        <div class="row">
            @php
                $relatedDoctors = \App\Models\Doctor::where('specialization', $doctor->specialization)
                    ->where('id', '!=', $doctor->id)
                    ->where('is_active', true)
                    ->where('is_available', true)
                    ->with(['department', 'services', 'reviews'])
                    ->limit(3)
                    ->get();
            @endphp
            
            @foreach($relatedDoctors as $relatedDoctor)
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="doctor-card">
                    <div class="doctor-image d-flex align-items-center justify-content-center">
                        @if($relatedDoctor->photo)
                            <img src="{{ asset('storage/' . $relatedDoctor->photo) }}" alt="{{ $relatedDoctor->name }}" class="doctor-image">
                        @else
                            <i class="fas fa-user-md text-white" style="font-size: 4rem;"></i>
                        @endif
                    </div>
                    <div class="doctor-info">
                        <h5 class="doctor-name">{{ $relatedDoctor->name }}</h5>
                        <p class="doctor-specialty">{{ $relatedDoctor->specialization }}</p>
                        <div class="doctor-rating">
                            <div class="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                            <span class="rating-text">({{ $relatedDoctor->reviews->count() }} تقييم)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('public.doctors.profile', $relatedDoctor->id) }}" class="btn btn-outline-facebook btn-sm flex-fill">
                                عرض الملف
                            </a>
                            <a href="{{ route('public.booking.form', $relatedDoctor->id) }}" class="btn btn-facebook btn-sm flex-fill">
                                احجز موعد
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function loadMoreReviews() {
        // Implementation for loading more reviews via AJAX
        alert('سيتم تحميل المزيد من التقييمات قريباً');
    }

    // Smooth scroll for booking buttons
    document.addEventListener('DOMContentLoaded', function() {
        const bookingButtons = document.querySelectorAll('a[href*="booking"]');
        
        bookingButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Add loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="loading"></span> جاري التحويل...';
                this.disabled = true;
                
                // Restore after a short delay (for UX)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 1000);
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .sticky-top {
        position: sticky !important;
    }
    
    .rating-stars {
        color: #FFD700;
    }
    
    .rating-stars .far {
        color: #E5E7EB;
    }
    
    .backdrop-blur {
        backdrop-filter: blur(10px);
    }
    
    .doctor-profile-image img,
    .doctor-profile-image > div {
        transition: transform 0.3s ease;
    }
    
    .doctor-profile-image:hover img,
    .doctor-profile-image:hover > div {
        transform: scale(1.05);
    }
    
    @media (max-width: 991px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
    }
</style>
@endpush