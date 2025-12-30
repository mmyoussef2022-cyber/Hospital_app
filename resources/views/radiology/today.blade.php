@extends('layouts.app')

@section('title', 'طلبات الأشعة اليوم')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-x-ray me-2"></i>
                        طلبات الأشعة اليوم
                    </h3>
                    <div class="d-flex gap-2">
                        <span class="badge bg-info fs-6">
                            {{ $orders->count() }} طلب
                        </span>
                        <a href="{{ route('radiology.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            طلب جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>المريض</th>
                                        <th>الطبيب</th>
                                        <th>نوع الفحص</th>
                                        <th>الأولوية</th>
                                        <th>الحالة</th>
                                        <th>وقت الطلب</th>
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
                                                    <strong>{{ $order->radiologyStudy->display_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $order->radiologyStudy->category_display }}</small>
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
                                                @if($order->has_urgent_findings)
                                                    <br>
                                                    <span class="badge bg-danger mt-1">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        نتائج عاجلة
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $order->ordered_at->format('H:i') }}
                                                    <br>
                                                    <small class="text-muted">{{ $order->ordered_at->diffForHumans() }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('radiology.show', $order) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($order->canBeScheduled())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-info" 
                                                                onclick="scheduleOrder({{ $order->id }})"
                                                                title="جدولة">
                                                            <i class="fas fa-calendar-plus"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($order->canBeStarted())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                onclick="startOrder({{ $order->id }})"
                                                                title="بدء الفحص">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($order->canBeCompleted())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-warning" 
                                                                onclick="completeOrder({{ $order->id }})"
                                                                title="إكمال الفحص">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($order->canBeReported())
                                                        <a href="{{ route('radiology.report-form', $order) }}" 
                                                           class="btn btn-sm btn-outline-dark" 
                                                           title="كتابة التقرير">
                                                            <i class="fas fa-file-medical"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-x-ray fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد طلبات أشعة لليوم</h5>
                            <p class="text-muted">يمكنك إنشاء طلب جديد من الزر أعلاه</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تحديث حالة الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="orderId" name="order_id">
                    <input type="hidden" id="newStatus" name="status">
                    
                    <div id="scheduleFields" style="display: none;">
                        <div class="mb-3">
                            <label for="scheduledAt" class="form-label">موعد الفحص</label>
                            <input type="datetime-local" class="form-control" id="scheduledAt" name="scheduled_at">
                        </div>
                    </div>
                    
                    <div id="completeFields" style="display: none;">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="contrastUsed" name="contrast_used">
                                <label class="form-check-label" for="contrastUsed">
                                    تم استخدام صبغة
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contrastNotes" class="form-label">ملاحظات الصبغة</label>
                            <textarea class="form-control" id="contrastNotes" name="contrast_notes" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="updateStatus()">تحديث</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function scheduleOrder(orderId) {
    document.getElementById('orderId').value = orderId;
    document.getElementById('newStatus').value = 'scheduled';
    document.getElementById('scheduleFields').style.display = 'block';
    document.getElementById('completeFields').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function startOrder(orderId) {
    updateOrderStatus(orderId, 'in_progress');
}

function completeOrder(orderId) {
    document.getElementById('orderId').value = orderId;
    document.getElementById('newStatus').value = 'completed';
    document.getElementById('scheduleFields').style.display = 'none';
    document.getElementById('completeFields').style.display = 'block';
    
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function updateStatus() {
    const formData = new FormData(document.getElementById('statusForm'));
    const orderId = formData.get('order_id');
    
    fetch(`/radiology/${orderId}/status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تحديث الحالة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث الحالة');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
}

function updateOrderStatus(orderId, status) {
    fetch(`/radiology/${orderId}/status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تحديث الحالة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث الحالة');
    });
}

// Auto-refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>
@endpush