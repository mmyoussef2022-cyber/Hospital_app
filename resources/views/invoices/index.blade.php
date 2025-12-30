@extends('layouts.app')

@section('page-title', 'إدارة الفواتير')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">إجمالي الفواتير</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_invoices']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-receipt fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">المبالغ المعلقة</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_amount'], 2) }} ريال</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">المبالغ المتأخرة</h6>
                            <h3 class="mb-0">{{ number_format($stats['overdue_amount'], 2) }} ريال</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">مدفوعات اليوم</h6>
                            <h3 class="mb-0">{{ number_format($stats['paid_today'], 2) }} ريال</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">الفواتير</h5>
            <div>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> إنشاء فاتورة جديدة
                </a>
                <a href="{{ route('invoices.dashboard') }}" class="btn btn-info">
                    <i class="bi bi-graph-up"></i> لوحة التحكم
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>متأخرة</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">النوع</label>
                    <select name="type" class="form-select">
                        <option value="">جميع الأنواع</option>
                        <option value="cash" {{ request('type') == 'cash' ? 'selected' : '' }}>نقدي</option>
                        <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>آجل</option>
                        <option value="insurance" {{ request('type') == 'insurance' ? 'selected' : '' }}>تأمين</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">المريض</label>
                    <select name="patient_id" class="form-select">
                        <option value="">جميع المرضى</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">الطبيب</label>
                    <select name="doctor_id" class="form-select">
                        <option value="">جميع الأطباء</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                
                <div class="col-md-8">
                    <label class="form-label">البحث</label>
                    <input type="text" name="search" class="form-control" placeholder="رقم الفاتورة، اسم المريض، رقم الهاتف..." value="{{ request('search') }}">
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> بحث
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> إعادة تعيين
                    </a>
                </div>
            </form>

            <!-- Invoices Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المريض</th>
                            <th>الطبيب</th>
                            <th>النوع</th>
                            <th>المبلغ الإجمالي</th>
                            <th>المبلغ المدفوع</th>
                            <th>المبلغ المتبقي</th>
                            <th>الحالة</th>
                            <th>تاريخ الفاتورة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->patient->name }}</td>
                                <td>{{ $invoice->doctor->name ?? 'غير محدد' }}</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->type == 'cash' ? 'success' : ($invoice->type == 'credit' ? 'warning' : 'info') }}">
                                        {{ $invoice->type_display }}
                                    </span>
                                </td>
                                <td>{{ number_format($invoice->total_amount, 2) }} ريال</td>
                                <td>{{ number_format($invoice->paid_amount, 2) }} ريال</td>
                                <td>{{ number_format($invoice->remaining_amount, 2) }} ريال</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $invoice->status == 'paid' ? 'success' : 
                                        ($invoice->status == 'pending' ? 'warning' : 
                                        ($invoice->status == 'overdue' ? 'danger' : 
                                        ($invoice->status == 'draft' ? 'secondary' : 'dark'))) 
                                    }}">
                                        {{ $invoice->status_display }}
                                    </span>
                                </td>
                                <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($invoice->status == 'draft')
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-sm btn-outline-info" title="طباعة" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        @if($invoice->remaining_amount > 0 && !in_array($invoice->status, ['paid', 'cancelled']))
                                            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-outline-success" title="دفع">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">لا توجد فواتير</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $invoices->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit form on filter change
    document.querySelectorAll('select[name="status"], select[name="type"], select[name="patient_id"], select[name="doctor_id"]').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush