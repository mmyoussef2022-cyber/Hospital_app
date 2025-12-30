<section id="doctors" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">{{ $settings->doctors_title ?? 'أطباؤنا المتميزون' }}</h2>
            <p class="lead text-muted">{{ $settings->doctors_subtitle ?? 'فريق من أفضل أطباء الأسنان المتخصصين' }}</p>
        </div>
        
        @if($doctors->count() > 0)
            <div class="row g-4">
                @foreach($doctors as $doctor)
                    <div class="col-lg-4 col-md-6">
                        <div class="card doctor-card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="doctor-avatar mb-3">
                                    @if($doctor->profile_image)
                                        <img src="{{ asset('storage/' . $doctor->profile_image) }}" 
                                             alt="{{ $doctor->name }}" 
                                             class="rounded-circle img-fluid"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                             style="width: 100px; height: 100px;">
                                            <i class="fas fa-user-md text-primary fa-2x"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <h5 class="card-title mb-2">{{ $doctor->name ?? ($doctor->user->name ?? 'طبيب') }}</h5>
                                <p class="text-muted mb-3">{{ $doctor->specialization ?? 'طبيب أسنان' }}</p>
                                
                                @if($doctor->biography)
                                    <p class="card-text small text-muted mb-3">{{ Str::limit($doctor->biography, 100) }}</p>
                                @endif
                                
                                <!-- Doctor Rating -->
                                <div class="mb-3">
                                    @php
                                        $rating = $doctor->rating ?? 4.5;
                                        $fullStars = floor($rating);
                                        $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    @endphp
                                    
                                    <div class="d-flex justify-content-center align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $fullStars)
                                                <i class="fas fa-star text-warning"></i>
                                            @elseif($i == $fullStars + 1 && $hasHalfStar)
                                                <i class="fas fa-star-half-alt text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                        <span class="ms-2 small text-muted">({{ number_format($rating, 1) }})</span>
                                    </div>
                                </div>
                                
                                <!-- Doctor Services -->
                                @if($doctor->services && $doctor->services->count() > 0)
                                    <div class="mb-3">
                                        <div class="d-flex flex-wrap justify-content-center gap-1">
                                            @foreach($doctor->services->take(3) as $service)
                                                <span class="badge bg-light text-dark small">{{ $service->name }}</span>
                                            @endforeach
                                            @if($doctor->services->count() > 3)
                                                <span class="badge bg-secondary small">+{{ $doctor->services->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('public.doctors.profile', $doctor->id) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-user me-1"></i>
                                        الملف الشخصي
                                    </a>
                                    <button class="btn btn-primary btn-sm" 
                                            onclick="bookWithDoctor({{ $doctor->id }})">
                                        <i class="fas fa-calendar-plus me-1"></i>
                                        احجز موعد
                                    </button>
                                </div>
                                
                                <!-- Availability Status -->
                                <div class="mt-3">
                                    @if($doctor->is_available)
                                        <span class="badge bg-success">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5em;"></i>
                                            متاح الآن
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>
                                            غير متاح حالياً
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($doctors->count() >= $limit)
                <div class="text-center mt-5">
                    <button wire:click="loadMore" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>
                        عرض المزيد من الأطباء
                    </button>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-user-md text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">لا يوجد أطباء متاحون حالياً</h4>
                <p class="text-muted">سيتم إضافة الأطباء قريباً. يرجى المراجعة لاحقاً.</p>
                <a href="#contact" class="btn btn-primary">
                    <i class="fas fa-phone me-2"></i>
                    اتصل بنا للاستفسار
                </a>
            </div>
        @endif
    </div>
</section>

@push('styles')
<style>
    .doctor-card {
        transition: all 0.3s ease;
        border-radius: 15px;
    }
    
    .doctor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    
    .doctor-avatar img,
    .doctor-avatar > div {
        transition: all 0.3s ease;
    }
    
    .doctor-card:hover .doctor-avatar img,
    .doctor-card:hover .doctor-avatar > div {
        transform: scale(1.05);
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .text-warning {
        color: #ffc107 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function bookWithDoctor(doctorId) {
        // Scroll to schedule section or open booking modal
        const scheduleSection = document.getElementById('schedule');
        if (scheduleSection) {
            scheduleSection.scrollIntoView({ behavior: 'smooth' });
            
            // Highlight the doctor in schedule if available
            setTimeout(() => {
                const doctorElements = document.querySelectorAll(`[data-doctor-id="${doctorId}"]`);
                doctorElements.forEach(el => {
                    el.classList.add('highlight-doctor');
                    setTimeout(() => el.classList.remove('highlight-doctor'), 3000);
                });
            }, 1000);
        } else {
            // Fallback: redirect to booking page
            window.location.href = `/booking?doctor=${doctorId}`;
        }
    }
    
    // Add highlight animation
    const style = document.createElement('style');
    style.textContent = `
        .highlight-doctor {
            animation: highlightPulse 2s ease-in-out;
            border: 2px solid #ffc107 !important;
        }
        
        @keyframes highlightPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush