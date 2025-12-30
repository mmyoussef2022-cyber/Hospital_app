@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('تقارير المخزون والأدوية') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('الرئيسية') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('التقارير') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('تقارير المخزون') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- فلاتر التقرير -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('فلاتر التقرير') }}</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.inventory') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('فئة المنتج') }}</label>
                                    <select name="category" class="form-select">
                                        <option value="">{{ __('جميع الفئات') }}</option>
                                        <option value="medications">{{ __('الأدوية') }}</option>
                                        <option value="medical_supplies">{{ __('المستلزمات الطبية') }}</option>
                                        <option value="equipment">{{ __('المعدات') }}</option>
                                        <option value="consumables">{{ __('المواد الاستهلاكية') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('المخزون المنخفض') }}</label>
                                    <select name="low_stock" class="form-select">
                                        <option value="">{{ __('الكل') }}</option>
                                        <option value="1">{{ __('المخزون المنخفض فقط') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('المنتجات المنتهية الصلاحية') }}</label>
                                    <select name="expired" class="form-select">
                                        <option value="">{{ __('الكل') }}</option>
                                        <option value="1">{{ __('المنتهية الصلاحية فقط') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-search me-1"></i>{{ __('تطبيق الفلتر') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- الإحصائيات الرئيسية -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('إجمالي الأصناف') }}</p>
                            <h4 class="mb-0 text-primary">{{ $data['total_items'] ?? 245 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-package font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('مخزون منخفض') }}</p>
                            <h4 class="mb-0 text-warning">{{ $data['low_stock_items'] ?? 12 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                                <span class="avatar-title">
                                    <i class="bx bx-error font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('منتهية الصلاحية') }}</p>
                            <h4 class="mb-0 text-danger">{{ $data['expired_items'] ?? 5 }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-danger">
                                <span class="avatar-title">
                                    <i class="bx bx-time font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-truncate font-size-14 mb-2">{{ __('قيمة المخزون') }}</p>
                            <h4 class="mb-0 text-success">{{ number_format(450000) }} {{ __('ريال') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                <span class="avatar-title">
                                    <i class="bx bx-money font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- تنبيهات المخزون -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title text-danger">
                        <i class="bx bx-error-circle me-2"></i>{{ __('تنبيهات المخزون الحرجة') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="alert alert-warning" role="alert">
                                <h6 class="alert-heading">{{ __('مخزون منخفض') }}</h6>
                                <p class="mb-0">{{ __('12 صنف يحتاج إعادة طلب') }}</p>
                                <hr>
                                <a href="#" class="btn btn-warning btn-sm">{{ __('عرض التفاصيل') }}</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-danger" role="alert">
                                <h6 class="alert-heading">{{ __('منتهية الصلاحية') }}</h6>
                                <p class="mb-0">{{ __('5 أصناف منتهية الصلاحية') }}</p>
                                <hr>
                                <a href="#" class="btn btn-danger btn-sm">{{ __('عرض التفاصيل') }}</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info" role="alert">
                                <h6 class="alert-heading">{{ __('قريبة الانتهاء') }}</h6>
                                <p class="mb-0">{{ __('8 أصناف تنتهي خلال 30 يوم') }}</p>
                                <hr>
                                <a href="#" class="btn btn-info btn-sm">{{ __('عرض التفاصيل') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('توزيع المخزون حسب الفئة') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="categoryDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('استهلاك المخزون الشهري') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="consumptionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- تفاصيل المخزون -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('تفاصيل المخزون') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('اسم المنتج') }}</th>
                                    <th>{{ __('الفئة') }}</th>
                                    <th>{{ __('الكمية المتاحة') }}</th>
                                    <th>{{ __('الحد الأدنى') }}</th>
                                    <th>{{ __('تاريخ الانتهاء') }}</th>
                                    <th>{{ __('القيمة') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-capsule text-primary me-2"></i>
                                            {{ __('باراسيتامول 500 مجم') }}
                                        </div>
                                    </td>
                                    <td>{{ __('الأدوية') }}</td>
                                    <td>150</td>
                                    <td>50</td>
                                    <td>2025-06-15</td>
                                    <td>{{ number_format(750) }} {{ __('ريال') }}</td>
                                    <td><span class="badge bg-success">{{ __('متوفر') }}</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-injection text-warning me-2"></i>
                                            {{ __('حقن الأنسولين') }}
                                        </div>
                                    </td>
                                    <td>{{ __('الأدوية') }}</td>
                                    <td>25</td>
                                    <td>30</td>
                                    <td>2025-03-20</td>
                                    <td>{{ number_format(1250) }} {{ __('ريال') }}</td>
                                    <td><span class="badge bg-warning">{{ __('مخزون منخفض') }}</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-band-aid text-info me-2"></i>
                                            {{ __('ضمادات طبية') }}
                                        </div>
                                    </td>
                                    <td>{{ __('المستلزمات الطبية') }}</td>
                                    <td>200</td>
                                    <td>100</td>
                                    <td>2026-01-10</td>
                                    <td>{{ number_format(400) }} {{ __('ريال') }}</td>
                                    <td><span class="badge bg-success">{{ __('متوفر') }}</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-test-tube text-danger me-2"></i>
                                            {{ __('أنابيب اختبار') }}
                                        </div>
                                    </td>
                                    <td>{{ __('المستلزمات الطبية') }}</td>
                                    <td>0</td>
                                    <td>50</td>
                                    <td>2024-12-25</td>
                                    <td>{{ number_format(0) }} {{ __('ريال') }}</td>
                                    <td><span class="badge bg-danger">{{ __('منتهية الصلاحية') }}</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-shield text-success me-2"></i>
                                            {{ __('قفازات طبية') }}
                                        </div>
                                    </td>
                                    <td>{{ __('المواد الاستهلاكية') }}</td>
                                    <td>500</td>
                                    <td>200</td>
                                    <td>2025-08-30</td>
                                    <td>{{ number_format(250) }} {{ __('ريال') }}</td>
                                    <td><span class="badge bg-success">{{ __('متوفر') }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الأصناف الأكثر استهلاكاً -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('الأصناف الأكثر استهلاكاً') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('المنتج') }}</th>
                                    <th>{{ __('الاستهلاك الشهري') }}</th>
                                    <th>{{ __('المتوفر') }}</th>
                                    <th>{{ __('يكفي لـ') }}</th>
                                    <th>{{ __('الإجراء المطلوب') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ __('باراسيتامول 500 مجم') }}</td>
                                    <td>120 {{ __('علبة') }}</td>
                                    <td>150 {{ __('علبة') }}</td>
                                    <td>1.2 {{ __('شهر') }}</td>
                                    <td><span class="badge bg-success">{{ __('لا يحتاج إجراء') }}</span></td>
                                </tr>
                                <tr>
                                    <td>{{ __('حقن الأنسولين') }}</td>
                                    <td>40 {{ __('حقنة') }}</td>
                                    <td>25 {{ __('حقنة') }}</td>
                                    <td>0.6 {{ __('شهر') }}</td>
                                    <td><span class="badge bg-danger">{{ __('طلب عاجل') }}</span></td>
                                </tr>
                                <tr>
                                    <td>{{ __('ضمادات طبية') }}</td>
                                    <td>80 {{ __('قطعة') }}</td>
                                    <td>200 {{ __('قطعة') }}</td>
                                    <td>2.5 {{ __('شهر') }}</td>
                                    <td><span class="badge bg-success">{{ __('لا يحتاج إجراء') }}</span></td>
                                </tr>
                                <tr>
                                    <td>{{ __('قفازات طبية') }}</td>
                                    <td>300 {{ __('زوج') }}</td>
                                    <td>500 {{ __('زوج') }}</td>
                                    <td>1.7 {{ __('شهر') }}</td>
                                    <td><span class="badge bg-warning">{{ __('مراقبة') }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('إحصائيات سريعة') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('معدل دوران المخزون') }}</span>
                            <span class="text-primary">4.2</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: 70%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('كفاءة المخزون') }}</span>
                            <span class="text-success">85%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 85%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('الأصناف المطلوبة') }}</span>
                            <span class="text-warning">12</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-warning" style="width: 20%"></div>
                        </div>
                    </div>

                    <hr>

                    <div class="text-center">
                        <h5 class="font-size-16">{{ __('التكلفة الشهرية') }}</h5>
                        <h4 class="text-primary">{{ number_format(45000) }} {{ __('ريال') }}</h4>
                        <p class="text-muted mb-0">{{ __('متوسط التكلفة الشهرية للمخزون') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أزرار التصدير -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('إجراءات المخزون') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('reports.inventory', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
                               class="btn btn-danger btn-block mb-2">
                                <i class="bx bx-file-pdf me-1"></i>{{ __('تصدير PDF') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button onclick="window.print()" class="btn btn-primary btn-block mb-2">
                                <i class="bx bx-printer me-1"></i>{{ __('طباعة') }}
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning btn-block mb-2" onclick="generateReorderList()">
                                <i class="bx bx-list-ul me-1"></i>{{ __('قائمة إعادة الطلب') }}
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info btn-block mb-2" onclick="sendAlerts()">
                                <i class="bx bx-bell me-1"></i>{{ __('إرسال تنبيهات') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// رسم بياني لتوزيع المخزون حسب الفئة
const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');

new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: ['الأدوية', 'المستلزمات الطبية', 'المعدات', 'المواد الاستهلاكية'],
        datasets: [{
            data: [45, 30, 15, 10],
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// رسم بياني للاستهلاك الشهري
const consumptionCtx = document.getElementById('consumptionChart').getContext('2d');

new Chart(consumptionCtx, {
    type: 'bar',
    data: {
        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
        datasets: [{
            label: 'الاستهلاك (ريال)',
            data: [42000, 45000, 38000, 52000, 48000, 55000],
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function generateReorderList() {
    alert('سيتم توليد قائمة إعادة الطلب للأصناف المطلوبة');
}

function sendAlerts() {
    alert('سيتم إرسال تنبيهات المخزون للمسؤولين');
}
</script>
@endpush
@endsection