@extends('layouts.app')

@section('title', 'عرض العائلة - ' . $familyCode)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class="bi bi-house-door me-2"></i>
                                عائلة {{ $familyHead->name }}
                            </h3>
                            <small class="opacity-75">رمز العائلة: {{ $familyCode }}</small>
                        </div>
                        <div>
                            <a href="{{ route('patients.families') }}" class="btn btn-light me-2">
                                <i class="bi bi-arrow-left me-1"></i>
                                العودة للعائلات
                            </a>
                            <a href="{{ route('patients.create') }}?family_code={{ $familyCode }}" class="btn btn-success">
                                <i class="bi bi-person-plus me-1"></i>
                                إضافة فرد جديد
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4 mb-2"></i>
                    <h4>{{ $familyMembers->count() }}</h4>
                    <p class="mb-0">إجمالي الأفراد</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="bi bi-gender-male display-4 mb-2"></i>
                    <h4>{{ $familyMembers->where('gender', 'male')->count() }}</h4>
                    <p class="mb-0">ذكور</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="bi bi-gender-female display-4 mb-2"></i>
                    <h4>{{ $familyMembers->where('gender', 'female')->count() }}</h4>
                    <p class="mb-0">إناث</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-heart display-4 mb-2"></i>
                    <h4>{{ number_format($familyMembers->avg('age'), 1) }}</h4>
                    <p class="mb-0">متوسط العمر</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Members -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        أفراد العائلة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($familyMembers as $member)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 {{ $member->id == $familyHead->id ? 'border-primary' : '' }}">
                                    @if($member->id == $familyHead->id)
                                        <div class="card-header bg-primary text-white">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-crown me-2"></i>
                                                <strong>رب الأسرة</strong>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="card-body">
                                        <!-- Profile Photo -->
                                        <div class="text-center mb-3">
                                            @if($member->profile_photo)
                                                <img src="{{ asset('storage/' . $member->profile_photo) }}" 
                                                     alt="صورة {{ $member->name }}" 
                                                     class="rounded-circle" 
                                                     width="80" height="80">
                                            @else
                                                <div class="bg-{{ $member->gender == 'male' ? 'primary' : 'danger' }} rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                                     style="width: 80px; height: 80px;">
                                                    <i class="bi bi-person text-white fs-2"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Member Info -->
                                        <div class="text-center">
                                            <h6 class="mb-1">{{ $member->name }}</h6>
                                            <p class="text-muted small mb-2">
                                                {{ $member->patient_number }}
                                            </p>
                                            
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">العمر</small>
                                                    <strong>{{ $member->age }} سنة</strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">الجنس</small>
                                                    <span class="badge bg-{{ $member->gender == 'male' ? 'primary' : 'danger' }}">
                                                        {{ $member->gender == 'male' ? 'ذكر' : 'أنثى' }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if($member->family_relation && $member->id != $familyHead->id)
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">صلة القرابة</small>
                                                    <span class="badge bg-info">{{ $member->family_relation }}</span>
                                                </div>
                                            @endif

                                            @if($member->blood_type)
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">فصيلة الدم</small>
                                                    <span class="badge bg-warning text-dark">{{ $member->blood_type }}</span>
                                                </div>
                                            @endif

                                            @if($member->phone)
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">الهاتف</small>
                                                    <small>{{ $member->phone }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('patients.show', $member) }}" 
                                               class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="bi bi-eye"></i>
                                                عرض
                                            </a>
                                            <a href="{{ route('patients.edit', $member) }}" 
                                               class="btn btn-outline-warning btn-sm flex-fill">
                                                <i class="bi bi-pencil"></i>
                                                تعديل
                                            </a>
                                            <a href="{{ route('appointments.create') }}?patient_id={{ $member->id }}" 
                                               class="btn btn-outline-success btn-sm flex-fill">
                                                <i class="bi bi-calendar-plus"></i>
                                                موعد
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Medical Summary -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-data me-2"></i>
                        ملخص طبي للعائلة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Recent Appointments -->
                        <div class="col-md-4">
                            <h6 class="text-muted">المواعيد الأخيرة</h6>
                            @php
                                $recentAppointments = \App\Models\Appointment::whereIn('patient_id', $familyMembers->pluck('id'))
                                    ->with(['patient', 'doctor'])
                                    ->orderBy('appointment_date', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            
                            @forelse($recentAppointments as $appointment)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                    <div>
                                        <small class="fw-bold">{{ $appointment->patient->name }}</small>
                                        <br>
                                        <small class="text-muted">{{ $appointment->appointment_date }}</small>
                                    </div>
                                    <span class="badge bg-{{ $appointment->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ $appointment->status }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted">لا توجد مواعيد حديثة</p>
                            @endforelse
                        </div>

                        <!-- Blood Types Distribution -->
                        <div class="col-md-4">
                            <h6 class="text-muted">فصائل الدم</h6>
                            @php
                                $bloodTypes = $familyMembers->whereNotNull('blood_type')->groupBy('blood_type');
                            @endphp
                            
                            @forelse($bloodTypes as $bloodType => $members)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-info">{{ $bloodType }}</span>
                                    <span class="small">{{ $members->count() }} أفراد</span>
                                </div>
                            @empty
                                <p class="text-muted">لم يتم تحديد فصائل الدم</p>
                            @endforelse
                        </div>

                        <!-- Age Distribution -->
                        <div class="col-md-4">
                            <h6 class="text-muted">التوزيع العمري</h6>
                            @php
                                $children = $familyMembers->where('age', '<', 18)->count();
                                $adults = $familyMembers->where('age', '>=', 18)->where('age', '<', 60)->count();
                                $seniors = $familyMembers->where('age', '>=', 60)->count();
                            @endphp
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>أطفال (أقل من 18)</span>
                                <span class="badge bg-success">{{ $children }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>بالغين (18-59)</span>
                                <span class="badge bg-primary">{{ $adults }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>كبار السن (60+)</span>
                                <span class="badge bg-warning">{{ $seniors }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush