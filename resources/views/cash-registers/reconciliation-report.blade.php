@extends('layouts.app')

@section('title', 'تقرير تسوية الصناديق النقدية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">تقرير تسوية الصناديق النقدية</h1>
            <p class="text-muted">تقرير شامل لتسوية الصناديق ليوم {{ $date->format('Y-m-d') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i>
                طباعة التقرير
            </button>
            <a href="{{ route('cash-registers.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-1"></i>
                لوحة التحكم
            </a>
            <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i>
                قائمة الصناديق
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cash-registers.reconciliation-report') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date" class="form-label">تاريخ التقرير</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="{{ request('date', $date->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="register_id" class="form-label">صندوق محدد (اختياري)</label>
                    <select class="form-select" id="register_id" name="register_id">
                        <option value="">جميع الصناديق</option>
                        @foreach(\App\Models\CashRegister::orderBy('register_number')->get() as $register)
                            <option value="{{ $register->id }}" {{ request('register_id') == $register->id ? 'selected' : '' }}>
                                {{ $register->register_number }} - {{ $register->register_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        عرض التقرير
                    </button>
                    <a href="{{ route('cash-registers.reconciliation-report') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i>
                        إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي الصناديق
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $report['total_registers'] }}</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $report['active_registers'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                إجمالي الإيرادات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($report['total_revenue'], 2) }} ريال</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                صناديق بفروقات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $report['registers_with_discrepancy'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">تفاصيل التقرير</h5>
            <div class="text-muted">
                <i class="fas fa-calendar me-1"></i>
                {{ $date->format('l, F j, Y') }}
            </div>
        </div>
        <div class="card-body">
            @if(count($report['registers']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الصندوق</th>
                                <th>اسم الصندوق</th>
                                <th>القسم</th>
                                <th>الحالة</th>
                                <th>الرصيد الحالي</th>
                                <th>الرصيد المتوقع</th>
                                <th>الفرق</th>
                                <th>معاملات اليوم</th>
                                <th>إيرادات اليوم</th>
                                <th>يحتاج تسوية</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['registers'] as $registerData)
                                <tr class="{{ $registerData['needs_reconciliation'] ? 'table-warning' : '' }}">
                                    <td>
                                        <a href="{{ route('cash-registers.show', $registerData['id']) }}" class="text-decoration-none">
                                            <strong>{{ $registerData['number'] }}</strong>
                                        </a>
                                    </td>
                                    <td>{{ $registerData['name'] }}</td>
                                    <td>{{ $registerData['department'] }}</td>
                                    <td>
                                        @switch($registerData['status'])
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
                                                <span class="badge bg-light text-dark">{{ $registerData['status'] }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ number_format($registerData['current_balance'], 2) }} ريال</td>
                                    <td>{{ number_format($registerData['expected_balance'], 2) }} ريال</td>
                                    <td>
                                        @if($registerData['difference'] != 0)
                                            <span class="fw-bold text-{{ $registerData['difference'] > 0 ? 'success' : 'danger' }}">
                                                {{ number_format($registerData['difference'], 2) }} ريال
                                            </span>
                                        @else
                                            <span class="text-muted">متوازن</span>
                                        @endif
                                    </td>
                                    <td>{{ $registerData['day_transactions'] }}</td>
                                    <td>{{ number_format($registerData['day_revenue'], 2) }} ريال</td>
                                    <td>
                                        @if($registerData['needs_reconciliation'])
                                            <span class="badge bg-warning">نعم</span>
                                        @else
                                            <span class="badge bg-success">لا</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('cash-registers.show', $registerData['id']) }}" 
                                               class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($registerData['needs_reconciliation'])
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#reconcileModal{{ $registerData['id'] }}" 
                                                        title="تسوية سريعة">
                                                    <i class="fas fa-balance-scale"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Quick Reconcile Modal -->
                                @if($registerData['needs_reconciliation'])
                                <div class="modal fade" id="reconcileModal{{ $registerData['id'] }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('cash-registers.reconcile', $registerData['id']) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تسوية سريعة - {{ $registerData['name'] }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">الرصيد المتوقع</label>
                                                        <input type="text" class="form-control" 
                                                               value="{{ number_format($registerData['expected_balance'], 2) }} ريال" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="actual_balance{{ $registerData['id'] }}" class="form-label">الرصيد الفعلي <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" 
                                                               id="actual_balance{{ $registerData['id'] }}" 
                                                               name="actual_balance" step="0.01" min="0" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="reconciliation_notes{{ $registerData['id'] }}" class="form-label">ملاحظات التسوية</label>
                                                        <textarea class="form-control" 
                                                                  id="reconciliation_notes{{ $registerData['id'] }}" 
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
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="7">الإجماليات</th>
                                <th>{{ $report['total_transactions'] }}</th>
                                <th>{{ number_format($report['total_revenue'], 2) }} ريال</th>
                                <th colspan="2">{{ $report['registers_with_discrepancy'] }} صندوق يحتاج تسوية</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد بيانات للتاريخ المحدد</h5>
                    <p class="text-muted">لم يتم العثور على صناديق نقدية أو معاملات للتاريخ {{ $date->format('Y-m-d') }}</p>
                    <a href="{{ route('cash-registers.reconciliation-report') }}" class="btn btn-primary">
                        <i class="fas fa-calendar me-1"></i>
                        اختيار تاريخ آخر
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Report Footer -->
    <div class="card mt-4 d-print-block">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>معلومات التقرير</h6>
                    <ul class="list-unstyled">
                        <li><strong>تاريخ التقرير:</strong> {{ $date->format('Y-m-d') }}</li>
                        <li><strong>وقت الإنشاء:</strong> {{ now()->format('Y-m-d H:i:s') }}</li>
                        <li><strong>المستخدم:</strong> {{ auth()->user()->name }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>ملخص الحالة</h6>
                    <ul class="list-unstyled">
                        <li><strong>الصناديق المتوازنة:</strong> {{ $report['total_registers'] - $report['registers_with_discrepancy'] }}</li>
                        <li><strong>الصناديق بفروقات:</strong> {{ $report['registers_with_discrepancy'] }}</li>
                        <li><strong>نسبة التوازن:</strong> 
                            @if($report['total_registers'] > 0)
                                {{ number_format((($report['total_registers'] - $report['registers_with_discrepancy']) / $report['total_registers']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn, .card-header .text-muted, .d-print-none {
        display: none !important;
    }
    
    .d-print-block {
        display: block !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    .badge {
        border: 1px solid #000;
        color: #000 !important;
        background-color: transparent !important;
    }
}
</style>
@endpush

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
            console.log('Difference for register ' + registerId + ':', difference);
        });
    });

    // Print functionality
    window.addEventListener('beforeprint', function() {
        document.title = 'تقرير تسوية الصناديق النقدية - {{ $date->format("Y-m-d") }}';
    });
});
</script>
@endpush