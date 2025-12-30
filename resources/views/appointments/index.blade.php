@extends('layouts.app')

@section('page-title', 'إدارة المواعيد')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check text-facebook"></i>
                        قائمة المواعيد
                    </h5>
                    <a href="{{ route('appointments.create') }}" class="btn btn-facebook">
                        <i class="bi bi-plus-circle"></i>
                        حجز موعد جديد
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">التاريخ</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date', today()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الطبيب</label>
                            <select name="doctor_id" class="form-select">
                                <option value="">جميع الأطباء</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>مجدول</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>جاري</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>لم يحضر</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-facebook">
                                    <i class="bi bi-search"></i>
                                    بحث
                                </button>
                                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <a href="{{ route('appointments.index', ['date' => today()->format('Y-m-d')]) }}" 
                                   class="btn btn-outline-facebook {{ request('date') == today()->format('Y-m-d') ? 'active' : '' }}">
                                    <i class="bi bi-calendar-day"></i>
                                    اليوم
                                </a>
                                <a href="{{ route('appointments.index', ['date' => today()->addDay()->format('Y-m-d')]) }}" 
                                   class="btn btn-outline-facebook {{ request('date') == today()->addDay()->format('Y-m-d') ? 'active' : '' }}">
                                    <i class="bi bi-calendar-plus"></i>
                                    غداً
                                </a>
                                <a href="{{ route('appointments.index', ['start_date' => now()->startOfWeek()->format('Y-m-d'), 'end_date' => now()->endOfWeek()->format('Y-m-d')]) }}" 
                                   class="btn btn-outline-facebook">
                                    <i class="bi bi-calendar-week"></i>
                                    هذا الأسبوع
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Table -->
                    @if($appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>التاريخ والوقت</th>
                                        <th>المريض</th>
                                        <th>الطبيب</th>
                                        <th>النوع</th>
                                        <th>المدة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $appointment->appointment_date->format('Y/m/d') }}</div>
                                                <small class="text-muted">{{ $appointment->appointment_time->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $appointment->patient->name }}</div>
                                                <small class="text-muted">{{ $appointment->patient->national_id }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $appointment->doctor->name }}</div>
                                                <small class="text-muted">{{ $appointment->doctor->job_title ?? 'طبيب' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $appointment->type_display }}</span>
                                            </td>
                                            <td>{{ $appointment->duration }} دقيقة</td>
                                            <td>
                                                <span class="badge bg-{{ $appointment->status_color }}">
                                                    {{ $appointment->status_display }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                                       class="btn btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($appointment->canBeRescheduled())
                                                        <a href="{{ route('appointments.edit', $appointment) }}" 
                                                           class="btn btn-outline-warning" title="تعديل">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endif
                                                    @if($appointment->canBeCancelled())
                                                        <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" 
                                                              class="d-inline" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الموعد؟')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="إلغاء">
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $appointments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">لا توجد مواعيد</h5>
                            <p class="text-muted">لم يتم العثور على مواعيد تطابق معايير البحث</p>
                            <a href="{{ route('appointments.create') }}" class="btn btn-facebook">
                                <i class="bi bi-plus-circle"></i>
                                حجز موعد جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterInputs = document.querySelectorAll('select[name="doctor_id"], select[name="status"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
@endsection