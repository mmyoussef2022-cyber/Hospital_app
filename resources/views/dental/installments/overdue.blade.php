@extends('layouts.app')

@section('title', 'الأقساط المتأخرة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        الأقساط المتأخرة
                    </h3>
                    <div>
                        <a href="{{ route('dental.installments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة لجميع الأقساط
                        </a>
                        <a href="{{ route('dental.installments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة قسط جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($installments->count() > 0)
                        <!-- Alert for overdue installments -->
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>تنبيه:</strong> يوجد {{ $installments->total() }} قسط متأخر يحتاج لمتابعة فورية.
                        </div>

                        <!-- Overdue Installments Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th>رقم القسط</th>
                                        <th>المريض</th>
                                        <th>العلاج</th>
                                        <th>المبلغ</th>
                                        <th>تاريخ الاستحقاق</th>
                                        <th>أيام التأخير</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($installments as $installment)
                                        <tr class="table-danger">
                                            <td>
                                                <input type="checkbox" name="installments[]" value="{{ $installment->id }}" class="installment-checkbox">
                                            </td>
                                            <td>
                                                <strong>{{ $installment->installment_number }}</strong>
                                            </td>
                                            <td>
                                                <a href="{{ route('patients.show', $installment->dentalTreatment->patient) }}" class="text-decoration-none">
                                                    {{ $installment->dentalTreatment->patient->name }}
                                                </a>
                                                <br>
                                                <small class="text-muted">{{ $installment->dentalTreatment->patient->phone }}</small>
                                            </td>
                                            <td>{{ $installment->dentalTreatment->title }}</td>
                                            <td>
                                                <span class="font-weight-bold text-danger">
                                                    {{ number_format($installment->amount, 2) }} ريال
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    {{ $installment->due_date->format('Y-m-d') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    {{ $installment->due_date->diffInDays(now()) }} يوم
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    متأخر
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('dental.installments.show', $installment) }}" class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('dental.installments.edit', $installment) }}" class="btn btn-sm btn-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('dental.installments.mark-paid', $installment) }}" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success" title="تحديد كمدفوع" onclick="return confirm('هل أنت متأكد من تحديد هذا القسط كمدفوع؟')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-primary" title="إرسال تذكير" onclick="sendReminder({{ $installment->id }})">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Bulk Actions -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bulk-action">إجراءات جماعية:</label>
                                    <div class="input-group">
                                        <select id="bulk-action" class="form-control">
                                            <option value="">اختر إجراء</option>
                                            <option value="mark_paid">تحديد كمدفوع</option>
                                            <option value="send_reminder">إرسال تذكير</option>
                                            <option value="delete">حذف</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" onclick="executeBulkAction()">تنفيذ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $installments->links() }}
                        </div>

                    @else
                        <!-- No overdue installments -->
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>ممتاز!</h4>
                            <p>لا توجد أقساط متأخرة حالياً. جميع الأقساط مدفوعة في مواعيدها.</p>
                            <a href="{{ route('dental.installments.index') }}" class="btn btn-primary">
                                عرض جميع الأقساط
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reminder Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إرسال تذكير دفع</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>هل تريد إرسال تذكير دفع للمريض؟</p>
                <div class="form-group">
                    <label>طريقة الإرسال:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="sms" id="sms">
                        <label class="form-check-label" for="sms">رسالة نصية</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="whatsapp" id="whatsapp">
                        <label class="form-check-label" for="whatsapp">واتساب</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="call" id="call">
                        <label class="form-check-label" for="call">مكالمة هاتفية</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="confirmSendReminder()">إرسال التذكير</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentInstallmentId = null;

// Select all checkbox functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.installment-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Send reminder function
function sendReminder(installmentId) {
    currentInstallmentId = installmentId;
    $('#reminderModal').modal('show');
}

function confirmSendReminder() {
    if (currentInstallmentId) {
        // Here you would implement the actual reminder sending logic
        fetch(`/dental/installments/${currentInstallmentId}/reminder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                methods: getSelectedReminderMethods()
            })
        })
        .then(response => response.json())
        .then(data => {
            $('#reminderModal').modal('hide');
            if (data.success) {
                alert('تم إرسال التذكير بنجاح');
                location.reload();
            } else {
                alert('حدث خطأ أثناء إرسال التذكير');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء إرسال التذكير');
        });
    }
}

function getSelectedReminderMethods() {
    const methods = [];
    if (document.getElementById('sms').checked) methods.push('sms');
    if (document.getElementById('whatsapp').checked) methods.push('whatsapp');
    if (document.getElementById('call').checked) methods.push('call');
    return methods;
}

// Bulk actions
function executeBulkAction() {
    const action = document.getElementById('bulk-action').value;
    const selectedInstallments = Array.from(document.querySelectorAll('.installment-checkbox:checked'))
        .map(checkbox => checkbox.value);

    if (!action) {
        alert('يرجى اختيار إجراء');
        return;
    }

    if (selectedInstallments.length === 0) {
        alert('يرجى اختيار أقساط للتنفيذ عليها');
        return;
    }

    if (confirm(`هل أنت متأكد من تنفيذ هذا الإجراء على ${selectedInstallments.length} قسط؟`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("dental.installments.bulk-action") }}';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        // Add action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);

        // Add selected installments
        selectedInstallments.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'installments[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection