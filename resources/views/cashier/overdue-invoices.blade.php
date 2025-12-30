@extends('layouts.app')

@section('title', 'الفواتير المتأخرة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        الفواتير المتأخرة
                    </h1>
                    <p class="text-muted mb-0">إدارة ومتابعة الفواتير المستحقة والمتأخرة</p>
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
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $overdueInvoices->total() }}</h4>
                            <p class="mb-0">فاتورة متأخرة</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
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
                            <h4>{{ number_format($overdueInvoices->sum('total_amount'), 0) }}</h4>
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
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $overdueInvoices->where('patient.insurancePolicy')->count() }}</h4>
                            <p class="mb-0">مرضى مؤمنين</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $overdueInvoices->whereNull('patient.insurance_policy_id')->count() }}</h4>
                            <p class="mb-0">مرضى نقديين</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أدوات التصفية والبحث -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">نوع المريض</label>
                            <select name="patient_type" class="form-select">
                                <option value="">جميع الأنواع</option>
                                <option value="insured" {{ request('patient_type') == 'insured' ? 'selected' : '' }}>مؤمن</option>
                                <option value="cash" {{ request('patient_type') == 'cash' ? 'selected' : '' }}>نقدي</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">فترة التأخير</label>
                            <select name="overdue_period" class="form-select">
                                <option value="">جميع الفترات</option>
                                <option value="1-7" {{ request('overdue_period') == '1-7' ? 'selected' : '' }}>1-7 أيام</option>
                                <option value="8-30" {{ request('overdue_period') == '8-30' ? 'selected' : '' }}>8-30 يوم</option>
                                <option value="31-90" {{ request('overdue_period') == '31-90' ? 'selected' : '' }}>31-90 يوم</option>
                                <option value="90+" {{ request('overdue_period') == '90+' ? 'selected' : '' }}>أكثر من 90 يوم</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" placeholder="اسم المريض أو رقم الفاتورة" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    بحث
                                </button>
                                <a href="{{ route('cashier.overdue-invoices') }}" class="btn btn-secondary">
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

    <!-- جدول الفواتير المتأخرة -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">قائمة الفواتير المتأخرة</h6>
                    <div>
                        <button class="btn btn-warning btn-sm" onclick="bulkSendReminders()">
                            <i class="fas fa-bell me-1"></i>
                            إرسال تذكيرات جماعية
                        </button>
                        <button class="btn btn-info btn-sm" onclick="exportOverdueReport()">
                            <i class="fas fa-download me-1"></i>
                            تصدير التقرير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($overdueInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th>رقم الفاتورة</th>
                                        <th>المريض</th>
                                        <th>نوع المريض</th>
                                        <th>المبلغ الإجمالي</th>
                                        <th>المبلغ المدفوع</th>
                                        <th>المبلغ المتبقي</th>
                                        <th>تاريخ الاستحقاق</th>
                                        <th>أيام التأخير</th>
                                        <th>آخر تذكير</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueInvoices as $invoice)
                                    @php
                                        $overdueDays = $invoice->due_date->diffInDays(now());
                                        $remainingAmount = $invoice->total_amount - $invoice->paid_amount;
                                        $overdueClass = $overdueDays > 90 ? 'table-danger' : ($overdueDays > 30 ? 'table-warning' : '');
                                    @endphp
                                    <tr class="{{ $overdueClass }}">
                                        <td>
                                            <input type="checkbox" class="invoice-checkbox" value="{{ $invoice->id }}">
                                        </td>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice->id) }}" class="text-decoration-none fw-bold">
                                                #{{ $invoice->invoice_number }}
                                            </a>
                                            <div class="small text-muted">{{ $invoice->created_at->format('d/m/Y') }}</div>
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
                                            <span class="fw-bold">{{ number_format($invoice->total_amount, 2) }} ر.س</span>
                                        </td>
                                        <td>
                                            <span class="text-success">{{ number_format($invoice->paid_amount, 2) }} ر.س</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-danger">{{ number_format($remainingAmount, 2) }} ر.س</span>
                                        </td>
                                        <td>
                                            <div>{{ $invoice->due_date->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $invoice->due_date->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($overdueDays > 90)
                                                <span class="badge bg-danger fs-6">{{ $overdueDays }} يوم</span>
                                            @elseif($overdueDays > 30)
                                                <span class="badge bg-warning fs-6">{{ $overdueDays }} يوم</span>
                                            @else
                                                <span class="badge bg-secondary fs-6">{{ $overdueDays }} يوم</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($invoice->last_reminder_sent)
                                                <div class="small">{{ $invoice->last_reminder_sent->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $invoice->last_reminder_sent->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">لم يتم إرسال</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary" onclick="processPayment({{ $invoice->id }})" title="معالجة دفع">
                                                    <i class="fas fa-credit-card"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="sendReminder({{ $invoice->id }})" title="إرسال تذكير">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info" onclick="viewInvoiceDetails({{ $invoice->id }})" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="schedulePaymentPlan({{ $invoice->id }})">
                                                            <i class="fas fa-calendar me-2"></i>جدولة خطة دفع
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="writeOffDebt({{ $invoice->id }})">
                                                            <i class="fas fa-times-circle me-2"></i>شطب الدين
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="transferToCollection({{ $invoice->id }})">
                                                            <i class="fas fa-hand-holding-usd me-2"></i>تحويل للتحصيل
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $overdueInvoices->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>لا توجد فواتير متأخرة</h4>
                            <p class="text-muted">جميع الفواتير تم دفعها في الوقت المحدد</p>
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
                <h5 class="modal-title">معالجة دفع الفاتورة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentModalContent">
                <!-- المحتوى سيتم تحميله ديناميكياً -->
            </div>
        </div>
    </div>
</div>

<!-- Modal خطة الدفع -->
<div class="modal fade" id="paymentPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">جدولة خطة دفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentPlanForm">
                    <input type="hidden" id="planInvoiceId">
                    <div class="mb-3">
                        <label class="form-label">عدد الأقساط</label>
                        <select class="form-select" id="installmentCount">
                            <option value="2">قسطين</option>
                            <option value="3">3 أقساط</option>
                            <option value="4">4 أقساط</option>
                            <option value="6">6 أقساط</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ أول قسط</label>
                        <input type="date" class="form-control" id="firstInstallmentDate" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" id="planNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="submitPaymentPlan()">إنشاء خطة الدفع</button>
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
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// الحصول على الفواتير المحددة
function getSelectedInvoices() {
    const checkboxes = document.querySelectorAll('.invoice-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// معالجة دفع الفاتورة
function processPayment(invoiceId) {
    // تحميل نموذج الدفع
    fetch(`{{ route("cashier.payment-form", ":id") }}`.replace(':id', invoiceId))
        .then(response => response.text())
        .then(html => {
            document.getElementById('paymentModalContent').innerHTML = html;
            $('#paymentModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل نموذج الدفع');
        });
}

// إرسال تذكير
function sendReminder(invoiceId) {
    if (confirm('هل تريد إرسال تذكير للمريض؟')) {
        fetch(`{{ route("cashier.send-reminder", ":id") }}`.replace(':id', invoiceId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم إرسال التذكير بنجاح');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في إرسال التذكير');
        });
    }
}

// إرسال تذكيرات جماعية
function bulkSendReminders() {
    const selectedInvoices = getSelectedInvoices();
    
    if (selectedInvoices.length === 0) {
        alert('يرجى تحديد فواتير لإرسال التذكيرات');
        return;
    }
    
    if (confirm(`هل تريد إرسال تذكيرات لـ ${selectedInvoices.length} فاتورة؟`)) {
        fetch('{{ route("cashier.bulk-send-reminders") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                invoice_ids: selectedInvoices
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم إرسال التذكيرات بنجاح');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في إرسال التذكيرات');
        });
    }
}

// عرض تفاصيل الفاتورة
function viewInvoiceDetails(invoiceId) {
    window.open(`{{ route("invoices.show", ":id") }}`.replace(':id', invoiceId), '_blank');
}

// جدولة خطة دفع
function schedulePaymentPlan(invoiceId) {
    document.getElementById('planInvoiceId').value = invoiceId;
    $('#paymentPlanModal').modal('show');
}

// إرسال خطة الدفع
function submitPaymentPlan() {
    const formData = {
        invoice_id: document.getElementById('planInvoiceId').value,
        installment_count: document.getElementById('installmentCount').value,
        first_installment_date: document.getElementById('firstInstallmentDate').value,
        notes: document.getElementById('planNotes').value
    };
    
    fetch('{{ route("cashier.create-payment-plan") }}', {
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
            alert('تم إنشاء خطة الدفع بنجاح');
            $('#paymentPlanModal').modal('hide');
            location.reload();
        } else {
            alert('خطأ: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إنشاء خطة الدفع');
    });
}

// شطب الدين
function writeOffDebt(invoiceId) {
    const reason = prompt('يرجى إدخال سبب شطب الدين:');
    if (!reason) return;
    
    if (confirm('هل أنت متأكد من شطب هذا الدين؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        fetch(`{{ route("cashier.write-off-debt", ":id") }}`.replace(':id', invoiceId), {
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
                alert('تم شطب الدين بنجاح');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في شطب الدين');
        });
    }
}

// تحويل للتحصيل
function transferToCollection(invoiceId) {
    if (confirm('هل تريد تحويل هذه الفاتورة لقسم التحصيل؟')) {
        fetch(`{{ route("cashier.transfer-to-collection", ":id") }}`.replace(':id', invoiceId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم تحويل الفاتورة لقسم التحصيل');
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في التحويل');
        });
    }
}

// تصدير تقرير الفواتير المتأخرة
function exportOverdueReport() {
    window.open('{{ route("cashier.export-overdue-report") }}', '_blank');
}
</script>
@endsection