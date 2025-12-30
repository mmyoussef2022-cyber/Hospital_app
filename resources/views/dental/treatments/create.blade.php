@extends('layouts.app')

@section('title', 'إنشاء خطة علاج أسنان جديدة')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-plus-circle text-success"></i>
                        إنشاء خطة علاج أسنان جديدة
                    </h1>
                    <p class="text-muted mb-0">إنشاء خطة علاج شاملة للمريض</p>
                </div>
                <div>
                    <a href="{{ route('dental.treatments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('dental.treatments.store') }}" method="POST" enctype="multipart/form-data" id="treatmentForm">
        @csrf
        
        <!-- Patient and Doctor Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-check"></i>
                            معلومات المريض والطبيب
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">المريض *</label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" 
                                        id="patient_id" 
                                        name="patient_id" 
                                        required>
                                    <option value="">اختر المريض</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" 
                                                {{ (old('patient_id') == $patient->id || ($selectedPatient && $selectedPatient->id == $patient->id)) ? 'selected' : '' }}>
                                            {{ $patient->name }} - {{ $patient->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label">طبيب الأسنان *</label>
                                <select class="form-select @error('doctor_id') is-invalid @enderror" 
                                        id="doctor_id" 
                                        name="doctor_id" 
                                        required>
                                    <option value="">اختر الطبيب</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }} - {{ $doctor->doctor?->specialization ?? 'طبيب أسنان' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Details -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-pulse"></i>
                            تفاصيل العلاج
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="treatment_type" class="form-label">نوع العلاج *</label>
                                <select class="form-select @error('treatment_type') is-invalid @enderror" 
                                        id="treatment_type" 
                                        name="treatment_type" 
                                        required>
                                    <option value="">اختر نوع العلاج</option>
                                    @foreach($treatmentTypes as $key => $type)
                                        <option value="{{ $key }}" {{ old('treatment_type') == $key ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('treatment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">الأولوية *</label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    @foreach($priorities as $key => $priorityName)
                                        <option value="{{ $key }}" {{ old('priority', 'normal') == $key ? 'selected' : '' }}>
                                            {{ $priorityName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="title" class="form-label">عنوان العلاج *</label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title') }}" 
                                       required 
                                       placeholder="مثال: تقويم الأسنان العلوية والسفلية">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">وصف العلاج *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="4" 
                                          required 
                                          placeholder="وصف تفصيلي للعلاج المطلوب والإجراءات المتوقعة">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">ملاحظات إضافية</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="أي ملاحظات أو تعليمات خاصة">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Treatment Schedule and Cost -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-range"></i>
                            الجدولة والتوقيت
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">تاريخ بداية العلاج *</label>
                            <input type="date" 
                                   class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="{{ old('start_date', date('Y-m-d')) }}" 
                                   required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="expected_end_date" class="form-label">تاريخ الانتهاء المتوقع *</label>
                            <input type="date" 
                                   class="form-control @error('expected_end_date') is-invalid @enderror" 
                                   id="expected_end_date" 
                                   name="expected_end_date" 
                                   value="{{ old('expected_end_date') }}" 
                                   required>
                            @error('expected_end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="total_sessions" class="form-label">عدد الجلسات المتوقعة *</label>
                            <input type="number" 
                                   class="form-control @error('total_sessions') is-invalid @enderror" 
                                   id="total_sessions" 
                                   name="total_sessions" 
                                   value="{{ old('total_sessions', 1) }}" 
                                   min="1" 
                                   max="50" 
                                   required>
                            @error('total_sessions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-currency-dollar"></i>
                            التكلفة والدفع
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="total_cost" class="form-label">التكلفة الإجمالية (ر.س) *</label>
                            <input type="number" 
                                   class="form-control @error('total_cost') is-invalid @enderror" 
                                   id="total_cost" 
                                   name="total_cost" 
                                   value="{{ old('total_cost') }}" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            @error('total_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">المبلغ المدفوع مقدماً (ر.س)</label>
                            <input type="number" 
                                   class="form-control @error('paid_amount') is-invalid @enderror" 
                                   id="paid_amount" 
                                   name="paid_amount" 
                                   value="{{ old('paid_amount', 0) }}" 
                                   step="0.01" 
                                   min="0">
                            @error('paid_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_type" class="form-label">نوع الدفع *</label>
                            <select class="form-select @error('payment_type') is-invalid @enderror" 
                                    id="payment_type" 
                                    name="payment_type" 
                                    required>
                                <option value="cash" {{ old('payment_type', 'cash') == 'cash' ? 'selected' : '' }}>نقدي</option>
                                <option value="installments" {{ old('payment_type') == 'installments' ? 'selected' : '' }}>أقساط</option>
                                <option value="insurance" {{ old('payment_type') == 'insurance' ? 'selected' : '' }}>تأمين</option>
                            </select>
                            @error('payment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="installmentFields" style="display: none;">
                            <div class="mb-3">
                                <label for="installment_months" class="form-label">عدد أشهر التقسيط</label>
                                <select class="form-select @error('installment_months') is-invalid @enderror" 
                                        id="installment_months" 
                                        name="installment_months">
                                    <option value="">اختر عدد الأشهر</option>
                                    @for($i = 2; $i <= 24; $i++)
                                        <option value="{{ $i }}" {{ old('installment_months') == $i ? 'selected' : '' }}>
                                            {{ $i }} شهر
                                        </option>
                                    @endfor
                                </select>
                                @error('installment_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="monthly_installment" class="form-label">القسط الشهري (ر.س)</label>
                                <input type="number" 
                                       class="form-control @error('monthly_installment') is-invalid @enderror" 
                                       id="monthly_installment" 
                                       name="monthly_installment" 
                                       value="{{ old('monthly_installment') }}" 
                                       step="0.01" 
                                       min="0" 
                                       readonly>
                                @error('monthly_installment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teeth Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-grid-3x3"></i>
                            الأسنان المتضررة
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">اختر الأسنان التي تحتاج للعلاج (اختياري)</p>
                        <div class="teeth-selector">
                            <!-- Upper teeth -->
                            <div class="mb-3">
                                <h6>الأسنان العلوية</h6>
                                <div class="row">
                                    @for($i = 1; $i <= 16; $i++)
                                        <div class="col-1 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input tooth-checkbox" 
                                                       type="checkbox" 
                                                       name="teeth_involved[]" 
                                                       value="{{ $i }}" 
                                                       id="tooth_{{ $i }}"
                                                       {{ in_array($i, old('teeth_involved', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tooth_{{ $i }}">
                                                    {{ $i }}
                                                </label>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <!-- Lower teeth -->
                            <div class="mb-3">
                                <h6>الأسنان السفلية</h6>
                                <div class="row">
                                    @for($i = 17; $i <= 32; $i++)
                                        <div class="col-1 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input tooth-checkbox" 
                                                       type="checkbox" 
                                                       name="teeth_involved[]" 
                                                       value="{{ $i }}" 
                                                       id="tooth_{{ $i }}"
                                                       {{ in_array($i, old('teeth_involved', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tooth_{{ $i }}">
                                                    {{ $i }}
                                                </label>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        @error('teeth_involved')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Plan and Files -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-medical"></i>
                            خطة العلاج والملفات
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="treatment_plan" class="form-label">خطة العلاج التفصيلية</label>
                                <textarea class="form-control @error('treatment_plan') is-invalid @enderror" 
                                          id="treatment_plan" 
                                          name="treatment_plan" 
                                          rows="5" 
                                          placeholder="خطة العلاج المفصلة مع الخطوات والجلسات المطلوبة">{{ old('treatment_plan') }}</textarea>
                                @error('treatment_plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pre_treatment_images" class="form-label">صور ما قبل العلاج</label>
                                <input type="file" 
                                       class="form-control @error('pre_treatment_images') is-invalid @enderror" 
                                       id="pre_treatment_images" 
                                       name="pre_treatment_images[]" 
                                       multiple 
                                       accept="image/*">
                                <div class="form-text">يمكن رفع عدة صور (JPG, PNG, GIF)</div>
                                @error('pre_treatment_images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="x_ray_images" class="form-label">صور الأشعة</label>
                                <input type="file" 
                                       class="form-control @error('x_ray_images') is-invalid @enderror" 
                                       id="x_ray_images" 
                                       name="x_ray_images[]" 
                                       multiple 
                                       accept="image/*">
                                <div class="form-text">صور الأشعة السينية للأسنان</div>
                                @error('x_ray_images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="medical_reports" class="form-label">التقارير الطبية</label>
                                <input type="file" 
                                       class="form-control @error('medical_reports') is-invalid @enderror" 
                                       id="medical_reports" 
                                       name="medical_reports[]" 
                                       multiple 
                                       accept=".pdf,.doc,.docx">
                                <div class="form-text">التقارير الطبية السابقة (PDF, DOC)</div>
                                @error('medical_reports')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    إعادة تعيين
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('dental.treatments.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-x-circle"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="bi bi-check-circle"></i>
                                    إنشاء خطة العلاج
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Cost Calculation Modal -->
<div class="modal fade" id="costCalculationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">حساب التكلفة التفصيلية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">التكلفة الأساسية</label>
                    <input type="number" class="form-control" id="baseCost" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">تكلفة إضافية</label>
                    <input type="number" class="form-control" id="additionalCost" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">خصم (%)</label>
                    <input type="number" class="form-control" id="discount" min="0" max="100">
                </div>
                <div class="alert alert-info">
                    <strong>المجموع: </strong><span id="totalCalculated">0</span> ر.س
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="applyCostCalculation()">تطبيق</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment type change handler
    const paymentType = document.getElementById('payment_type');
    const installmentFields = document.getElementById('installmentFields');
    const installmentMonths = document.getElementById('installment_months');
    const monthlyInstallment = document.getElementById('monthly_installment');
    const totalCost = document.getElementById('total_cost');
    const paidAmount = document.getElementById('paid_amount');

    paymentType.addEventListener('change', function() {
        if (this.value === 'installments') {
            installmentFields.style.display = 'block';
            installmentMonths.required = true;
        } else {
            installmentFields.style.display = 'none';
            installmentMonths.required = false;
            monthlyInstallment.value = '';
        }
    });

    // Calculate monthly installment
    function calculateInstallment() {
        const total = parseFloat(totalCost.value) || 0;
        const paid = parseFloat(paidAmount.value) || 0;
        const months = parseInt(installmentMonths.value) || 0;
        
        if (total > 0 && months > 0) {
            const remaining = total - paid;
            const monthly = remaining / months;
            monthlyInstallment.value = monthly.toFixed(2);
        }
    }

    installmentMonths.addEventListener('change', calculateInstallment);
    totalCost.addEventListener('input', calculateInstallment);
    paidAmount.addEventListener('input', calculateInstallment);

    // Date validation
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('expected_end_date');

    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        if (endDate.value && endDate.value < this.value) {
            endDate.value = '';
        }
    });

    // Form validation
    const form = document.getElementById('treatmentForm');
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري الحفظ...';
    });

    // Teeth selection counter
    const teethCheckboxes = document.querySelectorAll('.tooth-checkbox');
    const teethCounter = document.createElement('div');
    teethCounter.className = 'alert alert-info mt-2';
    teethCounter.style.display = 'none';
    document.querySelector('.teeth-selector').appendChild(teethCounter);

    teethCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const selectedTeeth = document.querySelectorAll('.tooth-checkbox:checked');
            if (selectedTeeth.length > 0) {
                teethCounter.style.display = 'block';
                teethCounter.innerHTML = `<i class="bi bi-info-circle"></i> تم اختيار ${selectedTeeth.length} سن للعلاج`;
            } else {
                teethCounter.style.display = 'none';
            }
        });
    });

    // Cost calculation modal
    const baseCost = document.getElementById('baseCost');
    const additionalCost = document.getElementById('additionalCost');
    const discount = document.getElementById('discount');
    const totalCalculated = document.getElementById('totalCalculated');

    function updateCalculation() {
        const base = parseFloat(baseCost.value) || 0;
        const additional = parseFloat(additionalCost.value) || 0;
        const discountPercent = parseFloat(discount.value) || 0;
        
        const subtotal = base + additional;
        const discountAmount = subtotal * (discountPercent / 100);
        const total = subtotal - discountAmount;
        
        totalCalculated.textContent = total.toFixed(2);
    }

    baseCost.addEventListener('input', updateCalculation);
    additionalCost.addEventListener('input', updateCalculation);
    discount.addEventListener('input', updateCalculation);

    // Initialize payment type display
    if (paymentType.value === 'installments') {
        installmentFields.style.display = 'block';
        installmentMonths.required = true;
    }
});

function resetForm() {
    if (confirm('هل أنت متأكد من إعادة تعيين النموذج؟ سيتم فقدان جميع البيانات المدخلة.')) {
        document.getElementById('treatmentForm').reset();
        document.getElementById('installmentFields').style.display = 'none';
        document.querySelector('.teeth-selector .alert').style.display = 'none';
    }
}

function applyCostCalculation() {
    const totalCalculated = document.getElementById('totalCalculated').textContent;
    document.getElementById('total_cost').value = totalCalculated;
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('costCalculationModal'));
    modal.hide();
    
    // Recalculate installment if needed
    if (document.getElementById('payment_type').value === 'installments') {
        const installmentMonths = parseInt(document.getElementById('installment_months').value) || 0;
        const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
        
        if (installmentMonths > 0) {
            const remaining = parseFloat(totalCalculated) - paidAmount;
            const monthly = remaining / installmentMonths;
            document.getElementById('monthly_installment').value = monthly.toFixed(2);
        }
    }
}

// Add cost calculation button
document.addEventListener('DOMContentLoaded', function() {
    const totalCostField = document.getElementById('total_cost');
    const calcButton = document.createElement('button');
    calcButton.type = 'button';
    calcButton.className = 'btn btn-outline-info btn-sm mt-2';
    calcButton.innerHTML = '<i class="bi bi-calculator"></i> حساب التكلفة';
    calcButton.onclick = function() {
        const modal = new bootstrap.Modal(document.getElementById('costCalculationModal'));
        modal.show();
    };
    totalCostField.parentNode.appendChild(calcButton);
});
</script>

<style>
.teeth-selector .form-check {
    text-align: center;
}

.teeth-selector .form-check-input {
    margin: 0 auto;
}

.teeth-selector .form-check-label {
    font-weight: bold;
    font-size: 0.9rem;
}

.tooth-checkbox:checked + label {
    color: #dc3545;
}

.card-header h5 {
    color: #2c3e50;
}

.form-label {
    font-weight: 600;
    color: #34495e;
}

.btn-success {
    background: linear-gradient(45deg, #27ae60, #2ecc71);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(45deg, #229954, #27ae60);
}

.alert-info {
    background-color: #e8f4fd;
    border-color: #bee5eb;
    color: #0c5460;
}

@media (max-width: 768px) {
    .teeth-selector .col-1 {
        flex: 0 0 12.5%;
        max-width: 12.5%;
    }
}
</style>
@endsection