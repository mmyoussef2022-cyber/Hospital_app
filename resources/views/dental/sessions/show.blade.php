@extends('layouts.app')

@section('title', 'تفاصيل الجلسة - ' . $session->session_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">🦷 تفاصيل الجلسة {{ $session->session_number }}</h2>
                    <p class="text-muted mb-0">{{ $session->session_title }}</p>
                </div>
                <div>
                    <a href="{{ route('dental.sessions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للقائمة
                    </a>
                    @can('update', $session)
                        <a href="{{ route('dental.sessions.edit', $session) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i>
                            تعديل
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Session Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">معلومات الجلسة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">رقم الجلسة:</th>
                                    <td>{{ $session->session_number }}</td>
                                </tr>
                                <tr>
                                    <th>ترتيب الجلسة:</th>
                                    <td>الجلسة {{ $session->session_order }}</td>
                                </tr>
                                <tr>
                                    <th>عنوان الجلسة:</th>
                                    <td>{{ $session->session_title }}</td>
                                </tr>
                                <tr>
                                    <th>التاريخ المجدول:</th>
                                    <td>{{ $session->scheduled_date ? $session->scheduled_date->format('Y-m-d') : 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <th>تاريخ الإكمال:</th>
                                    <td>{{ $session->completed_date ? $session->completed_date->format('Y-m-d') : 'لم تكتمل بعد' }}</td>
                                </tr>
                                <tr>
                                    <th>المدة:</th>
                                    <td>{{ $session->duration_display }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">الحالة:</th>
                                    <td>
                                        <span class="badge bg-{{ $session->status_color }}">
                                            {{ $session->status_display }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>التكلفة:</th>
                                    <td>{{ number_format($session->session_cost, 2) }} ر.س</td>
                                </tr>
                                <tr>
                                    <th>المبلغ المدفوع:</th>
                                    <td>{{ number_format($session->session_payment, 2) }} ر.س</td>
                                </tr>
                                <tr>
                                    <th>حالة الدفع:</th>
                                    <td>
                                        <span class="badge bg-{{ $session->payment_status_color }}">
                                            {{ $session->payment_status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>متابعة مطلوبة:</th>
                                    <td>
                                        @if($session->follow_up_required)
                                            <span class="text-warning">نعم</span>
                                            @if($session->follow_up_date)
                                                <br><small>{{ $session->follow_up_date->format('Y-m-d') }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">لا</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($session->session_description)
                        <div class="mt-3">
                            <h6>وصف الجلسة:</h6>
                            <p class="text-muted">{{ $session->session_description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Treatment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">معلومات خطة العلاج</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>المريض</h6>
                            <p class="mb-1"><strong>{{ $session->dentalTreatment->patient->name }}</strong></p>
                            <p class="text-muted mb-3">{{ $session->dentalTreatment->patient->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>الطبيب</h6>
                            <p class="mb-1"><strong>{{ $session->dentalTreatment->doctor->name }}</strong></p>
                            <p class="text-muted mb-3">{{ $session->dentalTreatment->doctor->specialization }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6>خطة العلاج</h6>
                            <p class="mb-1"><strong>{{ $session->dentalTreatment->title }}</strong></p>
                            <p class="text-muted">{{ $session->dentalTreatment->description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Procedures and Materials -->
            @if($session->procedures_performed || $session->materials_used)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">الإجراءات والمواد المستخدمة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($session->procedures_performed)
                                <div class="col-md-6">
                                    <h6>الإجراءات المنفذة:</h6>
                                    <ul class="list-unstyled">
                                        @foreach($session->procedures_performed as $procedure)
                                            <li><i class="bi bi-check-circle text-success"></i> {{ $procedure }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if($session->materials_used)
                                <div class="col-md-6">
                                    <h6>المواد المستخدمة:</h6>
                                    <ul class="list-unstyled">
                                        @foreach($session->materials_used as $material)
                                            <li><i class="bi bi-box text-info"></i> {{ $material }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Session Notes -->
            @if($session->session_notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ملاحظات الجلسة</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $session->session_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Next Session Plan -->
            @if($session->next_session_plan)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">خطة الجلسة القادمة</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $session->next_session_plan }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($session->canBeCompleted())
                            <form method="POST" action="{{ route('dental.sessions.complete', $session) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100"
                                        onclick="return confirm('هل أنت متأكد من تحديد هذه الجلسة كمكتملة؟')">
                                    <i class="bi bi-check-circle"></i>
                                    تحديد كمكتملة
                                </button>
                            </form>
                        @endif

                        @if($session->canBeCancelled())
                            <form method="POST" action="{{ route('dental.sessions.cancel', $session) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm w-100"
                                        onclick="return confirm('هل أنت متأكد من إلغاء هذه الجلسة؟')">
                                    <i class="bi bi-x-circle"></i>
                                    إلغاء الجلسة
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('dental.treatments.show', $session->dentalTreatment) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i>
                            عرض خطة العلاج
                        </a>

                        <a href="{{ route('patients.show', $session->dentalTreatment->patient) }}" 
                           class="btn btn-outline-info btn-sm">
                            <i class="bi bi-person"></i>
                            ملف المريض
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pain Assessment -->
            @if($session->pain_level_before !== null || $session->pain_level_after !== null)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">تقييم الألم</h5>
                    </div>
                    <div class="card-body">
                        @if($session->pain_level_before !== null)
                            <div class="mb-3">
                                <label class="form-label">مستوى الألم قبل الجلسة:</label>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-danger" 
                                         style="width: {{ ($session->pain_level_before / 10) * 100 }}%">
                                        {{ $session->pain_level_before }}/10
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($session->pain_level_after !== null)
                            <div class="mb-3">
                                <label class="form-label">مستوى الألم بعد الجلسة:</label>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ ($session->pain_level_after / 10) * 100 }}%">
                                        {{ $session->pain_level_after }}/10
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($session->pain_improvement !== null)
                            <div class="text-center">
                                <small class="text-muted">{{ $session->pain_improvement_display }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Complications -->
            @if($session->complications && count($session->complications) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0 text-warning">مضاعفات</h5>
                    </div>
                    <div class="card-body">
                        @foreach($session->complications as $complication)
                            <div class="alert alert-warning alert-sm mb-2">
                                <small>{{ $complication['description'] ?? $complication }}</small>
                                @if(isset($complication['timestamp']))
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($complication['timestamp'])->format('Y-m-d H:i') }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Session Photos -->
            @if($session->session_photos && count($session->session_photos) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">صور الجلسة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($session->session_photos as $photo)
                                <div class="col-6">
                                    <img src="{{ Storage::url($photo['path'] ?? $photo) }}" 
                                         class="img-fluid rounded" 
                                         alt="صورة الجلسة"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#photoModal{{ $loop->index }}"
                                         style="cursor: pointer;">
                                </div>

                                <!-- Photo Modal -->
                                <div class="modal fade" id="photoModal{{ $loop->index }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">صورة الجلسة</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ Storage::url($photo['path'] ?? $photo) }}" 
                                                     class="img-fluid" alt="صورة الجلسة">
                                                @if(isset($photo['description']))
                                                    <p class="mt-3">{{ $photo['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table-borderless th {
    font-weight: 600;
    color: #495057;
}

.progress {
    background-color: #e9ecef;
}

.alert-sm {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.img-fluid:hover {
    opacity: 0.8;
    transition: opacity 0.3s;
}
</style>
@endpush
@endsection