@extends('layouts.app')

@section('title', __('app.prescription_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-prescription-bottle-alt me-2"></i>
                        {{ __('app.prescription_details') }}
                    </h4>
                    <div class="btn-group">
                        <a href="{{ route('prescriptions.edit', $prescription) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('app.edit') }}
                        </a>
                        <a href="{{ route('prescriptions.print', $prescription) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-print me-1"></i>
                            {{ __('app.print') }}
                        </a>
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            {{ __('app.back') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Patient Information -->
                        <div class="col-md-6">
                            <div class="info-box bg-light p-3 rounded mb-3">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user-injured me-2"></i>
                                    {{ __('app.patient_information') }}
                                </h5>
                                <div class="row">
                                    <div class="col-sm-4"><strong>{{ __('app.name') }}:</strong></div>
                                    <div class="col-sm-8">{{ $prescription->patient->name }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4"><strong>{{ __('app.patient_number') }}:</strong></div>
                                    <div class="col-sm-8">{{ $prescription->patient->patient_number }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4"><strong>{{ __('app.age') }}:</strong></div>
                                    <div class="col-sm-8">{{ $prescription->patient->age }} {{ __('app.years') }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4"><strong>{{ __('app.gender') }}:</strong></div>
                                    <div class="col-sm-8">{{ __('app.' . $prescription->patient->gender) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Doctor Information -->
                        <div class="col-md-6">
                            <div class="info-box bg-light p-3 rounded mb-3">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-user-md me-2"></i>
                                    {{ __('app.doctor_information') }}
                                </h5>
                                <div class="row">
                                    <div class="col-sm-4"><strong>{{ __('app.doctor') }}:</strong></div>
                                    <div class="col-sm-8">{{ $prescription->doctor->name }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4"><strong>{{ __('app.specialization') }}:</strong></div>
                                    <div class="col-sm-8">{{ $prescription->doctor->specialization ?? __('app.not_specified') }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4"><strong>{{ __('app.prescription_date') }}:</strong></div>
                                    <div class="col-sm-8">{{ $prescription->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prescription Details -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-pills me-2"></i>
                                        {{ __('app.prescription_details') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>{{ __('app.medication_name') }}:</strong>
                                            <p class="mb-1">{{ $prescription->medication_name }}</p>
                                            @if($prescription->medication_name_ar)
                                                <p class="text-muted small">{{ $prescription->medication_name_ar }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('app.dosage') }}:</strong>
                                            <p>{{ $prescription->dosage }}</p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>{{ __('app.frequency') }}:</strong>
                                            <p class="mb-1">{{ $prescription->frequency }}</p>
                                            @if($prescription->frequency_ar)
                                                <p class="text-muted small">{{ $prescription->frequency_ar }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('app.duration') }}:</strong>
                                            <p>{{ $prescription->duration_days }} {{ __('app.days') }}</p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>{{ __('app.start_date') }}:</strong>
                                            <p>{{ $prescription->start_date }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('app.end_date') }}:</strong>
                                            <p>{{ $prescription->end_date }}</p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>{{ __('app.status') }}:</strong>
                                            <span class="badge badge-{{ $prescription->status == 'active' ? 'success' : ($prescription->status == 'completed' ? 'primary' : 'secondary') }}">
                                                {{ __('app.' . $prescription->status) }}
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            @if($prescription->is_controlled_substance)
                                                <strong>{{ __('app.controlled_substance') }}:</strong>
                                                <span class="badge badge-warning">{{ __('app.yes') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($prescription->instructions)
                                        <div class="mb-3">
                                            <strong>{{ __('app.instructions') }}:</strong>
                                            <div class="border p-2 rounded bg-light">
                                                <p class="mb-1">{{ $prescription->instructions }}</p>
                                                @if($prescription->instructions_ar)
                                                    <p class="text-muted small mb-0">{{ $prescription->instructions_ar }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($prescription->warnings)
                                        <div class="mb-3">
                                            <strong class="text-danger">{{ __('app.warnings') }}:</strong>
                                            <div class="border border-danger p-2 rounded bg-light">
                                                <p class="mb-1 text-danger">{{ $prescription->warnings }}</p>
                                                @if($prescription->warnings_ar)
                                                    <p class="text-muted small mb-0">{{ $prescription->warnings_ar }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($prescription->pharmacy_notes)
                                        <div class="mb-3">
                                            <strong>{{ __('app.pharmacy_notes') }}:</strong>
                                            <div class="border p-2 rounded bg-light">
                                                <p class="mb-1">{{ $prescription->pharmacy_notes }}</p>
                                                @if($prescription->pharmacy_notes_ar)
                                                    <p class="text-muted small mb-0">{{ $prescription->pharmacy_notes_ar }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Record Link -->
                    @if($prescription->medicalRecord)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-link me-2"></i>
                                    {{ __('app.related_medical_record') }}:
                                    <a href="{{ route('medical-records.show', $prescription->medicalRecord) }}" class="alert-link">
                                        {{ __('app.medical_record') }} #{{ $prescription->medicalRecord->id }}
                                        ({{ $prescription->medicalRecord->visit_date }})
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    @if($prescription->status == 'active')
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="btn-group">
                                    <form action="{{ route('prescriptions.complete', $prescription) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success" onclick="return confirm('{{ __('app.confirm_complete_prescription') }}')">
                                            <i class="fas fa-check me-1"></i>
                                            {{ __('app.mark_completed') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('prescriptions.cancel', $prescription) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('app.confirm_cancel_prescription') }}')">
                                            <i class="fas fa-times me-1"></i>
                                            {{ __('app.cancel') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection