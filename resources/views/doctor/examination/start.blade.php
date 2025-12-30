@extends('layouts.app')

@section('title', 'الكشف الطبي - ' . $appointment->patient->name)

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="row">
        <!-- Patient Info -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">معلومات المريض</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-lg mx-auto">
                            <div class="avatar-title bg-primary text-white rounded-circle">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="mt-2">{{ $appointment->patient->name }}</h5>
                        <p class="text-muted">{{ $appointment->patient->age ?? 'غير محدد' }} سنة</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <strong>رقم الهوية:</strong>
                            <p>{{ $appointment->patient->national_id ?? 'غير محدد' }}</p>
                        </div>
                        <div class="col-6">
                            <strong>رقم الجوال:</strong>
                            <p>{{ $appointment->patient->phone }}</p>
                        </div>
                        <div class="col-6">
                            <strong>الجنس:</strong>
                            <p>{{ $appointment->patient->gender === 'male' ? 'ذكر' : 'أنثى' }}</p>
                        </div>
                        <div class="col-6">
                            <strong>نوع المريض:</strong>
                            @if($appointment->patient->insurancePolicy)
                                <span class="badge bg-success">مؤمن</span>
                                <small class="d-block">{{ $appointment->patient->insurancePolicy->company->name ?? 'شركة التأمين' }}</small>
                            @else
                                <span class="badge bg-warning">نقدي</span>
                            @endif
                        </div>
                    </div>

                    @if(count($allergies) > 0)
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>الحساسية</h6>
                        <ul class="mb-0">
                            @foreach($allergies as $allergy)
                            <li>{{ $allergy }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Medical History -->
            @if($medicalHistory->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">التاريخ الطبي</h6>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @foreach($medicalHistory as $record)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $record->examination_date->format('d/m/Y') }}</strong>
                            <small class="text-muted">د. {{ $record->doctor->user->name }}</small>
                        </div>
                        <p class="mb-1"><strong>التشخيص:</strong> {{ $record->diagnosis }}</p>
                        <p class="mb-0 text-muted">{{ Str::limit($record->treatment_plan, 100) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Examination Form -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-stethoscope me-2"></i>
                        الكشف الطبي - {{ $appointment->appointment_time->format('H:i') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form id="examinationForm">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        
                        <!-- Chief Complaint -->
                        <div class="mb-3">
                            <label class="form-label">الشكوى الرئيسية *</label>
                            <textarea class="form-control" name="chief_complaint" rows="3" required 
                                      placeholder="اكتب الشكوى الرئيسية للمريض..."></textarea>
                        </div>

                        <!-- Symptoms -->
                        <div class="mb-3">
                            <label class="form-label">الأعراض</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="حمى" id="fever">
                                        <label class="form-check-label" for="fever">حمى</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="صداع" id="headache">
                                        <label class="form-check-label" for="headache">صداع</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="غثيان" id="nausea">
                                        <label class="form-check-label" for="nausea">غثيان</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="سعال" id="cough">
                                        <label class="form-check-label" for="cough">سعال</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="ضيق تنفس" id="breathing">
                                        <label class="form-check-label" for="breathing">ضيق تنفس</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="symptoms[]" value="ألم في الصدر" id="chest_pain">
                                        <label class="form-check-label" for="chest_pain">ألم في الصدر</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vital Signs -->
                        <div class="mb-3">
                            <label class="form-label">العلامات الحيوية</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">درجة الحرارة (°C)</label>
                                    <input type="number" class="form-control" name="vital_signs[temperature]" 
                                           step="0.1" min="30" max="45" placeholder="37.0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">ضغط الدم</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="vital_signs[blood_pressure_systolic]" 
                                               placeholder="120" min="60" max="300">
                                        <span class="input-group-text">/</span>
                                        <input type="number" class="form-control" name="vital_signs[blood_pressure_diastolic]" 
                                               placeholder="80" min="40" max="200">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">النبض</label>
                                    <input type="number" class="form-control" name="vital_signs[heart_rate]" 
                                           placeholder="72" min="30" max="200">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">التنفس</label>
                                    <input type="number" class="form-control" name="vital_signs[respiratory_rate]" 
                                           placeholder="16" min="8" max="60">
                                </div>
                            </div>
                        </div>

                        <!-- Physical Examination -->
                        <div class="mb-3">
                            <label class="form-label">الفحص السريري *</label>
                            <textarea class="form-control" name="physical_examination" rows="4" required
                                      placeholder="اكتب نتائج الفحص السريري..."></textarea>
                        </div>

                        <!-- Diagnosis -->
                        <div class="mb-3">
                            <label class="form-label">التشخيص *</label>
                            <textarea class="form-control" name="diagnosis" rows="3" required
                                      placeholder="اكتب التشخيص..."></textarea>
                        </div>

                        <!-- Treatment Plan -->
                        <div class="mb-3">
                            <label class="form-label">خطة العلاج *</label>
                            <textarea class="form-control" name="treatment_plan" rows="4" required
                                      placeholder="اكتب خطة العلاج..."></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label">ملاحظات إضافية</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="أي ملاحظات إضافية..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                <i class="fas fa-arrow-right me-2"></i>رجوع
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>حفظ التقرير والانتقال للخطوات التالية
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#examinationForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("doctor.examination.save-report", $appointment) }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    // الانتقال للخطوات التالية
                    setTimeout(function() {
                        window.location.href = response.next_steps_url;
                    }, 1500);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                showAlert('error', 'حدث خطأ أثناء حفظ التقرير الطبي');
            }
        });
    });
});

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