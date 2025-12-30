@extends('layouts.app')

@section('title', 'لوحة الخزينة المتقدمة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-cash-register text-primary me-2"></i>
                        لوحة الخزينة المتقدمة
                    </h1>
                    <p class="text-muted mb-0">إدارة شاملة لجميع المعاملات المالية وطرق الدفع</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-clock me-1"></i>
                        {{ now()->format('H:i') }} - {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- إحصائيات اليوم -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي المدفوعات اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($todayStats['total_payments'], 2) }} ر.س
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                المدفوعات النقدية
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($todayStats['cash_payments'], 2) }} ر.س
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                مدفوعات البطاقات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($todayStats['card_payments'], 2) }} ر.س
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                المعاملات المعلقة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $todayStats['pending_count'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الأدوات السريعة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools me-2"></i>
                        الأدوات السريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-primary btn-block" onclick="openPaymentModal()">
                                <i class="fas fa-plus me-2"></i>
                                معالجة دفع جديد
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-success btn-block" onclick="openCashPaymentModal()">
                                <i class="fas fa-money-bill me-2"></i>
                                دفع نقدي سريع
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-info btn-block" onclick="searchPatient()">
                                <i class="fas fa-search me-2"></i>
                                البحث عن مريض
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-warning btn-block" onclick="viewPendingPayments()">
                                <i class="fas fa-clock me-2"></i>
                                المدفوعات المعلقة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="row">
        <!-- المدفوعات المعلقة -->
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">المدفوعات المعلقة</h6>
                    <a href="{{ route('cashier.pending-payments') }}" class="btn btn-sm btn-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($pendingPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>المريض</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingPayments as $payment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial bg-primary rounded-circle">
                                                        {{ substr($payment->invoice->patient->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $payment->invoice->patient->name }}</div>
                                                    <small class="text-muted">{{ $payment->invoice->patient->national_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ number_format($payment->amount, 2) }} ر.س</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $payment->payment_method === 'cash' ? 'success' : 'info' }}">
                                                {{ $payment->payment_method === 'cash' ? 'نقدي' : 'بطاقة' }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->created_at->format('H:i') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="approvePayment({{ $payment->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectPayment({{ $payment->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">لا توجد مدفوعات معلقة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- إحصائيات طرق الدفع -->
        <div class="col-xl-6 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">توزيع طرق الدفع اليوم</h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- الفواتير المتأخرة -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">الفواتير المتأخرة</h6>
                    <a href="{{ route('cashier.overdue-invoices') }}" class="btn btn-sm btn-danger">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($overdueInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>رقم الفاتورة</th>
                                        <th>المريض</th>
                                        <th>نوع المريض</th>
                                        <th>المبلغ</th>
                                        <th>تاريخ الاستحقاق</th>
                                        <th>الأيام المتأخرة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueInvoices as $invoice)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">#{{ $invoice->invoice_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial bg-danger rounded-circle">
                                                        {{ substr($invoice->patient->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $invoice->patient->name }}</div>
                                                    <small class="text-muted">{{ $invoice->patient->phone }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($invoice->patient->insurancePolicy)
                                                <span class="badge bg-info">مؤمن</span>
                                                <small class="d-block text-muted">{{ $invoice->patient->insurancePolicy->company->name }}</small>
                                            @else
                                                <span class="badge bg-success">نقدي</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-danger">{{ number_format($invoice->total_amount, 2) }} ر.س</span>
                                        </td>
                                        <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $invoice->due_date->diffInDays(now()) }} يوم
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="processInvoicePayment({{ $invoice->id }})">
                                                <i class="fas fa-credit-card me-1"></i>
                                                دفع
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="sendReminder({{ $invoice->id }})">
                                                <i class="fas fa-bell me-1"></i>
                                                تذكير
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">لا توجد فواتير متأخرة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal معالجة الدفع -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">معالجة دفع جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم الفاتورة</label>
                            <input type="text" class="form-control" id="invoiceNumber" placeholder="أدخل رقم الفاتورة">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">طريقة الدفع</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">نقدي</option>
                                <option value="visa">فيزا</option>
                                <option value="mastercard">ماستركارد</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                                <option value="insurance">تأمين</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="patientInfo" class="alert alert-info" style="display: none;">
                        <!-- معلومات المريض ستظهر هنا -->
                    </div>
                    
                    <div id="cardDetails" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم البطاقة</label>
                                <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم حامل البطاقة</label>
                                <input type="text" class="form-control" id="cardHolder">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">تاريخ الانتهاء</label>
                                <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" class="form-control" id="amount" step="0.01">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="submitPayment()">معالجة الدفع</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// رسم بياني لطرق الدفع
const ctx = document.getElementById('paymentMethodsChart').getContext('2d');
const paymentMethodsChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['نقدي', 'فيزا', 'ماستركارد', 'تحويل بنكي', 'تأمين'],
        datasets: [{
            data: [
                {{ $paymentMethodStats['cash'] }},
                {{ $paymentMethodStats['visa'] }},
                {{ $paymentMethodStats['mastercard'] }},
                {{ $paymentMethodStats['bank_transfer'] }},
                {{ $paymentMethodStats['insurance'] }}
            ],
            backgroundColor: [
                '#28a745',
                '#007bff',
                '#6f42c1',
                '#fd7e14',
                '#20c997'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// فتح نافذة معالجة الدفع
function openPaymentModal() {
    $('#paymentModal').modal('show');
}

// فتح نافذة الدفع النقدي السريع
function openCashPaymentModal() {
    // تنفيذ منطق الدفع النقدي السريع
    console.log('فتح نافذة الدفع النقدي السريع');
}

// البحث عن مريض
function searchPatient() {
    // تنفيذ منطق البحث عن المريض
    console.log('البحث عن مريض');
}

// عرض المدفوعات المعلقة
function viewPendingPayments() {
    window.location.href = '{{ route("cashier.pending-payments") }}';
}

// معالجة الدفع
function submitPayment() {
    const formData = {
        invoice_number: $('#invoiceNumber').val(),
        payment_method: $('#paymentMethod').val(),
        amount: $('#amount').val(),
        card_number: $('#cardNumber').val(),
        card_holder: $('#cardHolder').val(),
        expiry_date: $('#expiryDate').val(),
        cvv: $('#cvv').val()
    };
    
    // إرسال البيانات للخادم
    fetch('{{ route("cashier.process-payment") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم معالجة الدفع بنجاح');
            $('#paymentModal').modal('hide');
            location.reload();
        } else {
            alert('خطأ: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في معالجة الدفع');
    });
}

// إظهار/إخفاء تفاصيل البطاقة
$('#paymentMethod').change(function() {
    const method = $(this).val();
    if (method === 'visa' || method === 'mastercard') {
        $('#cardDetails').show();
    } else {
        $('#cardDetails').hide();
    }
});

// موافقة على الدفع
function approvePayment(paymentId) {
    if (confirm('هل أنت متأكد من الموافقة على هذا الدفع؟')) {
        // تنفيذ منطق الموافقة
        console.log('الموافقة على الدفع:', paymentId);
    }
}

// رفض الدفع
function rejectPayment(paymentId) {
    if (confirm('هل أنت متأكد من رفض هذا الدفع؟')) {
        // تنفيذ منطق الرفض
        console.log('رفض الدفع:', paymentId);
    }
}

// معالجة دفع الفاتورة
function processInvoicePayment(invoiceId) {
    $('#invoiceNumber').val(invoiceId);
    $('#paymentModal').modal('show');
}

// إرسال تذكير
function sendReminder(invoiceId) {
    if (confirm('هل تريد إرسال تذكير للمريض؟')) {
        // تنفيذ منطق إرسال التذكير
        console.log('إرسال تذكير للفاتورة:', invoiceId);
    }
}
</script>
@endsection