@extends('layouts.app')

@section('page-title', 'حجز موعد جديد')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-plus text-facebook"></i>
                        حجز موعد جديد
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('appointments.store') }}" id="appointmentForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Patient Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">المريض <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                    <option value="">اختر المريض</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" 
                                                {{ (old('patient_id', $selectedPatient ? $selectedPatient->id : null) == $patient->id) ? 'selected' : '' }}
                                                data-national-id="{{ $patient->national_id }}">
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
                                <label for="doctor_id" class="form-label">الطبيب <span class="text-danger">*</span></label>
                                <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                    <option value="">اختر الطبيب</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }} - {{ $doctor->job_title ?? 'طبيب' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Appointment Date -->
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">تاريخ الموعد <span class="text-danger">*</span></label>
                                <input type="date" name="appointment_date" id="appointment_date" 
                                       class="form-control @error('appointment_date') is-invalid @enderror"
                                       value="{{ old('appointment_date', today()->format('Y-m-d')) }}" 
                                       min="{{ today()->format('Y-m-d') }}" required>
                                @error('appointment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Appointment Time -->
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">وقت الموعد <span class="text-danger">*</span></label>
                                <select name="appointment_time" id="appointment_time" 
                                        class="form-select @error('appointment_time') is-invalid @enderror" required>
                                    <option value="">اختر الوقت</option>
                                </select>
                                @error('appointment_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">سيتم عرض الأوقات المتاحة بعد اختيار الطبيب والتاريخ</div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Appointment Type -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">نوع الموعد <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">اختر النوع</option>
                                    <option value="consultation" {{ old('type') == 'consultation' ? 'selected' : '' }}>استشارة</option>
                                    <option value="follow_up" {{ old('type') == 'follow_up' ? 'selected' : '' }}>متابعة</option>
                                    <option value="emergency" {{ old('type') == 'emergency' ? 'selected' : '' }}>طوارئ</option>
                                    <option value="surgery" {{ old('type') == 'surgery' ? 'selected' : '' }}>جراحة</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duration -->
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">المدة (بالدقائق)</label>
                                <select name="duration" id="duration" class="form-select @error('duration') is-invalid @enderror">
                                    <option value="30" {{ old('duration', 30) == 30 ? 'selected' : '' }}>30 دقيقة</option>
                                    <option value="45" {{ old('duration') == 45 ? 'selected' : '' }}>45 دقيقة</option>
                                    <option value="60" {{ old('duration') == 60 ? 'selected' : '' }}>60 دقيقة</option>
                                    <option value="90" {{ old('duration') == 90 ? 'selected' : '' }}>90 دقيقة</option>
                                    <option value="120" {{ old('duration') == 120 ? 'selected' : '' }}>120 دقيقة</option>
                                </select>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="أي ملاحظات إضافية حول الموعد...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Available Slots Display -->
                        <div id="availableSlots" class="mb-3" style="display: none;">
                            <label class="form-label">الأوقات المتاحة:</label>
                            <div id="slotsContainer" class="d-flex flex-wrap gap-2"></div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-right"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-facebook" id="submitBtn">
                                <i class="bi bi-check-circle"></i>
                                حجز الموعد
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const durationSelect = document.getElementById('duration');
    const availableSlotsDiv = document.getElementById('availableSlots');
    const slotsContainer = document.getElementById('slotsContainer');
    const submitBtn = document.getElementById('submitBtn');

    // Load available slots when doctor, date, or duration changes
    function loadAvailableSlots() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        const duration = durationSelect.value;

        if (!doctorId || !date) {
            timeSelect.innerHTML = '<option value="">اختر الوقت</option>';
            availableSlotsDiv.style.display = 'none';
            return;
        }

        // Show loading
        timeSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        timeSelect.disabled = true;
        submitBtn.disabled = true;

        // Fetch available slots
        fetch(`{{ route('appointments.available-slots') }}?doctor_id=${doctorId}&date=${date}&duration=${duration}`)
            .then(response => response.json())
            .then(data => {
                timeSelect.innerHTML = '<option value="">اختر الوقت</option>';
                slotsContainer.innerHTML = '';

                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        // Add to select dropdown
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = slot;
                        timeSelect.appendChild(option);

                        // Add to visual slots display
                        const slotBtn = document.createElement('button');
                        slotBtn.type = 'button';
                        slotBtn.className = 'btn btn-outline-facebook btn-sm';
                        slotBtn.textContent = slot;
                        slotBtn.onclick = function() {
                            // Remove active class from all slots
                            slotsContainer.querySelectorAll('.btn').forEach(btn => {
                                btn.classList.remove('active');
                                btn.classList.add('btn-outline-facebook');
                                btn.classList.remove('btn-facebook');
                            });
                            
                            // Add active class to clicked slot
                            this.classList.add('active', 'btn-facebook');
                            this.classList.remove('btn-outline-facebook');
                            
                            // Set select value
                            timeSelect.value = slot;
                        };
                        slotsContainer.appendChild(slotBtn);
                    });

                    availableSlotsDiv.style.display = 'block';
                } else {
                    availableSlotsDiv.style.display = 'none';
                    const noSlotsOption = document.createElement('option');
                    noSlotsOption.value = '';
                    noSlotsOption.textContent = 'لا توجد أوقات متاحة';
                    timeSelect.appendChild(noSlotsOption);
                }

                timeSelect.disabled = false;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                timeSelect.innerHTML = '<option value="">خطأ في تحميل الأوقات</option>';
                timeSelect.disabled = false;
                submitBtn.disabled = false;
                availableSlotsDiv.style.display = 'none';
            });
    }

    // Event listeners
    doctorSelect.addEventListener('change', loadAvailableSlots);
    dateInput.addEventListener('change', loadAvailableSlots);
    durationSelect.addEventListener('change', loadAvailableSlots);

    // Load slots on page load if doctor and date are already selected
    if (doctorSelect.value && dateInput.value) {
        loadAvailableSlots();
    }

    // Patient search functionality
    const patientSelect = document.getElementById('patient_id');
    if (patientSelect) {
        // Add search functionality (can be enhanced with Select2 or similar)
        patientSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.nationalId) {
                console.log('Selected patient:', selectedOption.text);
            }
        });
    }
});
</script>
@endpush
@endsection