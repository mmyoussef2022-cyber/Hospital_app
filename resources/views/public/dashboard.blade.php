@extends('layouts.public')

@section('title', 'لوحة التحكم - مركز محمد يوسف لطب الأسنان')
@section('meta-description', 'لوحة تحكم المريض في مركز محمد يوسف لطب الأسنان - تابع مواعيدك وسجلاتك الطبية')

@section('content')
<!-- Dashboard Header -->
<section class="py-5 bg-primary text-white" style="margin-top: 80px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="display-5 mb-3">مرحباً، {{ $user->name }}</h1>
                <p class="lead">
                    أهلاً بك في لوحة التحكم الخاصة بك. يمكنك من هنا متابعة مواعيدك وإدارة بياناتك الشخصية.
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('public.index') }}" class="text-white-50">الرئيسية</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">لوحة التحكم</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-4 text-center" data-aos="fade-left">
                <div class="bg-white bg-opacity-10 p-4 rounded-3">
                    <div class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="mb-0">مريض مسجل</h5>
                    <small>عضو منذ {{ $user->created_at->format('Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Stats -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 50px; height: 50px;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4 class="text-primary mb-1">{{ $appointments->total() }}</h4>
                        <small class="text-muted">إجمالي المواعيد</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 50px; height: 50px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 class="text-success mb-1">{{ $appointments->where('status', 'completed')->count() }}</h4>
                        <small class="text-muted">مواعيد مكتملة</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 50px; height: 50px;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4 class="text-warning mb-1">{{ $appointments->where('status', 'scheduled')->count() }}</h4>
                        <small class="text-muted">مواعيد قادمة</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 50px; height: 50px;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h4 class="text-info mb-1">{{ $appointments->pluck('doctor_id')->unique()->count() }}</h4>
                        <small class="text-muted">أطباء مختلفون</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Dashboard Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Left Column - Appointments & Actions -->
            <div class="col-lg-8">
                <!-- Quick Actions -->
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2 text-primary"></i>
                            إجراءات سريعة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('public.booking.form') }}" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-calendar-plus mb-2" style="font-size: 2rem;"></i>
                                    <span>حجز موعد جديد</span>
                                </a>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-user-md mb-2" style="font-size: 2rem;"></i>
                                    <span>تصفح الأطباء</span>
                                </a>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" onclick="updateProfile()">
                                    <i class="fas fa-user-edit mb-2" style="font-size: 2rem;"></i>
                                    <span>تحديث البيانات</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments List -->
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            مواعيدي
                        </h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="appointmentFilter" id="all" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="all">الكل</label>
                            
                            <input type="radio" class="btn-check" name="appointmentFilter" id="upcoming" autocomplete="off">
                            <label class="btn btn-outline-primary" for="upcoming">القادمة</label>
                            
                            <input type="radio" class="btn-check" name="appointmentFilter" id="completed" autocomplete="off">
                            <label class="btn btn-outline-primary" for="completed">المكتملة</label>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($appointments->count() > 0)
                            <div id="appointments-list">
                                @foreach($appointments as $appointment)
                                <div class="appointment-item border rounded p-3 mb-3" 
                                     data-status="{{ $appointment->status }}"
                                     data-date="{{ $appointment->appointment_date }}">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center mb-2 mb-md-0">
                                            <div class="appointment-date">
                                                <div class="date-day">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d') }}</div>
                                                <div class="date-month">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M') }}</div>
                                                <div class="date-year">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('Y') }}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h6 class="mb-1">د. {{ $appointment->doctor->name ?? 'غير محدد' }}</h6>
                                            <p class="text-muted small mb-1">{{ $appointment->doctor->specialization ?? 'تخصص عام' }}</p>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                                            </p>
                                        </div>
                                        
                                        <div class="col-md-2 text-center">
                                            @switch($appointment->status)
                                                @case('scheduled')
                                                    <span class="badge bg-primary">مجدول</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge bg-success">مؤكد</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">ملغي</span>
                                                    @break
                                                @case('urgent')
                                                    <span class="badge bg-warning">عاجل</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $appointment->status }}</span>
                                            @endswitch
                                        </div>
                                        
                                        <div class="col-md-2 text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewAppointmentDetails({{ $appointment->id }})">
                                                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                                                    </a></li>
                                                    @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                                        <li><a class="dropdown-item text-warning" href="#" onclick="rescheduleAppointment({{ $appointment->id }})">
                                                            <i class="fas fa-calendar-alt me-2"></i>إعادة جدولة
                                                        </a></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="cancelAppointment({{ $appointment->id }})">
                                                            <i class="fas fa-times me-2"></i>إلغاء الموعد
                                                        </a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($appointment->reason)
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted">
                                            <strong>سبب الزيارة:</strong> {{ $appointment->reason }}
                                        </small>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $appointments->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 4rem;"></i>
                                <h5 class="text-muted mb-3">لا توجد مواعيد حتى الآن</h5>
                                <p class="text-muted mb-4">احجز موعدك الأول معنا واستمتع بخدماتنا الطبية المتميزة</p>
                                <a href="{{ route('public.booking.form') }}" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    احجز موعدك الأول
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Profile & Info -->
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="card mb-4 sticky-top" style="top: 100px;" data-aos="fade-left">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            الملف الشخصي
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="mb-1">{{ $user->name }}</h5>
                            <p class="text-muted small mb-0">{{ $user->email }}</p>
                        </div>
                        
                        <div class="profile-info">
                            <div class="mb-2">
                                <strong class="text-primary">الهاتف:</strong>
                                <span class="float-end">{{ $user->phone ?? 'غير محدد' }}</span>
                            </div>
                            
                            <div class="mb-2">
                                <strong class="text-primary">تاريخ الميلاد:</strong>
                                <span class="float-end">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') : 'غير محدد' }}</span>
                            </div>
                            
                            <div class="mb-2">
                                <strong class="text-primary">الجنس:</strong>
                                <span class="float-end">{{ $user->gender === 'male' ? 'ذكر' : ($user->gender === 'female' ? 'أنثى' : 'غير محدد') }}</span>
                            </div>
                            
                            <div class="mb-2">
                                <strong class="text-primary">تاريخ التسجيل:</strong>
                                <span class="float-end">{{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        
                        <div class="d-grid mt-3">
                            <button class="btn btn-outline-primary" onclick="updateProfile()">
                                <i class="fas fa-edit me-2"></i>
                                تحديث البيانات
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Patient Info -->
                @if($patient)
                <div class="card mb-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-id-card me-2 text-primary"></i>
                            بيانات المريض
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong class="text-primary">رقم الهوية:</strong>
                            <span class="float-end">{{ $patient->national_id }}</span>
                        </div>
                        
                        @if($patient->address)
                        <div class="mb-2">
                            <strong class="text-primary">العنوان:</strong>
                            <span class="float-end">{{ Str::limit($patient->address, 20) }}</span>
                        </div>
                        @endif
                        
                        @if($patient->emergency_contact)
                        <div class="mb-2">
                            <strong class="text-primary">رقم الطوارئ:</strong>
                            <span class="float-end">{{ $patient->emergency_contact }}</span>
                        </div>
                        @endif
                        
                        @if($patient->family_code)
                        <div class="mb-2">
                            <strong class="text-primary">رمز العائلة:</strong>
                            <span class="float-end">{{ $patient->family_code }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quick Contact -->
                <div class="card" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            تواصل سريع
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="tel:+966123456789" class="btn btn-success">
                                <i class="fas fa-phone me-2"></i>
                                اتصال مباشر
                            </a>
                            
                            <a href="https://wa.me/966123456789" class="btn btn-outline-success" target="_blank">
                                <i class="fab fa-whatsapp me-2"></i>
                                واتساب
                            </a>
                            
                            <button class="btn btn-outline-primary" onclick="requestCallback()">
                                <i class="fas fa-phone-alt me-2"></i>
                                طلب معاودة اتصال
                            </button>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                خدمة العملاء: 8 ص - 10 م
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Update Profile Modal -->
<div class="modal fade" id="updateProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تحديث البيانات الشخصية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateProfileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="update_name" class="form-label">الاسم الكامل</label>
                            <input type="text" class="form-control" id="update_name" value="{{ $user->name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="update_email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="update_email" value="{{ $user->email }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="update_phone" class="form-label">رقم الهاتف</label>
                            <input type="tel" class="form-control" id="update_phone" value="{{ $user->phone }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="update_date_of_birth" class="form-label">تاريخ الميلاد</label>
                            <input type="date" class="form-control" id="update_date_of_birth" value="{{ $user->date_of_birth }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_gender" class="form-label">الجنس</label>
                        <select class="form-select" id="update_gender">
                            <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>ذكر</option>
                            <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>أنثى</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="saveProfileUpdate()">حفظ التغييرات</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filter appointments
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('input[name="appointmentFilter"]');
        
        filterButtons.forEach(button => {
            button.addEventListener('change', function() {
                filterAppointments(this.id);
            });
        });
    });

    function filterAppointments(filter) {
        const appointments = document.querySelectorAll('.appointment-item');
        const today = new Date().toISOString().split('T')[0];
        
        appointments.forEach(appointment => {
            const status = appointment.dataset.status;
            const date = appointment.dataset.date;
            let show = false;
            
            switch(filter) {
                case 'all':
                    show = true;
                    break;
                case 'upcoming':
                    show = date >= today && ['scheduled', 'confirmed', 'urgent'].includes(status);
                    break;
                case 'completed':
                    show = status === 'completed';
                    break;
            }
            
            appointment.style.display = show ? 'block' : 'none';
        });
    }

    function updateProfile() {
        const modal = new bootstrap.Modal(document.getElementById('updateProfileModal'));
        modal.show();
    }

    function saveProfileUpdate() {
        // Simulate saving profile update
        alert('سيتم تحديث البيانات قريباً');
        bootstrap.Modal.getInstance(document.getElementById('updateProfileModal')).hide();
    }

    function viewAppointmentDetails(appointmentId) {
        alert(`عرض تفاصيل الموعد رقم: ${appointmentId}`);
    }

    function rescheduleAppointment(appointmentId) {
        if (confirm('هل تريد إعادة جدولة هذا الموعد؟')) {
            alert('سيتم توجيهك لصفحة إعادة الجدولة');
        }
    }

    function cancelAppointment(appointmentId) {
        if (confirm('هل أنت متأكد من إلغاء هذا الموعد؟')) {
            alert('تم إلغاء الموعد بنجاح');
            location.reload();
        }
    }

    function requestCallback() {
        alert('تم تسجيل طلب معاودة الاتصال. سنتواصل معك خلال 30 دقيقة.');
    }

    // Auto-refresh appointments every 5 minutes
    setInterval(() => {
        // In a real implementation, this would fetch updated appointment data
        console.log('Checking for appointment updates...');
    }, 300000);
</script>
@endpush

@push('styles')
<style>
    .appointment-date {
        background: var(--facebook-blue);
        color: white;
        border-radius: 12px;
        padding: 10px;
        text-align: center;
        min-width: 70px;
    }
    
    .date-day {
        font-size: 1.5rem;
        font-weight: bold;
        line-height: 1;
    }
    
    .date-month {
        font-size: 0.8rem;
        text-transform: uppercase;
        line-height: 1;
    }
    
    .date-year {
        font-size: 0.7rem;
        opacity: 0.8;
        line-height: 1;
    }
    
    .appointment-item {
        transition: all 0.3s ease;
        border: 2px solid transparent !important;
    }
    
    .appointment-item:hover {
        border-color: var(--facebook-blue) !important;
        background: rgba(24, 119, 242, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .profile-info {
        font-size: 0.9rem;
    }
    
    .profile-info > div {
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 8px;
    }
    
    .profile-info > div:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .sticky-top {
        position: sticky !important;
    }
    
    .btn-check:checked + .btn {
        background-color: var(--facebook-blue);
        border-color: var(--facebook-blue);
        color: white;
    }
    
    @media (max-width: 991px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
    }
    
    @media (max-width: 576px) {
        .appointment-date {
            min-width: 60px;
            padding: 8px;
        }
        
        .date-day {
            font-size: 1.2rem;
        }
        
        .date-month {
            font-size: 0.7rem;
        }
        
        .date-year {
            font-size: 0.6rem;
        }
    }
</style>
@endpush