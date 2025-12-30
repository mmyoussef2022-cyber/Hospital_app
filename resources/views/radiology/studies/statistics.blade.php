@extends('layouts.app')

@section('title', 'إحصائيات فحوصات الأشعة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات فحوصات الأشعة
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('radiology-studies.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Date Range Filter -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">من تاريخ</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">إلى تاريخ</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i>
                                            تحديث الإحصائيات
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Overview Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-x-ray fa-2x mb-2"></i>
                                    <h4>{{ $stats['total_studies'] }}</h4>
                                    <p class="mb-0">إجمالي الفحوصات</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h4>{{ $stats['active_studies'] }}</h4>
                                    <p class="mb-0">فحوصات نشطة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                                    <h4>{{ $stats['inactive_studies'] }}</h4>
                                    <p class="mb-0">فحوصات غير نشطة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-bolt fa-2x mb-2"></i>
                                    <h4>{{ $stats['urgent_capable_studies'] }}</h4>
                                    <p class="mb-0">قابلة للعجل</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-tint fa-2x mb-2"></i>
                                    <h4>{{ $stats['studies_with_contrast'] }}</h4>
                                    <p class="mb-0">تتطلب صبغة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-utensils fa-2x mb-2"></i>
                                    <h4>{{ $stats['studies_requiring_fasting'] }}</h4>
                                    <p class="mb-0">تتطلب صيام</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Studies by Category -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>
                                        الفحوصات حسب الفئة
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($studiesByCategory->count() > 0)
                                        <canvas id="categoryChart" width="400" height="200"></canvas>
                                        <div class="mt-3">
                                            @foreach($studiesByCategory as $category => $count)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>{{ \App\Models\RadiologyStudy::getCategories()[$category] ?? $category }}</span>
                                                    <strong>{{ $count }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                            <p>لا توجد بيانات للعرض</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Popular Studies -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-star me-2"></i>
                                        الفحوصات الأكثر طلباً
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($popularStudies->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>الفحص</th>
                                                        <th>عدد الطلبات</th>
                                                        <th>النسبة</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $totalOrders = $popularStudies->sum('orders_count'); @endphp
                                                    @foreach($popularStudies as $study)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $study->name }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ $study->category_display }}</small>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary">{{ $study->orders_count }}</span>
                                                            </td>
                                                            <td>
                                                                @if($totalOrders > 0)
                                                                    <div class="progress" style="height: 20px;">
                                                                        <div class="progress-bar" 
                                                                             role="progressbar" 
                                                                             style="width: {{ ($study->orders_count / $totalOrders) * 100 }}%">
                                                                            {{ number_format(($study->orders_count / $totalOrders) * 100, 1) }}%
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    0%
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-star fa-3x mb-3"></i>
                                            <p>لا توجد طلبات في الفترة المحددة</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue by Study -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        الإيرادات حسب الفحص
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($revenueByStudy->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>الفحص</th>
                                                        <th>الإيرادات</th>
                                                        <th>النسبة</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $totalRevenue = $revenueByStudy->sum('revenue'); @endphp
                                                    @foreach($revenueByStudy as $study)
                                                        <tr>
                                                            <td>{{ $study['name'] }}</td>
                                                            <td>
                                                                <strong class="text-success">{{ number_format($study['revenue'], 2) }} ر.س</strong>
                                                            </td>
                                                            <td>
                                                                @if($totalRevenue > 0)
                                                                    <div class="progress" style="height: 25px;">
                                                                        <div class="progress-bar bg-success" 
                                                                             role="progressbar" 
                                                                             style="width: {{ ($study['revenue'] / $totalRevenue) * 100 }}%">
                                                                            {{ number_format(($study['revenue'] / $totalRevenue) * 100, 1) }}%
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    0%
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th>الإجمالي</th>
                                                        <th class="text-success">{{ number_format($totalRevenue, 2) }} ر.س</th>
                                                        <th>100%</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                                            <p>لا توجد إيرادات في الفترة المحددة</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.progress {
    background-color: #e9ecef;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.text-muted {
    color: #6c757d !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Category Chart
    @if($studiesByCategory->count() > 0)
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($studiesByCategory as $category => $count)
                    '{{ \App\Models\RadiologyStudy::getCategories()[$category] ?? $category }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($studiesByCategory as $category => $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    @endif

    // Auto-refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush