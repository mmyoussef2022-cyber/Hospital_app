@extends('layouts.app')

@section('title', 'تفاصيل الوردية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">تفاصيل الوردية #{{ $shift->shift_number }}</h1>
            <p class="text-muted">{{ $shift->shift_date->format('Y-m-d') }} - {{ $shift->shift_type_display }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($shift->can_start)
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#startShiftModal">
                    <i class="fas fa-play me-1"></i>
                    بدء الوردية
                </button>
            @endif
            
            @if($shift->can_end)
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#endShiftModal">
                    <i class="fas fa-stop me-1"></i>
                    إنهاء الوردية
                </button>
            @endif
            
            @if(in_array($shift->status, ['scheduled', 'active']))
                <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>
                    تعديل
                </a>
            @endif
            
            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Shift Information -->
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">معلومات الوردية</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">الموظف</label>
                            <div class="fw-bold">{{ $shift->user->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">القسم</label>
                            <div class="fw-bold">{{ $shift->department->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">نوع الوردية</label>
                            <div class="fw-bold">{{ $shift->shift_type_display }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">الحالة</label>
                            <div>
                                <span class="badge bg-{{ $shift->status_color }}">{{ $shift->status_display }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">الوقت المجدول</label>
                            <div class="fw-bold">{{ $shift->scheduled_start }} - {{ $shift->scheduled_end }}</div>
                        </div>
                        @if($shift->actual_start || $shift->actual_end)
                        <div class="col-md-6">
                            <label class="form-label text-muted">الوقت الفعلي</label>
                            <div class="fw-bold">
                                {{ $shift->actual_start ?? 'لم تبدأ' }} - {{ $shift->actual_end ?? 'لم تنته' }}
                            </div>
                        </div>
                        @endif
                        @if($shift->supervisor)
                        <div class="col-md-6">
                            <label class="form-label text-muted">المشرف</label>
                            <div class="fw-bold">{{ $shift->supervisor->name }}</div>
                        </div>
                        @endif
                        @if($shift->cashRegister)
                        <div class="col-md-6">
                            <label class="form-label text-muted">الصندوق</label>
                            <div class="fw-bold">{{ $shift->cashRegister->name }} - {{ $shift->cashRegister->location }}</div>
                        </div>
                        @endif
                    </div>
                    
                    @if($shift->shift_notes)
                    <div class="mt-3">
                        <label class="form-label text-muted">ملاحظات الوردية</label>
                        <div class="border rounded p-2 bg-light">{{ $shift->shift_notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Financial Summary -->
            @if($shift->status !== 'scheduled')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">الملخص المالي</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary mb-0">{{ number_format($shift->opening_balance ?? 0, 2) }}</div>
                                <small class="text-muted">الرصيد الافتتاحي</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success mb-0">{{ number_format($shift->total_revenue ?? 0, 2) }}</div>
                                <small class="text-muted">إجمالي الإيرادات</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info mb-0">{{ number_format($shift->closing_balance ?? 0, 2) }}</div>
                                <small class="text-muted">الرصيد الختامي</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 {{ $shift->cash_difference >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    {{ number_format($shift->cash_difference ?? 0, 2) }}
                                </div>
                                <small class="text-muted">الفرق النقدي</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Transactions -->
            @if($shift->transactions->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">المعاملات</h5>
                    <span class="badge bg-primary">{{ $shift->transactions->count() }} معاملة</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>الوقت</th>
                                    <th>المريض</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>طريقة الدفع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shift->transactions->take(10) as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('H:i') }}</td>
                                    <td>{{ $transaction->patient->name ?? 'غير محدد' }}</td>
                                    <td>{{ $transaction->transaction_type_display }}</td>
                                    <td>{{ number_format($transaction->amount, 2) }} ريال</td>
                                    <td>{{ $transaction->payment_method_display }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($shift->transactions->count() > 10)
                    <div class="text-center mt-2">
                        <small class="text-muted">عرض 10 من أصل {{ $shift->transactions->count() }} معاملة</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">حالة الوردية</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge bg-{{ $shift->status_color }} fs-6 px-3 py-2">{{ $shift->status_display }}</span>
                    </div>
                    
                    @if($shift->status === 'active')
                    <div class="mb-3">
                        <div class="text-muted small">مدة الوردية</div>
                        <div class="fw-bold" id="shift-duration">
                            {{ $shift->actual_start ? \Carbon\Carbon::parse($shift->actual_start)->diffForHumans() : 'لم تبدأ' }}
                        </div>
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        @if($shift->can_start)
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#startShiftModal">
                                بدء الوردية
                            </button>
                        @endif
                        
                        @if($shift->can_end)
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#endShiftModal">
                                إنهاء الوردية
                            </button>
                        @endif
                        
                        @if($shift->status === 'scheduled')
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelShiftModal">
                                إلغاء الوردية
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            @if($shift->status !== 'scheduled')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">إحصائيات</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="fw-bold text-primary">{{ $shift->total_transactions ?? 0 }}</div>
                                <small class="text-muted">المعاملات</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="fw-bold text-success">{{ $shift->patients_served ?? 0 }}</div>
                                <small class="text-muted">المرضى</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($shift->status === 'active')
                            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#verifyCashModal">
                                <i class="fas fa-calculator me-1"></i>
                                التحقق من النقدية
                            </button>
                        @endif
                        
                        <a href="{{ route('shifts.calendar') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar me-1"></i>
                            عرض التقويم
                        </a>
                        
                        @if($shift->report)
                            <button type="button" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-file-alt me-1"></i>
                                عرض التقرير
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start Shift Modal -->
@if($shift->can_start)
<div class="modal fade" id="startShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('shifts.start', $shift) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">بدء الوردية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="opening_balance" class="form-label">الرصيد الافتتاحي <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="opening_balance" name="opening_balance" 
                               step="0.01" min="0" required>
                        <div class="form-text">أدخل الرصيد النقدي في بداية الوردية</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">بدء الوردية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- End Shift Modal -->
@if($shift->can_end)
<div class="modal fade" id="endShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('shifts.end', $shift) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إنهاء الوردية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="closing_balance" class="form-label">الرصيد الختامي <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="closing_balance" name="closing_balance" 
                               step="0.01" min="0" required>
                        <div class="form-text">أدخل الرصيد النقدي في نهاية الوردية</div>
                    </div>
                    <div class="mb-3">
                        <label for="end_shift_notes" class="form-label">ملاحظات الإغلاق</label>
                        <textarea class="form-control" id="end_shift_notes" name="shift_notes" rows="3"
                                  placeholder="أدخل أي ملاحظات حول إنهاء الوردية"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">إنهاء الوردية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Cancel Shift Modal -->
@if($shift->status === 'scheduled')
<div class="modal fade" id="cancelShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إلغاء الوردية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">سبب الإلغاء <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancel_reason" name="reason" rows="3" required
                                  placeholder="أدخل سبب إلغاء الوردية"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">إلغاء الوردية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Verify Cash Modal -->
@if($shift->status === 'active')
<div class="modal fade" id="verifyCashModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('shifts.verify-cash', $shift) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">التحقق من النقدية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="actual_balance" class="form-label">الرصيد الفعلي <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="actual_balance" name="actual_balance" 
                               step="0.01" min="0" required>
                        <div class="form-text">أدخل الرصيد النقدي الفعلي الموجود في الصندوق</div>
                    </div>
                    <div class="alert alert-info">
                        <strong>الرصيد المتوقع:</strong> {{ number_format(($shift->opening_balance ?? 0) + ($shift->total_revenue ?? 0), 2) }} ريال
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-info">تأكيد التحقق</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update shift duration for active shifts
    @if($shift->status === 'active' && $shift->actual_start)
    function updateDuration() {
        const startTime = new Date('{{ $shift->shift_date->format("Y-m-d") }}T{{ $shift->actual_start }}');
        const now = new Date();
        const diff = now - startTime;
        
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById('shift-duration').textContent = `${hours} ساعة و ${minutes} دقيقة`;
    }
    
    // Update every minute
    updateDuration();
    setInterval(updateDuration, 60000);
    @endif
});
</script>
@endpush