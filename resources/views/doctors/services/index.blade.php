@extends('layouts.app')

@section('title', 'إدارة خدمات الأطباء')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-gear-wide-connected"></i>
                        {{ __('app.doctor_services') }}
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('app.doctor_services') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    @if(request('doctor_id'))
                        <a href="{{ route('doctors.services.create', ['doctor' => request('doctor_id')]) }}" class="btn btn-success me-2">
                            <i class="bi bi-plus-circle"></i>
                            {{ __('app.add_new') }} {{ __('app.doctor_services') }}
                        </a>
                    @else
                        <div class="dropdown me-2">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-plus-circle"></i>
                                {{ __('app.add_new') }} {{ __('app.doctor_services') }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach($doctors as $doctor)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('doctors.services.create', ['doctor' => $doctor->id]) }}">
                                            {{ $doctor->user->name }} - {{ $doctor->specialization }}
                                        </a>
                                    </li>
                                @endforeach
                                @if($doctors->isEmpty())
                                    <li><span class="dropdown-item text-muted">{{ __('app.no_data_available') }}</span></li>
                                @endif
                            </ul>
                        </div>
                    @endif
                    <button type="button" class="btn btn-outline-facebook me-2" onclick="loadStatistics()">
                        <i class="bi bi-bar-chart"></i>
                        {{ __('app.statistics') }}
                    </button>
                    <button type="button" class="btn btn-facebook" data-bs-toggle="modal" data-bs-target="#bulkActionsModal">
                        <i class="bi bi-list-check"></i>
                        {{ __('app.bulk_actions') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statisticsCards" style="display: none;">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">إجمالي الخدمات</h6>
                            <h3 class="mb-0" id="totalServices">0</h3>
                        </div>
                        <i class="bi bi-gear-wide-connected fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">الخدمات النشطة</h6>
                            <h3 class="mb-0" id="activeServices">0</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">متوسط السعر</h6>
                            <h3 class="mb-0" id="averagePrice">0 ريال</h3>
                        </div>
                        <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">متوسط المدة</h6>
                            <h3 class="mb-0" id="averageDuration">0 دقيقة</h3>
                        </div>
                        <i class="bi bi-clock fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i>
                البحث والتصفية
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('doctor-services.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">البحث</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="اسم الخدمة أو الطبيب...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="category" class="form-label">الفئة</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">جميع الفئات</option>
                            @foreach($categories as $key => $value)
                                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">الحالة</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">جميع الحالات</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="appointment_required" class="form-label">يتطلب موعد</label>
                        <select class="form-select" id="appointment_required" name="appointment_required">
                            <option value="">الكل</option>
                            <option value="yes" {{ request('appointment_required') == 'yes' ? 'selected' : '' }}>نعم</option>
                            <option value="no" {{ request('appointment_required') == 'no' ? 'selected' : '' }}>لا</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="doctor_id" class="form-label">الطبيب</label>
                        <select class="form-select" id="doctor_id" name="doctor_id">
                            <option value="">جميع الأطباء</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->user->name }} - {{ $doctor->specialization }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-facebook me-2">
                            <i class="bi bi-search"></i>
                            بحث
                        </button>
                        <a href="{{ route('doctor-services.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Services List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-list"></i>
                    قائمة الخدمات ({{ $services->total() }})
                </h6>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn" style="display: none;">
                        <i class="bi bi-trash"></i>
                        حذف المحدد
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="bulkActivateBtn" style="display: none;">
                        <i class="bi bi-check-circle"></i>
                        تفعيل المحدد
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($services->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>الخدمة</th>
                                <th>الطبيب</th>
                                <th>الفئة</th>
                                <th>السعر</th>
                                <th>المدة</th>
                                <th>الحالة</th>
                                <th>موعد مطلوب</th>
                                <th width="150">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $service)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input service-checkbox" 
                                               value="{{ $service->id }}">
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $service->service_name }}</div>
                                            @if($service->service_name_en)
                                                <small class="text-muted">{{ $service->service_name_en }}</small>
                                            @endif
                                            @if($service->description)
                                                <div class="text-muted small mt-1">
                                                    {{ Str::limit($service->description, 100) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $service->doctor->user->name }}</div>
                                            <small class="text-muted">{{ $service->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $service->category_display }}</span>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold">{{ $service->price_formatted }}</span>
                                    </td>
                                    <td>
                                        <span class="text-info">{{ $service->duration_formatted }}</span>
                                    </td>
                                    <td>
                                        {!! $service->status_badge !!}
                                    </td>
                                    <td>
                                        {!! $service->appointment_badge !!}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('doctors.services.show', [$service->doctor, $service]) }}" 
                                               class="btn btn-outline-info" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('doctors.services.edit', [$service->doctor, $service]) }}" 
                                               class="btn btn-outline-warning" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('doctors.services.create', $service->doctor) }}" 
                                               class="btn btn-outline-success" title="إضافة خدمة جديدة لنفس الطبيب">
                                                <i class="bi bi-plus"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-{{ $service->is_active ? 'warning' : 'success' }}" 
                                                    onclick="toggleServiceStatus({{ $service->id }})" title="تغيير الحالة">
                                                <i class="bi bi-{{ $service->is_active ? 'pause' : 'play' }}-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteService({{ $service->id }}, '{{ $service->service_name }}')" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $services->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-gear-wide-connected text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">لا توجد خدمات</h5>
                    <p class="text-muted">لم يتم العثور على خدمات تطابق معايير البحث</p>
                    @if($doctors->isNotEmpty())
                        <div class="mt-4">
                            @if(request('doctor_id'))
                                <a href="{{ route('doctors.services.create', ['doctor' => request('doctor_id')]) }}" class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i>
                                    {{ __('app.add_new') }} {{ __('app.doctor_services') }}
                                </a>
                            @else
                                <div class="dropdown">
                                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-plus-circle"></i>
                                        {{ __('app.add_new') }} {{ __('app.doctor_services') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach($doctors as $doctor)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('doctors.services.create', ['doctor' => $doctor->id]) }}">
                                                    {{ $doctor->user->name }} - {{ $doctor->specialization }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">العمليات المجمعة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>اختر العملية المطلوب تطبيقها على الخدمات المحددة:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="bulkAction('activate')">
                        <i class="bi bi-check-circle"></i>
                        تفعيل الخدمات المحددة
                    </button>
                    <button type="button" class="btn btn-warning" onclick="bulkAction('deactivate')">
                        <i class="bi bi-pause-circle"></i>
                        إلغاء تفعيل الخدمات المحددة
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkAction('delete')">
                        <i class="bi bi-trash"></i>
                        حذف الخدمات المحددة
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Load statistics
function loadStatistics() {
    fetch('{{ route("doctor-services.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalServices').textContent = data.total;
            document.getElementById('activeServices').textContent = data.active;
            document.getElementById('averagePrice').textContent = Math.round(data.average_price || 0) + ' ريال';
            document.getElementById('averageDuration').textContent = Math.round(data.average_duration || 0) + ' دقيقة';
            
            document.getElementById('statisticsCards').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
            alert('حدث خطأ في تحميل الإحصائيات');
        });
}

// Toggle service status
function toggleServiceStatus(serviceId) {
    fetch(`/doctor-services/${serviceId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تغيير حالة الخدمة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تغيير حالة الخدمة');
    });
}

// Delete service
function deleteService(serviceId, serviceName) {
    if (confirm(`هل أنت متأكد من حذف الخدمة "${serviceName}"؟`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/doctor-services/${serviceId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk actions
function bulkAction(action) {
    const selectedServices = Array.from(document.querySelectorAll('.service-checkbox:checked')).map(cb => cb.value);
    
    if (selectedServices.length === 0) {
        alert('يرجى اختيار خدمة واحدة على الأقل');
        return;
    }

    let confirmMessage = '';
    switch (action) {
        case 'activate':
            confirmMessage = `هل أنت متأكد من تفعيل ${selectedServices.length} خدمة؟`;
            break;
        case 'deactivate':
            confirmMessage = `هل أنت متأكد من إلغاء تفعيل ${selectedServices.length} خدمة؟`;
            break;
        case 'delete':
            confirmMessage = `هل أنت متأكد من حذف ${selectedServices.length} خدمة؟ هذا الإجراء لا يمكن التراجع عنه.`;
            break;
    }

    if (confirm(confirmMessage)) {
        fetch('{{ route("doctor-services.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                service_ids: selectedServices
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('حدث خطأ في تنفيذ العملية');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تنفيذ العملية');
        });
    }

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal'));
    modal.hide();
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.service-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkButtons();
});

// Update bulk buttons visibility
function updateBulkButtons() {
    const selectedCount = document.querySelectorAll('.service-checkbox:checked').length;
    const bulkButtons = document.querySelectorAll('#bulkDeleteBtn, #bulkActivateBtn');
    
    bulkButtons.forEach(btn => {
        btn.style.display = selectedCount > 0 ? 'inline-block' : 'none';
    });
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.service-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkButtons);
    });
});
</script>
@endpush