@extends('layouts.app')

@section('title', 'الخطوات التالية - ' . $patient->name)

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="row">
        <!-- Patient Summary -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ملخص الكشف</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h5>{{ $patient->name }}</h5>
                        <p class="text-muted">{{ $medicalRecord->examination_date->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>الشكوى الرئيسية:</strong>
                        <p class="text-muted">{{ $medicalRecord->chief_complaint }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>التشخيص:</strong>
                        <p class="text-muted">{{ $medicalRecord->diagnosis }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>خطة العلاج:</strong>
                        <p class="text-muted">{{ Str::limit($medicalRecord->treatment_plan, 150) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Steps Options -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-route me-2"></i>
                        الخطوات التالية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Write Prescription -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-prescription fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">كتابة روشتة طبية</h5>
                                    <p class="card-text">وصف الأدوية والعلاجات المطلوبة للمريض</p>
                                    <button class="btn btn-primary" onclick="openPrescriptionModal()">
                                        <i class="fas fa-plus me-2"></i>كتابة روشتة
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Order Lab Tests -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-flask fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">طلب تحاليل مخبرية</h5>
                                    <p class="card-text">طلب التحاليل المطلوبة لتأكيد التشخيص</p>
                                    <button class="btn btn-success" onclick="orderLabTests()">
                                        <i class="fas fa-plus me-2"></i>طلب تحاليل
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Order Radiology -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-x-ray fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">طلب أشعة</h5>
                                    <p class="card-text">طلب الفحوصات الإشعاعية المطلوبة</p>
                                    <button class="btn btn-info" onclick="orderRadiology()">
                                        <i class="fas fa-plus me-2"></i>طلب أشعة
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Transfer to Specialist -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-warning h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-md fa-3x text-warning mb-3"></i>
                                    <h5 class="card-title">تحويل لمختص</h5>
                                    <p class="card-text">تحويل المريض لطبيب مختص في تخصص آخر</p>
                                    <button class="btn btn-warning" onclick="openTransferModal()">
                                        <i class="fas fa-share me-2"></i>تحويل لمختص
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Book Follow-up -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-secondary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-plus fa-3x text-secondary mb-3"></i>
                                    <h5 class="card-title">حجز موعد متابعة</h5>
                                    <p class="card-text">تحديد موعد للمتابعة مع نفس الطبيب</p>
                                    <button class="btn btn-secondary" onclick="openFollowUpModal()">
                                        <i class="fas fa-calendar me-2"></i>حجز متابعة
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Complete Visit -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-dark h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-3x text-dark mb-3"></i>
                                    <h5 class="card-title">إنهاء الزيارة</h5>
                                    <p class="card-text">إنهاء الزيارة والعودة للوحة التحكم</p>
                                    <button class="btn btn-dark" onclick="completeVisit()">
                                        <i class="fas fa-check me-2"></i>إنهاء الزيارة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Prescription Modal -->
<div class="modal fade" id="prescriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">كتابة روشتة طبية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="prescriptionForm">
                @csrf
                <div class="modal-body">
                    <div id="medicationsContainer">
                        <div class="medication-item border p-3 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">اسم الدواء *</label>
                                    <input type="text" class="form-control" name="medications[0][name]" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الجرعة *</label>
                                    <input type="text" class="form-control" name="medications[0][dosage]" 
                                           placeholder="مثال: 500 مجم" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">عدد المرات *</label>
                                    <input type="text" class="form-control" name="medications[0][frequency]" 
                                           placeholder="مثال: 3 مرات يومياً" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">المدة *</label>
                                    <input type="text" class="form-control" name="medications[0][duration]" 
                                           placeholder="مثال: 7 أيام" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">تعليمات الاستخدام</label>
                                    <textarea class="form-control" name="medications[0][instructions]" rows="2"
                                              placeholder="تعليمات خاصة للمريض..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary" onclick="addMedication()">
                        <i class="fas fa-plus me-2"></i>إضافة دواء آخر
                    </button>
                    
                    <div class="mt-3">
                        <label class="form-label">ملاحظات عامة</label>
                        <textarea class="form-control" name="notes" rows="3"
                                  placeholder="ملاحظات عامة للروشتة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ الروشتة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تحويل لطبيب مختص</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الطبيب المختص *</label>
                        <select class="form-select" name="target_doctor_id" required>
                            <option value="">اختر الطبيب المختص</option>
                            @foreach($availableDoctors as $availableDoctor)
                            <option value="{{ $availableDoctor->id }}">
                                د. {{ $availableDoctor->user->name }} - {{ $availableDoctor->specialization ?? 'طبيب عام' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">سبب التحويل *</label>
                        <textarea class="form-control" name="transfer_reason" rows="3" required
                                  placeholder="اكتب سبب التحويل..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">درجة الأولوية *</label>
                            <select class="form-select" name="urgency" required>
                                <option value="routine">عادي</option>
                                <option value="urgent">عاجل</option>
                                <option value="emergency">طوارئ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">تاريخ الموعد المقترح</label>
                            <input type="date" class="form-control" name="appointment_date" 
                                   min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">ملاحظات إضافية</label>
                        <textarea class="form-control" name="notes" rows="2"
                                  placeholder="أي ملاحظات إضافية للطبيب المختص..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">تحويل المريض</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Follow-up Modal -->
<div class="modal fade" id="followUpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">حجز موعد متابعة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="followUpForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">تاريخ المتابعة *</label>
                            <input type="date" class="form-control" name="follow_up_date" required
                                   min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">وقت المتابعة *</label>
                            <input type="time" class="form-control" name="follow_up_time" required>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">سبب المتابعة *</label>
                        <textarea class="form-control" name="follow_up_reason" rows="3" required
                                  placeholder="اكتب سبب المتابعة..."></textarea>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" name="notes" rows="2"
                                  placeholder="أي ملاحظات إضافية..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-secondary">حجز الموعد</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let medicationCount = 1;

// Open modals
function openPrescriptionModal() {
    $('#prescriptionModal').modal('show');
}

function openTransferModal() {
    $('#transferModal').modal('show');
}

function openFollowUpModal() {
    $('#followUpModal').modal('show');
}

// Add medication to prescription
function addMedication() {
    const container = $('#medicationsContainer');
    const newMedication = `
        <div class="medication-item border p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6>دواء ${medicationCount + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMedication(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">اسم الدواء *</label>
                    <input type="text" class="form-control" name="medications[${medicationCount}][name]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الجرعة *</label>
                    <input type="text" class="form-control" name="medications[${medicationCount}][dosage]" 
                           placeholder="مثال: 500 مجم" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">عدد المرات *</label>
                    <input type="text" class="form-control" name="medications[${medicationCount}][frequency]" 
                           placeholder="مثال: 3 مرات يومياً" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">المدة *</label>
                    <input type="text" class="form-control" name="medications[${medicationCount}][duration]" 
                           placeholder="مثال: 7 أيام" required>
                </div>
                <div class="col-12">
                    <label class="form-label">تعليمات الاستخدام</label>
                    <textarea class="form-control" name="medications[${medicationCount}][instructions]" rows="2"
                              placeholder="تعليمات خاصة للمريض..."></textarea>
                </div>
            </div>
        </div>
    `;
    container.append(newMedication);
    medicationCount++;
}

function removeMedication(button) {
    $(button).closest('.medication-item').remove();
}

// Form submissions
$('#prescriptionForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("doctor.prescription.write", $medicalRecord) }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#prescriptionModal').modal('hide');
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            showAlert('error', 'حدث خطأ أثناء حفظ الروشتة');
        }
    });
});

$('#transferForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("doctor.transfer.specialist", $medicalRecord) }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#transferModal').modal('hide');
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            showAlert('error', 'حدث خطأ أثناء تحويل المريض');
        }
    });
});

$('#followUpForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("doctor.follow-up.book", $medicalRecord) }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#followUpModal').modal('hide');
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            showAlert('error', 'حدث خطأ أثناء حجز موعد المتابعة');
        }
    });
});

// Quick actions
function orderLabTests() {
    window.open('/doctor/lab-orders/create?patient_id={{ $patient->id }}&medical_record_id={{ $medicalRecord->id }}', '_blank');
}

function orderRadiology() {
    window.open('/doctor/radiology-orders/create?patient_id={{ $patient->id }}&medical_record_id={{ $medicalRecord->id }}', '_blank');
}

function completeVisit() {
    if (confirm('هل تريد إنهاء الزيارة والعودة للوحة التحكم؟')) {
        window.location.href = '{{ route("doctor.integrated.dashboard") }}';
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush