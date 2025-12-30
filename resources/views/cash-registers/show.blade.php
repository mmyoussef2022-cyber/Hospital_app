@extends('layouts.app')

@section('title', 'تفاصيل الصندوق النقدي')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">تفاصيل الصندوق #{{ $cashRegister->register_number }}</h1>
            <p class="text-muted">{{ $cashRegister->register_name }} - {{ $cashRegister->department->name }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($cashRegister->status === 'inactive')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#activateModal">
                    <i class="fas fa-play me-1"></i>
                    تفعيل الصندوق
                </button>
            @endif
            
            @if($cashRegister->status === 'active')
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reconcileModal">
                    <i class="fas fa-balance-scale me-1"></i>
                    تسوية الصندوق
                </button>
                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#adjustModal">
                    <i class="fas fa-edit me-1"></i>
                    تعديل الرصيد
                </button>
            @endif
            
            <a href="{{ route('cash-registers.edit', $cashRegister) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>
                تعديل
            </a>
            
            <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Cash Register Information -->
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">معلومات الصندوق</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">رقم الصندوق</label>
                            <div class="fw-bold">{{ $cashRegister->register_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">اسم الصندوق</label>
                            <div class="fw-bold">{{ $cashRegister->register_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">القسم</label>
                            <div class="fw-bold">{{ $cashRegister->department->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">الموقع</label>
                            <div class="fw-bold">{{ $cashRegister->location ?? 'غير محدد' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">الحالة</label>
                            <div>
                                <span class="badge bg-{{ $cashRegister->status_color }} fs-6 px-3 py-2">
                                    {{ $cashRegister->status_display }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">تاريخ الإنشاء</label>
                            <div class="fw-bold">{{ $cashRegister->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">الملخص المالي</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary mb-0">{{ number_format($cashRegister->opening_balance, 2) }}</div>
                                <small class="text-muted">الرصيد الافتتاحي</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success mb-0">{{ number_format($cashRegister->current_balance, 2) }}</div>
                                <small class="text-muted">الرصيد الحالي</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info mb-0">{{ number_format($cashRegister->expected_balance, 2) }}</div>
                                <small class="text-muted">الرصيد المتوقع</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 {{ $cashRegister->reconciliation_difference >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    {{ number_format($cashRegister->reconciliation_difference, 2) }}
                                </div>
                                <small class="text-muted">فرق التسوية</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">إحصائيات اليوم</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h5 text-primary mb-0">{{ $todayStats['transactions_count'] }}</div>
                                <small class="text-muted">عدد المعاملات</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h5 text-success mb-0">{{ number_format($todayStats['revenue'], 2) }}</div>
                                <small class="text-muted">إجمالي الإيرادات</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                @if($todayStats['current_shift'])
                                    <div class="h6 text-info mb-0">{{ $todayStats['current_shift']->user->name }}</div>
                                    <small class="text-muted">الوردية الحالية</small>
                                @else
                                    <div class="h6 text-muted mb-0">لا توجد وردية</div>
                                    <small class="text-muted">غير نشط</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Shifts -->
            @if($cashRegister->shifts->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">الورديات الأخيرة</h5>
                    <a href="{{ route('shifts.index', ['cash_register_id' => $cashRegister->id]) }}" class="btn btn-sm btn-outline-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>رقم الوردية</th>
                                    <th>الموظف</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>الإيرادات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cashRegister->shifts->take(5) as $shift)
                                <tr>
                                    <td>{{ $shift->shift_number }}</td>
                                    <td>{{ $shift->user->name }}</td>
                                    <td>{{ $shift->shift_date->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $shift->status_color }}">{{ $shift->status_display }}</span>
                                    </td>
                                    <td>{{ number_format($shift->total_revenue ?? 0, 2) }} ريال</td>
                                    <td>
                                        <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Transactions -->
            @if($cashRegister->transactions->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">المعاملات الأخيرة</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>الوقت</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>الوصف</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cashRegister->transactions->take(10) as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('H:i') }}</td>
                                    <td>{{ $transaction->transaction_type_display }}</td>
                                    <td>{{ number_format($transaction->amount, 2) }} ريال</td>
                                    <td>{{ $transaction->description ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">حالة الصندوق</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge bg-{{ $cashRegister->status_color }} fs-6 px-3 py-2">
                            {{ $cashRegister->status_display }}
                        </span>
                    </div>
                    
                    @if($cashRegister->last_reconciled_at)
                    <div class="mb-3">
                        <div class="text-muted small">آخر تسوية</div>
                        <div class="fw-bold">{{ $cashRegister->last_reconciled_at->format('Y-m-d H:i') }}</div>
                        @if($cashRegister->lastReconciledBy)
                            <div class="small text-muted">بواسطة: {{ $cashRegister->lastReconciledBy->name }}</div>
                        @endif
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        @if($cashRegister->status === 'active')
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reconcileModal">
                                تسوية الصندوق
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal">
                                تعديل الرصيد
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                                وضع الصيانة
                            </button>
                        @elseif($cashRegister->status === 'inactive')
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#activateModal">
                                تفعيل الصندوق
                            </button>
                        @elseif($cashRegister->status === 'maintenance')
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#activateModal">
                                إنهاء الصيانة
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('shifts.create', ['cash_register_id' => $cashRegister->id]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            إنشاء وردية جديدة
                        </a>
                        <a href="{{ route('cash-registers.reconciliation-report', ['register_id' => $cashRegister->id]) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-alt me-1"></i>
                            تقرير التسوية
                        </a>
                        <a href="{{ route('cash-registers.dashboard') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            لوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reconcile Modal -->
<div class="modal fade" id="reconcileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cash-registers.reconcile', $cashRegister) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">تسوية الصندوق</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الرصيد المتوقع</label>
                        <input type="text" class="form-control" 
                               value="{{ number_format($cashRegister->expected_balance, 2) }} ريال" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="actual_balance" class="form-label">الرصيد الفعلي <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="actual_balance" name="actual_balance" 
                               step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="reconciliation_notes" class="form-label">ملاحظات التسوية</label>
                        <textarea class="form-control" id="reconciliation_notes" name="reconciliation_notes" rows="3"></textarea>
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

<!-- Adjust Balance Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cash-registers.adjust', $cashRegister) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">تعديل رصيد الصندوق</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الرصيد الحالي</label>
                        <input type="text" class="form-control" 
                               value="{{ number_format($cashRegister->current_balance, 2) }} ريال" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="adjustment_amount" class="form-label">مبلغ التعديل <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="adjustment_amount" name="adjustment_amount" 
                               step="0.01" required>
                        <div class="form-text">استخدم قيمة موجبة للإضافة وسالبة للخصم</div>
                    </div>
                    <div class="mb-3">
                        <label for="adjustment_reason" class="form-label">سبب التعديل <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="adjustment_reason" name="adjustment_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-info">تعديل الرصيد</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Activate Modal -->
<div class="modal fade" id="activateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cash-registers.activate', $cashRegister) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">تفعيل الصندوق</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من تفعيل هذا الصندوق؟</p>
                    <p class="text-muted">سيصبح الصندوق متاحاً للاستخدام في الورديات والمعاملات.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تفعيل الصندوق</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cash-registers.maintenance', $cashRegister) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">وضع الصيانة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="maintenance_reason" class="form-label">سبب الصيانة</label>
                        <textarea class="form-control" id="maintenance_reason" name="maintenance_reason" rows="3"></textarea>
                    </div>
                    <p class="text-warning">سيصبح الصندوق غير متاح للاستخدام أثناء فترة الصيانة.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">وضع الصيانة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection