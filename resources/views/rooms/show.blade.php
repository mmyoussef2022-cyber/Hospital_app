@extends('layouts.app')

@section('title', 'تفاصيل الغرفة ' . $room->room_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Room Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تفاصيل الغرفة {{ $room->room_number }}</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
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
                                    <td><strong>رقم الغرفة:</strong></td>
                                    <td>{{ $room->room_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>النوع:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $room->room_type_display }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>القسم:</strong></td>
                                    <td>{{ $room->department }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الطابق:</strong></td>
                                    <td>{{ $room->floor }}</td>
                                </tr>
                                @if($room->wing)
                                <tr>
                                    <td><strong>الجناح:</strong></td>
                                    <td>{{ $room->wing }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>السعة:</strong></td>
                                    <td>{{ $room->capacity }} سرير</td>
                                </tr>
                                <tr>
                                    <td><strong>الحالة:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $room->status_color }}">
                                            {{ $room->status_display }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>السعر اليومي:</strong></td>
                                    <td>{{ number_format($room->daily_rate, 2) }} ريال</td>
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
                                            <h4>{{ $stats['active_assignments'] ?? 0 }}</h4>
                                            <p class="mb-0">تخصيصات نشطة</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-info text-white text-center">
                                        <div class="card-body">
                                            <h4>{{ number_format($stats['total_revenue'] ?? 0, 2) }}</h4>
                                            <p class="mb-0">إجمالي الإيرادات</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-warning text-white text-center">
                                        <div class="card-body">
                                            <h4>{{ number_format($stats['average_stay'] ?? 0, 1) }}</h4>
                                            <p class="mb-0">متوسط الإقامة (أيام)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($room->description)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>الوصف</h5>
                            <p class="text-muted">{{ $room->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Amenities and Equipment -->
                    <div class="row mt-4">
                        @if($room->amenities && count($room->amenities) > 0)
                        <div class="col-md-6">
                            <h5>المرافق</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($room->amenities as $amenity)
                                    <span class="badge bg-light text-dark">{{ $amenity }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($room->equipment && count($room->equipment) > 0)
                        <div class="col-md-6">
                            <h5>المعدات الطبية</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($room->equipment as $equipment)
                                    <span class="badge bg-light text-dark">{{ $equipment }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Beds Information -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">الأسرة ({{ $room->beds->count() }})</h5>
                    <a href="{{ route('beds.create', ['room_id' => $room->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> إضافة سرير
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($room->beds as $bed)
                            <div class="col-md-4 mb-3">
                                <div class="card border-{{ $bed->status_color }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-title">سرير {{ $bed->bed_number }}</h6>
                                            <span class="badge bg-{{ $bed->status_color }}">
                                                {{ $bed->status_display }}
                                            </span>
                                        </div>
                                        <p class="card-text">
                                            <small class="text-muted">{{ $bed->bed_type_display }}</small>
                                        </p>
                                        
                                        @if($bed->currentAssignment)
                                            <div class="mt-2">
                                                <strong>المريض:</strong> {{ $bed->currentAssignment->patient->name ?? 'غير محدد' }}<br>
                                                <small class="text-muted">
                                                    منذ {{ $bed->currentAssignment->assigned_at ? $bed->currentAssignment->assigned_at->diffForHumans() : 'غير محدد' }}
                                                </small>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-2">
                                            <a href="{{ route('beds.show', $bed) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> عرض
                                            </a>
                                            @if($bed->status === 'available')
                                                <button type="button" class="btn btn-sm btn-success" onclick="assignPatientToBed({{ $bed->id }})">
                                                    <i class="fas fa-user-plus"></i> تخصيص
                                                </button>
                                            @elseif($bed->status === 'occupied')
                                                <button type="button" class="btn btn-sm btn-warning" onclick="dischargePatientFromBed({{ $bed->id }})">
                                                    <i class="fas fa-user-minus"></i> خروج
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center">
                                <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا توجد أسرة في هذه الغرفة</p>
                                <a href="{{ route('beds.create', ['room_id' => $room->id]) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة سرير
                                </a>
                            </div>
                        @endforelse
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
                    @if($room->status === 'available')
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="assignPatientModal({{ $room->id }})">
                            <i class="fas fa-user-plus"></i> تخصيص مريض
                        </button>
                    @endif
                    
                    @if($room->status === 'occupied')
                        <button type="button" class="btn btn-warning w-100 mb-2" onclick="dischargeAllPatients({{ $room->id }})">
                            <i class="fas fa-user-minus"></i> خروج جميع المرضى
                        </button>
                    @endif
                    
                    @if($room->status !== 'maintenance')
                        <button type="button" class="btn btn-secondary w-100 mb-2" onclick="markMaintenance({{ $room->id }})">
                            <i class="fas fa-wrench"></i> وضع في الصيانة
                        </button>
                    @else
                        <button type="button" class="btn btn-primary w-100 mb-2" onclick="completeMaintenance({{ $room->id }})">
                            <i class="fas fa-tools"></i> إنهاء الصيانة
                        </button>
                    @endif
                    
                    <button type="button" class="btn btn-info w-100 mb-2" onclick="markCleaned({{ $room->id }})">
                        <i class="fas fa-broom"></i> تسجيل التنظيف
                    </button>
                </div>
            </div>

            <!-- Recent Assignments -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">آخر التخصيصات</h5>
                </div>
                <div class="card-body">
                    @forelse($room->assignments->take(5) as $assignment)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong>{{ $assignment->patient->name ?? 'غير محدد' }}</strong>
                                <small class="text-muted d-block">
                                    {{ $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d') : 'غير محدد' }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $assignment->status_color }}">
                                {{ $assignment->status_display }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-center">لا توجد تخصيصات سابقة</p>
                    @endforelse
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
                    <input type="hidden" id="room_id" name="room_id" value="{{ $room->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">المريض</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">اختر المريض</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">السرير</label>
                        <select name="bed_id" class="form-select">
                            <option value="">تخصيص تلقائي</option>
                            @foreach($room->beds->where('status', 'available') as $bed)
                                <option value="{{ $bed->id }}">سرير {{ $bed->bed_number }}</option>
                            @endforeach
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
    $('#assignPatientModal').modal('show');
}

function assignPatientToBed(bedId) {
    // Implementation for bed-specific assignment
    console.log('Assign patient to bed:', bedId);
}

function dischargePatientFromBed(bedId) {
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

function markCleaned(roomId) {
    fetch(`/rooms/${roomId}/mark-cleaned`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم تسجيل تنظيف الغرفة بنجاح');
        } else {
            alert(data.message);
        }
    });
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