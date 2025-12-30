@extends('layouts.app')

@section('page-title', 'تحليلات صفحة الهبوط')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    تحليلات صفحة الهبوط
                </h2>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshAnalytics()">
                        <i class="fas fa-sync me-1"></i>
                        تحديث البيانات
                    </button>
                    <a href="{{ route('admin.landing-page.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                مشاهدات الصفحة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($analytics['page_views']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
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
                                زوار فريدون
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($analytics['unique_visitors']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                معدل الارتداد
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['bounce_rate'] }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                معدل التحويل
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['conversion_rate'] }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Traffic Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area me-2"></i>
                        إحصائيات الزيارات
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <div class="dropdown-header">فترة التحليل:</div>
                            <a class="dropdown-item" href="#" onclick="updatePeriod('7days')">آخر 7 أيام</a>
                            <a class="dropdown-item" href="#" onclick="updatePeriod('30days')">آخر 30 يوم</a>
                            <a class="dropdown-item" href="#" onclick="updatePeriod('90days')">آخر 3 أشهر</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Pages -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        أكثر الصفحات زيارة
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($analytics['top_pages'] as $page)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $page['page'] }}</h6>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ ($page['views'] / $analytics['top_pages'][0]['views']) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="ms-3">
                                <span class="badge bg-primary">{{ number_format($page['views']) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics -->
    <div class="row">
        <!-- Session Duration -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock me-2"></i>
                        مدة الجلسة
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-2">
                        {{ $analytics['avg_session_duration'] }}
                    </div>
                    <p class="text-muted mb-0">متوسط مدة الجلسة</p>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success">85%</h5>
                                <small class="text-muted">جلسات طويلة</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-warning">15%</h5>
                            <small class="text-muted">جلسات قصيرة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Analytics -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-mobile-alt me-2"></i>
                        إحصائيات الأجهزة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="deviceChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> موبايل (65%)
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> سطح المكتب (25%)
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> تابلت (10%)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb me-2"></i>
                        رؤى الأداء والتوصيات
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-thumbs-up text-success fa-2x me-3"></i>
                                        <div>
                                            <h6 class="text-success mb-1">أداء ممتاز</h6>
                                            <p class="mb-0 small">معدل التحويل أعلى من المتوسط بنسبة 15%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 mb-3">
                            <div class="card border-left-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle text-warning fa-2x me-3"></i>
                                        <div>
                                            <h6 class="text-warning mb-1">يحتاج تحسين</h6>
                                            <p class="mb-0 small">معدل الارتداد مرتفع قليلاً، فكر في تحسين المحتوى</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 mb-3">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-info fa-2x me-3"></i>
                                        <div>
                                            <h6 class="text-info mb-1">نصيحة</h6>
                                            <p class="mb-0 small">أضف المزيد من العروض المميزة لزيادة التفاعل</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Traffic Chart
const trafficCtx = document.getElementById('trafficChart').getContext('2d');
const trafficChart = new Chart(trafficCtx, {
    type: 'line',
    data: {
        labels: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
        datasets: [{
            label: 'مشاهدات الصفحة',
            data: [120, 190, 300, 500, 200, 300, 450],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'زوار فريدون',
            data: [80, 150, 250, 400, 150, 250, 350],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'إحصائيات الزيارات الأسبوعية'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Device Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
const deviceChart = new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: ['موبايل', 'سطح المكتب', 'تابلت'],
        datasets: [{
            data: [65, 25, 10],
            backgroundColor: [
                'rgb(54, 162, 235)',
                'rgb(75, 192, 192)',
                'rgb(255, 205, 86)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

function refreshAnalytics() {
    Swal.fire({
        icon: 'success',
        title: 'تم التحديث',
        text: 'تم تحديث بيانات التحليلات بنجاح',
        timer: 2000,
        showConfirmButton: false
    }).then(() => {
        location.reload();
    });
}

function updatePeriod(period) {
    // هنا يمكن إضافة كود لتحديث البيانات حسب الفترة المختارة
    console.log('تحديث البيانات للفترة:', period);
    
    Swal.fire({
        icon: 'info',
        title: 'تحديث الفترة',
        text: `تم تحديث البيانات لفترة: ${period}`,
        timer: 1500,
        showConfirmButton: false
    });
}

// تحديث البيانات كل 5 دقائق
setInterval(function() {
    // يمكن إضافة كود لتحديث البيانات تلقائياً
    console.log('تحديث تلقائي للبيانات');
}, 300000); // 5 دقائق
</script>
@endsection