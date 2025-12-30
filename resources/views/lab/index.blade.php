@extends('layouts.app')

@section('title', 'إدارة طلبات المختبر')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-flask me-2"></i>
                        إدارة طلبات المختبر
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('lab.today') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-calendar-day me-1"></i>
                            طلبات اليوم
                        </a>
                        <a href="{{ route('lab.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            طلب جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>تم التحقق</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="priority" class="form-select">
                                        <option value="">جميع الأولويات</option>
                                        <option value="routine" {{ request('priority') == 'routine' ? 'selected' : '' }}>عادي</option>
                                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
                                        <option value="stat" {{ request('priority') == 'stat' ? 'selected' : '' }}>طارئ</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date" class="form-control" value="{{ request('date') }}" placeholder="التاريخ">
                                </div>
                                <div class="col-md-2">
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
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="critical_values" value="1" 
                                               {{ request('critical_values') ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            قيم حرجة فقط
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> بحث
                                    </button>
                                    <a href="{{ route('lab.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>المريض</th>
                                        <th>الطبيب</th>
                                        <th>الفحوصات</th>
                                        <th>الأولوية</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th>المبلغ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $order->order_number }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->patient->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $order->patient->national_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->doctor->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $order->doctor->department->name ?? 'غير محدد' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-info">{{ $order->labTest->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->priority_color }}">
                                                    {{ $order->priority_display }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->status_color }}">
                                                    {{ $order->status_display }}
                                                </span>
                                                @if($order->has_critical_values)
                                                    <br>
                                                    <span class="badge bg-danger mt-1">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        قيم حرجة
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $order->ordered_at->format('Y-m-d') }}
                                                    <br>
                                                    <small class="text-muted">{{ $order->ordered_at->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ number_format($order->total_amount, 2) }} ريال</strong>
                                                @if($order->is_paid)
                                                    <br>
                                                    <span class="badge bg-success">مدفوع</span>
                                                @else
                                                    <br>
                                                    <span class="badge bg-warning">غير مدفوع</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('lab.show', $order) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($order->canBeEdited())
                                                        <a href="{{ route('lab.edit', $order) }}" 
                                                           class="btn btn-sm btn-outline-warning" 
                                                           title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($order->canAddResults())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                onclick="addResults({{ $order->id }})"
                                                                title="إضافة النتائج">
                                                            <i class="fas fa-plus-circle"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($order->canBeVerified())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-info" 
                                                                onclick="verifyResults({{ $order->id }})"
                                                                title="التحقق من النتائج">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد طلبات مختبر</h5>
                            <p class="text-muted">يمكنك إنشاء طلب جديد من الزر أعلاه</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة النتائج</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="resultsForm">
                    <input type="hidden" id="orderId" name="order_id">
                    <div id="testsContainer">
                        <!-- Test results will be loaded here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="saveResults()">حفظ النتائج</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addResults(orderId) {
    document.getElementById('orderId').value = orderId;
    
    // Load test results form via AJAX
    fetch(`/lab/${orderId}/results-form`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('testsContainer').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('resultsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل النموذج');
        });
}

function saveResults() {
    const formData = new FormData(document.getElementById('resultsForm'));
    const orderId = formData.get('order_id');
    
    fetch(`/lab/${orderId}/results`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في حفظ النتائج');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في حفظ النتائج');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('resultsModal')).hide();
}

function verifyResults(orderId) {
    if (confirm('هل أنت متأكد من التحقق من هذه النتائج؟')) {
        fetch(`/lab/${orderId}/verify`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ في التحقق من النتائج');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في التحقق من النتائج');
        });
    }
}

// Auto-refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>
@endpush