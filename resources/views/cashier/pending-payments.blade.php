@extends('layouts.app')

@section('title', 'المدفوعات المعلقة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-hourglass-half text-warning me-2"></i>
                        المدفوعات المعلقة
                    </h1>
                    <p class="text-muted mb-0">إدارة ومراجعة المدفوعات التي تحتاج موافقة</p>
                </div>
                <div>
                    <a href="{{ route('cashier.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $pendingPayments->total() }}</h4>
                            <p class="mb-0">إجمالي المعلقة</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
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
                            <h4>{{ number_format($pendingPayments->sum('amount'), 0) }}</h4>
                            <p class="mb-0">إجمالي المبلغ (ر.س)</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill fa-2x"></i>
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
                            <h4>{{ $pendingPayments->where('payment_method', 'bank_transfer')->count() }}</h4>
                            <p class="mb-0">تحويلات بنكية</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-university fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $pendingPayments->where('payment_method', 'insurance')->count() }}</h4>
                            <p class="mb-0">مطالبات تأمين</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أدوات التصفية -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">طريقة الدفع</label>
                            <select name="payment_method" class="form-select">
                                <option value="">جميع الطرق</option>
                                <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقدي</option>
                                <option value="visa" {{ request('payment_method') == 'visa' ? 'selected' : '' }}>فيزا</option>
                                <option value="mastercard" {{ request('payment_method') == 'mastercard' ? 'selected' : '' }}>ماستركارد</option>
                                <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                                <option value="insurance" {{ request('payment_method') == 'insurance' ? 'selected' : '' }}>تأمين</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    بحث
                                </button>
                                <a href="{{ route('cashier.pending-payments') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh me-1"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول المدفوعات المعلقة -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">قائمة المدفوعات المعلقة</h6>
                    <div>
                        <button class="btn btn-success btn-sm" onclick="bulkApprove()">
                            <i class="fas fa-check me-1"></i>
                            موافقة جماعية
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="bulkReject()">
                            <i class="fas fa-times me-1"></i>
                            رفض جماعي
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th>رقم الدفع</th>
                                        <th>المريض</th>
                                        <th>نوع المريض</th>
                                        <th>رقم الفاتورة</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>التاريخ والوقت</th>
                                        <th>معالج بواسطة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingPayments as $payment)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="payment-checkbox" value="{{ $payment->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">#{{ $payment->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial bg-primary rounded-circle">
                                                        {{ substr($payment->invoice->patient->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $payment->invoice->patient->name }}</div>
                                                    <small class="text-muted">{{ $payment->invoice->patient->phone }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($payment->invoice->patient->insurancePolicy)
                                                <span class="badge bg-info">مؤمن</span>
                                                <small class="d-block text-muted">{{ $payment->invoice->patient->insurancePolicy->company->name }}</small>
                                            @else
                                                <span class="badge bg-success">نقدي</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('invoices.show', $payment->invoice->id) }}" class="text-decoration-none">
                                                #{{ $payment->invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ number_format($payment->amount, 2) }} ر.س</span>
                                        </td>
                                        <td>
                                            @switch($payment->payment_method)
                                                @case('cash')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-money-bill me-1"></i>
                                                        نقدي
                                                    </span>
                                                    @break
                                                @case('visa')
                                                    <span class="badge bg-primary">
                                                        <i class="fab fa-cc-visa me-1"></i>
                                                        فيزا
                                                    </span>
                                                    @break
                                                @case('mastercard')
                                                    <span class="badge bg-warning">
                                                        <i class="fab fa-cc-mastercard me-1"></i>
                                                        ماستركارد
                                                    </span>
                                                    @break
                                                @case('bank_transfer')
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-university me-1"></i>
                                                        تحويل بنكي
                                                    </span>
                                                    @break
                                                @case('insurance')
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-shield-alt me-1"></i>
                                                        تأمين
                                                    </span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <div>{{ $payment->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-1">
                                                    <span class="avatar-initial bg-secondary rounded-circle">
                                                        {{ substr($payment->processedBy->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <small>{{ $payment->processedBy->name }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>
                                                معلق
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-success" onclick="approvePayment({{ $payment->id }})" title="موافقة">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="rejectPayment({{ $payment->id }})" title="رفض">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info" onclick="viewPaymentDetails({{ $payment->id }})" title="التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $pendingPayments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>لا توجد مدفوعات معلقة</h4>
                            <p class="text-muted">جميع المدفوعات تم معالجتها بنجاح</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal تفاصيل الدفع -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الدفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- المحتوى سيتم تحميله ديناميكياً -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-success" id="approveFromModal">موافقة</button>
                <button type="button" class="btn btn-danger" id="rejectFromModal">رفض</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// تحديد/إلغاء تحديد الكل
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.payment-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// الحصول على المدفوعات المحددة
function getSelectedPayments() {
    const checkboxes = document.querySelectorAll('.payment-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// موافقة جماعية
function bulkApprove() {
    const selectedPayments = getSelectedPayments();
    
    if (selectedPayments.length === 0) {
        alert('يرجى تحديد مدفوعات للموافقة عليها');
        return;
    }
    
    if (confirm(`هل أنت متأكد من الموافقة على ${selectedPayments.length} مدفوعة؟`)) {
        // إرسال طلب الموافقة الجماعية
        fetch('{{ route("cashier.bulk-approve") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payment_ids: selectedPayments
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم الموافقة على المدفوعات بنجاح');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في معالجة الطلب');
        });
    }
}

// رفض جماعي
function bulkReject() {
    const selectedPayments = getSelectedPayments();
    
    if (selectedPayments.length === 0) {
        alert('يرجى تحديد مدفوعات لرفضها');
        return;
    }
    
    const reason = prompt('يرجى إدخال سبب الرفض:');
    if (!reason) return;
    
    if (confirm(`هل أنت متأكد من رفض ${selectedPayments.length} مدفوعة؟`)) {
        // إرسال طلب الرفض الجماعي
        fetch('{{ route("cashier.bulk-reject") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payment_ids: selectedPayments,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم رفض المدفوعات بنجاح');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في معالجة الطلب');
        });
    }
}

// موافقة على دفعة واحدة
function approvePayment(paymentId) {
    if (confirm('هل أنت متأكد من الموافقة على هذا الدفع؟')) {
        fetch(`{{ route("cashier.approve-payment", ":id") }}`.replace(':id', paymentId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم الموافقة على الدفع بنجاح');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في معالجة الطلب');
        });
    }
}

// رفض دفعة واحدة
function rejectPayment(paymentId) {
    const reason = prompt('يرجى إدخال سبب الرفض:');
    if (!reason) return;
    
    if (confirm('هل أنت متأكد من رفض هذا الدفع؟')) {
        fetch(`{{ route("cashier.reject-payment", ":id") }}`.replace(':id', paymentId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم رفض الدفع بنجاح');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في معالجة الطلب');
        });
    }
}

// عرض تفاصيل الدفع
function viewPaymentDetails(paymentId) {
    fetch(`{{ route("cashier.payment-details", ":id") }}`.replace(':id', paymentId))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('paymentDetailsContent').innerHTML = data.html;
                $('#paymentDetailsModal').modal('show');
                
                // ربط الأزرار بالدفعة
                document.getElementById('approveFromModal').onclick = () => {
                    $('#paymentDetailsModal').modal('hide');
                    approvePayment(paymentId);
                };
                
                document.getElementById('rejectFromModal').onclick = () => {
                    $('#paymentDetailsModal').modal('hide');
                    rejectPayment(paymentId);
                };
            } else {
                alert('خطأ في تحميل التفاصيل');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل التفاصيل');
        });
}
</script>
@endsection