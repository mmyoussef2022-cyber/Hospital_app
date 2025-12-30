@extends('layouts.app')

@section('page-title', 'إدارة المطالبات التأمينية')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">إجمالي المطالبات</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_claims']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-medical fa-2x opacity-75"></i>
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
                            <h6 class="card-title">قيد المراجعة</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_claims']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
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
                            <h6 class="card-title">موافق عليها</h6>
                            <h3 class="mb-0">{{ number_format($stats['approved_claims']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                            <h6 class="card-title">المبلغ المدفوع</h6>
                            <h3 class="mb-0">{{ number_format($stats['paid_amount'], 2) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-medical text-primary me-2"></i>
                        إدارة المطالبات التأمينية
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('insurance-claims.dashboard') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-1"></i>
                            لوحة التحكم
                        </a>
                        <a href="{{ route('insurance-claims.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            إضافة مطالبة جديدة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">شركة التأمين</label>
                            <select name="company_id" class="form-select">
                                <option value="">جميع الشركات</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>مقدم</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>قيد المراجعة</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الأولوية</label>
                            <select name="priority" class="form-select">
                                <option value="">جميع الأولويات</option>
                                <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>عادي</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
                                <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>طارئ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('insurance-claims.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Claims Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>رقم المطالبة</th>
                                    <th>المريض</th>
                                    <th>شركة التأمين</th>
                                    <th>تاريخ الخدمة</th>
                                    <th>المبلغ الإجمالي</th>
                                    <th>المبلغ المغطى</th>
                                    <th>الأولوية</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input claim-checkbox" value="{{ $claim->id }}">
                                        </td>
                                        <td>
                                            <strong class="text-primary">{{ $claim->claim_number ?: 'غير محدد' }}</strong>
                                        </td>
                                        <td>{{ $claim->patient->full_name }}</td>
                                        <td>{{ $claim->insuranceCompany->name }}</td>
                                        <td>{{ $claim->service_date->format('Y-m-d') }}</td>
                                        <td>{{ number_format($claim->total_amount, 2) }}</td>
                                        <td>{{ number_format($claim->covered_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $claim->priority_color }}">
                                                {{ $claim->priority_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $claim->status_color }}">
                                                {{ $claim->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('insurance-claims.show', $claim) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($claim->can_be_edited)
                                                    <a href="{{ route('insurance-claims.edit', $claim) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if($claim->can_be_submitted)
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="submitClaim({{ $claim->id }})" title="تقديم">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                @endif
                                                @if($claim->can_be_reviewed)
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="startReview({{ $claim->id }})" title="بدء المراجعة">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                @endif
                                                @if($claim->can_be_approved)
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="approveClaim({{ $claim->id }})" title="موافقة">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد مطالبات تأمينية</p>
                                            <a href="{{ route('insurance-claims.create') }}" class="btn btn-primary">
                                                إضافة مطالبة جديدة
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Bulk Actions -->
                    @if($claims->count() > 0)
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted">الإجراءات الجماعية:</span>
                                    <select id="bulkAction" class="form-select form-select-sm" style="width: auto;">
                                        <option value="">اختر إجراء</option>
                                        <option value="submit">تقديم المطالبات</option>
                                        <option value="approve">الموافقة على المطالبات</option>
                                        <option value="reject">رفض المطالبات</option>
                                        <option value="cancel">إلغاء المطالبات</option>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="executeBulkAction()">تنفيذ</button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Pagination -->
                    @if($claims->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $claims->links() }}
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
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.claim-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Individual claim actions
function submitClaim(claimId) {
    if (confirm('هل أنت متأكد من تقديم هذه المطالبة؟')) {
        fetch(`/insurance-claims/${claimId}/submit`, {
            method: 'POST',
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
                alert(data.message || 'حدث خطأ أثناء تقديم المطالبة');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تقديم المطالبة');
        });
    }
}

function startReview(claimId) {
    if (confirm('هل أنت متأكد من بدء مراجعة هذه المطالبة؟')) {
        fetch(`/insurance-claims/${claimId}/start-review`, {
            method: 'POST',
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
                alert(data.message || 'حدث خطأ أثناء بدء المراجعة');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء بدء المراجعة');
        });
    }
}

function approveClaim(claimId) {
    const approvedAmount = prompt('أدخل المبلغ الموافق عليه (اتركه فارغاً للموافقة على المبلغ الكامل):');
    if (approvedAmount !== null) {
        fetch(`/insurance-claims/${claimId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                approved_amount: approvedAmount || null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'حدث خطأ أثناء الموافقة على المطالبة');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء الموافقة على المطالبة');
        });
    }
}

// Bulk actions
function executeBulkAction() {
    const action = document.getElementById('bulkAction').value;
    if (!action) {
        alert('يرجى اختيار إجراء');
        return;
    }

    const selectedClaims = Array.from(document.querySelectorAll('.claim-checkbox:checked')).map(cb => cb.value);
    if (selectedClaims.length === 0) {
        alert('يرجى اختيار مطالبة واحدة على الأقل');
        return;
    }

    let reason = null;
    if (action === 'reject' || action === 'cancel') {
        reason = prompt('أدخل السبب:');
        if (!reason) {
            alert('السبب مطلوب لهذا الإجراء');
            return;
        }
    }

    if (confirm(`هل أنت متأكد من تنفيذ هذا الإجراء على ${selectedClaims.length} مطالبة؟`)) {
        fetch('/insurance-claims/bulk-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                action: action,
                claim_ids: selectedClaims,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'حدث خطأ أثناء تنفيذ الإجراء');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تنفيذ الإجراء');
        });
    }
}
</script>
@endpush