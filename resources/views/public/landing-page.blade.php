@extends('layouts.public')

@section('title', $settings->meta_title ?? 'مركز محمد يوسف لطب الأسنان')
@section('description', $settings->meta_description ?? 'مركز متخصص في طب الأسنان يقدم أفضل الخدمات الطبية')

@section('content')
<!-- Hero Section -->
@if($settings->hero_section_enabled ?? true)
    @livewire('landing-page-hero')
@endif

<!-- About Section -->
@if($settings->about_section_enabled ?? true)
<section id="about" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold text-primary mb-4">{{ $settings->about_title ?? 'نبذة عنا' }}</h2>
                <p class="lead mb-4">{{ $settings->about_content ?? 'نحن مركز متخصص في طب الأسنان نقدم أفضل الخدمات الطبية بأحدث التقنيات والمعدات.' }}</p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>أطباء متخصصون</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>أحدث التقنيات</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>خدمة 24/7</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>أسعار مناسبة</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    @if($settings->about_images)
                        @foreach($settings->about_images as $image)
                            <div class="col-6">
                                <img src="{{ asset('storage/' . $image) }}" alt="صورة المركز" class="img-fluid rounded shadow">
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <img src="{{ asset('images/dental-clinic.jpg') }}" alt="مركز طب الأسنان" class="img-fluid rounded shadow">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Services Section -->
@if($settings->services_section_enabled ?? true)
<section id="services" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">{{ $settings->services_title ?? 'خدماتنا' }}</h2>
            <p class="lead text-muted">{{ $settings->services_subtitle ?? 'نقدم مجموعة شاملة من خدمات طب الأسنان' }}</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-tooth text-primary fa-2x"></i>
                        </div>
                        <h5 class="card-title">علاج الأسنان</h5>
                        <p class="card-text text-muted">علاج شامل لجميع مشاكل الأسنان بأحدث التقنيات</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-smile text-success fa-2x"></i>
                        </div>
                        <h5 class="card-title">تجميل الأسنان</h5>
                        <p class="card-text text-muted">خدمات تجميل الأسنان للحصول على ابتسامة مثالية</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md text-info fa-2x"></i>
                        </div>
                        <h5 class="card-title">جراحة الأسنان</h5>
                        <p class="card-text text-muted">جراحات متقدمة وزراعة الأسنان بأعلى معايير الجودة</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-child text-warning fa-2x"></i>
                        </div>
                        <h5 class="card-title">طب أسنان الأطفال</h5>
                        <p class="card-text text-muted">رعاية خاصة لأسنان الأطفال في بيئة مريحة وآمنة</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-x-ray text-danger fa-2x"></i>
                        </div>
                        <h5 class="card-title">الأشعة والتشخيص</h5>
                        <p class="card-text text-muted">أحدث أجهزة الأشعة للتشخيص الدقيق</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-clock text-secondary fa-2x"></i>
                        </div>
                        <h5 class="card-title">طوارئ الأسنان</h5>
                        <p class="card-text text-muted">خدمة طوارئ متاحة على مدار الساعة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Doctors Section -->
@if($settings->doctors_section_enabled ?? true)
    @livewire('featured-doctors')
@endif

<!-- Offers Section -->
@if($settings->offers_section_enabled ?? true)
    @livewire('landing-page-offers')
@endif

<!-- Schedule Section -->
@if($settings->schedule_section_enabled ?? true)
    @livewire('doctor-schedule-overview')
@endif

<!-- Location Section -->
@if($settings->location_section_enabled ?? true)
<section id="location" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">{{ $settings->location_title ?? 'موقعنا' }}</h2>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">معلومات التواصل</h5>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-3"></i>
                            <div>
                                <strong>العنوان:</strong><br>
                                {{ $settings->address_text ?? 'شارع الملك فهد، حي العليا، الرياض' }}
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone text-primary me-3"></i>
                            <div>
                                <strong>الهاتف:</strong><br>
                                <a href="tel:{{ $settings->phone_primary }}" class="text-decoration-none">{{ $settings->phone_primary ?? '+966 11 123 4567' }}</a>
                            </div>
                        </div>
                        
                        @if($settings->phone_emergency)
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-ambulance text-danger me-3"></i>
                            <div>
                                <strong>الطوارئ:</strong><br>
                                <a href="tel:{{ $settings->phone_emergency }}" class="text-decoration-none text-danger">{{ $settings->phone_emergency }}</a>
                            </div>
                        </div>
                        @endif
                        
                        @if($settings->email_primary)
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-envelope text-primary me-3"></i>
                            <div>
                                <strong>البريد الإلكتروني:</strong><br>
                                <a href="mailto:{{ $settings->email_primary }}" class="text-decoration-none">{{ $settings->email_primary }}</a>
                            </div>
                        </div>
                        @endif
                        
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-3"></i>
                            <div>
                                <strong>ساعات العمل:</strong><br>
                                السبت - الخميس: 9:00 ص - 9:00 م<br>
                                الجمعة: 2:00 م - 9:00 م
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card border-0 shadow">
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.397!2d46.686!3d24.713!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjTCsDQyJzQ3LjAiTiA0NsKwNDEnMDkuNiJF!5e0!3m2!1sen!2ssa!4v1234567890"
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"
                                class="rounded">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Chatbot Widget -->
@livewire('chatbot-widget')

@endsection

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, {{ $settings->primary_color ?? '#1877F2' }}, {{ $settings->secondary_color ?? '#42A5F5' }});
    }
    
    .btn-primary {
        background-color: {{ $settings->primary_color ?? '#1877F2' }};
        border-color: {{ $settings->primary_color ?? '#1877F2' }};
    }
    
    .text-primary {
        color: {{ $settings->primary_color ?? '#1877F2' }} !important;
    }
    
    .bg-primary {
        background-color: {{ $settings->primary_color ?? '#1877F2' }} !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all cards and sections
    document.querySelectorAll('.card, section').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
</script>
@endpush