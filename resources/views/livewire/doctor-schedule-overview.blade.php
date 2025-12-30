<section id="schedule" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">جدول المواعيد المتاحة</h2>
            <p class="lead text-muted">اختر الوقت المناسب لك واحجز موعدك</p>
        </div>
        
        <div class="row">
            <!-- Date Selection -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            اختر التاريخ
                        </h5>
                    </div>
                    <div class="card-body">
                        <input type="date" 
                               wire:model.live="selectedDate" 
                               class="form-control form-control-lg"
                               min="{{ date('Y-m-d') }}"
                               max="{{ date('Y-m-d', strtotime('+30 days')) }}">
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                يمكنك الحجز لمدة تصل إلى 30 يوماً مقدماً
                            </small>
                        </div>
                        
                        <!-- Quick Date Selection -->
                        <div class="mt-3">
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-primary btn-sm" 
                                        wire:click="$set('selectedDate', '{{ date('Y-m-d') }}')">
                                    اليوم
                                </button>
                                <button class="btn btn-outline-primary btn-sm" 
                                        wire:click="$set('selectedDate', '{{ date('Y-m-d', strtotime('+1 day')) }}')">
                                    غداً
                                </button>
                                <button class="btn btn-outline-primary btn-sm" 
                                        wire:click="$set('selectedDate', '{{ date('Y-m-d', strtotime('+7 days')) }}')">
                                    الأسبوع القادم
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Available Doctors -->
                @if($doctors->count() > 0)
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-user-md me-2"></i>
                                الأطباء المتاحون
                            </h6>
                        </div>
                        <div class="card-body p-2">
                            @foreach($doctors->take(5) as $doctor)
                                <div class="d-flex align-items-center p-2 rounded mb-2 bg-light">
                                    <div class="me-2">
                                        @if($doctor->profile_image)
                                            <img src="{{ asset('storage/' . $doctor->profile_image) }}" 
                                                 alt="{{ $doctor->name }}" 
                                                 class="rounded-circle"
                                                 style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 30px; height: 30px;">
                                                <i class="fas fa-user-md text-white small"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-bold">{{ $doctor->name ?? ($doctor->user->name ?? 'طبيب') }}</div>
                                        <div class="small text-muted">{{ $doctor->specialization ?? 'طبيب أسنان' }}</div>
                                    </div>
                                    <div>
                                        @if($doctor->is_available)
                                            <span class="badge bg-success small">متاح</span>
                                        @else
                                            <span class="badge bg-warning small">مشغول</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Time Slots -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            المواعيد المتاحة - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($availableSlots) > 0)
                            <div class="row g-3">
                                @foreach($availableSlots as $slot)
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <div class="time-slot {{ $slot['available'] ? 'available' : 'unavailable' }}"
                                             data-doctor-id="{{ $slot['doctor_id'] ?? '' }}">
                                            <div class="time-slot-content">
                                                <div class="time">{{ $slot['time'] }}</div>
                                                <div class="status">
                                                    @if($slot['available'])
                                                        <i class="fas fa-check-circle text-success"></i>
                                                        <span class="small">متاح</span>
                                                    @else
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        <span class="small">محجوز</span>
                                                    @endif
                                                </div>
                                                
                                                @if($slot['available'])
                                                    <button class="btn btn-primary btn-sm mt-2 w-100" 
                                                            wire:click="bookSlot('{{ $slot['time'] }}')">
                                                        <i class="fas fa-calendar-plus me-1"></i>
                                                        احجز
                                                    </button>
                                                @else
                                                    <button class="btn btn-secondary btn-sm mt-2 w-100" disabled>
                                                        <i class="fas fa-ban me-1"></i>
                                                        غير متاح
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Legend -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">متاح للحجز</span>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-times-circle text-danger me-2"></i>
                                        <span class="small">محجوز</span>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-clock text-warning me-2"></i>
                                        <span class="small">كل موعد 30 دقيقة</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mb-3">لا توجد مواعيد متاحة في هذا التاريخ</h5>
                                <p class="text-muted">يرجى اختيار تاريخ آخر أو الاتصال بنا مباشرة</p>
                                <button class="btn btn-primary" onclick="showContactInfo()">
                                    <i class="fas fa-phone me-2"></i>
                                    اتصل بنا
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Booking Instructions -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            تعليمات الحجز
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                يرجى الحضور قبل 15 دقيقة من موعدك
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                إحضار بطاقة الهوية وبطاقة التأمين
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                يمكن إلغاء أو تعديل الموعد قبل 24 ساعة
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                سيتم إرسال تذكير قبل الموعد بيوم واحد
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .time-slot {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .time-slot.available {
        border-color: #28a745;
        background-color: #f8fff9;
    }
    
    .time-slot.available:hover {
        border-color: #20c997;
        background-color: #e8f8f5;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .time-slot.unavailable {
        border-color: #dc3545;
        background-color: #fff5f5;
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .time-slot-content {
        width: 100%;
    }
    
    .time-slot .time {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .time-slot .status {
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .time-slot.available .time {
        color: #28a745;
    }
    
    .time-slot.unavailable .time {
        color: #dc3545;
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    
    .card {
        border-radius: 10px;
    }
    
    .highlight-doctor {
        animation: highlightPulse 2s ease-in-out;
        border: 2px solid #ffc107 !important;
    }
    
    @keyframes highlightPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
</style>
@endpush

@push('scripts')
<script>
    // Listen for slot booking events
    document.addEventListener('livewire:init', () => {
        Livewire.on('slot-booked', (event) => {
            const { time, date } = event;
            
            // Show success message
            showBookingSuccess(time, date);
            
            // You could also redirect to a booking form or show a modal
            // window.location.href = `/booking/confirm?time=${time}&date=${date}`;
        });
    });
    
    function showBookingSuccess(time, date) {
        // Create and show a success toast/alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>تم الحجز بنجاح!</strong><br>
            الموعد: ${time} - ${date}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    function showContactInfo() {
        // Show contact modal or scroll to contact section
        const contactSection = document.getElementById('location');
        if (contactSection) {
            contactSection.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>
@endpush