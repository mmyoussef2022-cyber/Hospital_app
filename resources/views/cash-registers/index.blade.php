@extends('layouts.app')

@section('title', 'إدارة الصناديق النقدية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">إدارة الصناديق النقدية</h1>
            <p class="text-muted">إدارة ومراقبة الصناديق النقدية في المستشفى</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cash-registers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                إضافة صندوق جديد
            </a>
            <a href="{{ route('cash-registers.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-tachometer-alt me-1"></i>
                لوحة التحكم
            </a>
            <a href="{{ route('cash-registers.reconciliation-report') }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-alt me-1"></i>
                تقرير التسوية
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي الصناديق
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_registers'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                الصناديق النشطة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_registers'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                تحتاج تسوية
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['registers_needing_reconciliation'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                إجمالي الرصيد
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_balance'], 2) }} ريال</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cash-registers.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">الحالة</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">جميع الحالات</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>صيانة</option>
                            <option value="reconciling" {{ request('status') == 'reconciling' ? 'selected' : '' }}>قيد التسوية</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="department_id" class="form-label">القسم</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">جميع الأقسام</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="needs_reconciliation" class="form-label">تحتاج تسوية</label>
                        <select class="form-select" id="needs_reconciliation" name="needs_reconciliation">
                            <option value="">الكل</option>
                            <option value="1" {{ request('needs_reconciliation') == '1' ? 'selected' : '' }}>نعم</option>
                            <option value="0" {{ request('needs_reconciliation') == '0' ? 'selected' : '' }}>لا</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="search" class="form-label">البحث</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="رقم الصندوق، الاسم، أو الموقع">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            بحث
                        </button>
                        <a href="{{ route('cash-registers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo me-1"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cash Registers Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">قائمة الصناديق النقدية</h5>
        </div>
        <div class="card-body">
            @if($registers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>رقم الصندوق</th>
                                <th>اسم الصندوق</th>
                                <th>القسم</th>
                                <th>الموقع</th>
                                <th>الحالة</th>
                                <th>الرصيد الحالي</th>
                                <th>فرق التسوية</th>
                                <th>آخر تسوية</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registers as $register)
                                <tr>
                                    <td>
                                        <a href="{{ route('cash-registers.show', $register) }}" class="text-decoration-none">
                                            <strong>{{ $register->register_number }}</strong>
                                        </a>
                                    </td>
                                    <td>{{ $register->register_name }}</td>
                                    <td>{{ $register->department->name }}</td>
                                    <td>{{ $register->location ?? 'غير محدد' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $register->status_color }}">
                                            {{ $register->status_display }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($register->current_balance, 2) }} ريال</td>
                                    <td>
                                        @if($register->reconciliation_difference != 0)
                                            <span class="text-{{ $register->reconciliation_difference > 0 ? 'success' : 'danger' }}">
                                                {{ number_format($register->reconciliation_difference, 2) }} ريال
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($register->last_reconciled_at)
                                            {{ $register->last_reconciled_at->format('Y-m-d H:i') }}
                                        @else
                                            <span class="text-muted">لم يتم</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('cash-registers.show', $register) }}" 
                                               class="btn btn-sm btn-outline-primary" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('cash-registers.edit', $register) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($register->status === 'active')
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#reconcileModal{{ $register->id }}" 
                                                        title="تسوية">
                                                    <i class="fas fa-balance-scale"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Reconcile Modal -->
                                <div class="modal fade" id="reconcileModal{{ $register->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('cash-registers.reconcile', $register) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تسوية الصندوق - {{ $register->register_name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">الرصيد المتوقع</label>
                                                        <input type="text" class="form-control" 
                                                               value="{{ number_format($register->expected_balance, 2) }} ريال" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="actual_balance{{ $register->id }}" class="form-label">الرصيد الفعلي <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" 
                                                               id="actual_balance{{ $register->id }}" 
                                                               name="actual_balance" step="0.01" min="0" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="reconciliation_notes{{ $register->id }}" class="form-label">ملاحظات التسوية</label>
                                                        <textarea class="form-control" 
                                                                  id="reconciliation_notes{{ $register->id }}" 
                                                                  name="reconciliation_notes" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn btn-warning">تسوية الصندوق</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $registers->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-cash-register fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد صناديق نقدية</h5>
                    <p class="text-muted">لم يتم العثور على صناديق تطابق معايير البحث</p>
                    <a href="{{ route('cash-registers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة صندوق جديد
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate difference when actual balance is entered
    document.querySelectorAll('[id^="actual_balance"]').forEach(function(input) {
        input.addEventListener('input', function() {
            const registerId = this.id.replace('actual_balance', '');
            const expectedBalance = parseFloat(this.closest('.modal-content').querySelector('input[readonly]').value.replace(/[^\d.-]/g, ''));
            const actualBalance = parseFloat(this.value) || 0;
            const difference = actualBalance - expectedBalance;
            
            // You could add a difference display here if needed
            console.log('Difference:', difference);
        });
    });
});
</script>
@endpush