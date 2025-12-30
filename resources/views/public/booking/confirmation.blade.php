@extends('layouts.public')

@section('title', 'تأكيد الحجز - مركز محمد يوسف لطب الأسنان')
@section('meta-description', 'تم تأكيد حجز موعدك بنجاح في مركز محمد يوسف لطب الأسنان')

@section('content')
<!-- Success Header -->
<section class="py-5 bg-success text-white" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <div class="mb-4">
                    <div class="bg-white text-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="fas fa-check" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h1 class="display-4 mb-3">تم تأكيد حجزك بنجاح!</h1>
                <p class="lead">
                    شكراً لك على اختيار مركز محمد يوسف لطب الأسنان. تم حجز موعدك وسنتواصل معك قريباً للتأكيد.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Appointment Details -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Appointment Card -->
                <div class="card shadow-lg mb-4" data-aos="fade-up">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>
                            تفاصيل الموعد
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-primary mb-1">الطبيب المعالج</h6>
                                        <p class="mb-0">د. {{ $doctor->name }}</p>
                                        <small class="text-muted">{{ $doctor->specialization }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-primary mb-1">التاريخ والوقت</h6>
                                        <p class="mb-0">{{ $appointment_datetime->format('l, d F Y') }}</p>
                                        <small class="text-muted">{{ $appointment_datetime->format('h:i A') }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-primary mb-1">اسم المريض</h6>
                                        <p class="mb-0">{{ $patient->name }}</p>
                                        <small class="text-muted">{{ $patient->phone }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-primary mb-1">رقم الموعد</h6>
                                        <p class="mb-0">#{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</p>
                                        <small class="text-muted">احتفظ بهذا الرقم للمراجعة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($appointment->reason)
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6 class="text-primary mb-2">سبب الزيارة</h6>
                            <p class="mb-0">{{ $appointment->reason }}</p>
                        </div>
                        @endif
                        
                        @if($appointment->status === 'urgent')
                        <div class="mt-3">
                            <span class="badge bg-warning fs-6">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                موعد عاجل
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Important Instructions -->
                <div class="card mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            تعليمات مهمة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">قبل الحضور</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        احضر قبل 15 دقيقة من موعدك
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        أحضر الهوية الوطنية أو الإقامة
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        أحضر بطاقة التأمين (إن وجدت)
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        أحضر التقارير الطبية السابقة
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-primary">سياسة الإلغاء والتأجيل</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-warning me-2"></i>
                                        يمكن الإلغاء قبل 24 ساعة
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-phone text-info me-2"></i>
                                        اتصل لتعديل الموعد
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        رسوم إضافية للإلغاء المتأخر
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <button class="btn btn-primary btn-lg" onclick="printAppointment()">
                            <i class="fas fa-print me-2"></i>
                            طباعة التفاصيل
                        </button>
                        
                        <a href="tel:+966123456789" class="btn btn-success btn-lg">
                            <i class="fas fa-phone me-2"></i>
                            اتصل بالعيادة
                        </a>
                        
                        <a href="{{ route('public.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            العودة للرئيسية
                        </a>
                        
                        @auth
                        <a href="{{ route('public.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            لوحة التحكم
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Contact Card -->
                <div class="card mb-4" data-aos="fade-left">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-headset me-2"></i>
                            تواصل معنا
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">الهاتف الرئيسي</h6>
                            <p class="mb-0">
                                <a href="tel:+966123456789" class="text-decoration-none">
                                    <i class="fas fa-phone me-2"></i>
                                    966123456789+
                                </a>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">الطوارئ</h6>
                            <p class="mb-0">
                                <a href="tel:+966987654321" class="text-decoration-none text-danger">
                                    <i class="fas fa-ambulance me-2"></i>
                                    966987654321+
                                </a>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">واتساب</h6>
                            <p class="mb-0">
                                <a href="https://wa.me/966123456789" class="text-decoration-none text-success" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    مراسلة واتساب
                                </a>
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">خدمة العملاء متاحة 24/7</small>
                        </div>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="card mb-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            موقع العيادة
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">
                            <strong>مركز محمد يوسف لطب الأسنان</strong><br>
                            شارع الملك فهد، الرياض<br>
                            المملكة العربية السعودية
                        </p>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">ساعات العمل</h6>
                            <p class="small mb-0">
                                السبت - الخميس: 8:00 ص - 10:00 م<br>
                                الجمعة: 2:00 م - 10:00 م
                            </p>
                        </div>
                        
                        <div class="d-grid">
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-directions me-2"></i>
                                عرض على الخريطة
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-list-check me-2 text-primary"></i>
                            الخطوات التالية
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">تأكيد الحجز</h6>
                                    <small class="text-muted">تم بنجاح ✓</small>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">رسالة تأكيد</h6>
                                    <small class="text-muted">خلال 30 دقيقة</small>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">تذكير بالموعد</h6>
                                    <small class="text-muted">قبل 24 ساعة</small>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">الحضور للعيادة</h6>
                                    <small class="text-muted">{{ $appointment_datetime->format('d/m/Y h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Services -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h3>خدمات إضافية قد تهمك</h3>
            <p class="text-muted">استفد من خدماتنا المتنوعة</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h5>حجز مواعيد إضافية</h5>
                        <p class="text-muted">احجز مواعيد أخرى لأفراد العائلة</p>
                        <a href="{{ route('public.booking.form') }}" class="btn btn-outline-primary">
                            احجز الآن
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h5>تصفح الأطباء</h5>
                        <p class="text-muted">تعرف على فريقنا الطبي المتميز</p>
                        <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-success">
                            تصفح الأطباء
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h5>إنشاء حساب</h5>
                        <p class="text-muted">أنشئ حساباً لمتابعة مواعيدك</p>
                        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#registerModal">
                            إنشاء حساب
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Print Styles -->
<div id="printable-content" style="display: none;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2>مركز محمد يوسف لطب الأسنان</h2>
        <p>شارع الملك فهد، الرياض | هاتف: 966123456789+</p>
        <hr>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h3>تفاصيل الموعد</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>رقم الموعد:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">#{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>الطبيب:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">د. {{ $doctor->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>التخصص:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $doctor->specialization }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>اسم المريض:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $patient->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>التاريخ:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $appointment_datetime->format('l, d F Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>الوقت:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $appointment_datetime->format('h:i A') }}</td>
            </tr>
            @if($appointment->reason)
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>سبب الزيارة:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $appointment->reason }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div style="margin-top: 30px;">
        <h4>تعليمات مهمة:</h4>
        <ul>
            <li>احضر قبل 15 دقيقة من موعدك</li>
            <li>أحضر الهوية الوطنية أو الإقامة</li>
            <li>أحضر بطاقة التأمين (إن وجدت)</li>
            <li>أحضر التقارير الطبية السابقة</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">
        <p>تم الطباعة في: {{ now()->format('d/m/Y h:i A') }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function printAppointment() {
        const printContent = document.getElementById('printable-content').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        
        // Reload the page to restore functionality
        location.reload();
    }

    // Auto-scroll to top on page load
    document.addEventListener('DOMContentLoaded', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Show success animation
        setTimeout(() => {
            const successIcon = document.querySelector('.fa-check');
            if (successIcon) {
                successIcon.style.animation = 'bounce 1s ease-in-out';
            }
        }, 500);
    });

    // Add bounce animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes bounce {
            0%, 20%, 60%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            80% {
                transform: translateY(-10px);
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #E5E7EB;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -23px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #E5E7EB;
    }
    
    .timeline-content h6 {
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    .timeline-content small {
        font-size: 0.8rem;
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        
        #printable-content,
        #printable-content * {
            visibility: visible;
        }
        
        #printable-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
    
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
</style>
@endpush