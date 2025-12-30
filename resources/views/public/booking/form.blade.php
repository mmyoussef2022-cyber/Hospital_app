@extends('layouts.public')

@section('title', 'حجز موعد - مركز محمد يوسف لطب الأسنان')
@section('meta-description', 'احجز موعدك مع أفضل الأطباء في مركز محمد يوسف لطب الأسنان بسهولة وسرعة')

@section('content')
<!-- Page Header -->
<section class="py-5 bg-primary text-white" style="margin-top: 80px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="display-4 mb-3">حجز موعد جديد</h1>
                <p class="lead">
                    احجز موعدك مع أفضل الأطباء المتخصصين في بضع خطوات بسيطة
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('public.index') }}" class="text-white-50">الرئيسية</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">حجز موعد</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-4 text-center" data-aos="fade-left">
                <div class="bg-white bg-opacity-10 p-4 rounded-3">
                    <i class="fas fa-calendar-plus" style="font-size: 3rem;"></i>
                    <h4 class="mt-2 mb-0">حجز فوري</h4>
                    <p class="mb-0">تأكيد خلال دقائق</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Steps -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="step-item active" data-step="1">
                        <div class="step-circle">1</div>
                        <span class="step-label">اختيار الطبيب</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">2</div>
                        <span class="step-label">اختيار الموعد</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">3</div>
                        <span class="step-label">بيانات المريض</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" data-step="4">
                        <div class="step-circle">4</div>
                        <span class="step-label">التأكيد</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Form -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                @if($errors->any())
                    <div class="alert alert-danger" data-aos="fade-up">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>يرجى تصحيح الأخطاء التالية:</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" data-aos="fade-up">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('public.booking.process') }}" id="bookingForm" data-aos="fade-up">
                    @csrf
                    
                    <!-- Step 1: Doctor Selection -->
                    <div class="card mb-4 step-content" id="step-1">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-md me-2 text-primary"></i>
                                الخطوة 1: اختيار الطبيب والتخصص
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($doctor)
                                <!-- Pre-selected Doctor -->
                                <div class="selected-doctor-card p-3 border rounded bg-light">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center">
                                            @if($doctor->photo)
                                                <img src="{{ asset('storage/' . $doctor->photo) }}" alt="{{ $doctor->name }}" 
                                                     class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                                     style="width: 80px; height: 80px;">
                                                    <i class="fas fa-user-md" style="font-size: 2rem;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-8">
                                            <h5 class="mb-1">د. {{ $doctor->name }}</h5>
                                            <p class="text-primary mb-1">{{ $doctor->specialization }}</p>
                                            <p class="text-muted small mb-0">{{ $doctor->department->name ?? '' }}</p>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeDoctorSelection()">
                                                تغيير الطبيب
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="doctor_id" value="{{ $doctor->id }}" id="selected_doctor_id">
                            @else
                                <!-- Doctor Selection -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="department_select" class="form-label">القسم الطبي</label>
                                        <select class="form-select" id="department_select" onchange="filterDoctorsByDepartment()">
                                            <option value="">اختر القسم الطبي</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="doctor_search" class="form-label">البحث عن طبيب</label>
                                        <input type="text" class="form-control" id="doctor_search" 
                                               placeholder="ابحث بالاسم أو التخصص..." onkeyup="searchDoctors()">
                                    </div>
                                </div>
                                
                                <div class="doctors-grid" id="doctors_list">
                                    @foreach($doctors->take(6) as $doc)
                                    <div class="doctor-selection-card" data-doctor-id="{{ $doc->id }}" 
                                         data-department="{{ $doc->department_id }}" 
                                         data-name="{{ strtolower($doc->name) }}" 
                                         data-specialty="{{ strtolower($doc->specialization) }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 text-center">
                                                @if($doc->photo)
                                                    <img src="{{ asset('storage/' . $doc->photo) }}" alt="{{ $doc->name }}" 
                                                         class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-user-md"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="mb-1">د. {{ $doc->name }}</h6>
                                                <p class="text-primary small mb-1">{{ $doc->specialization }}</p>
                                                <p class="text-muted small mb-0">{{ $doc->years_of_experience }} سنة خبرة</p>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        onclick="selectDoctor({{ $doc->id }}, '{{ $doc->name }}', '{{ $doc->specialization }}')">
                                                    اختيار
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <input type="hidden" name="doctor_id" id="selected_doctor_id" required>
                            @endif
                        </div>
                    </div>

                    <!-- Step 2: Date & Time Selection -->
                    <div class="card mb-4 step-content" id="step-2" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                الخطوة 2: اختيار التاريخ والوقت
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="appointment_date" class="form-label">تاريخ الموعد</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                           max="{{ date('Y-m-d', strtotime('+30 days')) }}" 
                                           onchange="loadAvailableSlots()" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">نوع الموعد</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="emergency" value="0" id="regular" checked>
                                        <label class="form-check-label" for="regular">
                                            موعد عادي
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="emergency" value="1" id="urgent">
                                        <label class="form-check-label" for="urgent">
                                            موعد عاجل
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="available_slots_container" style="display: none;">
                                <label class="form-label">الأوقات المتاحة</label>
                                <div id="available_slots"></div>
                                <input type="hidden" name="appointment_time" id="selected_time" required>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Patient Information -->
                    <div class="card mb-4 step-content" id="step-3" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2 text-primary"></i>
                                الخطوة 3: بيانات المريض
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="patient_name" class="form-label">الاسم الكامل *</label>
                                    <input type="text" class="form-control" id="patient_name" name="patient_name" 
                                           value="{{ old('patient_name') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="patient_phone" class="form-label">رقم الهاتف *</label>
                                    <input type="tel" class="form-control" id="patient_phone" name="patient_phone" 
                                           value="{{ old('patient_phone') }}" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="patient_email" class="form-label">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" id="patient_email" name="patient_email" 
                                           value="{{ old('patient_email') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="national_id" class="form-label">رقم الهوية الوطنية *</label>
                                    <input type="text" class="form-control" id="national_id" name="national_id" 
                                           value="{{ old('national_id') }}" maxlength="10" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">تاريخ الميلاد *</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="{{ old('date_of_birth') }}" max="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">الجنس *</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">اختر الجنس</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">العنوان</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="{{ old('address') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact" class="form-label">رقم الطوارئ</label>
                                    <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact" 
                                           value="{{ old('emergency_contact') }}">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reason" class="form-label">سبب الزيارة</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" 
                                          placeholder="اذكر سبب الزيارة أو الأعراض...">{{ old('reason') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Confirmation -->
                    <div class="card mb-4 step-content" id="step-4" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2 text-primary"></i>
                                الخطوة 4: مراجعة وتأكيد الحجز
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="booking_summary">
                                <!-- Summary will be populated by JavaScript -->
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms_agreement" required>
                                <label class="form-check-label" for="terms_agreement">
                                    أوافق على <a href="#" class="text-primary">شروط وأحكام</a> الخدمة و<a href="#" class="text-primary">سياسة الخصوصية</a>
                                </label>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>ملاحظة:</strong> سيتم إرسال رسالة تأكيد عبر الهاتف والبريد الإلكتروني. يرجى الحضور قبل 15 دقيقة من موعدك.
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="prev_btn" onclick="previousStep()" style="display: none;">
                            <i class="fas fa-arrow-right me-2"></i>
                            السابق
                        </button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-facebook" id="next_btn" onclick="nextStep()">
                                التالي
                                <i class="fas fa-arrow-left ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submit_btn" style="display: none;">
                                <i class="fas fa-check me-2"></i>
                                تأكيد الحجز
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;" data-aos="fade-left">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            معلومات مهمة
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">ساعات العمل</h6>
                            <p class="small mb-0">
                                السبت - الخميس: 8:00 ص - 10:00 م<br>
                                الجمعة: 2:00 م - 10:00 م
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">سياسة الإلغاء</h6>
                            <p class="small mb-0">
                                يمكن إلغاء أو تعديل الموعد قبل 24 ساعة من الموعد المحدد
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">المستندات المطلوبة</h6>
                            <ul class="small mb-0">
                                <li>الهوية الوطنية أو الإقامة</li>
                                <li>بطاقة التأمين (إن وجدت)</li>
                                <li>التقارير الطبية السابقة</li>
                            </ul>
                        </div>
                        
                        <div class="text-center">
                            <p class="small text-muted mb-2">هل تحتاج مساعدة؟</p>
                            <a href="tel:+966123456789" class="btn btn-outline-primary w-100">
                                <i class="fas fa-phone me-2"></i>
                                اتصل بنا
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 4;

    // Initialize form
    document.addEventListener('DOMContentLoaded', function() {
        updateStepDisplay();
        
        @if($doctor)
            // If doctor is pre-selected, move to step 2
            nextStep();
        @endif
    });

    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
                
                if (currentStep === 4) {
                    generateBookingSummary();
                }
            }
        }
    }

    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepDisplay();
        }
    }

    function updateStepDisplay() {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(step => {
            step.style.display = 'none';
        });
        
        // Show current step
        document.getElementById(`step-${currentStep}`).style.display = 'block';
        
        // Update step indicators
        document.querySelectorAll('.step-item').forEach((item, index) => {
            item.classList.remove('active', 'completed');
            if (index + 1 < currentStep) {
                item.classList.add('completed');
            } else if (index + 1 === currentStep) {
                item.classList.add('active');
            }
        });
        
        // Update navigation buttons
        document.getElementById('prev_btn').style.display = currentStep > 1 ? 'block' : 'none';
        document.getElementById('next_btn').style.display = currentStep < totalSteps ? 'block' : 'none';
        document.getElementById('submit_btn').style.display = currentStep === totalSteps ? 'block' : 'none';
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        switch (currentStep) {
            case 1:
                const doctorId = document.getElementById('selected_doctor_id').value;
                if (!doctorId) {
                    alert('يرجى اختيار طبيب');
                    return false;
                }
                break;
                
            case 2:
                const date = document.getElementById('appointment_date').value;
                const time = document.getElementById('selected_time').value;
                if (!date) {
                    alert('يرجى اختيار تاريخ الموعد');
                    return false;
                }
                if (!time) {
                    alert('يرجى اختيار وقت الموعد');
                    return false;
                }
                break;
                
            case 3:
                const requiredFields = ['patient_name', 'patient_phone', 'national_id', 'date_of_birth', 'gender'];
                for (let field of requiredFields) {
                    const element = document.getElementById(field);
                    if (!element.value.trim()) {
                        alert(`يرجى ملء حقل ${element.previousElementSibling.textContent}`);
                        element.focus();
                        return false;
                    }
                }
                
                // Validate national ID
                const nationalId = document.getElementById('national_id').value;
                if (nationalId.length !== 10 || !/^\d+$/.test(nationalId)) {
                    alert('رقم الهوية الوطنية يجب أن يكون 10 أرقام');
                    return false;
                }
                break;
                
            case 4:
                const termsAgreement = document.getElementById('terms_agreement');
                if (!termsAgreement.checked) {
                    alert('يرجى الموافقة على الشروط والأحكام');
                    return false;
                }
                break;
        }
        return true;
    }

    function selectDoctor(doctorId, doctorName, specialty) {
        document.getElementById('selected_doctor_id').value = doctorId;
        
        // Update UI to show selected doctor
        const selectedCard = `
            <div class="selected-doctor-card p-3 border rounded bg-light">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">د. ${doctorName}</h6>
                        <p class="text-primary small mb-0">${specialty}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeDoctorSelection()">
                            تغيير الطبيب
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('doctors_list').innerHTML = selectedCard;
    }

    function changeDoctorSelection() {
        location.reload(); // Simple way to reset doctor selection
    }

    function filterDoctorsByDepartment() {
        const departmentId = document.getElementById('department_select').value;
        const doctorCards = document.querySelectorAll('.doctor-selection-card');
        
        doctorCards.forEach(card => {
            if (!departmentId || card.dataset.department === departmentId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function searchDoctors() {
        const searchTerm = document.getElementById('doctor_search').value.toLowerCase();
        const doctorCards = document.querySelectorAll('.doctor-selection-card');
        
        doctorCards.forEach(card => {
            const name = card.dataset.name;
            const specialty = card.dataset.specialty;
            
            if (name.includes(searchTerm) || specialty.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function loadAvailableSlots() {
        const doctorId = document.getElementById('selected_doctor_id').value;
        const date = document.getElementById('appointment_date').value;
        
        if (!doctorId || !date) return;
        
        const container = document.getElementById('available_slots_container');
        const slotsDiv = document.getElementById('available_slots');
        
        // Show loading
        slotsDiv.innerHTML = '<div class="text-center"><div class="loading"></div> جاري تحميل المواعيد المتاحة...</div>';
        container.style.display = 'block';
        
        // Fetch available slots
        fetch(`{{ route('public.available-slots') }}?doctor_id=${doctorId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.slots && Object.keys(data.slots).length > 0) {
                    let slotsHtml = '';
                    
                    Object.keys(data.slots).forEach(period => {
                        if (data.slots[period].length > 0) {
                            slotsHtml += `<h6 class="mt-3 mb-2">${period === 'morning' ? 'الفترة الصباحية' : 'الفترة المسائية'}</h6>`;
                            slotsHtml += '<div class="d-flex flex-wrap gap-2">';
                            
                            data.slots[period].forEach(time => {
                                slotsHtml += `
                                    <button type="button" class="btn btn-outline-primary time-slot" 
                                            onclick="selectTime('${time}')" data-time="${time}">
                                        ${time}
                                    </button>
                                `;
                            });
                            
                            slotsHtml += '</div>';
                        }
                    });
                    
                    slotsDiv.innerHTML = slotsHtml;
                } else {
                    slotsDiv.innerHTML = '<div class="alert alert-warning">لا توجد مواعيد متاحة في هذا التاريخ. يرجى اختيار تاريخ آخر.</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                slotsDiv.innerHTML = '<div class="alert alert-danger">حدث خطأ في تحميل المواعيد. يرجى المحاولة مرة أخرى.</div>';
            });
    }

    function selectTime(time) {
        // Remove active class from all time slots
        document.querySelectorAll('.time-slot').forEach(slot => {
            slot.classList.remove('btn-primary');
            slot.classList.add('btn-outline-primary');
        });
        
        // Add active class to selected slot
        event.target.classList.remove('btn-outline-primary');
        event.target.classList.add('btn-primary');
        
        // Set selected time
        document.getElementById('selected_time').value = time;
    }

    function generateBookingSummary() {
        const doctorName = document.querySelector('.selected-doctor-card h6, .selected-doctor-card h5')?.textContent || 'غير محدد';
        const date = document.getElementById('appointment_date').value;
        const time = document.getElementById('selected_time').value;
        const patientName = document.getElementById('patient_name').value;
        const patientPhone = document.getElementById('patient_phone').value;
        const reason = document.getElementById('reason').value;
        const emergency = document.querySelector('input[name="emergency"]:checked').value === '1';
        
        const formattedDate = new Date(date).toLocaleDateString('ar-SA', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const summaryHtml = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary">الطبيب</h6>
                    <p class="mb-0">${doctorName}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary">التاريخ والوقت</h6>
                    <p class="mb-0">${formattedDate}<br>${time}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary">اسم المريض</h6>
                    <p class="mb-0">${patientName}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary">رقم الهاتف</h6>
                    <p class="mb-0">${patientPhone}</p>
                </div>
                ${emergency ? '<div class="col-12 mb-3"><span class="badge bg-warning">موعد عاجل</span></div>' : ''}
                ${reason ? `<div class="col-12 mb-3"><h6 class="text-primary">سبب الزيارة</h6><p class="mb-0">${reason}</p></div>` : ''}
            </div>
        `;
        
        document.getElementById('booking_summary').innerHTML = summaryHtml;
    }

    // Form submission
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submit_btn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<span class="loading"></span> جاري الحجز...';
        submitBtn.disabled = true;
        
        // The form will submit normally, but we show loading state
        setTimeout(() => {
            if (!this.submitted) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }, 5000);
        
        this.submitted = true;
    });
</script>
@endpush

@push('styles')
<style>
    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #E5E7EB;
        color: #6B7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .step-item.active .step-circle {
        background: var(--facebook-blue);
        color: white;
    }
    
    .step-item.completed .step-circle {
        background: var(--success);
        color: white;
    }
    
    .step-label {
        font-size: 0.875rem;
        color: #6B7280;
        text-align: center;
    }
    
    .step-item.active .step-label {
        color: var(--facebook-blue);
        font-weight: 600;
    }
    
    .step-line {
        flex: 1;
        height: 2px;
        background: #E5E7EB;
        margin: 0 1rem;
        align-self: flex-start;
        margin-top: 20px;
    }
    
    .doctor-selection-card {
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .doctor-selection-card:hover {
        border-color: var(--facebook-blue);
        background: rgba(24, 119, 242, 0.05);
    }
    
    .selected-doctor-card {
        border: 2px solid var(--facebook-blue) !important;
        background: rgba(24, 119, 242, 0.1) !important;
    }
    
    .time-slot {
        min-width: 80px;
        margin-bottom: 0.5rem;
    }
    
    .doctors-grid {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .sticky-top {
        position: sticky !important;
    }
    
    @media (max-width: 991px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
        
        .step-item {
            flex-direction: row;
            text-align: left;
        }
        
        .step-circle {
            margin-bottom: 0;
            margin-right: 0.5rem;
        }
        
        .step-line {
            display: none;
        }
    }
    
    @media (max-width: 576px) {
        .step-label {
            font-size: 0.75rem;
        }
        
        .step-circle {
            width: 30px;
            height: 30px;
            font-size: 0.875rem;
        }
    }
</style>
@endpush