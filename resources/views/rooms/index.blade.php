@extends('layouts.app')

@section('title', 'إدارة الغرف')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إدارة الغرف</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('rooms.dashboard') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> لوحة التحكم
                        </a>
                        <a href="{{ route('rooms.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة غرفة جديدة
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['total_rooms'] ?? 0 }}</h4>
                                            <p class="mb-0">إجمالي الغرف</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-bed fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['available_rooms'] ?? 0 }}</h4>
                                            <p class="mb-0">غرف متاحة</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['occupied_rooms'] ?? 0 }}</h4>
                                            <p class="mb-0">غرف مشغولة</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-injured fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ number_format($stats['occupancy_rate'] ?? 0, 1) }}%</h4>
                                            <p class="mb-0">معدل الإشغال</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-percentage fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-2">
                                    <select name="room_type" class="form-select">
                                        <option value="">جميع الأنواع</option>
                                        @foreach($roomTypes as $type)
                                            <option value="{{ $type }}" {{ request('room_type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="department" class="form-select">
                                        <option value="">جميع الأقسام</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                                {{ $dept }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="floor" class="form-select">
                                        <option value="">جميع الطوابق</option>
                                        @foreach($floors as $floor)
                                            <option value="{{ $floor }}" {{ request('floor') == $floor ? 'selected' : '' }}>
                                                الطابق {{ $floor }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="availability" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>متاحة</option>
                                        <option value="occupied" {{ request('availability') == 'occupied' ? 'selected' : '' }}>مشغولة</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="search" class="form-control" placeholder="رقم الغرفة" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-search"></i> بحث
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Rooms Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>رقم الغرفة</th>
                                    <th>النوع</th>
                                    <th>القسم</th>
                                    <th>الطابق</th>
                                    <th>السعة</th>
                                    <th>الأسرة المتاحة</th>
                                    <th>الحالة</th>
                                    <th>السعر اليومي</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rooms as $room)
                                    <tr>
                                        <td>
                                            <strong>{{ $room->room_number }}</strong>
                                            @if($room->wing)
                                                <small class="text-muted d-block">{{ $room->wing }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $room->room_type_display }}</span>
                                        </td>
                                        <td>{{ $room->department }}</td>
                                        <td>{{ $room->floor }}</td>
                                        <td>{{ $room->capacity }}</td>
                                        <td>
                                            <span class="badge bg-{{ $room->available_beds_count > 0 ? 'success' : 'danger' }}">
                                                {{ $room->available_beds_count }}/{{ $room->capacity }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $room->status_color }}">
                                                {{ $room->status_display }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($room->daily_rate, 2) }} ريال</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($room->status === 'available')
                                                    <button type="button" class="btn btn-sm btn-success" onclick="assignPatientModal({{ $room->id }})" title="تخصيص مريض">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                @endif
                                                @if($room->status === 'maintenance')
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="completeMaintenance({{ $room->id }})" title="إنهاء الصيانة">
                                                        <i class="fas fa-tools"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="markMaintenance({{ $room->id }})" title="صيانة">
                                                        <i class="fas fa-wrench"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد غرف مطابقة للبحث</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($rooms->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $rooms->appends(request()->query())->links() }}
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
                <h5 class="modal-title">تخصيص مريض للغرفة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignPatientForm">
                <div class="modal-body">
                    <input type="hidden" id="room_id" name="room_id">
                    
                    <div class="mb-3">
                        <label class="form-label">المريض</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">اختر المريض</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">السرير (اختياري)</label>
                        <select name="bed_id" class="form-select">
                            <option value="">تخصيص تلقائي</option>
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
function assignPatientModal(roomId) {
    $('#room_id').val(roomId);
    $('#assignPatientModal').modal('show');
    
    // Load available patients and beds
    loadAvailablePatients();
    loadAvailableBeds(roomId);
}

function loadAvailablePatients() {
    // This would typically load from an API endpoint
    // For now, we'll use a placeholder
}

function loadAvailableBeds(roomId) {
    // Load available beds for the room
    fetch(`/rooms/${roomId}/beds`)
        .then(response => response.json())
        .then(beds => {
            const bedSelect = document.querySelector('select[name="bed_id"]');
            bedSelect.innerHTML = '<option value="">تخصيص تلقائي</option>';
            
            beds.forEach(bed => {
                bedSelect.innerHTML += `<option value="${bed.id}">سرير ${bed.bed_number}</option>`;
            });
        });
}

function markMaintenance(roomId) {
    if (confirm('هل أنت متأكد من وضع الغرفة في الصيانة؟')) {
        fetch(`/rooms/${roomId}/mark-maintenance`, {
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

function completeMaintenance(roomId) {
    if (confirm('هل تم إنهاء صيانة الغرفة؟')) {
        fetch(`/rooms/${roomId}/complete-maintenance`, {
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

// Handle form submission
document.getElementById('assignPatientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const roomId = formData.get('room_id');
    
    fetch(`/rooms/${roomId}/assign-patient`, {
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