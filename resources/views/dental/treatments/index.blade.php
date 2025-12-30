@extends('layouts.app')

@section('title', 'إدارة علاج الأسنان')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-heart-pulse text-primary"></i>
                        إدارة علاج الأسنان
                    </h1>
                    <p class="text-muted mb-0">إدارة خطط العلاج والجلسات والأقساط</p>
                </div>
                <div class="d-flex gap-2">
                    @can('doctors.create')
                    <a href="{{ route('dental.treatments.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i>
                        خطة علاج جديدة
                    </a>
                    @endcan
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statisticsModal">
                        <i class="bi bi-graph-up"></i>
                        الإحصائيات
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-clipboard-pulse text-primary" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">إجمالي العلاجات</h5>
                    <h3 class="text-primary">{{ $treatments->total() }}</h3>
                    <small class="text-muted">خطة علاج</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">قيد التنفيذ</h5>
                    <h3 class="text-warning">{{ $treatments->where('status', 'in_progress')->count() }}</h3>
                    <small class="text-muted">علاج نشط</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">مكتملة</h5>
                    <h3 class="text-success">{{ $treatments->where('status', 'completed')->count() }}</h3>
                    <small class="text-muted">علاج مكتمل</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-credit-card text-info" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">بالأقساط</h5>
                    <h3 class="text-info">{{ $treatments->where('payment_type', 'installments')->count() }}</h3>
                    <small class="text-muted">دفع بالتقسيط</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i>
                        البحث والتصفية
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('dental.treatments.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="search" class="form-label">البحث</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="رقم العلاج، العنوان، أو اسم المريض">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="treatment_type" class="form-label">نوع العلاج</label>
                                <select class="form-select" id="treatment_type" name="treatment_type">
                                    <option value="">جميع الأنواع</option>
                                    @foreach($treatmentTypes as $key => $type)
                                        <option value="{{ $key }}" {{ request('treatment_type') == $key ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="status" class="form-label">الحالة</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">جميع الحالات</option>
                                    @foreach($statuses as $key => $statusName)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $statusName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="doctor_id" class="form-label">الطبيب</label>
                                <select class="form-select" id="doctor_id" name="doctor_id">
                                    <option value="">جميع الأطباء</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="payment_type" class="form-label">نوع الدفع</label>
                                <select class="form-select" id="payment_type" name="payment_type">
                                    <option value="">جميع الأنواع</option>
                                    <option value="cash" {{ request('payment_type') == 'cash' ? 'selected' : '' }}>نقدي</option>
                                    <option value="installments" {{ request('payment_type') == 'installments' ? 'selected' : '' }}>أقساط</option>
                                    <option value="insurance" {{ request('payment_type') == 'insurance' ? 'selected' : '' }}>تأمين</option>
                                </select>
                            </div>
                            <div class="col-md-1 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <a href="{{ route('dental.treatments.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Treatments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i>
                            قائمة العلاجات ({{ $treatments->total() }})
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkActionsBtn" style="display: none;">
                                <i class="bi bi-check-square"></i>
                                إجراءات مجمعة
                            </button>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportTreatments()">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($treatments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>رقم العلاج</th>
                                        <th>المريض</th>
                                        <th>الطبيب</th>
                                        <th>نوع العلاج</th>
                                        <th>التكلفة</th>
                                        <th>التقدم</th>
                                        <th>الحالة</th>
                                        <th>تاريخ البداية</th>
                                        <th width="120">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($treatments as $treatment)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input treatment-checkbox" value="{{ $treatment->id }}">
                                            </td>
                                            <td>
                                                <a href="{{ route('dental.treatments.show', $treatment) }}" class="text-decoration-none">
                                                    <strong>{{ $treatment->treatment_number }}</strong>
                                                </a>
                                                <br>
                                                <small class="text-muted">{{ $treatment->title }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 32px; height: 32px;">
                                                        <i class="bi bi-person-fill text-white"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $treatment->patient->name }}</div>
                                                        <small class="text-muted">{{ $treatment->patient->phone }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ $treatment->doctor->name }}</div>
                                                <small class="text-muted">{{ $treatment->doctor->doctor?->specialization ?? 'طبيب أسنان' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $treatment->treatment_type_display }}</span>
                                                @if($treatment->priority !== 'normal')
                                                    <br><span class="badge bg-{{ $treatment->priority_color }} mt-1">{{ $treatment->priority_display }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ number_format($treatment->total_cost, 2) }} ر.س</div>
                                                <small class="text-muted">{{ $treatment->payment_type_display }}</small>
                                                @if($treatment->payment_type === 'installments')
                                                    <br><small class="text-info">{{ $treatment->installment_months }} شهر</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="progress mb-1" style="height: 6px;">
                                                    <div class="progress-bar" 
                                                         role="progressbar" 
                                                         style="width: {{ $treatment->progress_percentage }}%"
                                                         aria-valuenow="{{ $treatment->progress_percentage }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted">{{ $treatment->completed_sessions }}/{{ $treatment->total_sessions }} جلسة</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $treatment->status_color }}">{{ $treatment->status_display }}</span>
                                                @if($treatment->is_overdue)
                                                    <br><small class="text-danger">متأخر {{ abs($treatment->remaining_days) }} يوم</small>
                                                @elseif($treatment->remaining_days > 0)
                                                    <br><small class="text-muted">{{ $treatment->remaining_days }} يوم متبقي</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ $treatment->start_date->format('Y/m/d') }}</div>
                                                <small class="text-muted">{{ $treatment->start_date->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('dental.treatments.show', $treatment) }}" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @can('doctors.edit')
                                                        @if($treatment->canBeEdited())
                                                            <a href="{{ route('dental.treatments.edit', $treatment) }}" 
                                                               class="btn btn-outline-secondary btn-sm" 
                                                               title="تعديل">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                        @endif
                                                    @endcan
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $treatment->id }})">
                                                                <i class="bi bi-arrow-repeat"></i> تغيير الحالة
                                                            </a></li>
                                                            @if($treatment->sessions->count() > 0)
                                                                <li><a class="dropdown-item" href="#">
                                                                    <i class="bi bi-calendar-event"></i> الجلسات ({{ $treatment->sessions->count() }})
                                                                </a></li>
                                                            @endif
                                                            @if($treatment->installments->count() > 0)
                                                                <li><a class="dropdown-item" href="#">
                                                                    <i class="bi bi-credit-card"></i> الأقساط ({{ $treatment->installments->count() }})
                                                                </a></li>
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                            @can('doctors.delete')
                                                                @if($treatment->canBeCancelled())
                                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteTreatment({{ $treatment->id }})">
                                                                        <i class="bi bi-trash"></i> حذف
                                                                    </a></li>
                                                                @endif
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $treatments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">لا توجد علاجات</h5>
                            <p class="text-muted">لم يتم العثور على أي علاجات تطابق معايير البحث</p>
                            @can('doctors.create')
                                <a href="{{ route('dental.treatments.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i>
                                    إنشاء خطة علاج جديدة
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const treatmentCheckboxes = document.querySelectorAll('.treatment-checkbox');
    const bulkActionsBtn = document.getElementById('bulkActionsBtn');
    
    selectAllCheckbox?.addEventListener('change', function() {
        treatmentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkActions();
    });
    
    treatmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });
    
    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.treatment-checkbox:checked');
        if (checkedBoxes.length > 0) {
            bulkActionsBtn.style.display = 'block';
        } else {
            bulkActionsBtn.style.display = 'none';
        }
    }
});

function updateStatus(treatmentId) {
    // TODO: Implement status update modal
    alert('ميزة تحديث الحالة قيد التطوير');
}

function deleteTreatment(treatmentId) {
    if (confirm('هل أنت متأكد من حذف هذه الخطة العلاجية؟')) {
        // TODO: Implement delete functionality
        alert('ميزة الحذف قيد التطوير');
    }
}

function exportTreatments() {
    // TODO: Implement export functionality
    alert('ميزة التصدير قيد التطوير');
}
</script>
@endpush