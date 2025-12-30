@extends('layouts.app')

@section('title', 'تفاصيل الفحص المخبري - ' . $labTest->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-vial me-2"></i>
                        تفاصيل الفحص المخبري
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('lab-tests.edit', $labTest) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>
                            تعديل
                        </a>
                        <a href="{{ route('lab-tests.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="info-label">كود الفحص:</label>
                                        <span class="badge bg-secondary fs-6">{{ $labTest->code }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="info-label">الحالة:</label>
                                        <span class="badge bg-{{ $labTest->is_active ? 'success' : 'danger' }} fs-6">
                                            {{ $labTest->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="info-item mb-3">
                                        <label class="info-label">اسم الفحص:</label>
                                        <div class="info-value">{{ $labTest->name }}</div>
                                    </div>
                                </div>
                                @if($labTest->name_en)
                                <div class="col-md-12">
                                    <div class="info-item mb-3">
                                        <label class="info-label">الاسم بالإنجليزية:</label>
                                        <div class="info-value">{{ $labTest->name_en }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($labTest->description)
                                <div class="col-md-12">
                                    <div class="info-item mb-3">
                                        <label class="info-label">الوصف:</label>
                                        <div class="info-value">{{ $labTest->description }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        إحصائيات الفحص
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="stat-item mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span>إجمالي الطلبات:</span>
                                            <strong class="text-primary">{{ $stats['total_orders'] }}</strong>
                                        </div>
                                    </div>
                                    <div class="stat-item mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span>الطلبات المكتملة:</span>
                                            <strong class="text-success">{{ $stats['completed_orders'] }}</strong>
                                        </div>
                                    </div>
                                    <div class="stat-item mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span>الطلبات المعلقة:</span>
                                            <strong class="text-warning">{{ $stats['pending_orders'] }}</strong>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="stat-item">
                                        <div class="d-flex justify-content-between">
                                            <span>إجمالي الإيرادات:</span>
                                            <strong class="text-success">{{ number_format($stats['total_revenue'], 2) }} ر.س</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Test Details -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">الفئة:</label>
                                <span class="badge bg-info fs-6">{{ $labTest->category }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">السعر:</label>
                                <div class="info-value text-success fw-bold">{{ number_format($labTest->price, 2) }} ر.س</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">المدة:</label>
                                <span class="badge bg-warning text-dark fs-6">{{ $labTest->duration_minutes }} دقيقة</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">نوع العينة:</label>
                                <div class="info-value">{{ $labTest->specimen_type }}</div>
                            </div>
                        </div>
                    </div>

                    @if($labTest->preparation_instructions)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-item mb-3">
                                <label class="info-label">تعليمات التحضير:</label>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ $labTest->preparation_instructions }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Normal Ranges and Critical Values -->
                    @if($labTest->normal_ranges || $labTest->critical_values)
                    <hr>
                    <div class="row">
                        @if($labTest->normal_ranges)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-chart-line me-1"></i>
                                        المعدلات الطبيعية
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $normalRanges = is_string($labTest->normal_ranges) 
                                            ? json_decode($labTest->normal_ranges, true) 
                                            : $labTest->normal_ranges;
                                    @endphp
                                    
                                    @if(is_array($normalRanges))
                                        @foreach($normalRanges as $key => $range)
                                            <div class="range-item mb-2">
                                                <strong>{{ ucfirst($key) }}:</strong>
                                                @if(is_array($range))
                                                    @if(isset($range['min']) && isset($range['max']))
                                                        {{ $range['min'] }} - {{ $range['max'] }}
                                                        @if(isset($range['unit']))
                                                            {{ $range['unit'] }}
                                                        @endif
                                                    @else
                                                        {{ json_encode($range) }}
                                                    @endif
                                                @else
                                                    {{ $range }}
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <pre class="mb-0">{{ json_encode($normalRanges, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($labTest->critical_values)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        القيم الحرجة
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $criticalValues = is_string($labTest->critical_values) 
                                            ? json_decode($labTest->critical_values, true) 
                                            : $labTest->critical_values;
                                    @endphp
                                    
                                    @if(is_array($criticalValues))
                                        @foreach($criticalValues as $key => $value)
                                            <div class="critical-item mb-2">
                                                <strong class="text-danger">{{ ucfirst($key) }}:</strong>
                                                @if(is_array($value))
                                                    {{ json_encode($value) }}
                                                @else
                                                    {{ $value }}
                                                    @if(isset($criticalValues['unit']))
                                                        {{ $criticalValues['unit'] }}
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <pre class="mb-0">{{ json_encode($criticalValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Recent Orders -->
                    @if($labTest->orders->count() > 0)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-history me-2"></i>
                                آخر الطلبات ({{ $labTest->orders->count() }} من {{ $stats['total_orders'] }})
                            </h5>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>رقم الطلب</th>
                                            <th>المريض</th>
                                            <th>الطبيب</th>
                                            <th>تاريخ الطلب</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($labTest->orders as $order)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">#{{ $order->id }}</span>
                                                </td>
                                                <td>
                                                    @if($order->patient)
                                                        <strong>{{ $order->patient->first_name }} {{ $order->patient->last_name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $order->patient->phone }}</small>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($order->doctor)
                                                        {{ $order->doctor->first_name }} {{ $order->doctor->last_name }}
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td>{{ $order->ordered_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'warning',
                                                            'in_progress' => 'info',
                                                            'completed' => 'success',
                                                            'verified' => 'primary',
                                                            'cancelled' => 'danger'
                                                        ];
                                                        $statusLabels = [
                                                            'pending' => 'في الانتظار',
                                                            'in_progress' => 'قيد التنفيذ',
                                                            'completed' => 'مكتمل',
                                                            'verified' => 'تم التحقق',
                                                            'cancelled' => 'ملغي'
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('lab.show', $order) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="عرض الطلب">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($stats['total_orders'] > $labTest->orders->count())
                                <div class="text-center mt-3">
                                    <a href="{{ route('lab.index', ['test_id' => $labTest->id]) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-list me-1"></i>
                                        عرض جميع الطلبات ({{ $stats['total_orders'] }})
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.info-label {
    font-weight: 600;
    color: #6c757d;
    display: block;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1.1em;
    color: #495057;
}

.info-item {
    padding: 0.5rem 0;
}

.stat-item {
    font-size: 0.9em;
}

.range-item,
.critical-item {
    padding: 0.25rem 0;
    border-bottom: 1px solid #e9ecef;
}

.range-item:last-child,
.critical-item:last-child {
    border-bottom: none;
}

.badge {
    font-size: 0.8em;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    border-top: none;
    font-weight: 600;
}

pre {
    background-color: #f8f9fa;
    padding: 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.85em;
}
</style>
@endpush