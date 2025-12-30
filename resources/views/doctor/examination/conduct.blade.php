@extends('layouts.app')

@section('title', 'إجراء الكشف الطبي')

@section('content')
<div class="container-fluid" dir="rtl">
    <!-- Patient Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">كشف طبي - {{ $patient->name }}</h3>
                            <div class="row">
                                <div class="col-md-3">
                                    <small><i class="fas fa-id-card"></i> الرقم الطبي: {{ $patient->medical_number }}</small>
                                </div>
                                <div class="col-md-3">
                                    <small><i class="fas fa-birthday-cake"></i> العمر: {{ $patient->age }} سنة</small>
                                </div>
                                <div class="col-md-3">
                                    <small><i class="fas fa-venus-mars"></i> الجنس: {{ $patient->gender === 'male' ? 'ذكر' : 'أنثى' }}</small>
                                </div>
                                <div class="col-md-3">
                                    <small><i class="fas fa-phone"></i> الهاتف: {{ $patient->phone }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-light btn-sm" onclick="viewPatientHistory()">
                                <i class="fas fa-history"></i> التاريخ الطبي
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="viewAllergies()">
                                <i class="fas fa-exclamation-triangle"></i> الحساسية
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="examinationForm" action="{{ route('doctor.examination.save', $patient) }}" method="POST">
        @csrf
        <div class="row">
            <!-- Left Column - Examination Form -->
            <div class="col-lg-8">
                <!-- Chief Complaint -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">الشكوى الرئيسية</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="chief_complaint">وصف الشكوى الرئيسية *</label>
                            <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3" 
                                placeholder="اكتب الشكوى الرئيسية للمريض..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Symptoms -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">الأعراض</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>الأعراض الشائعة</h6>
                                <div class="form-check-list">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="fever" id="symptom_fever">
                                        <label class="form-check-label" for="symptom_fever">حمى</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="headache" id="symptom_headache">
                                        <label class="form-check-label" for="symptom_headache">صداع</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="cough" id="symptom_cough">
                                        <label class="form-check-label" for="symptom_cough">سعال</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="fatigue" id="symptom_fatigue">
                                        <label class="form-check-label" for="symptom_fatigue">إرهاق</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="nausea" id="symptom_nausea">
                                        <label class="form-check-label" for="symptom_nausea">غثيان</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>الأعراض الحرجة</h6>
                                <div class="form-check-list">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="chest_pain" id="symptom_chest_pain">
                                        <label class="form-check-label text-danger" for="symptom_chest_pain">ألم في الصدر</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="difficulty_breathing" id="symptom_breathing">
                                        <label class="form-check-label text-danger" for="symptom_breathing">صعوبة في التنفس</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="severe_headache" id="symptom_severe_headache">
                                        <label class="form-check-label text-danger" for="symptom_severe_headache">صداع شديد</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="high_fever" id="symptom_high_fever">
                                        <label class="form-check-label text-danger" for="symptom_high_fever">حمى عالية</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="custom_symptoms">أعراض أخرى</label>
                            <textarea class="form-control" id="custom_symptoms" name="custom_symptoms" rows="2" 
                                placeholder="اكتب أي أعراض أخرى..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Vital Signs -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">العلامات الحيوية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="temperature">درجة الحرارة (°C)</label>
                                    <input type="number" class="form-control" id="temperature" name="vital_signs[temperature]" 
                                        step="0.1" min="30" max="45" placeholder="36.5">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="heart_rate">معدل النبض (نبضة/دقيقة)</label>
                                    <input type="number" class="form-control" id="heart_rate" name="vital_signs[heart_rate]" 
                                        min="30" max="200" placeholder="72">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="respiratory_rate">معدل التنفس (نفس/دقيقة)</label>
                                    <input type="number" class="form-control" id="respiratory_rate" name="vital_signs[respiratory_rate]" 
                                        min="8" max="60" placeholder="16">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="blood_pressure_systolic">ضغط الدم الانقباضي</label>
                                    <input type="number" class="form-control" id="blood_pressure_systolic" 
                                        name="vital_signs[blood_pressure_systolic]" min="60" max="300" placeholder="120">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="blood_pressure_diastolic">ضغط الدم الانبساطي</label>
                                    <input type="number" class="form-control" id="blood_pressure_diastolic" 
                                        name="vital_signs[blood_pressure_diastolic]" min="40" max="200" placeholder="80">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="oxygen_saturation">تشبع الأكسجين (%)</label>
                                    <input type="number" class="form-control" id="oxygen_saturation" 
                                        name="vital_signs[oxygen_saturation]" min="70" max="100" placeholder="98">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Physical Examination -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">الفحص السريري</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="physical_examination">نتائج الفحص السريري *</label>
                            <textarea class="form-control" id="physical_examination" name="physical_examination" rows="5" 
                                placeholder="اكتب نتائج الفحص السريري..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Diagnosis -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">التشخيص</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="diagnosis">التشخيص النهائي *</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" 
                                placeholder="اكتب التشخيص النهائي..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Treatment Plan -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">خطة العلاج</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="treatment_plan">خطة العلاج *</label>
                            <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="4" 
                                placeholder="اكتب خطة العلاج المقترحة..." required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="follow_up_date">موعد المتابعة</label>
                                    <input type="date" class="form-control" id="follow_up_date" name="follow_up_date" 
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">ملاحظات إضافية</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="notes">ملاحظات</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                placeholder="أي ملاحظات إضافية..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-lg btn-block">
                                    <i class="fas fa-save"></i> حفظ الكشف
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="saveDraft()">
                                    <i class="fas fa-file-alt"></i> حفظ كمسودة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Patient Info & History -->
            <div class="col-lg-4">
                <!-- Allergies & Warnings -->
                @if(count($allergies) > 0 || count($warnings) > 0)
                <div class="card shadow mb-4 border-left-danger">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">تحذيرات مهمة</h6>
                    </div>
                    <div class="card-body">
                        @if(count($allergies) > 0)
                            <h6 class="text-danger">الحساسية:</h6>
                            <ul class="list-unstyled">
                                @foreach($allergies as $allergy)
                                <li><i class="fas fa-exclamation-triangle text-danger"></i> {{ $allergy }}</li>
                                @endforeach
                            </ul>
                        @endif
                        
                        @if(count($warnings) > 0)
                            <h6 class="text-warning">تحذيرات أخرى:</h6>
                            <ul class="list-unstyled">
                                @foreach($warnings as $warning)
                                <li><i class="fas fa-exclamation-circle text-warning"></i> {{ $warning }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Current Prescriptions -->
                @if($currentPrescriptions->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">الأدوية الحالية</h6>
                    </div>
                    <div class="card-body">
                        @foreach($currentPrescriptions as $prescription)
                            <div class="mb-3 p-2 bg-light rounded">
                                <small class="text-muted">{{ $prescription->created_at->format('d/m/Y') }}</small>
                                @foreach($prescription->prescriptionItems as $item)
                                <div class="d-flex justify-content-between">
                                    <span>{{ $item->medication->name ?? 'دواء' }}</span>
                                    <small class="text-muted">{{ $item->dosage }}</small>
                                </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Pending Tests -->
                @if($pendingTests->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">الفحوصات المعلقة</h6>
                    </div>
                    <div class="card-body">
                        @foreach($pendingTests as $test)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $test->name }}</span>
                            <span class="badge badge-warning">معلق</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Recent Medical History -->
                @if($medicalHistory->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">التاريخ الطبي الحديث</h6>
                    </div>
                    <div class="card-body">
                        @foreach($medicalHistory->take(3) as $record)
                        <div class="mb-3 p-2 bg-light rounded">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $record->diagnosis }}</strong>
                                <small class="text-muted">{{ $record->created_at->format('d/m/Y') }}</small>
                            </div>
                            <p class="mb-0 text-muted small">{{ Str::limit($record->treatment_plan, 100) }}</p>
                        </div>
                        @endforeach
                        @if($medicalHistory->count() > 3)
                        <button type="button" class="btn btn-sm btn-outline-primary btn-block" onclick="viewFullHistory()">
                            عرض التاريخ الكامل ({{ $medicalHistory->count() }} سجل)
                        </button>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">إجراءات سريعة</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="openPrescriptionModal()">
                                <i class="fas fa-prescription-bottle-alt"></i> وصفة طبية
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="openLabOrderModal()">
                                <i class="fas fa-flask"></i> طلب تحاليل
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="openRadiologyModal()">
                                <i class="fas fa-x-ray"></i> طلب أشعة
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openConsultationModal()">
                                <i class="fas fa-user-md"></i> استشارة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('doctor.modals.prescription-quick')
@include('doctor.modals.lab-order-quick')
@include('doctor.modals.radiology-quick')
@include('doctor.modals.consultation-quick')

@endsection

@push('styles')
<style>
.form-check-list .form-check {
    margin-bottom: 0.5rem;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.card-header.bg-danger {
    background-color: #e74a3b !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-save functionality
    let autoSaveTimer;
    
    $('#examinationForm input, #examinationForm textarea').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(saveDraft, 30000); // Auto-save after 30 seconds of inactivity
    });

    // Form validation
    $('#examinationForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitExamination();
        }
    });

    // Vital signs validation
    $('#temperature, #heart_rate, #blood_pressure_systolic, #blood_pressure_diastolic, #oxygen_saturation').on('blur', function() {
        validateVitalSign(this);
    });
});

function validateForm() {
    let isValid = true;
    
    // Check required fields
    const requiredFields = ['chief_complaint', 'physical_examination', 'diagnosis', 'treatment_plan'];
    
    requiredFields.forEach(function(field) {
        const element = document.getElementById(field);
        if (!element.value.trim()) {
            showFieldError(element, 'هذا الحقل مطلوب');
            isValid = false;
        } else {
            clearFieldError(element);
        }
    });

    // Check if at least one symptom is selected
    const symptoms = document.querySelectorAll('input[name="symptoms[]"]:checked');
    if (symptoms.length === 0 && !document.getElementById('custom_symptoms').value.trim()) {
        alert('يرجى تحديد الأعراض أو كتابة أعراض مخصصة');
        isValid = false;
    }

    return isValid;
}

function validateVitalSign(element) {
    const value = parseFloat(element.value);
    const fieldName = element.name.split('[')[1].split(']')[0];
    
    const ranges = {
        'temperature': { min: 35, max: 42, normal: [36, 37.5] },
        'heart_rate': { min: 40, max: 180, normal: [60, 100] },
        'blood_pressure_systolic': { min: 70, max: 250, normal: [90, 140] },
        'blood_pressure_diastolic': { min: 40, max: 150, normal: [60, 90] },
        'oxygen_saturation': { min: 80, max: 100, normal: [95, 100] }
    };

    if (value && ranges[fieldName]) {
        const range = ranges[fieldName];
        
        if (value < range.min || value > range.max) {
            showFieldWarning(element, 'قيمة غير طبيعية - يرجى التحقق');
        } else if (value < range.normal[0] || value > range.normal[1]) {
            showFieldWarning(element, 'قيمة خارج النطاق الطبيعي');
        } else {
            clearFieldError(element);
        }
    }
}

function submitExamination() {
    const formData = new FormData(document.getElementById('examinationForm'));
    
    // Show loading
    Swal.fire({
        title: 'جاري الحفظ...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(document.getElementById('examinationForm').action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'تم الحفظ بنجاح',
                text: data.message,
                confirmButtonText: 'الخطوات التالية'
            }).then(() => {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.href = '/doctor/dashboard';
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'خطأ في الحفظ',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'خطأ في الاتصال',
            text: 'حدث خطأ أثناء حفظ البيانات'
        });
    });
}

function saveDraft() {
    const formData = new FormData(document.getElementById('examinationForm'));
    formData.append('is_draft', '1');
    
    fetch('/doctor/examination/save-draft', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('تم حفظ المسودة', 'success');
        }
    })
    .catch(error => {
        console.error('Draft save error:', error);
    });
}

function showFieldError(element, message) {
    element.classList.add('is-invalid');
    
    let feedback = element.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        element.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function showFieldWarning(element, message) {
    element.classList.add('is-warning');
    element.style.borderColor = '#f6c23e';
    
    let feedback = element.parentNode.querySelector('.warning-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'warning-feedback text-warning small';
        element.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearFieldError(element) {
    element.classList.remove('is-invalid', 'is-warning');
    element.style.borderColor = '';
    
    const feedback = element.parentNode.querySelector('.invalid-feedback, .warning-feedback');
    if (feedback) {
        feedback.remove();
    }
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Quick action functions
function openPrescriptionModal() {
    $('#prescriptionQuickModal').modal('show');
}

function openLabOrderModal() {
    $('#labOrderQuickModal').modal('show');
}

function openRadiologyModal() {
    $('#radiologyQuickModal').modal('show');
}

function openConsultationModal() {
    $('#consultationQuickModal').modal('show');
}

function viewPatientHistory() {
    window.open(`/patients/{{ $patient->id }}/medical-history`, '_blank');
}

function viewAllergies() {
    // Show allergies modal
    alert('عرض تفاصيل الحساسية');
}

function viewFullHistory() {
    window.open(`/patients/{{ $patient->id }}/medical-history`, '_blank');
}
</script>
@endpush