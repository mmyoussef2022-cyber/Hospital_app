@extends('layouts.app')

@section('title', 'تعديل خطة العلاج - ' . $treatment->title)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-pencil-square text-warning"></i>
                        تعديل خطة العلاج
                    </h1>
                    <p class="text-muted mb-0">{{ $treatment->title }}</p>
                </div>
                <div>
                    <a href="{{ route('dental.treatments.show', $treatment) }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-right"></i>
                        العودة للتفاصيل
                    </a>
                    <a href="{{ route('dental.treatments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list"></i>
                        القائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($treatment->status === 'completed')
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>تنبيه:</strong> هذا العلاج مكتمل. تأكد من ضرورة التعديل قبل الحفظ.
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('dental.treatments.update', $treatment) }}" method="POST" enctype="multipart/form-data" id="treatmentForm">
        @csrf
        @method('PUT')
        
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
                                                {{ (old('patient_id', $treatment->patient_id) == $patient->id) ? 'selected' : '' }}>
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
                                        <option value="{{ $doctor->id }}" 
                                                {{ (old('doctor_id', $treatment->doctor_id) == $doctor->id) ? 'selected' : '' }}>
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
                                        <option value="{{ $key }}" 
                                                {{ (old('treatment_type', $treatment->treatment_type) == $key) ? 'selected' : '' }}>
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
                                        <option value="{{ $key }}" 
                                                {{ (old('priority', $treatment->priority) == $key) ? 'selected' : '' }}>
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
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">حالة العلاج *</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    @foreach($statuses as $key => $statusName)
                                        <option value="{{ $key }}" 
                                                {{ (old('status', $treatment->status) == $key) ? 'selected' : '' }}>
                                            {{ $statusName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="progress_percentage" class="form-label">نسبة التقدم (%)</label>
                                <input type="number" 
                                       class="form-control @error('progress_percentage') is-invalid @enderror" 
                                       id="progress_percentage" 
                                       name="progress_percentage" 
                                       value="{{ old('progress_percentage', $treatment->progress_percentage) }}" 
                                       min="0" 
                                       max="100">
                                @error('progress_percentage')
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
                                       value="{{ old('title', $treatment->title) }}" 
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
                                          placeholder="وصف تفصيلي للعلاج المطلوب والإجراءات المتوقعة">{{ old('description', $treatment->description) }}</textarea>
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
                                          placeholder="أي ملاحظات أو تعليمات خاصة">{{ old('notes', $treatment->notes) }}</textarea>
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
                                   value="{{ old('start_date', $treatment->start_date->format('Y-m-d')) }}" 
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
                                   value="{{ old('expected_end_date', $treatment->expected_end_date->format('Y-m-d')) }}" 
                                   required>
                            @error('expected_end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if($treatment->status === 'completed')
                            <div class="mb-3">
                                <label for="actual_end_date" class="form-label">تاريخ الانتهاء الفعلي</label>
                                <input type="date" 
                                       class="form-control @error('actual_end_date') is-invalid @enderror" 
                                       id="actual_end_date" 
                                       name="actual_end_date" 
                                       value="{{ old('actual_end_date', $treatment->actual_end_date?->format('Y-m-d')) }}">
                                @error('actual_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="total_sessions" class="form-label">عدد الجلسات المتوقعة *</label>
                            <input type="number" 
                                   class="form-control @error('total_sessions') is-invalid @enderror" 
                                   id="total_sessions" 
                                   name="total_sessions" 
                                   value="{{ old('total_sessions', $treatment->total_sessions) }}" 
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
                                   value="{{ old('total_cost', $treatment->total_cost) }}" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            @error('total_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">المبلغ المدفوع (ر.س)</label>
                            <input type="number" 
                                   class="form-control @error('paid_amount') is-invalid @enderror" 
                                   id="paid_amount" 
                                   name="paid_amount" 
                                   value="{{ old('paid_amount', $treatment->paid_amount) }}" 
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
                                <option value="cash" {{ (old('payment_type', $treatment->payment_type) == 'cash') ? 'selected' : '' }}>نقدي</option>
                                <option value="installments" {{ (old('payment_type', $treatment->payment_type) == 'installments') ? 'selected' : '' }}>أقساط</option>
                                <option value="insurance" {{ (old('payment_type', $treatment->payment_type) == 'insurance') ? 'selected' : '' }}>تأمين</option>
                            </select>
                            @error('payment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="installmentFields" style="display: {{ (old('payment_type', $treatment->payment_type) == 'installments') ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label for="installment_months" class="form-label">عدد أشهر التقسيط</label>
                                <select class="form-select @error('installment_months') is-invalid @enderror" 
                                        id="installment_months" 
                                        name="installment_months">
                                    <option value="">اختر عدد الأشهر</option>
                                    @for($i = 2; $i <= 24; $i++)
                                        <option value="{{ $i }}" 
                                                {{ (old('installment_months', $treatment->installment_months) == $i) ? 'selected' : '' }}>
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
                                       value="{{ old('monthly_installment', $treatment->monthly_installment) }}" 
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
                                                       {{ in_array($i, old('teeth_involved', $treatment->teeth_involved ?? [])) ? 'checked' : '' }}>
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
                                                       {{ in_array($i, old('teeth_involved', $treatment->teeth_involved ?? [])) ? 'checked' : '' }}>
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

        <!-- Treatment Plan -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-medical"></i>
                            خطة العلاج التفصيلية
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="treatment_plan" class="form-label">خطة العلاج</label>
                            <textarea class="form-control @error('treatment_plan') is-invalid @enderror" 
                                      id="treatment_plan" 
                                      name="treatment_plan" 
                                      rows="5" 
                                      placeholder="خطة العلاج المفصلة مع الخطوات والجلسات المطلوبة">{{ old('treatment_plan', $treatment->treatment_plan) }}</textarea>
                            @error('treatment_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <button type="button" class="btn btn-outline-info" onclick="showChanges()">
                                    <i class="bi bi-eye"></i>
                                    معاينة التغييرات
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('dental.treatments.show', $treatment) }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-x-circle"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-warning" id="submitBtn">
                                    <i class="bi bi-check-circle"></i>
                                    حفظ التعديلات
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Changes Preview Modal -->
<div class="modal fade" id="changesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">معاينة التغييرات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="changesContent">
                    <!-- Changes will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
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

    function updateTeethCounter() {
        const selectedTeeth = document.querySelectorAll('.tooth-checkbox:checked');
        if (selectedTeeth.length > 0) {
            teethCounter.style.display = 'block';
            teethCounter.innerHTML = `<i class="bi bi-info-circle"></i> تم اختيار ${selectedTeeth.length} سن للعلاج`;
        } else {
            teethCounter.style.display = 'none';
        }
    }

    teethCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTeethCounter);
    });

    // Initialize teeth counter
    updateTeethCounter();

    // Status change handler
    const statusSelect = document.getElementById('status');
    const progressInput = document.getElementById('progress_percentage');
    const actualEndDateField = document.getElementById('actual_end_date');

    statusSelect.addEventListener('change', function() {
        if (this.value === 'completed') {
            progressInput.value = 100;
            if (actualEndDateField && !actualEndDateField.value) {
                actualEndDateField.value = new Date().toISOString().split('T')[0];
            }
        } else if (this.value === 'planned') {
            progressInput.value = 0;
        }
    });
});

function showChanges() {
    // This would compare current form values with original values
    // For now, just show a simple message
    const changesContent = document.getElementById('changesContent');
    changesContent.innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            سيتم عرض التغييرات المقترحة هنا قبل الحفظ.
        </div>
        <p>التغييرات المكتشفة:</p>
        <ul>
            <li>تم تعديل بعض الحقول</li>
            <li>يرجى مراجعة البيانات قبل الحفظ</li>
        </ul>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('changesModal'));
    modal.show();
}
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

.btn-warning {
    background: linear-gradient(45deg, #f39c12, #e67e22);
    border: none;
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(45deg, #e67e22, #d35400);
    color: white;
}

@media (max-width: 768px) {
    .teeth-selector .col-1 {
        flex: 0 0 12.5%;
        max-width: 12.5%;
    }
}
</style>
@endsection