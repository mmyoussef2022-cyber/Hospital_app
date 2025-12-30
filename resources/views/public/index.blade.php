@extends('layouts.public')

@section('title', 'مركز محمد يوسف لطب الأسنان - الصفحة الرئيسية')
@section('meta-description', 'مركز محمد يوسف لطب الأسنان يقدم أفضل الخدمات الطبية المتخصصة مع فريق من أمهر الأطباء')
@section('meta-keywords', 'طب الأسنان, مستشفى, عيادة, أطباء, حجز موعد, الرياض')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="hero-content">
                    <h1 class="hero-title">
                        مرحباً بكم في مركز محمد يوسف لطب الأسنان
                    </h1>
                    <p class="hero-subtitle">
                        نقدم أفضل الخدمات الطبية المتخصصة مع فريق من أمهر الأطباء والمتخصصين في بيئة طبية متطورة وآمنة
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('public.booking.form') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-calendar-plus"></i>
                            احجز موعدك الآن
                        </a>
                        <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-facebook btn-lg">
                            <i class="fas fa-user-md"></i>
                            تصفح الأطباء
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="text-center">
                    <div class="hero-image-placeholder" style="background: rgba(255,255,255,0.1); border-radius: 20px; padding: 3rem; backdrop-filter: blur(10px);">
                        <i class="fas fa-hospital-alt" style="font-size: 8rem; color: rgba(255,255,255,0.8);"></i>
                        <h3 class="text-white mt-3">خدمات طبية متميزة</h3>
                        <p class="text-white-50">أحدث التقنيات الطبية</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card">
                    <div class="stat-number">{{ $statistics['doctors_count'] }}+</div>
                    <div class="stat-label">طبيب متخصص</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card">
                    <div class="stat-number">{{ $statistics['departments_count'] }}+</div>
                    <div class="stat-label">قسم طبي</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card">
                    <div class="stat-number">{{ $statistics['services_count'] }}+</div>
                    <div class="stat-label">خدمة طبية</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-card">
                    <div class="stat-number">{{ $statistics['satisfied_patients'] }}+</div>
                    <div class="stat-label">مريض راضٍ</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Doctors Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-3">أطباؤنا المتميزون</h2>
            <p class="lead text-muted">فريق من أمهر الأطباء والمتخصصين في مختلف المجالات الطبية</p>
        </div>
        
        <div class="row">
            @foreach($featuredDoctors as $doctor)
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="doctor-card">
                    <div class="doctor-image d-flex align-items-center justify-content-center">
                        @if($doctor->photo)
                            <img src="{{ asset('storage/' . $doctor->photo) }}" alt="{{ $doctor->name }}" class="doctor-image">
                        @else
                            <i class="fas fa-user-md text-white" style="font-size: 4rem;"></i>
                        @endif
                    </div>
                    <div class="doctor-info">
                        <h5 class="doctor-name">{{ $doctor->name }}</h5>
                        <p class="doctor-specialty">{{ $doctor->specialization }}</p>
                        <div class="doctor-rating">
                            <div class="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                            <span class="rating-text">({{ $doctor->reviews_count ?? 0 }} تقييم)</span>
                        </div>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-graduation-cap me-1"></i>
                            {{ $doctor->years_of_experience }} سنة خبرة
                        </p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('public.doctors.profile', $doctor->id) }}" class="btn btn-outline-facebook btn-sm flex-fill">
                                <i class="fas fa-eye"></i>
                                عرض الملف
                            </a>
                            <a href="{{ route('public.booking.form', $doctor->id) }}" class="btn btn-facebook btn-sm flex-fill">
                                <i class="fas fa-calendar-plus"></i>
                                احجز موعد
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="{{ route('public.doctors.index') }}" class="btn btn-facebook btn-lg">
                <i class="fas fa-users"></i>
                عرض جميع الأطباء
            </a>
        </div>
    </div>
</section>

<!-- Departments Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-3">أقسامنا الطبية</h2>
            <p class="lead text-muted">تخصصات طبية شاملة تلبي جميع احتياجاتكم الصحية</p>
        </div>
        
        <div class="row">
            @foreach($departments as $department)
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-stethoscope text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">{{ $department->name }}</h5>
                        <p class="card-text text-muted">{{ $department->description ?? 'قسم طبي متخصص يقدم أفضل الخدمات الطبية' }}</p>
                        <div class="mb-3">
                            <span class="badge bg-primary">{{ $department->doctors_count }} طبيب</span>
                        </div>
                        <a href="{{ route('public.doctors.index', ['department' => $department->id]) }}" class="btn btn-outline-facebook">
                            <i class="fas fa-arrow-left"></i>
                            عرض الأطباء
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5" id="services">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-3">خدماتنا الطبية</h2>
            <p class="lead text-muted">مجموعة شاملة من الخدمات الطبية المتخصصة</p>
        </div>
        
        <div class="row">
            @foreach($popularServices->take(6) as $service)
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-tooth"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">{{ $service->name }}</h5>
                                <p class="card-text text-muted small">{{ Str::limit($service->description, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold">
                                        @if($service->price)
                                            {{ number_format($service->price) }} ريال
                                        @else
                                            حسب الحالة
                                        @endif
                                    </span>
                                    <small class="text-muted">
                                        {{ $service->duration ?? 30 }} دقيقة
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="{{ route('public.doctors.index') }}" class="btn btn-facebook btn-lg">
                <i class="fas fa-list"></i>
                عرض جميع الخدمات
            </a>
        </div>
    </div>
</section>

<!-- Quick Booking Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="display-4 mb-3">احجز موعدك بسهولة</h2>
                <p class="lead mb-4">
                    نظام حجز مواعيد متطور يتيح لك حجز موعدك مع الطبيب المناسب في الوقت الذي يناسبك
                </p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check-circle me-2"></i> حجز فوري ومؤكد</li>
                    <li class="mb-2"><i class="fas fa-check-circle me-2"></i> اختيار الطبيب والوقت المناسب</li>
                    <li class="mb-2"><i class="fas fa-check-circle me-2"></i> تذكير بالموعد عبر الرسائل</li>
                    <li class="mb-2"><i class="fas fa-check-circle me-2"></i> إمكانية إعادة الجدولة</li>
                </ul>
            </div>
            <div class="col-lg-4 text-center" data-aos="fade-left">
                <div class="bg-white text-dark p-4 rounded-3 shadow">
                    <h4 class="mb-3">احجز الآن</h4>
                    <p class="text-muted mb-4">ابدأ رحلتك نحو صحة أفضل</p>
                    <a href="{{ route('public.booking.form') }}" class="btn btn-success btn-lg w-100 mb-3">
                        <i class="fas fa-calendar-plus"></i>
                        حجز موعد جديد
                    </a>
                    <p class="small text-muted mb-0">
                        أو اتصل بنا على: <strong>966123456789+</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-3">لماذا تختارنا؟</h2>
            <p class="lead text-muted">نتميز بالجودة والخبرة والتقنيات الحديثة</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-user-md" style="font-size: 2rem;"></i>
                    </div>
                    <h5>أطباء متخصصون</h5>
                    <p class="text-muted">فريق من أمهر الأطباء والمتخصصين</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-microscope" style="font-size: 2rem;"></i>
                    </div>
                    <h5>تقنيات حديثة</h5>
                    <p class="text-muted">أحدث الأجهزة والتقنيات الطبية</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center">
                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-clock" style="font-size: 2rem;"></i>
                    </div>
                    <h5>خدمة 24/7</h5>
                    <p class="text-muted">خدمة الطوارئ متاحة على مدار الساعة</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center">
                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-shield-alt" style="font-size: 2rem;"></i>
                    </div>
                    <h5>بيئة آمنة</h5>
                    <p class="text-muted">أعلى معايير السلامة والنظافة</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5 bg-light" id="contact">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-3">تواصل معنا</h2>
            <p class="lead text-muted">نحن هنا لخدمتكم والإجابة على استفساراتكم</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>العنوان</h5>
                        <p class="text-muted">شارع الملك فهد، الرياض<br>المملكة العربية السعودية</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h5>الهاتف</h5>
                        <p class="text-muted">966123456789+<br>للطوارئ: 966987654321+</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5>البريد الإلكتروني</h5>
                        <p class="text-muted">info@dentalcenter.com<br>appointments@dentalcenter.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Counter animation for statistics
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number');
        
        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace('+', ''));
            const increment = target / 100;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.ceil(current) + '+';
                }
            }, 20);
        });
    }

    // Trigger counter animation when stats section is visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    });

    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }
</script>
@endpush