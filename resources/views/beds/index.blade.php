@extends('layouts.app')

@section('title', 'إدارة الأسرة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إدارة الأسرة</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('rooms.dashboard') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> لوحة التحكم
                        </a>
                        <a href="{{ route('beds.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة سرير جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-2">
                                    <select name="room_id" class="form-select">
                                        <option value="">جميع الغرف</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                                {{ $room->room_number }} ({{ $room->room_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="bed_type" class="form-select">
                                        <option value="">جميع الأنواع</option>
                                        @foreach($bedTypes as $type)
                                            <option value="{{ $type }}" {{ request('bed_type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>متاح</option>
                                        <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>مشغول</option>
                                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>صيانة</option>
                                        <option value="cleaning" {{ request('status') == 'cleaning' ? 'selected' : '' }}>تنظيف</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="room_type" class="form-select">
                                        <option value="">جميع أنواع الغرف</option>
                                        @foreach($roomTypes as $type)
                                            <option value="{{ $type }}" {{ request('room_type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="search" class="form-control" placeholder="رقم السرير أو الغرفة" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-search"></i> بحث
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Beds Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>رقم السرير</th>
                                    <th>الغرفة</th>
                                    <th>نوع السرير</th>
                                    <th>نوع الغرفة</th>
                                    <th>الحالة</th>
                                    <th>المريض الحالي</th>
                                    <th>آخر تنظيف</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($beds as $bed)
                                    <tr>
                                        <td>
                                            <strong>{{ $bed->full_bed_number }}</strong>
                                            @if($bed->features && count($bed->features) > 0)
                                                <div class="mt-1">
                                                    @foreach($bed->features as $feature)
                                                        <span class="badge bg-light text-dark">{{ $feature }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('rooms.show', $bed->room) }}" class="text-decoration-none">
                                                {{ $bed->room->room_number }}
                                            </a>
                                            <small class="text-muted d-block">{{ $bed->room->department }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $bed->bed_type_display }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $bed->room->room_type_display }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $bed->status_color }}">
                                                {{ $bed->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($bed->currentAssignment)
                                                <strong>{{ $bed->currentAssignment->patient->name ?? 'غير محدد' }}</strong>
                                                <small class="text-muted d-block">
                                                    منذ {{ $bed->currentAssignment->assigned_at ? $bed->currentAssignment->assigned_at->diffForHumans() : 'غير محدد' }}
                                                </small>
                                            @else
                                                <span class="text-muted">لا يوجد</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($bed->last_cleaned_at)
                                                {{ $bed->last_cleaned_at->diffForHumans() }}
                                                @if($bed->needsCleaning())
                                                    <span class="badge bg-warning">يحتاج تنظيف</span>
                                                @endif
                                            @else
                                                <span class="text-muted">لم يتم التنظيف</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('beds.show', $bed) }}" class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('beds.edit', $bed) }}" class="btn btn-sm btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($bed->status === 'available')
                                                    <button type="button" class="btn btn-sm btn-success" onclick="assignPatientModal({{ $bed->id }})" title="تخصيص مريض">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleStatus({{ $bed->id }})" title="صيانة">
                                                        <i class="fas fa-wrench"></i>
                                                    </button>
                                                @elseif($bed->status === 'occupied')
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="dischargePatient({{ $bed->id }})" title="خروج المريض">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                @elseif($bed->status === 'maintenance')
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="completeMaintenance({{ $bed->id }})" title="إنهاء الصيانة">
                                                        <i class="fas fa-tools"></i>
                                                    </button>
                                                @elseif($bed->status === 'cleaning')
                                                    <button type="button" class="btn btn-sm btn-info" onclick="markCleaned({{ $bed->id }})" title="تم التنظيف">
                                                        <i class="fas fa-broom"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد أسرة مطابقة للبحث</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($beds->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $beds->appends(request()->query())->links() }}
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
                    <input type="hidden" id="bed_id" name="bed_id">
                    
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
function assignPatientModal(bedId) {
    $('#bed_id').val(bedId);
    $('#assignPatientModal').modal('show');
    
    // Load available patients
    loadAvailablePatients();
}

function loadAvailablePatients() {
    // This would typically load from an API endpoint
    // For now, we'll use a placeholder
}

function dischargePatient(bedId) {
    if (confirm('هل أنت متأكد من خروج المريض من هذا السرير؟')) {
        fetch(`/beds/${bedId}/discharge-patient`, {
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

function toggleStatus(bedId) {
    fetch(`/beds/${bedId}/toggle-status`, {
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

function completeMaintenance(bedId) {
    if (confirm('هل تم إنهاء صيانة السرير؟')) {
        fetch(`/beds/${bedId}/complete-maintenance`, {
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

function markCleaned(bedId) {
    fetch(`/beds/${bedId}/mark-cleaned`, {
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
    const bedId = formData.get('bed_id');
    
    fetch(`/beds/${bedId}/assign-patient`, {
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