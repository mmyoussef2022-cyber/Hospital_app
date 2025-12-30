@extends('layouts.app')

@section('page-title', 'مواعيد اليوم')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-day text-facebook"></i>
                        مواعيد اليوم
                        <small class="text-muted ms-2">{{ today()->format('Y/m/d') }}</small>
                    </h5>
                    <a href="{{ route('appointments.create') }}" class="btn btn-facebook">
                        <i class="bi bi-plus-circle"></i>
                        حجز موعد جديد
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Quick Navigation -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <a href="{{ route('appointments.today') }}" 
                                   class="btn btn-facebook active">
                                    <i class="bi bi-calendar-day"></i>
                                    اليوم
                                </a>
                                <a href="{{ route('appointments.index', ['date' => today()->addDay()->format('Y-m-d')]) }}" 
                                   class="btn btn-outline-facebook">
                                    <i class="bi bi-calendar-plus"></i>
                                    غداً
                                </a>
                                <a href="{{ route('appointments.index') }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-calendar-check"></i>
                                    جميع المواعيد
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Display -->
                    @if($appointments->count() > 0)
                        <!-- Summary Stats -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $appointments->count() }}</h4>
                                        <small>إجمالي المواعيد</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $appointments->where('status', 'confirmed')->count() }}</h4>
                                        <small>مؤكد</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $appointments->where('status', 'scheduled')->count() }}</h4>
                                        <small>مجدول</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $appointments->where('status', 'in_progress')->count() }}</h4>
                                        <small>جاري</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Appointments Cards -->
                        <div class="row">
                            @foreach($appointments as $appointment)
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-clock text-muted me-2"></i>
                                                <strong>{{ $appointment->appointment_time->format('H:i') }}</strong>
                                                <small class="text-muted ms-2">({{ $appointment->duration }} دقيقة)</small>
                                            </div>
                                            <span class="badge bg-{{ $appointment->status_color }}">
                                                {{ $appointment->status_display }}
                                            </span>
                                        </div>
                                        
                                        <div class="card-body">
                                            <!-- Patient Info -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-person-fill text-primary me-2"></i>
                                                    <h6 class="mb-0">{{ $appointment->patient->name }}</h6>
                                                </div>
                                                <div class="text-muted small">
                                                    <div><i class="bi bi-card-text me-1"></i>{{ $appointment->patient->national_id }}</div>
                                                    @if($appointment->patient->phone)
                                                        <div><i class="bi bi-telephone me-1"></i>{{ $appointment->patient->phone }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Doctor Info -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-person-badge text-success me-2"></i>
                                                    <h6 class="mb-0">{{ $appointment->doctor->name }}</h6>
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $appointment->doctor->job_title ?? 'طبيب' }}
                                                </div>
                                            </div>

                                            <!-- Appointment Type -->
                                            <div class="mb-3">
                                                <span class="badge bg-info">{{ $appointment->type_display }}</span>
                                            </div>

                                            <!-- Notes -->
                                            @if($appointment->notes)
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="bi bi-sticky me-1"></i>
                                                        {{ Str::limit($appointment->notes, 50) }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Actions -->
                                        <div class="card-footer bg-light">
                                            <div class="btn-group w-100" role="group">
                                                <a href="{{ route('appointments.show', $appointment) }}" 
                                                   class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                    عرض
                                                </a>
                                                
                                                @if($appointment->canBeRescheduled())
                                                    <a href="{{ route('appointments.edit', $appointment) }}" 
                                                       class="btn btn-outline-warning btn-sm">
                                                        <i class="bi bi-pencil"></i>
                                                        تعديل
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="text-muted mb-3">لا توجد مواعيد اليوم</h4>
                            <p class="text-muted mb-4">
                                لم يتم جدولة أي مواعيد لتاريخ {{ today()->format('Y/m/d') }}
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('appointments.create') }}" class="btn btn-facebook">
                                    <i class="bi bi-plus-circle"></i>
                                    حجز موعد جديد
                                </a>
                                <a href="{{ route('appointments.index') }}" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-calendar-check"></i>
                                    جميع المواعيد
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection