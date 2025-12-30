<section id="offers" class="py-5 bg-gradient">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">العروض والخصومات</h2>
            <p class="lead text-muted">استفد من عروضنا الحصرية والخصومات المميزة</p>
        </div>
        
        @if($offers->count() > 0)
            <!-- Featured Offer -->
            @if($featuredOffer)
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="card featured-offer border-0 shadow-lg overflow-hidden">
                            <div class="card-body p-0">
                                <div class="row g-0">
                                    <div class="col-lg-8">
                                        <div class="p-5">
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="badge bg-danger fs-6 me-3">عرض مميز</span>
                                                @if($featuredOffer->discount_type === 'percentage')
                                                    <span class="badge bg-success fs-5">خصم {{ $featuredOffer->discount_value }}%</span>
                                                @elseif($featuredOffer->discount_type === 'fixed')
                                                    <span class="badge bg-success fs-5">خصم {{ $featuredOffer->discount_value }} ريال</span>
                                                @else
                                                    <span class="badge bg-success fs-5">مجاني</span>
                                                @endif
                                            </div>
                                            
                                            <h3 class="card-title display-6 fw-bold text-primary mb-3">
                                                {{ $featuredOffer->title }}
                                            </h3>
                                            
                                            <p class="card-text lead mb-4">
                                                {{ $featuredOffer->description }}
                                            </p>
                                            
                                            @if($featuredOffer->terms_conditions)
                                                <div class="mb-4">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        {{ $featuredOffer->terms_conditions }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                                <button class="btn btn-primary btn-lg px-4" 
                                                        wire:click="claimOffer({{ $featuredOffer->id }})">
                                                    <i class="fas fa-gift me-2"></i>
                                                    احصل على العرض
                                                </button>
                                                
                                                <button class="btn btn-outline-secondary" 
                                                        wire:click="shareOffer({{ $featuredOffer->id }})">
                                                    <i class="fas fa-share-alt me-2"></i>
                                                    شارك العرض
                                                </button>
                                                
                                                @if($featuredOffer->end_date)
                                                    <div class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        ينتهي في {{ \Carbon\Carbon::parse($featuredOffer->end_date)->diffForHumans() }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-4">
                                        <div class="h-100 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center p-4">
                                            @if($featuredOffer->image)
                                                <img src="{{ asset('storage/' . $featuredOffer->image) }}" 
                                                     alt="{{ $featuredOffer->title }}" 
                                                     class="img-fluid rounded">
                                            @else
                                                <div class="text-center">
                                                    <i class="fas fa-percentage text-primary mb-3" style="font-size: 4rem;"></i>
                                                    <h4 class="text-primary">عرض خاص</h4>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Other Offers -->
            @if($offers->count() > 1)
                <div class="row g-4">
                    @foreach($offers->skip(1) as $offer)
                        <div class="col-lg-4 col-md-6">
                            <div class="card offer-card h-100 border-0 shadow-sm">
                                <div class="card-header bg-white border-0 text-center pt-4">
                                    @if($offer->discount_type === 'percentage')
                                        <div class="offer-badge bg-gradient-primary text-white">
                                            <span class="display-6 fw-bold">{{ $offer->discount_value }}%</span>
                                            <div class="small">خصم</div>
                                        </div>
                                    @elseif($offer->discount_type === 'fixed')
                                        <div class="offer-badge bg-gradient-success text-white">
                                            <span class="h4 fw-bold">{{ $offer->discount_value }}</span>
                                            <div class="small">ريال خصم</div>
                                        </div>
                                    @else
                                        <div class="offer-badge bg-gradient-warning text-white">
                                            <span class="h5 fw-bold">مجاني</span>
                                            <div class="small">100%</div>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-3">{{ $offer->title }}</h5>
                                    <p class="card-text text-muted mb-4">{{ $offer->description }}</p>
                                    
                                    @if($offer->usage_limit)
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>
                                                محدود لـ {{ $offer->usage_limit }} شخص
                                            </small>
                                        </div>
                                    @endif
                                    
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" 
                                                wire:click="claimOffer({{ $offer->id }})">
                                            <i class="fas fa-gift me-2"></i>
                                            احصل على العرض
                                        </button>
                                        
                                        <button class="btn btn-outline-secondary btn-sm" 
                                                wire:click="shareOffer({{ $offer->id }})">
                                            <i class="fas fa-share-alt me-1"></i>
                                            شارك
                                        </button>
                                    </div>
                                </div>
                                
                                @if($offer->end_date)
                                    <div class="card-footer bg-light border-0 text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            ينتهي {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <!-- Call to Action -->
            <div class="text-center mt-5">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body p-5">
                        <h4 class="text-primary mb-3">لا تفوت العروض الحصرية!</h4>
                        <p class="text-muted mb-4">اشترك في نشرتنا البريدية لتصلك أحدث العروض والخصومات</p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="email" class="form-control" placeholder="أدخل بريدك الإلكتروني">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        اشترك
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Offers Available -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-gift text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">لا توجد عروض متاحة حالياً</h4>
                <p class="text-muted mb-4">ترقب عروضنا الجديدة قريباً</p>
                <a href="#contact" class="btn btn-primary">
                    <i class="fas fa-bell me-2"></i>
                    أعلمني بالعروض الجديدة
                </a>
            </div>
        @endif
    </div>
</section>

@push('styles')
<style>
    .bg-gradient {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .featured-offer {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }
    
    .featured-offer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
    }
    
    .offer-card {
        transition: all 0.3s ease;
        border-radius: 15px;
    }
    
    .offer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    
    .offer-badge {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        position: relative;
    }
    
    .offer-badge::before {
        content: '';
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        border-radius: 50%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107, #e0a800);
    }
    
    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }
    
    .card-footer {
        border-radius: 0 0 15px 15px !important;
    }
    
    .input-group .form-control {
        border-radius: 25px 0 0 25px;
    }
    
    .input-group .btn {
        border-radius: 0 25px 25px 0;
    }
</style>
@endpush

@push('scripts')
<script>
    // Listen for offer events
    document.addEventListener('livewire:init', () => {
        Livewire.on('offer-claimed', (event) => {
            const { offer_id } = event;
            showOfferClaimedMessage();
        });
        
        Livewire.on('offer-shared', (event) => {
            const { url } = event;
            shareOffer(url);
        });
    });
    
    function showOfferClaimedMessage() {
        // Show success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="fas fa-gift me-2"></i>
            <strong>تم الحصول على العرض!</strong><br>
            سيتم التواصل معك قريباً لتفعيل العرض
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    function shareOffer(url) {
        if (navigator.share) {
            navigator.share({
                title: 'عرض خاص من مركز محمد يوسف لطب الأسنان',
                text: 'لا تفوت هذا العرض المميز!',
                url: url
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                const alert = document.createElement('div');
                alert.className = 'alert alert-info alert-dismissible fade show position-fixed';
                alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                alert.innerHTML = `
                    <i class="fas fa-copy me-2"></i>
                    تم نسخ رابط العرض!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.body.appendChild(alert);
                
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 3000);
            });
        }
    }
</script>
@endpush