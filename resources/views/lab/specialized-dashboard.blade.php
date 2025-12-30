@extends('layouts.app')

@section('title', 'لوحة تحكم المختبر المتخصصة')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-microscope me-2"></i>
                        لوحة تحكم المختبر المتخصصة
                    </h2>
                    <p class="text-muted mb-0">إدارة شاملة لجميع طلبات وعمليات المختبر</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportModal">
                        <i class="fas fa-chart-bar me-1"></i>
                        تقارير
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
                        <i class="fas fa-vials fa-2x"></i>
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
                    <small class="text-muted">طلبات مكتملة</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h4 class="text-danger mb-1">{{ $todayStats['critical_results'] }}</h4>
                    <small class="text-muted">نتائج حرجة</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-tachometer-alt fa-2x"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ $todayStats['urgent_orders'] }}</h4>
                    <small class="text-muted">طلبات عاجلة</small>
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
        <!-- Critical Results Alert -->
        @if($criticalResults->count() > 0)
        <div class="col-12 mb-4">
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">تنبيه: نتائج حرجة تحتاج إشعار فوري!</h5>
                        <p class="mb-0">يوجد {{ $criticalResults->count() }} نتيجة حرجة تحتاج إشعار الطبيب المعالج فوراً</p>
                    </div>
                    <button class="btn btn-light" onclick="showCriticalResults()">
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
                                    <th>التحليل</th>
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
                                    <td>{{ $order->labTest->name }}</td>
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
                                        <span class="badge bg-warning">{{ $order->status_display }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewOrderDetails({{ $order->id }})" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" onclick="updateOrderStatus({{ $order->id }}, 'collected')" title="تم جمع العينة">
                                                <i class="fas fa-vial"></i>
                                            </button>
                                            <button class="btn btn-outline-info" onclick="addResult({{ $order->id }})" title="إضافة نتيجة">
                                                <i class="fas fa-plus"></i>
                                            </button>
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
                                    <small class="text-muted">{{ $order->labTest->name }}</small>
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

            <!-- Expiring Samples -->
            @if($expiringsamples->count() > 0)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-hourglass-half me-2"></i>
                        عينات قاربت على الانتهاء ({{ $expiringsamples->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($expiringsamples->take(5) as $sample)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong class="text-warning">#{{ $sample->order_number }}</strong>
                                    <br>
                                    <small>{{ $sample->patient->name }}</small>
                                    <br>
                                    <small class="text-muted">{{ $sample->labTest->name }}</small>
                                </div>
                                <small class="text-warning">
                                    جُمعت {{ $sample->collected_at->diffForHumans() }}
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
                        <button class="btn btn-outline-primary" onclick="showCriticalResults()">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            النتائج الحرجة ({{ $criticalResults->count() }})
                        </button>
                        <button class="btn btn-outline-info" onclick="showTodayStats()">
                            <i class="fas fa-chart-line me-2"></i>
                            إحصائيات اليوم
                        </button>
                        <button class="btn btn-outline-success" onclick="exportTodayResults()">
                            <i class="fas fa-download me-2"></i>
                            تصدير نتائج اليوم
                        </button>
                        <button class="btn btn-outline-warning" onclick="showWorkloadStats()">
                            <i class="fas fa-users me-2"></i>
                            توزيع الأحمال
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
@include('lab.modals.order-details')
@include('lab.modals.add-result')
@include('lab.modals.critical-results')
@include('lab.modals.report-generator')

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
            label: 'الحرجة',
            data: workloadData.map(item => item.critical),
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
    fetch(`/lab-specialized/orders/${orderId}/details`)
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
        fetch(`/lab-specialized/orders/${orderId}/status`, {
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

function addResult(orderId) {
    // Show add result modal
    document.getElementById('addResultOrderId').value = orderId;
    new bootstrap.Modal(document.getElementById('addResultModal')).show();
}

function showCriticalResults() {
    new bootstrap.Modal(document.getElementById('criticalResultsModal')).show();
}

function showTodayStats() {
    const stats = @json($todayStats);
    let statsHTML = '<div class="row">';
    
    Object.entries(stats).forEach(([key, value]) => {
        const labels = {
            'total_orders': 'إجمالي الطلبات',
            'pending_orders': 'طلبات معلقة',
            'completed_orders': 'طلبات مكتملة',
            'critical_results': 'نتائج حرجة',
            'urgent_orders': 'طلبات عاجلة',
            'samples_collected': 'عينات مجمعة',
            'results_verified': 'نتائج مؤكدة',
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

function exportTodayResults() {
    window.open('/lab-specialized/reports/generate?report_type=daily&date_from=' + 
                new Date().toISOString().split('T')[0] + 
                '&date_to=' + new Date().toISOString().split('T')[0], '_blank');
}

function showWorkloadStats() {
    const workloadStats = @json($workloadStats);
    console.log('Workload Statistics:', workloadStats);
    // Implementation for showing detailed workload statistics
}

function generateOrderDetailsHTML(order) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6>معلومات الطلب</h6>
                <p><strong>رقم الطلب:</strong> #${order.order_number}</p>
                <p><strong>التحليل:</strong> ${order.lab_test.name}</p>
                <p><strong>الأولوية:</strong> ${order.priority}</p>
                <p><strong>الحالة:</strong> ${order.status}</p>
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