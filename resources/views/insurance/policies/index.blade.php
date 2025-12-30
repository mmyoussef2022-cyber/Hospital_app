@extends('layouts.app')

@section('page-title', 'إدارة بوالص التأمين')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract text-primary me-2"></i>
                        إدارة بوالص التأمين
                    </h5>
                    <a href="{{ route('insurance-policies.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة بوليصة جديدة
                    </a>
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
                            <label class="form-label">نوع البوليصة</label>
                            <select name="policy_type" class="form-select">
                                <option value="">جميع الأنواع</option>
                                <option value="individual" {{ request('policy_type') == 'individual' ? 'selected' : '' }}>فردي</option>
                                <option value="family" {{ request('policy_type') == 'family' ? 'selected' : '' }}>عائلي</option>
                                <option value="group" {{ request('policy_type') == 'group' ? 'selected' : '' }}>جماعي</option>
                                <option value="corporate" {{ request('policy_type') == 'corporate' ? 'selected' : '' }}>شركات</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>معلق</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهي</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" placeholder="رقم البوليصة أو الاسم..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('insurance-policies.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Policies Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم البوليصة</th>
                                    <th>اسم البوليصة</th>
                                    <th>شركة التأمين</th>
                                    <th>النوع</th>
                                    <th>نسبة التغطية</th>
                                    <th>تاريخ السريان</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($policies as $policy)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $policy->policy_number }}</strong>
                                        </td>
                                        <td>{{ $policy->policy_name }}</td>
                                        <td>{{ $policy->insuranceCompany->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $policy->policy_type_display }}</span>
                                        </td>
                                        <td>{{ $policy->coverage_percentage }}%</td>
                                        <td>{{ $policy->effective_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($policy->expiry_date)
                                                <span class="{{ $policy->is_expiring_soon ? 'text-warning' : '' }}">
                                                    {{ $policy->expiry_date->format('Y-m-d') }}
                                                </span>
                                                @if($policy->is_expiring_soon)
                                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="ينتهي قريباً"></i>
                                                @endif
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $policy->status === 'active' ? 'success' : ($policy->status === 'suspended' ? 'warning' : 'danger') }}">
                                                {{ $policy->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('insurance-policies.show', $policy) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('insurance-policies.edit', $policy) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($policy->status === 'active')
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="togglePolicyStatus({{ $policy->id }}, 'suspend')" title="تعليق">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                @elseif($policy->status === 'suspended')
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="togglePolicyStatus({{ $policy->id }}, 'activate')" title="تفعيل">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                                @if($policy->status !== 'cancelled')
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="togglePolicyStatus({{ $policy->id }}, 'cancel')" title="إلغاء">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد بوالص تأمين</p>
                                            <a href="{{ route('insurance-policies.create') }}" class="btn btn-primary">
                                                إضافة بوليصة جديدة
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($policies->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $policies->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تغيير حالة البوليصة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="policyId">
                    <input type="hidden" id="action">
                    
                    <div class="mb-3" id="reasonGroup" style="display: none;">
                        <label class="form-label">السبب</label>
                        <textarea class="form-control" id="reason" rows="3" placeholder="اختياري..."></textarea>
                    </div>
                    
                    <p id="confirmMessage"></p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="confirmStatusChange()">تأكيد</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePolicyStatus(policyId, action) {
    document.getElementById('policyId').value = policyId;
    document.getElementById('action').value = action;
    
    const reasonGroup = document.getElementById('reasonGroup');
    const confirmMessage = document.getElementById('confirmMessage');
    
    let message = '';
    let showReason = false;
    
    switch(action) {
        case 'activate':
            message = 'هل أنت متأكد من تفعيل هذه البوليصة؟';
            break;
        case 'suspend':
            message = 'هل أنت متأكد من تعليق هذه البوليصة؟';
            showReason = true;
            break;
        case 'cancel':
            message = 'هل أنت متأكد من إلغاء هذه البوليصة؟ لا يمكن التراجع عن هذا الإجراء.';
            showReason = true;
            break;
    }
    
    confirmMessage.textContent = message;
    reasonGroup.style.display = showReason ? 'block' : 'none';
    
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function confirmStatusChange() {
    const policyId = document.getElementById('policyId').value;
    const action = document.getElementById('action').value;
    const reason = document.getElementById('reason').value;
    
    fetch(`/insurance-policies/${policyId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء تحديث حالة البوليصة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تحديث حالة البوليصة');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
}
</script>
@endpush