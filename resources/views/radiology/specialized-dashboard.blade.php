@extends('layouts.app')

@section('title', 'لوحة تحكم الأشعة المتخصصة')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-x-ray me-2"></i>
                        لوحة تحكم الأشعة المتخصصة
                    </h2>
                    <p class="text-muted mb-0">إدارة شاملة لجميع طلبات وعمليات الأشعة</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportModal">
                        <i class="fas fa-chart-bar me-1"></i>
                        تقارير
                    </button>
                    <button class="btn btn-outline-info" onclick="showScheduleView()">
                        <i class="fas fa-calendar-alt me-1"></i>
                        الجدولة
                    </button>
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i>
                        تحديث
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-x-ray fa-2x"></i>
                    </div>
                    <h4 class="text-primary mb-1">{{ $todayStats['total_orders'] }}</h4>
                    <small class="text-muted">إجمالي الطلبات اليوم</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ $todayStats['pending_orders'] }}</h4>
                    <small class="text-muted">طلبات معلقة</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h4 class="text-success mb-1">{{ $todayStats['completed_orders'] }}</h4>
                    <small class="text-muted">فحوصات مكتملة</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-file-medical-alt fa-2x"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ $todayStats['reported_studies'] }}</h4>
                    <small class="text-muted">تقارير مكتملة</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h4 class="text-danger mb-1">{{ $todayStats['urgent_findings'] }}</h4>
                    <small class="text-muted">نتائج عاجلة</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <h4 class="text-secondary mb-1">{{ $todayStats['overdue_orders'] }}</h4>
                    <small class="text-muted">طلبات متأخرة</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Urgent Findings Alert -->
        @if($urgentFindings->count() > 0)
        <div class="col-12 mb-4">
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">تنبيه: نتائج عاجلة تحتاج إشعار فوري!</h5>
                        <p class="mb-0">يوجد {{ $urgentFindings->count() }} نتيجة عاجلة تحتاج إشعار الطبيب المعالج فوراً</p>
                    </div>
                    <button class="btn btn-light" onclick="showUrgentFindings()">
                        <i class="fas fa-eye me-1"></i>
                        عرض النتائج
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Pending Orders by Priority -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list-ul text-primary me-2"></i>
                            الطلبات المعلقة حسب الأولوية
                        </h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="priorityFilter" id="all" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="all">الكل</label>
                            
                            <input type="radio" class="btn-check" name="priorityFilter" id="stat" autocomplete="off">
                            <label class="btn btn-outline-danger" for="stat">عاجل جداً</label>
                            
                            <input type="radio" class="btn-check" name="priorityFilter" id="urgent" autocomplete="off">
                            <label class="btn btn-outline-warning" for="urgent">عاجل</label>
                            
                            <input type="radio" class="btn-check" name="priorityFilter" id="routine" autocomplete="off">
                            <label class="btn btn-outline-info" for="routine">عادي</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pendingOrdersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>المريض</th>
                                    <th>نوع الفحص</th>
                                    <th>الأولوية</th>
                                    <th>وقت الطلب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingOrders->flatten() as $order)
                                <tr data-priority="{{ $order->priority }}">
                                    <td>
                                        <strong class="text-primary">#{{ $order->order_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->patient->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $order->patient->national_id }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $order->radiologyStudy->name }}</td>
                                    <td>
                                        @php
                                            $priorityClass = match($order->priority) {
                                                'stat' => 'danger',
                                                'urgent' => 'warning',
                                                'routine' => 'info',
                                                default => 'secondary'
                                            };
                                            $priorityText = match($order->priority) {
                                                'stat' => 'عاجل جداً',
                                                'urgent' => 'عاجل',
                                                'routine' => 'عادي',
                                                default => 'غير محدد'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $priorityClass }}">{{ $priorityText }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $order->ordered_at->format('H:i') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $order->ordered_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($order->status) {
                                                'ordered' => 'warning',
                                                'scheduled' => 'info',
                                                'in_progress' => 'primary',
                                                'completed' => 'success',
                                                'reported' => 'dark',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                            $statusText = match($order->status) {
                                                'ordered' => 'مطلوب',
                                                'scheduled' => 'مجدول',
                                                'in_progress' => 'قيد التنفيذ',
                                                'completed' => 'مكتمل',
                                                'reported' => 'تم التقرير',
                                                'cancelled' => 'ملغي',
                                                default => 'غير محدد'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewOrderDetails({{ $order->id }})" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($order->status === 'ordered')
                                            <button class="btn btn-outline-info" onclick="scheduleOrder({{ $order->id }})" title="جدولة الفحص">
                                                <i class="fas fa-calendar-plus"></i>
                                            </button>
                                            @endif
                                            @if($order->status === 'scheduled')
                                            <button class="btn btn-outline-success" onclick="updateOrderStatus({{ $order->id }}, 'in_progress')" title="بدء الفحص">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            @endif
                                            @if($order->status === 'in_progress')
                                            <button class="btn btn-outline-success" onclick="updateOrderStatus({{ $order->id }}, 'completed')" title="إكمال الفحص">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                            @if($order->status === 'completed')
                                            <button class="btn btn-outline-dark" onclick="addReport({{ $order->id }})" title="إضافة تقرير">
                                                <i class="fas fa-file-medical-alt"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Alerts -->
        <div class="col-lg-4 mb-4">
            <!-- Today's Schedule -->
            @if($todaySchedule->count() > 0)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calendar-day me-2"></i>
                        جدول اليوم ({{ $todaySchedule->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($todaySchedule->take(5) as $order)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong class="text-info">#{{ $order->order_number }}</strong>
                                    <br>
                                    <small>{{ $order->patient->name }}</small>
                                    <br>
                                    <small class="text-muted">{{ $order->radiologyStudy->name }}</small>
                                </div>
                                <small class="text-info">
                                    {{ $order->scheduled_at ? $order->scheduled_at->format('H:i') : 'غير محدد' }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Overdue Orders -->
            @if($overdueOrders->count() > 0)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-danger text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>
                        طلبات متأخرة ({{ $overdueOrders->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($overdueOrders->take(5) as $order)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong class="text-danger">#{{ $order->order_number }}</strong>
                                    <br>
                                    <small>{{ $order->patient->name }}</small>
                                    <br>
                                    <small class="text-muted">{{ $order->radiologyStudy->name }}</small>
                                </div>
                                <small class="text-danger">
                                    {{ $order->ordered_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-danger" onclick="showUrgentFindings()">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            النتائج العاجلة ({{ $urgentFindings->count() }})
                        </button>
                        <button class="btn btn-outline-info" onclick="showTodaySchedule()">
                            <i class="fas fa-calendar-day me-2"></i>
                            جدول اليوم ({{ $todaySchedule->count() }})
                        </button>
                        <button class="btn btn-outline-primary" onclick="showTodayStats()">
                            <i class="fas fa-chart-line me-2"></i>
                            إحصائيات اليوم
                        </button>
                        <button class="btn btn-outline-success" onclick="exportTodayReports()">
                            <i class="fas fa-download me-2"></i>
                            تصدير تقارير اليوم
                        </button>
                        <button class="btn btn-outline-warning" onclick="showEquipmentStatus()">
                            <i class="fas fa-cogs me-2"></i>
                            حالة الأجهزة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workload Statistics Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area text-primary me-2"></i>
                        إحصائيات الأحمال - آخر 7 أيام
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="workloadChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('radiology.modals.order-details')
@include('radiology.modals.schedule-order')
@include('radiology.modals.add-report')
@include('radiology.modals.urgent-findings')
@include('radiology.modals.report-generator')

@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.alert {
    border-radius: 10px;
}

.card {
    border-radius: 10px;
}

.btn {
    border-radius: 6px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize workload chart
const workloadData = @json($workloadStats['daily_stats']);
const ctx = document.getElementById('workloadChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: workloadData.map(item => item.date_display),
        datasets: [{
            label: 'الطلبات',
            data: workloadData.map(item => item.orders),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.4
        }, {
            label: 'المكتملة',
            data: workloadData.map(item => item.completed),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4
        }, {
            label: 'التقارير',
            data: workloadData.map(item => item.reported),
            borderColor: 'rgb(153, 102, 255)',
            backgroundColor: 'rgba(153, 102, 255, 0.1)',
            tension: 0.4
        }, {
            label: 'العاجلة',
            data: workloadData.map(item => item.urgent_findings),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Priority filter functionality
document.querySelectorAll('input[name="priorityFilter"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const priority = this.id;
        const rows = document.querySelectorAll('#pendingOrdersTable tbody tr');
        
        rows.forEach(row => {
            if (priority === 'all' || row.dataset.priority === priority) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function viewOrderDetails(orderId) {
    // Load order details via AJAX
    fetch(`/radiology-specialized/orders/${orderId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate modal with order details
                document.getElementById('orderDetailsModal').querySelector('.modal-body').innerHTML = 
                    generateOrderDetailsHTML(data.order);
                new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء جلب تفاصيل الطلب');
        });
}

function updateOrderStatus(orderId, status) {
    if (confirm('هل أنت متأكد من تحديث حالة الطلب؟')) {
        fetch(`/radiology-specialized/orders/${orderId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                refreshDashboard();
            } else {
                alert(data.message || 'حدث خطأ أثناء تحديث الحالة');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تحديث الحالة');
        });
    }
}

function scheduleOrder(orderId) {
    // Show schedule order modal
    document.getElementById('scheduleOrderId').value = orderId;
    new bootstrap.Modal(document.getElementById('scheduleOrderModal')).show();
}

function addReport(orderId) {
    // Show add report modal
    document.getElementById('addReportOrderId').value = orderId;
    new bootstrap.Modal(document.getElementById('addReportModal')).show();
}

function showUrgentFindings() {
    new bootstrap.Modal(document.getElementById('urgentFindingsModal')).show();
}

function showScheduleView() {
    // Implementation for schedule view
    window.location.href = '/radiology-schedule';
}

function showTodaySchedule() {
    const schedule = @json($todaySchedule);
    console.log('Today Schedule:', schedule);
    // Implementation for showing detailed schedule
}

function showTodayStats() {
    const stats = @json($todayStats);
    let statsHTML = '<div class="row">';
    
    Object.entries(stats).forEach(([key, value]) => {
        const labels = {
            'total_orders': 'إجمالي الطلبات',
            'pending_orders': 'طلبات معلقة',
            'completed_orders': 'فحوصات مكتملة',
            'reported_studies': 'تقارير مكتملة',
            'urgent_orders': 'طلبات عاجلة',
            'scheduled_today': 'مجدولة اليوم',
            'urgent_findings': 'نتائج عاجلة',
            'overdue_orders': 'طلبات متأخرة'
        };
        
        statsHTML += `
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-primary">${value}</h4>
                        <p class="mb-0">${labels[key] || key}</p>
                    </div>
                </div>
            </div>
        `;
    });
    
    statsHTML += '</div>';
    
    // Show in a modal or alert
    const modal = new bootstrap.Modal(document.createElement('div'));
    // Implementation would go here
}

function exportTodayReports() {
    window.open('/radiology-specialized/reports/generate?report_type=daily&date_from=' + 
                new Date().toISOString().split('T')[0] + 
                '&date_to=' + new Date().toISOString().split('T')[0], '_blank');
}

function showEquipmentStatus() {
    const equipmentStats = @json($workloadStats['equipment_utilization']);
    console.log('Equipment Status:', equipmentStats);
    // Implementation for showing equipment status
}

function generateOrderDetailsHTML(order) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6>معلومات الطلب</h6>
                <p><strong>رقم الطلب:</strong> #${order.order_number}</p>
                <p><strong>نوع الفحص:</strong> ${order.radiology_study.name}</p>
                <p><strong>الأولوية:</strong> ${order.priority}</p>
                <p><strong>الحالة:</strong> ${order.status}</p>
                ${order.scheduled_at ? `<p><strong>موعد الفحص:</strong> ${order.scheduled_at}</p>` : ''}
            </div>
            <div class="col-md-6">
                <h6>معلومات المريض</h6>
                <p><strong>الاسم:</strong> ${order.patient.name}</p>
                <p><strong>الهوية:</strong> ${order.patient.national_id}</p>
                <p><strong>الطبيب:</strong> ${order.doctor.name}</p>
            </div>
        </div>
    `;
}
</script>
@endpush