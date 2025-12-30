@extends('layouts.app')

@section('title', 'تعديل طلب المختبر')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-edit me-2"></i>
                        تعديل طلب المختبر
                    </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('lab.index') }}">المختبر</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lab.show', $lab) }}">{{ $lab->order_number }}</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('lab.show', $lab) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        العودة
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('lab.update', $lab) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Main Form -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    معلومات الطلب الأساسية
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_id" class="form-label">المريض <span class="text-danger">*</span></label>
                                        <select class="form-select @error('patient_id') is-invalid @enderror" 
                                                id="patient_id" name="patient_id" required>
                                            <option value="">اختر المريض</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" 
                                                        {{ old('patient_id', $lab->patient_id) == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->name }} - {{ $patient->national_id }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('patient_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="doctor_id" class="form-label">الطبيب المعالج <span class="text-danger">*</span></label>
                                        <select class="form-select @error('doctor_id') is-invalid @enderror" 
                                                id="doctor_id" name="doctor_id" required>
                                            <option value="">اختر الطبيب</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" 
                                                        {{ old('doctor_id', $lab->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->name }} - {{ $doctor->department->name ?? 'غير محدد' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="lab_test_id" class="form-label">نوع الفحص <span class="text-danger">*</span></label>
                                        <select class="form-select @error('lab_test_id') is-invalid @enderror" 
                                                id="lab_test_id" name="lab_test_id" required>
                                            <option value="">اختر نوع الفحص</option>
                                            @foreach($labTests as $test)
                                                <option value="{{ $test->id }}" 
                                                        data-price="{{ $test->price }}"
                                                        data-duration="{{ $test->duration_minutes }}"
                                                        data-description="{{ $test->description }}"
                                                        data-sample-type="{{ $test->sample_type }}"
                                                        data-preparation="{{ $test->preparation_instructions }}"
                                                        {{ old('lab_test_id', $lab->lab_test_id) == $test->id ? 'selected' : '' }}>
                                                    {{ $test->name }} - {{ number_format($test->price, 2) }} ريال
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('lab_test_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="priority" class="form-label">الأولوية <span class="text-danger">*</span></label>
                                        <select class="form-select @error('priority') is-invalid @enderror" 
                                                id="priority" name="priority" required>
                                            <option value="routine" {{ old('priority', $lab->priority) == 'routine' ? 'selected' : '' }}>
                                                عادي
                                            </option>
                                            <option value="urgent" {{ old('priority', $lab->priority) == 'urgent' ? 'selected' : '' }}>
                                                عاجل
                                            </option>
                                            <option value="stat" {{ old('priority', $lab->priority) == 'stat' ? 'selected' : '' }}>
                                                طارئ
                                            </option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="clinical_notes" class="form-label">الملاحظات السريرية</label>
                                        <textarea class="form-control @error('clinical_notes') is-invalid @enderror" 
                                                  id="clinical_notes" name="clinical_notes" rows="3"
                                                  placeholder="أدخل أي ملاحظات سريرية مهمة...">{{ old('clinical_notes', $lab->clinical_notes) }}</textarea>
                                        @error('clinical_notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Results Section (if exists) -->
                        @if($lab->results->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    النتائج الحالية
                                </h5>
                                <button type="button" class="btn btn-primary btn-sm" onclick="toggleResultsEdit()">
                                    <i class="fas fa-edit me-1"></i>
                                    تعديل النتائج
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="resultsDisplay">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>المعامل</th>
                                                    <th>القيمة</th>
                                                    <th>الوحدة</th>
                                                    <th>المدى المرجعي</th>
                                                    <th>الحالة</th>
                                                    <th>الملاحظات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lab->results as $result)
                                                <tr class="{{ $result->is_critical ? 'table-danger' : '' }}">
                                                    <td class="fw-bold">{{ $result->parameter_name }}</td>
                                                    <td>
                                                        <span class="fw-bold">{{ $result->value }}</span>
                                                        @if($result->is_critical)
                                                            <i class="fas fa-exclamation-triangle text-danger ms-1" title="قيمة حرجة"></i>
                                                        @endif
                                                    </td>
                                                    <td>{{ $result->unit }}</td>
                                                    <td class="text-muted">{{ $result->reference_range }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $result->flag_color }}">
                                                            {{ $result->flag_display }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($result->notes)
                                                            <small class="text-muted">{{ $result->notes }}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div id="resultsEdit" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        يمكنك تعديل النتائج الموجودة أو إضافة نتائج جديدة
                                    </div>
                                    
                                    <div id="resultsContainer">
                                        @foreach($lab->results as $index => $result)
                                        <div class="result-row border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">اسم المعامل</label>
                                                    <input type="text" class="form-control" 
                                                           name="results[{{ $index }}][parameter_name]" 
                                                           value="{{ $result->parameter_name }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">القيمة</label>
                                                    <input type="text" class="form-control" 
                                                           name="results[{{ $index }}][value]" 
                                                           value="{{ $result->value }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">الوحدة</label>
                                                    <input type="text" class="form-control" 
                                                           name="results[{{ $index }}][unit]" 
                                                           value="{{ $result->unit }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">المدى المرجعي</label>
                                                    <input type="text" class="form-control" 
                                                           name="results[{{ $index }}][reference_range]" 
                                                           value="{{ $result->reference_range }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">الحالة</label>
                                                    <select class="form-select" name="results[{{ $index }}][flag]" required>
                                                        <option value="normal" {{ $result->flag == 'normal' ? 'selected' : '' }}>طبيعي</option>
                                                        <option value="high" {{ $result->flag == 'high' ? 'selected' : '' }}>مرتفع</option>
                                                        <option value="low" {{ $result->flag == 'low' ? 'selected' : '' }}>منخفض</option>
                                                        <option value="critical_high" {{ $result->flag == 'critical_high' ? 'selected' : '' }}>مرتفع حرج</option>
                                                        <option value="critical_low" {{ $result->flag == 'critical_low' ? 'selected' : '' }}>منخفض حرج</option>
                                                        <option value="abnormal" {{ $result->flag == 'abnormal' ? 'selected' : '' }}>غير طبيعي</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <label class="form-label">الملاحظات</label>
                                                    <textarea class="form-control" name="results[{{ $index }}][notes]" rows="2">{{ $result->notes }}</textarea>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeResult(this)">
                                                        <i class="fas fa-trash me-1"></i>
                                                        حذف النتيجة
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    <button type="button" class="btn btn-success" onclick="addResult()">
                                        <i class="fas fa-plus me-1"></i>
                                        إضافة نتيجة جديدة
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            حفظ التغييرات
                                        </button>
                                        <a href="{{ route('lab.show', $lab) }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            إلغاء
                                        </a>
                                    </div>
                                    
                                    @if($lab->canBeCancelled())
                                    <button type="button" class="btn btn-danger" onclick="cancelOrder()">
                                        <i class="fas fa-trash me-1"></i>
                                        إلغاء الطلب
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Test Details -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-vial me-2"></i>
                                    تفاصيل الفحص
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="testDetails">
                                    <p class="text-muted">اختر نوع الفحص لعرض التفاصيل</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Status -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    حالة الطلب
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>رقم الطلب:</strong> {{ $lab->order_number }}
                                </div>
                                <div class="mb-3">
                                    <strong>الحالة الحالية:</strong>
                                    <span class="badge bg-{{ $lab->status_color }} fs-6">
                                        {{ $lab->status_display }}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>تاريخ الطلب:</strong> {{ $lab->ordered_at->format('Y-m-d H:i') }}
                                </div>
                                @if($lab->collected_at)
                                <div class="mb-3">
                                    <strong>تاريخ جمع العينة:</strong> {{ $lab->collected_at->format('Y-m-d H:i') }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Help -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-question-circle me-2"></i>
                                    مساعدة
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-info-circle text-info me-2"></i>
                                        يمكن تعديل الطلبات في حالة "مطلوب" أو "تم جمع العينة" فقط
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        تأكد من صحة البيانات قبل الحفظ
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-secondary me-2"></i>
                                        سيتم تحديث المبلغ تلقائياً عند تغيير نوع الفحص
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let resultIndex = {{ $lab->results->count() }};

document.addEventListener('DOMContentLoaded', function() {
    // Update test details when selection changes
    document.getElementById('lab_test_id').addEventListener('change', function() {
        updateTestDetails();
    });
    
    // Initialize test details
    updateTestDetails();
});

function updateTestDetails() {
    const select = document.getElementById('lab_test_id');
    const selectedOption = select.options[select.selectedIndex];
    const detailsDiv = document.getElementById('testDetails');
    
    if (selectedOption.value) {
        const price = selectedOption.dataset.price;
        const duration = selectedOption.dataset.duration;
        const description = selectedOption.dataset.description;
        const sampleType = selectedOption.dataset.sampleType;
        const preparation = selectedOption.dataset.preparation;
        
        let html = `
            <div class="mb-3">
                <h6 class="text-primary">${selectedOption.text.split(' - ')[0]}</h6>
                ${description ? `<p class="text-muted small">${description}</p>` : ''}
            </div>
            
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="fw-bold">السعر:</td>
                    <td class="text-success fw-bold">${parseFloat(price).toLocaleString()} ريال</td>
                </tr>
                ${duration ? `
                <tr>
                    <td class="fw-bold">المدة:</td>
                    <td>${duration} دقيقة</td>
                </tr>
                ` : ''}
                ${sampleType ? `
                <tr>
                    <td class="fw-bold">نوع العينة:</td>
                    <td>${sampleType}</td>
                </tr>
                ` : ''}
            </table>
            
            ${preparation ? `
            <div class="alert alert-warning">
                <h6 class="alert-heading">تعليمات التحضير:</h6>
                <small>${preparation}</small>
            </div>
            ` : ''}
        `;
        
        detailsDiv.innerHTML = html;
    } else {
        detailsDiv.innerHTML = '<p class="text-muted">اختر نوع الفحص لعرض التفاصيل</p>';
    }
}

function toggleResultsEdit() {
    const display = document.getElementById('resultsDisplay');
    const edit = document.getElementById('resultsEdit');
    
    if (edit.style.display === 'none') {
        display.style.display = 'none';
        edit.style.display = 'block';
    } else {
        display.style.display = 'block';
        edit.style.display = 'none';
    }
}

function addResult() {
    const container = document.getElementById('resultsContainer');
    const resultHtml = `
        <div class="result-row border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">اسم المعامل</label>
                    <input type="text" class="form-control" name="results[${resultIndex}][parameter_name]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">القيمة</label>
                    <input type="text" class="form-control" name="results[${resultIndex}][value]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الوحدة</label>
                    <input type="text" class="form-control" name="results[${resultIndex}][unit]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">المدى المرجعي</label>
                    <input type="text" class="form-control" name="results[${resultIndex}][reference_range]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" name="results[${resultIndex}][flag]" required>
                        <option value="normal">طبيعي</option>
                        <option value="high">مرتفع</option>
                        <option value="low">منخفض</option>
                        <option value="critical_high">مرتفع حرج</option>
                        <option value="critical_low">منخفض حرج</option>
                        <option value="abnormal">غير طبيعي</option>
                    </select>
                </div>
                <div class="col-12 mt-2">
                    <label class="form-label">الملاحظات</label>
                    <textarea class="form-control" name="results[${resultIndex}][notes]" rows="2"></textarea>
                </div>
                <div class="col-12 mt-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeResult(this)">
                        <i class="fas fa-trash me-1"></i>
                        حذف النتيجة
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', resultHtml);
    resultIndex++;
}

function removeResult(button) {
    if (confirm('هل أنت متأكد من حذف هذه النتيجة؟')) {
        button.closest('.result-row').remove();
    }
}

function cancelOrder() {
    if (confirm('هل أنت متأكد من إلغاء هذا الطلب؟ لا يمكن التراجع عن هذا الإجراء.')) {
        fetch(`/lab/{{ $lab->id }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/lab';
            } else {
                alert('حدث خطأ في إلغاء الطلب');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في إلغاء الطلب');
        });
    }
}
</script>
@endpush