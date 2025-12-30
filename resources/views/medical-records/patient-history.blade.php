@extends('layouts.app')

@section('title', __('app.patient_history') . ' - ' . $patient->name)

@push('styles')
<link href="{{ asset('css/medical-records.css') }}" rel="stylesheet">
@endpush

@section('content')
<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-2 mb-md-0">
                        <i class="fas fa-history"></i> {{ __('app.patient_history') }}
                    </h3>
                    <div class="card-tools d-flex flex-wrap gap-2">
                        <a href="{{ route('medical-records.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 
                            <span class="d-none d-sm-inline">{{ __('app.back') }}</span>
                        </a>
                        <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 
                            <span class="d-none d-sm-inline">{{ __('app.add_new') }} {{ __('app.medical_record') }}</span>
                            <span class="d-sm-none">جديد</span>
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Patient Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-user"></i> {{ __('app.patient_information') }}</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>{{ __('app.name') }}:</strong> {{ $patient->name }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('app.phone') }}:</strong> {{ $patient->phone }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('app.age') }}:</strong> {{ $patient->age }} {{ __('app.years') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('app.gender') }}:</strong> {{ $patient->gender_localized }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Records History -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>{{ __('app.visit_date') }}</th>
                                    <th>{{ __('app.doctor') }}</th>
                                    <th>{{ __('app.visit_type') }}</th>
                                    <th>{{ __('app.chief_complaint') }}</th>
                                    <th>{{ __('app.diagnosis') }}</th>
                                    <th>{{ __('app.treatment') }}</th>
                                    <th>{{ __('app.is_emergency') }}</th>
                                    <th>{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                    <tr>
                                        <td data-label="{{ __('app.visit_date') }}">
                                            <strong>{{ $record->visit_date->format('Y-m-d') }}</strong><br>
                                            <small class="text-muted">{{ $record->visit_date->format('H:i') }}</small>
                                        </td>
                                        <td data-label="{{ __('app.doctor') }}">{{ $record->doctor->name }}</td>
                                        <td data-label="{{ __('app.visit_type') }}">
                                            @php
                                                $visitTypeColors = [
                                                    'consultation' => 'bg-primary',
                                                    'follow_up' => 'bg-success',
                                                    'emergency' => 'bg-danger',
                                                    'routine_checkup' => 'bg-info',
                                                    'procedure' => 'bg-warning'
                                                ];
                                                $colorClass = $visitTypeColors[$record->visit_type] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $colorClass }}">{{ $record->visit_type_localized }}</span>
                                        </td>
                                        <td data-label="{{ __('app.chief_complaint') }}">{{ Str::limit($record->chief_complaint_localized, 40) }}</td>
                                        <td data-label="{{ __('app.diagnosis') }}">
                                            @if(is_array($record->diagnosis))
                                                <div class="diagnosis-container">
                                                    @foreach($record->diagnosis as $diagnosis)
                                                        <span class="badge diagnosis-badge" title="{{ $diagnosis }}">{{ Str::limit($diagnosis, 25) }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="diagnosis-text">{{ Str::limit($record->diagnosis_localized, 40) }}</span>
                                            @endif
                                        </td>
                                        <td data-label="{{ __('app.treatment') }}">
                            <div class="treatment-container">
                                <span class="treatment-text">{{ Str::limit($record->treatment_localized, 40) }}</span>
                                @if($record->prescriptions->count() > 0)
                                    <a href="{{ route('prescriptions.index', ['medical_record_id' => $record->id]) }}" 
                                       class="medication-link" 
                                       title="عرض {{ $record->prescriptions->count() }} وصفة طبية مرتبطة بهذه الزيارة">
                                        <i class="fas fa-pills"></i>
                                        الأدوية
                                        <span class="prescription-count">{{ $record->prescriptions->count() }}</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                                        <td data-label="{{ __('app.is_emergency') }}">
                                            @if($record->is_emergency)
                                                <span class="badge bg-danger">{{ __('app.yes') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('app.no') }}</span>
                                            @endif
                                        </td>
                                        <td data-label="{{ __('app.actions') }}">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-outline-info" title="{{ __('app.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($record->canEdit())
                                                    <a href="{{ route('medical-records.edit', $record) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-info-circle"></i>
                                                {{ __('app.no_medical_records_found') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($records->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $records->links() }}
                        </div>
                    @endif

                    <!-- Statistics -->
                    <div class="row mt-4">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-medical"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('app.total_visits') }}</span>
                                    <span class="info-box-number">{{ $records->total() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('app.last_visit') }}</span>
                                    <span class="info-box-number">
                                        @if($records->count() > 0)
                                            <small>{{ $records->first()->visit_date->diffForHumans() }}</small>
                                        @else
                                            {{ __('app.no_visits') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('app.emergency_visits') }}</span>
                                    <span class="info-box-number">
                                        {{ $records->where('is_emergency', true)->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-user-md"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('app.doctors_visited') }}</span>
                                    <span class="info-box-number">
                                        {{ $records->pluck('doctor_id')->unique()->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    // Show loading on page navigation
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link && !link.hasAttribute('target') && !link.href.includes('#')) {
            showLoading();
        }
    });
    
    // Show loading on form submission
    document.addEventListener('submit', function(e) {
        showLoading();
    });
    
    function showLoading() {
        if (loadingOverlay) {
            loadingOverlay.classList.add('show');
        }
    }
    
    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.classList.remove('show');
        }
    }
    
    // Hide loading when page is fully loaded
    window.addEventListener('load', hideLoading);
    
    // Hide loading on page show (back button)
    window.addEventListener('pageshow', hideLoading);
    
    // Enhanced tooltips for mobile
    if (window.innerWidth <= 768) {
        const badges = document.querySelectorAll('.diagnosis-badge[title]');
        badges.forEach(badge => {
            badge.addEventListener('click', function(e) {
                e.preventDefault();
                const title = this.getAttribute('title');
                if (title) {
                    alert(title);
                }
            });
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-warning)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>
@endpush
@endsection