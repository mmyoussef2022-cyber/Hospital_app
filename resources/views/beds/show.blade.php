@extends('layouts.app')

@section('title', 'تفاصيل السرير ' . $bed->full_bed_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Bed Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تفاصيل السرير {{ $bed->full_bed_number }}</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('beds.edit', $bed) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('beds.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3">المعلومات الأساسية</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>رقم السرير:</strong></td>
                                    <td>{{ $bed->full_bed_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الغرفة:</strong></td>
                                    <td>
                                        <a href="{{ route('rooms.show', $bed->room) }}" class="text-decoration-none">
                                            {{ $bed->room->room_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>نوع السرير:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $bed->bed_type_display }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>نوع الغرفة:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $bed->room->room_type_display }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>القسم:</strong></td>
                                    <td>{{ $bed->room->department }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الطابق:</strong></td>
                                    <td>{{ $bed->room->floor }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الحالة:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $bed->status_color }}">
                                            {{ $bed->status_display }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>آخر تنظيف:</strong></td>
                                    <td>
                                        @if($bed->last_cleaned_at)
                                            {{ $bed->last_cleaned_at->format('Y-m-d H:i') }}
                                            <small class="text-muted d-block">{{ $bed->last_cleaned_at->diffForHumans() }}</small>
                                            @if($bed->needsCleaning())
                                                <span class="badge bg-warning">يحتاج تنظيف</span>
                                            @endif
                                        @else
                                            <span class="text-muted">لم يتم التنظيف بعد</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Statistics -->
                        <div class="col-md-6">
                            <h5 class="mb-3">الإحصائيات</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-primary text-white text-center">
                                        <div class="card-body">
                                            <h4>{{ $stats['total_assignments'] ?? 0 }}</h4>
                                            <p class="mb-0">إجمالي التخصيصات</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-white text-center">
                                        <div class="card-body">
                                            <h4>{{ $stats['total_days_occupied'] ?? 0 }}</h4>
                                            <p class="mb-0">أيام الإشغال</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="card bg-info text-white text-center">
                                        <div class="card-body">
                                            <h4>{{ number_format($stats['average_stay'] ?? 0, 1) }}</h4>
                                            <p class="mb-0">متوسط الإقامة (أيام)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Patient -->
                    @if($stats['current_patient'])
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-user-injured"></i> المريض الحالي</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>الاسم:</strong> {{ $bed->currentAssignment->patient->name ?? 'غير محدد' }}<br>
                                                <strong>رقم الهوية:</strong> {{ $bed->currentAssignment->patient->national_id ?? 'غير محدد' }}<br>
                                                <strong>تاريخ الدخول:</strong> {{ $bed->currentAssignment->assigned_at ? $bed->currentAssignment->assigned_at->format('Y-m-d H:i') : 'غير محدد' }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>مدة الإقامة:</strong> {{ $bed->currentAssignment->duration_display ?? 'غير محدد' }}<br>
                                                <strong>الخروج المتوقع:</strong> 
                                                @if($bed->currentAssignment->expected_discharge_at)
                                                    {{ $bed->currentAssignment->expected_discharge_at->format('Y-m-d H:i') }}
                                                    @if($bed->currentAssignment->is_overdue)
                                                        <span class="badge bg-danger">متأخر</span>
                                                    @endif
                                                @else
                                                    غير محدد
                                                @endif
                                            </div>
                                        </div>
                                        @if($bed->currentAssignment->assignment_notes)
                                            <div class="mt-2">
                                                <strong>ملاحظات:</strong> {{ $bed->currentAssignment->assignment_notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Features -->
                    @if($bed->features && count($bed->features) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>المميزات</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($bed->features as $feature)
                                    <span class="badge bg-light text-dark">{{ $feature }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Assignment History -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">تاريخ التخصيصات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>تاريخ الدخول</th>
                                    <th>تاريخ الخروج</th>
                                    <th>المدة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bed->assignments->take(10) as $assignment)
                                    <tr>
                                        <td>
                                            <strong>{{ $assignment->patient->name ?? 'غير محدد' }}</strong>
                                            <small class="text-muted d-block">{{ $assignment->patient->national_id ?? '' }}</small>
                                        </td>
                                        <td>{{ $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d H:i') : 'غير محدد' }}</td>
                                        <td>
                                            @if($assignment->actual_discharge_at)
                                                {{ $assignment->actual_discharge_at->format('Y-m-d H:i') }}
                                            @elseif($assignment->status === 'active')
                                                <span class="text-muted">لا يزال مقيماً</span>
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </td>
                                        <td>{{ $assignment->duration_display }}</td>
                                        <td>
                                            <span class="badge bg-{{ $assignment->status_color }}">
                                                {{ $assignment->status_display }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">لا توجد تخصيصات سابقة</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    @if($bed->status === 'available')
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="assignPatientModal()">
                            <i class="fas fa-user-plus"></i> تخصيص مريض
                        </button>
                        <button type="button" class="btn btn-secondary w-100 mb-2" onclick="markMaintenance()">
                            <i class="fas fa-wrench"></i> وضع في الصيانة
                        </button>
                    @elseif($bed->status === 'occupied')
                        <button type="button" class="btn btn-warning w-100 mb-2" onclick="dischargePatient()">
                            <i class="fas fa-user-minus"></i> خروج المريض
                        </button>
                    @elseif($bed->status === 'maintenance')
                        <button type="button" class="btn btn-primary w-100 mb-2" onclick="completeMaintenance()">
                            <i class="fas fa-tools"></i> إنهاء الصيانة
                        </button>
                    @elseif($bed->status === 'cleaning')
                        <button type="button" class="btn btn-info w-100 mb-2" onclick="markCleaned()">
                            <i class="fas fa-broom"></i> تم التنظيف
                        </button>
                    @endif
                    
                    @if($bed->status !== 'cleaning')
                        <button type="button" class="btn btn-info w-100 mb-2" onclick="markCleaned()">
                            <i class="fas fa-broom"></i> تسجيل التنظيف
                        </button>
                    @endif
                </div>
            </div>

            <!-- Room Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">معلومات الغرفة</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>رقم الغرفة:</strong></td>
                            <td>{{ $bed->room->room_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>النوع:</strong></td>
                            <td>{{ $bed->room->room_type_display }}</td>
                        </tr>
                        <tr>
                            <td><strong>السعة:</strong></td>
                            <td>{{ $bed->room->capacity }} سرير</td>
                        </tr>
                        <tr>
                            <td><strong>المتاح:</strong></td>
                            <td>{{ $bed->room->available_beds_count }} سرير</td>
                        </tr>
                        <tr>
                            <td><strong>السعر اليومي:</strong></td>
                            <td>{{ number_format($bed->room->daily_rate, 2) }} ريال</td>
                        </tr>
                    </table>
                    <a href="{{ route('rooms.show', $bed->room) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-eye"></i> عرض تفاصيل الغرفة
                    </a>
                </div>
            </div>

            <!-- Cleaning Status -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">حالة التنظيف</h5>
                </div>
                <div class="card-body">
                    @if($bed->last_cleaned_at)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>آخر تنظيف:</span>
                            <span class="text-muted">{{ $bed->last_cleaned_at->diffForHumans() }}</span>
                        </div>
                        @if($bed->needsCleaning())
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                السرير يحتاج إلى تنظيف
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                السرير نظيف
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            لم يتم تسجيل تنظيف للسرير بعد
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Assignment Modal -->
<div class="modal fade" id="assignPatientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تخصيص مريض للسرير</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignPatientForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">المريض</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">اختر المريض</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تاريخ الخروج المتوقع</label>
                        <input type="datetime-local" name="expected_discharge_at" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="assignment_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تخصيص</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function assignPatientModal() {
    $('#assignPatientModal').modal('show');
}

function dischargePatient() {
    if (confirm('هل أنت متأكد من خروج المريض من هذا السرير؟')) {
        fetch(`/beds/{{ $bed->id }}/discharge-patient`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function markMaintenance() {
    if (confirm('هل أنت متأكد من وضع السرير في الصيانة؟')) {
        fetch(`/beds/{{ $bed->id }}/mark-maintenance`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function completeMaintenance() {
    if (confirm('هل تم إنهاء صيانة السرير؟')) {
        fetch(`/beds/{{ $bed->id }}/complete-maintenance`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function markCleaned() {
    fetch(`/beds/{{ $bed->id }}/mark-cleaned`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

// Handle form submission
document.getElementById('assignPatientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/beds/{{ $bed->id }}/assign-patient`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#assignPatientModal').modal('hide');
            location.reload();
        } else {
            alert(data.message);
        }
    });
});
</script>
@endpush