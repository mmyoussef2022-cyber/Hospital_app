@extends('layouts.app')

@section('page-title', 'إدارة شهادات الأطباء')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-award text-facebook"></i>
                        شهادات الأطباء
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-facebook" onclick="loadStatistics()">
                            <i class="bi bi-bar-chart"></i>
                            الإحصائيات
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="loadExpiringSoon()">
                            <i class="bi bi-exclamation-triangle"></i>
                            تنتهي قريباً
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="عنوان الشهادة، المؤسسة، اسم الطبيب..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">نوع الشهادة</label>
                            <select name="type" class="form-select">
                                <option value="">جميع الأنواع</option>
                                @foreach($types as $key => $value)
                                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">حالة التحقق</label>
                            <select name="verification_status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="verified" {{ request('verification_status') == 'verified' ? 'selected' : '' }}>مُتحقق منها</option>
                                <option value="pending" {{ request('verification_status') == 'pending' ? 'selected' : '' }}>في انتظار التحقق</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">حالة الانتهاء</label>
                            <select name="expiry_status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="expiring_soon" {{ request('expiry_status') == 'expiring_soon' ? 'selected' : '' }}>تنتهي قريباً</option>
                                <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-facebook">
                                    <i class="bi bi-search"></i>
                                    بحث
                                </button>
                                <a href="{{ route('doctor-certificates.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label" for="selectAll">
                                        تحديد الكل
                                    </label>
                                </div>
                                <div class="btn-group" id="bulkActions" style="display: none;">
                                    <button type="button" class="btn btn-success btn-sm" onclick="bulkVerify()">
                                        <i class="bi bi-check-circle"></i>
                                        تحقق جماعي
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4" id="statisticsCards" style="display: none;">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">إجمالي الشهادات</h6>
                                    <h4 class="mb-0" id="totalCertificates">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">مُتحقق منها</h6>
                                    <h4 class="mb-0" id="verifiedCertificates">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">في الانتظار</h6>
                                    <h4 class="mb-0" id="pendingCertificates">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">تنتهي قريباً</h6>
                                    <h4 class="mb-0" id="expiringSoonCertificates">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">منتهية</h6>
                                    <h4 class="mb-0" id="expiredCertificates">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">الأنواع</h6>
                                    <h4 class="mb-0" id="certificateTypes">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Certificates Table -->
                    @if($certificates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllTable" class="form-check-input">
                                        </th>
                                        <th>الشهادة</th>
                                        <th>الطبيب</th>
                                        <th>المؤسسة</th>
                                        <th>التواريخ</th>
                                        <th>حالة التحقق</th>
                                        <th>حالة الانتهاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($certificates as $certificate)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input certificate-checkbox" 
                                                       value="{{ $certificate->id }}">
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $certificate->title }}</div>
                                                    <small class="text-muted">
                                                        <span class="badge bg-info">{{ $certificate->type_display }}</span>
                                                        @if($certificate->certificate_number)
                                                            <br>رقم: {{ $certificate->certificate_number }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $certificate->doctor->full_name }}</div>
                                                    <small class="text-muted">{{ $certificate->doctor->doctor_number }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $certificate->institution }}</div>
                                                    @if($certificate->country)
                                                        <small class="text-muted">{{ $certificate->country }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i>
                                                        الإصدار: {{ $certificate->issue_date->format('Y-m-d') }}
                                                    </small>
                                                    @if($certificate->expiry_date)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar-x"></i>
                                                            الانتهاء: {{ $certificate->expiry_date->format('Y-m-d') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div id="verification-status-{{ $certificate->id }}">
                                                    {!! $certificate->status_display !!}
                                                    @if($certificate->is_verified && $certificate->verifiedBy)
                                                        <br>
                                                        <small class="text-muted">
                                                            بواسطة: {{ $certificate->verifiedBy->name }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($certificate->expiry_date)
                                                    {!! $certificate->expiry_status !!}
                                                @else
                                                    <span class="badge bg-secondary">لا تنتهي</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('doctors.certificates.show', [$certificate->doctor, $certificate]) }}" 
                                                       class="btn btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($certificate->file_path)
                                                        <a href="{{ route('doctor-certificates.download', $certificate) }}" 
                                                           class="btn btn-outline-primary" title="تحميل">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    @endif
                                                    @if(!$certificate->is_verified)
                                                        <button type="button" 
                                                                class="btn btn-outline-success" 
                                                                onclick="verifyCertificate({{ $certificate->id }})"
                                                                title="تحقق">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-outline-warning" 
                                                                onclick="unverifyCertificate({{ $certificate->id }})"
                                                                title="إلغاء التحقق">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('doctors.certificates.edit', [$certificate->doctor, $certificate]) }}" 
                                                       class="btn btn-outline-warning" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('doctors.certificates.destroy', [$certificate->doctor, $certificate]) }}" 
                                                          class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الشهادة؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $certificates->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-award text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">لا توجد شهادات</h5>
                            <p class="text-muted">لم يتم العثور على شهادات تطابق معايير البحث</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expiring Soon Modal -->
<div class="modal fade" id="expiringSoonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    الشهادات التي تنتهي قريباً
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="expiringSoonContent">
                    <div class="text-center">
                        <div class="spinner-border text-facebook" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.certificate-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.certificate-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

// Individual checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('certificate-checkbox')) {
        toggleBulkActions();
    }
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.certificate-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Verify certificate
function verifyCertificate(certificateId) {
    if (!confirm('هل أنت متأكد من التحقق من هذه الشهادة؟')) {
        return;
    }
    
    fetch(`/doctor-certificates/${certificateId}/verify`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في التحقق من الشهادة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في التحقق من الشهادة');
    });
}

// Unverify certificate
function unverifyCertificate(certificateId) {
    if (!confirm('هل أنت متأكد من إلغاء التحقق من هذه الشهادة؟')) {
        return;
    }
    
    fetch(`/doctor-certificates/${certificateId}/unverify`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في إلغاء التحقق من الشهادة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إلغاء التحقق من الشهادة');
    });
}

// Bulk verify
function bulkVerify() {
    const checkedBoxes = document.querySelectorAll('.certificate-checkbox:checked');
    const certificateIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (certificateIds.length === 0) {
        alert('يرجى تحديد شهادات للتحقق منها');
        return;
    }
    
    if (!confirm(`هل أنت متأكد من التحقق من ${certificateIds.length} شهادة؟`)) {
        return;
    }
    
    fetch('/doctor-certificates/bulk-verify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ certificate_ids: certificateIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('حدث خطأ في التحقق الجماعي');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في التحقق الجماعي');
    });
}

// Load statistics
function loadStatistics() {
    const statsCards = document.getElementById('statisticsCards');
    
    if (statsCards.style.display === 'none') {
        fetch('/doctor-certificates/statistics')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalCertificates').textContent = data.total;
            document.getElementById('verifiedCertificates').textContent = data.verified;
            document.getElementById('pendingCertificates').textContent = data.pending;
            document.getElementById('expiringSoonCertificates').textContent = data.expiring_soon;
            document.getElementById('expiredCertificates').textContent = data.expired;
            document.getElementById('certificateTypes').textContent = Object.keys(data.by_type).length;
            
            statsCards.style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل الإحصائيات');
        });
    } else {
        statsCards.style.display = 'none';
    }
}

// Load expiring soon certificates
function loadExpiringSoon() {
    const modal = new bootstrap.Modal(document.getElementById('expiringSoonModal'));
    modal.show();
    
    fetch('/doctor-certificates/expiring-soon')
    .then(response => response.json())
    .then(data => {
        let content = '';
        if (data.certificates.length > 0) {
            content = '<div class="table-responsive"><table class="table table-sm">';
            content += '<thead><tr><th>الشهادة</th><th>الطبيب</th><th>تاريخ الانتهاء</th></tr></thead><tbody>';
            
            data.certificates.forEach(cert => {
                content += `<tr>
                    <td>${cert.title}</td>
                    <td>${cert.doctor.user.name}</td>
                    <td>${cert.expiry_date}</td>
                </tr>`;
            });
            
            content += '</tbody></table></div>';
        } else {
            content = '<div class="text-center py-4"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i><h6 class="text-muted mt-2">لا توجد شهادات تنتهي قريباً</h6></div>';
        }
        
        document.getElementById('expiringSoonContent').innerHTML = content;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('expiringSoonContent').innerHTML = '<div class="alert alert-danger">حدث خطأ في تحميل البيانات</div>';
    });
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('select[name="type"], select[name="verification_status"], select[name="expiry_status"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
@endsection