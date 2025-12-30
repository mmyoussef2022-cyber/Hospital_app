@extends('layouts.app')

@section('title', __('app.create_prescription'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('app.create_prescription') }}
                    </h4>
                    <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('app.back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('prescriptions.store') }}" method="POST">
                        @csrf

                        <!-- Hidden field for medical record if provided -->
                        @if($selectedMedicalRecord)
                            <input type="hidden" name="medical_record_id" value="{{ $selectedMedicalRecord->id }}">
                        @endif

                        <div class="row">
                            <!-- Patient Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="patient_id" class="form-label">{{ __('app.patient') }} <span class="text-danger">*</span></label>
                                    <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                        <option value="">{{ __('app.select_patient') }}</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id', $selectedPatient?->id) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->name }} ({{ $patient->patient_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Doctor Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="doctor_id" class="form-label">{{ __('app.doctor') }} <span class="text-danger">*</span></label>
                                    <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                        <option value="">{{ __('app.select_doctor') }}</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Medication Name -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="medication_name" class="form-label">{{ __('app.medication_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="medication_name" id="medication_name" 
                                           class="form-control @error('medication_name') is-invalid @enderror" 
                                           value="{{ old('medication_name') }}" required>
                                    @error('medication_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Medication Name Arabic -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="medication_name_ar" class="form-label">{{ __('app.medication_name_ar') }}</label>
                                    <input type="text" name="medication_name_ar" id="medication_name_ar" 
                                           class="form-control @error('medication_name_ar') is-invalid @enderror" 
                                           value="{{ old('medication_name_ar') }}">
                                    @error('medication_name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Dosage -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="dosage" class="form-label">{{ __('app.dosage') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="dosage" id="dosage" 
                                           class="form-control @error('dosage') is-invalid @enderror" 
                                           value="{{ old('dosage') }}" required>
                                    @error('dosage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="duration_days" class="form-label">{{ __('app.duration_days') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="duration_days" id="duration_days" 
                                           class="form-control @error('duration_days') is-invalid @enderror" 
                                           value="{{ old('duration_days') }}" min="1" required>
                                    @error('duration_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Frequency -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="frequency" class="form-label">{{ __('app.frequency') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="frequency" id="frequency" 
                                           class="form-control @error('frequency') is-invalid @enderror" 
                                           value="{{ old('frequency') }}" required>
                                    @error('frequency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Frequency Arabic -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="frequency_ar" class="form-label">{{ __('app.frequency_ar') }}</label>
                                    <input type="text" name="frequency_ar" id="frequency_ar" 
                                           class="form-control @error('frequency_ar') is-invalid @enderror" 
                                           value="{{ old('frequency_ar') }}">
                                    @error('frequency_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Date -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_date" class="form-label">{{ __('app.start_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           value="{{ old('start_date', date('Y-m-d')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_date" class="form-label">{{ __('app.end_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Controlled Substance -->
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_controlled_substance" id="is_controlled_substance" 
                                               class="form-check-input @error('is_controlled_substance') is-invalid @enderror" 
                                               value="1" {{ old('is_controlled_substance') ? 'checked' : '' }}>
                                        <label for="is_controlled_substance" class="form-check-label">
                                            {{ __('app.controlled_substance') }}
                                        </label>
                                        @error('is_controlled_substance')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="form-group mb-3">
                            <label for="instructions" class="form-label">{{ __('app.instructions') }} <span class="text-danger">*</span></label>
                            <textarea name="instructions" id="instructions" rows="3" 
                                      class="form-control @error('instructions') is-invalid @enderror" required>{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Instructions Arabic -->
                        <div class="form-group mb-3">
                            <label for="instructions_ar" class="form-label">{{ __('app.instructions_ar') }}</label>
                            <textarea name="instructions_ar" id="instructions_ar" rows="3" 
                                      class="form-control @error('instructions_ar') is-invalid @enderror">{{ old('instructions_ar') }}</textarea>
                            @error('instructions_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warnings -->
                        <div class="form-group mb-3">
                            <label for="warnings" class="form-label">{{ __('app.warnings') }}</label>
                            <textarea name="warnings" id="warnings" rows="2" 
                                      class="form-control @error('warnings') is-invalid @enderror">{{ old('warnings') }}</textarea>
                            @error('warnings')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warnings Arabic -->
                        <div class="form-group mb-3">
                            <label for="warnings_ar" class="form-label">{{ __('app.warnings_ar') }}</label>
                            <textarea name="warnings_ar" id="warnings_ar" rows="2" 
                                      class="form-control @error('warnings_ar') is-invalid @enderror">{{ old('warnings_ar') }}</textarea>
                            @error('warnings_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pharmacy Notes -->
                        <div class="form-group mb-3">
                            <label for="pharmacy_notes" class="form-label">{{ __('app.pharmacy_notes') }}</label>
                            <input type="text" name="pharmacy_notes" id="pharmacy_notes" 
                                   class="form-control @error('pharmacy_notes') is-invalid @enderror" 
                                   value="{{ old('pharmacy_notes') }}">
                            @error('pharmacy_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pharmacy Notes Arabic -->
                        <div class="form-group mb-3">
                            <label for="pharmacy_notes_ar" class="form-label">{{ __('app.pharmacy_notes_ar') }}</label>
                            <input type="text" name="pharmacy_notes_ar" id="pharmacy_notes_ar" 
                                   class="form-control @error('pharmacy_notes_ar') is-invalid @enderror" 
                                   value="{{ old('pharmacy_notes_ar') }}">
                            @error('pharmacy_notes_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('app.create_prescription') }}
                            </button>
                            <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('app.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate end date based on start date and duration
    const startDateInput = document.getElementById('start_date');
    const durationInput = document.getElementById('duration_days');
    const endDateInput = document.getElementById('end_date');
    
    function calculateEndDate() {
        const startDate = startDateInput.value;
        const duration = parseInt(durationInput.value);
        
        if (startDate && duration && duration > 0) {
            const start = new Date(startDate);
            const end = new Date(start);
            end.setDate(start.getDate() + duration - 1);
            
            const year = end.getFullYear();
            const month = String(end.getMonth() + 1).padStart(2, '0');
            const day = String(end.getDate()).padStart(2, '0');
            
            endDateInput.value = `${year}-${month}-${day}`;
        }
    }
    
    startDateInput.addEventListener('change', calculateEndDate);
    durationInput.addEventListener('input', calculateEndDate);
    
    // Initial calculation if values are present
    calculateEndDate();
});
</script>
@endsection