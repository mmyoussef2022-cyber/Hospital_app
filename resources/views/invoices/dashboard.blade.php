@extends('layouts.app')

@section('page-title', 'لوحة تحكم الفواتير')

@section('content')
<div class="container-fluid">
    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">إجمالي الفواتير</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_invoices']) }}</h3>
                            <small>المبلغ الإجمالي: {{ number_format($stats['total_amount'], 2) }} ريال</small>
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
                            <h6 class="card-title">الفواتير المعلقة</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_invoices']) }}</h3>
                            <small>المبلغ: {{ number_format($stats['pending_amount'], 2) }} ريال</small>
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
                            <h6 class="card-title">الفواتير المتأخرة</h6>
                            <h3 class="mb-0">{{ number_format($stats['overdue_invoices']) }}</h3>
                            <small>المبلغ: {{ number_format($stats['overdue_amount'], 2) }} ريال</small>
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
                            <h6 class="card-title">الفواتير المدفوعة</h6>
                            <h3 class="mb-0">{{ number_format($stats['paid_invoices']) }}</h3>
                            <small>المبلغ: {{ number_format($stats['paid_amount'], 2) }} ريال</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Statistics -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">فواتير اليوم</h6>
                            <h3 class="mb-0">{{ number_format($stats['today_invoices']) }}</h3>
                            <small>المبلغ: {{ number_format($stats['today_amount'], 2) }} ريال</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-day fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">فواتير الشهر</h6>
                            <h3 class="mb-0">{{ number_format($stats['month_invoices']) }}</h3>
                            <small>المبلغ: {{ number_format($stats['month_amount'], 2) }} ريال</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-month fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Invoices -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الفواتير الحديثة</h5>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    @forelse($recentInvoices as $invoice)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>{{ $invoice->invoice_number }}</strong>
                                <br>
                                <small class="text-muted">{{ $invoice->patient->name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ 
                                    $invoice->status == 'paid' ? 'success' : 
                                    ($invoice->status == 'pending' ? 'warning' : 
                                    ($invoice->status == 'overdue' ? 'danger' : 'secondary')) 
                                }}">
                                    {{ $invoice->status_display }}
                                </span>
                                <br>
                                <small class="text-muted">{{ number_format($invoice->total_amount, 2) }} ريال</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">لا توجد فواتير حديثة</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Overdue Invoices -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الفواتير المتأخرة</h5>
                    <span class="badge bg-danger">{{ $overdueInvoices->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($overdueInvoices as $invoice)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>{{ $invoice->invoice_number }}</strong>
                                <br>
                                <small class="text-muted">{{ $invoice->patient->name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="text-danger">
                                    {{ number_format($invoice->remaining_amount, 2) }} ريال
                                </span>
                                <br>
                                <small class="text-muted">
                                    @if($invoice->due_date)
                                        متأخر {{ $invoice->due_date->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">لا توجد فواتير متأخرة</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">إحصائيات الفواتير الشهرية</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> إنشاء فاتورة جديدة
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('payments.create') }}" class="btn btn-success w-100">
                                <i class="bi bi-credit-card"></i> تسجيل دفعة
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="btn btn-danger w-100">
                                <i class="bi bi-exclamation-triangle"></i> الفواتير المتأخرة
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-warning w-100" onclick="markOverdueInvoices()">
                                <i class="bi bi-clock"></i> تحديث الفواتير المتأخرة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($monthlyData);
    
    const months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 
                   'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
    
    const chartData = {
        labels: months,
        datasets: [{
            label: 'عدد الفواتير',
            data: months.map((month, index) => {
                const data = monthlyData.find(item => item.month === index + 1);
                return data ? data.count : 0;
            }),
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            yAxisID: 'y'
        }, {
            label: 'المبلغ (ريال)',
            data: months.map((month, index) => {
                const data = monthlyData.find(item => item.month === index + 1);
                return data ? data.amount : 0;
            }),
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 2,
            yAxisID: 'y1'
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'الشهر'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'عدد الفواتير'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'المبلغ (ريال)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Mark overdue invoices
    function markOverdueInvoices() {
        if (confirm('هل تريد تحديث حالة الفواتير المتأخرة؟')) {
            fetch('{{ route("invoices.mark-overdue") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء التحديث');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء التحديث');
            });
        }
    }
</script>
@endpush