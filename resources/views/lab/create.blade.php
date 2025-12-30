@extends('layouts.app')

@section('title', 'طلب فحص مختبر جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-flask me-2"></i>
                        طلب فحص مختبر جديد
                    </h3>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('lab.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Patient Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">المريض <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                    <option value="">اختر المريض</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id', $selectedPatient?->id) == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} - {{ $patient->national_id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Doctor Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label">الطبيب المعالج <span class="text-danger">*</span></label>
                                <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                    <option value="">اختر الطبيب</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }} - {{ $doctor->department->name ?? 'غير محدد' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Lab Test Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="lab_test_id" class="form-label">نوع الفحص <span class="text-danger">*</span></label>
                                <select name="lab_test_id" id="lab_test_id" class="form-select @error('lab_test_id') is-invalid @enderror" required>
                                    <option value="">اختر نوع الفحص</option>
                                    @foreach($labTests as $test)
                                        <option value="{{ $test->id }}" 
                                                data-price="{{ $test->price }}"
                                                data-duration="{{ $test->duration_minutes }}"
                                                {{ old('lab_test_id') == $test->id ? 'selected' : '' }}>
                                            {{ $test->name }} - {{ $test->formatted_price }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lab_test_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">الأولوية <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="routine" {{ old('priority') == 'routine' ? 'selected' : '' }}>عادي</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
                                    <option value="stat" {{ old('priority') == 'stat' ? 'selected' : '' }}>طارئ</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Clinical Notes -->
                        <div class="mb-3">
                            <label for="clinical_notes" class="form-label">الملاحظات السريرية</label>
                            <textarea name="clinical_notes" id="clinical_notes" 
                                      class="form-control @error('clinical_notes') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="أدخل الملاحظات السريرية والأعراض...">{{ old('clinical_notes') }}</textarea>
                            @error('clinical_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Test Details Display -->
                        <div id="testDetails" class="alert alert-info" style="display: none;">
                            <h6>تفاصيل الفحص:</h6>
                            <div id="testInfo"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('lab.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                العودة
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ الطلب
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
document.addEventListener('DOMContentLoaded', function() {
    const labTestSelect = document.getElementById('lab_test_id');
    const testDetails = document.getElementById('testDetails');
    const testInfo = document.getElementById('testInfo');

    labTestSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const price = selectedOption.dataset.price;
            const duration = selectedOption.dataset.duration;
            const testName = selectedOption.text.split(' - ')[0];
            
            testInfo.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <strong>اسم الفحص:</strong> ${testName}
                    </div>
                    <div class="col-md-4">
                        <strong>السعر:</strong> ${price} ريال
                    </div>
                    <div class="col-md-4">
                        <strong>المدة المتوقعة:</strong> ${duration} دقيقة
                    </div>
                </div>
            `;
            testDetails.style.display = 'block';
        } else {
            testDetails.style.display = 'none';
        }
    });

    // Trigger change event if there's a pre-selected value
    if (labTestSelect.value) {
        labTestSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush