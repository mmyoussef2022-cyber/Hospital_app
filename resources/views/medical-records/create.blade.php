@extends('layouts.app')

@section('title', __('app.add_new') . ' ' . __('app.medical_record'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('app.add_new') }} {{ __('app.medical_record') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('medical-records.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('app.back') }}
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('medical-records.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Patient Selection -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id">{{ __('app.patient') }} <span class="text-danger">*</span></label>
                                    <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                        <option value="">{{ __('app.select') }} {{ __('app.patient') }}</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ (old('patient_id') == $patient->id || ($selectedPatient && $selectedPatient->id == $patient->id)) ? 'selected' : '' }}>
                                                {{ $patient->name }} - {{ $patient->phone }}
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
                                <div class="form-group">
                                    <label for="doctor_id">{{ __('app.doctor') }} <span class="text-danger">*</span></label>
                                    <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                        <option value="">{{ __('app.select') }} {{ __('app.doctor') }}</option>
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
                            <!-- Visit Date -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="visit_date">{{ __('app.visit_date') }} <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="visit_date" id="visit_date" class="form-control @error('visit_date') is-invalid @enderror" value="{{ old('visit_date', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('visit_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Visit Type -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="visit_type">{{ __('app.visit_type') }} <span class="text-danger">*</span></label>
                                    <select name="visit_type" id="visit_type" class="form-control @error('visit_type') is-invalid @enderror" required>
                                        <option value="consultation" {{ old('visit_type') == 'consultation' ? 'selected' : '' }}>{{ __('app.consultation') }}</option>
                                        <option value="follow_up" {{ old('visit_type') == 'follow_up' ? 'selected' : '' }}>{{ __('app.follow_up') }}</option>
                                        <option value="emergency" {{ old('visit_type') == 'emergency' ? 'selected' : '' }}>{{ __('app.emergency') }}</option>
                                        <option value="routine_checkup" {{ old('visit_type') == 'routine_checkup' ? 'selected' : '' }}>{{ __('app.routine_checkup') }}</option>
                                        <option value="procedure" {{ old('visit_type') == 'procedure' ? 'selected' : '' }}>{{ __('app.procedure') }}</option>
                                    </select>
                                    @error('visit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Emergency -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="is_emergency">{{ __('app.is_emergency') }}</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="is_emergency" id="is_emergency" class="form-check-input" value="1" {{ old('is_emergency') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_emergency">
                                            {{ __('app.yes') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chief Complaint -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="chief_complaint">{{ __('app.chief_complaint') }} ({{ __('app.english') }}) <span class="text-danger">*</span></label>
                                    <textarea name="chief_complaint" id="chief_complaint" class="form-control @error('chief_complaint') is-invalid @enderror" rows="3" required>{{ old('chief_complaint') }}</textarea>
                                    @error('chief_complaint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="chief_complaint_ar">{{ __('app.chief_complaint') }} ({{ __('app.arabic') }})</label>
                                    <textarea name="chief_complaint_ar" id="chief_complaint_ar" class="form-control @error('chief_complaint_ar') is-invalid @enderror" rows="3">{{ old('chief_complaint_ar') }}</textarea>
                                    @error('chief_complaint_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="diagnosis">{{ __('app.diagnosis') }} ({{ __('app.english') }}) <span class="text-danger">*</span></label>
                                    <textarea name="diagnosis[]" id="diagnosis" class="form-control @error('diagnosis') is-invalid @enderror" rows="3" required>{{ old('diagnosis.0') }}</textarea>
                                    @error('diagnosis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="diagnosis_ar">{{ __('app.diagnosis') }} ({{ __('app.arabic') }})</label>
                                    <textarea name="diagnosis_ar[]" id="diagnosis_ar" class="form-control @error('diagnosis_ar') is-invalid @enderror" rows="3">{{ old('diagnosis_ar.0') }}</textarea>
                                    @error('diagnosis_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Treatment -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="treatment">{{ __('app.treatment') }} ({{ __('app.english') }}) <span class="text-danger">*</span></label>
                                    <textarea name="treatment" id="treatment" class="form-control @error('treatment') is-invalid @enderror" rows="4" required>{{ old('treatment') }}</textarea>
                                    @error('treatment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="treatment_ar">{{ __('app.treatment') }} ({{ __('app.arabic') }})</label>
                                    <textarea name="treatment_ar" id="treatment_ar" class="form-control @error('treatment_ar') is-invalid @enderror" rows="4">{{ old('treatment_ar') }}</textarea>
                                    @error('treatment_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes">{{ __('app.notes') }} ({{ __('app.english') }})</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes_ar">{{ __('app.notes') }} ({{ __('app.arabic') }})</label>
                                    <textarea name="notes_ar" id="notes_ar" class="form-control @error('notes_ar') is-invalid @enderror" rows="3">{{ old('notes_ar') }}</textarea>
                                    @error('notes_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Follow-up Date -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="follow_up_date">{{ __('app.follow_up_date') }}</label>
                                    <input type="date" name="follow_up_date" id="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date') }}">
                                    @error('follow_up_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div class="form-group">
                            <label for="attachments">{{ __('app.attachments') }}</label>
                            <input type="file" name="attachments[]" id="attachments" class="form-control-file @error('attachments') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="form-text text-muted">{{ __('app.max_file_size') }}: 10MB. {{ __('app.allowed_types') }}: PDF, JPG, PNG, DOC, DOCX</small>
                            @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('app.save') }}
                        </button>
                        <a href="{{ route('medical-records.index') }}" class="btn btn-secondary">
                            {{ __('app.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection