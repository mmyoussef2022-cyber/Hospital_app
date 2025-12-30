<section class="hero-section text-white py-5 position-relative overflow-hidden">
    <div class="container py-5">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInUp">
                        {{ $settings->hero_title ?? 'مركز محمد يوسف لطب الأسنان' }}
                    </h1>
                    <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                        {{ $settings->hero_subtitle ?? 'نقدم أفضل خدمات طب الأسنان بأحدث التقنيات والمعدات الطبية المتطورة' }}
                    </p>
                    
                    <div class="d-flex flex-wrap gap-3 mb-5 animate__animated animate__fadeInUp animate__delay-2s">
                        <a href="#services" class="btn btn-light btn-lg px-4 py-3 rounded-pill">
                            <i class="fas fa-tooth me-2"></i>
                            {{ $settings->hero_cta_primary_text ?? 'خدماتنا' }}
                        </a>
                        <a href="#doctors" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ $settings->hero_cta_secondary_text ?? 'احجز موعد' }}
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="row g-4 animate__animated animate__fadeInUp animate__delay-3s">
                        <div class="col-6 col-md-3">
                            <div class="text-center">
                                <div class="h2 fw-bold mb-1">{{ number_format($stats['patients']) }}+</div>
                                <small class="text-white-50">مريض سعيد</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center">
                                <div class="h2 fw-bold mb-1">{{ $stats['doctors'] }}+</div>
                                <small class="text-white-50">طبيب متخصص</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center">
                                <div class="h2 fw-bold mb-1">{{ $stats['services'] }}+</div>
                                <small class="text-white-50">خدمة طبية</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center">
                                <div class="h2 fw-bold mb-1">{{ $stats['experience'] }}+</div>
                                <small class="text-white-50">سنوات خبرة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-image text-center animate__animated animate__fadeInRight animate__delay-1s">
                    @if($settings->hero_background_image)
                        <img src="{{ asset('storage/' . $settings->hero_background_image) }}" 
                             alt="مركز طب الأسنان" 
                             class="img-fluid rounded-3 shadow-lg">
                    @else
                        <div class="bg-white bg-opacity-10 rounded-3 p-5 backdrop-blur">
                            <i class="fas fa-tooth display-1 mb-4"></i>
                            <h3 class="h4 mb-3">ابتسامتك هي أولويتنا</h3>
                            <p class="mb-0">نحن هنا لنجعل ابتسامتك أكثر إشراقاً وصحة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Background Elements -->
    <div class="position-absolute top-0 end-0 w-50 h-100 opacity-10">
        <svg viewBox="0 0 100 100" class="w-100 h-100">
            <circle cx="80" cy="20" r="2" fill="currentColor">
                <animate attributeName="opacity" values="0;1;0" dur="3s" repeatCount="indefinite"/>
            </circle>
            <circle cx="90" cy="40" r="1.5" fill="currentColor">
                <animate attributeName="opacity" values="0;1;0" dur="2s" repeatCount="indefinite" begin="1s"/>
            </circle>
            <circle cx="85" cy="60" r="1" fill="currentColor">
                <animate attributeName="opacity" values="0;1;0" dur="4s" repeatCount="indefinite" begin="2s"/>
            </circle>
        </svg>
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
    .hero-section {
        background: linear-gradient(135deg, {{ $settings->primary_color ?? '#1877F2' }}, {{ $settings->secondary_color ?? '#42A5F5' }});
        position: relative;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        pointer-events: none;
    }
    
    .min-vh-75 {
        min-height: 75vh;
    }
    
    .backdrop-blur {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    
    .hero-content h1 {
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .hero-content .lead {
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .btn-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
    
    .btn-outline-light:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
</style>
@endpush