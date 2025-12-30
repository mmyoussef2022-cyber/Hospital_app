@extends('layouts.app')

@section('title', 'لوحة تحكم الصناديق النقدية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">لوحة تحكم الصناديق النقدية</h1>
            <p class="text-muted">مراقبة شاملة لجميع الصناديق النقدية في المستشفى</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cash-registers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                إضافة صندوق جديد
            </a>
            <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i>
                قائمة الصناديق
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

    <div class="row">
        <!-- Registers by Status -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">الصناديق حسب الحالة</h5>
                </div>
                <div class="card-body">
                    @if($registersByStatus->count() > 0)
                        @foreach($registersByStatus as $status => $registers)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">
                                        @switch($status)
                                            @case('active')
                                                <span class="badge bg-success">نشط</span>
                                                @break
                                            @case('inactive')
                                                <span class="badge bg-secondary">غير نشط</span>
                                                @break
                                            @case('maintenance')
                                                <span class="badge bg-warning">صيانة</span>
                                                @break
                                            @case('reconciling')
                                                <span class="badge bg-info">قيد التسوية</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $status }}</span>
                                        @endswitch
                                    </h6>
                                    <span class="text-muted">{{ $registers->count() }} صندوق</span>
                                </div>
                                <div class="list-group list-group-flush">
                                    @foreach($registers->take(5) as $register)
                                        <div class="list-group-item px-0 py-2 border-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <a href="{{ route('cash-registers.show', $register) }}" class="text-decoration-none">
                                                        <strong>{{ $register->register_number }}</strong>
                                                    </a>
                                                    <div class="small text-muted">{{ $register->register_name }} - {{ $register->department->name }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold">{{ number_format($register->current_balance, 2) }} ريال</div>
                                                    @if($register->reconciliation_difference != 0)
                                                        <div class="small text-{{ $register->reconciliation_difference > 0 ? 'success' : 'danger' }}">
                                                            فرق: {{ number_format($register->reconciliation_difference, 2) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($registers->count() > 5)
                                        <div class="text-center py-2">
                                            <a href="{{ route('cash-registers.index', ['status' => $status]) }}" class="btn btn-sm btn-outline-primary">
                                                عرض الكل ({{ $registers->count() }})
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-cash-register fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">لا توجد صناديق نقدية</h6>
                            <a href="{{ route('cash-registers.create') }}" class="btn btn-primary btn-sm">
                                إضافة صندوق جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Reconciliations -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">التسويات الأخيرة</h5>
                    <a href="{{ route('cash-registers.reconciliation-report') }}" class="btn btn-sm btn-outline-primary">
                        عرض التقرير
                    </a>
                </div>
                <div class="card-body">
                    @if($recentReconciliations->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentReconciliations as $register)
                                <div class="list-group-item px-0 py-3 border-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <a href="{{ route('cash-registers.show', $register) }}" class="text-decoration-none">
                                                <strong>{{ $register->register_number }}</strong>
                                            </a>
                                            <div class="small text-muted">{{ $register->register_name }}</div>
                                            <div class="small text-muted">{{ $register->department->name }}</div>
                                            @if($register->lastReconciledBy)
                                                <div class="small text-muted">بواسطة: {{ $register->lastReconciledBy->name }}</div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">{{ $register->last_reconciled_at->format('Y-m-d H:i') }}</div>
                                            @if($register->reconciliation_difference != 0)
                                                <div class="small text-{{ $register->reconciliation_difference > 0 ? 'success' : 'danger' }}">
                                                    فرق: {{ number_format($register->reconciliation_difference, 2) }} ريال
                                                </div>
                                            @else
                                                <div class="small text-success">متوازن</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-balance-scale fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">لا توجد تسويات حديثة</h6>
                            <p class="text-muted small">لم يتم إجراء أي تسويات مؤخراً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Discrepancies Alert -->
    @if($discrepancies->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        صناديق تحتاج انتباه - فروقات في التسوية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الصندوق</th>
                                    <th>اسم الصندوق</th>
                                    <th>القسم</th>
                                    <th>الرصيد الحالي</th>
                                    <th>الرصيد المتوقع</th>
                                    <th>الفرق</th>
                                    <th>آخر تسوية</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($discrepancies as $register)
                                    <tr>
                                        <td>
                                            <a href="{{ route('cash-registers.show', $register) }}" class="text-decoration-none">
                                                <strong>{{ $register->register_number }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $register->register_name }}</td>
                                        <td>{{ $register->department->name }}</td>
                                        <td>{{ number_format($register->current_balance, 2) }} ريال</td>
                                        <td>{{ number_format($register->expected_balance, 2) }} ريال</td>
                                        <td>
                                            <span class="fw-bold text-{{ $register->reconciliation_difference > 0 ? 'success' : 'danger' }}">
                                                {{ number_format($register->reconciliation_difference, 2) }} ريال
                                            </span>
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
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#reconcileModal{{ $register->id }}" 
                                                        title="تسوية">
                                                    <i class="fas fa-balance-scale"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Quick Reconcile Modal -->
                                    <div class="modal fade" id="reconcileModal{{ $register->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('cash-registers.reconcile', $register) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تسوية سريعة - {{ $register->register_name }}</h5>
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
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 300000); // 5 minutes

    // Real-time balance calculation in reconcile modals
    document.querySelectorAll('[id^="actual_balance"]').forEach(function(input) {
        input.addEventListener('input', function() {
            const registerId = this.id.replace('actual_balance', '');
            const expectedBalance = parseFloat(this.closest('.modal-content').querySelector('input[readonly]').value.replace(/[^\d.-]/g, ''));
            const actualBalance = parseFloat(this.value) || 0;
            const difference = actualBalance - expectedBalance;
            
            // You could add a difference display here if needed
            console.log('Difference for register ' + registerId + ':', difference);
        });
    });
});
</script>
@endpush