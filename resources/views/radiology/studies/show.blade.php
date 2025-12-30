@extends('layouts.app')

@section('title', 'تفاصيل فحص الأشعة - ' . $radiologyStudy->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-x-ray me-2"></i>
                        تفاصيل فحص الأشعة
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('radiology-studies.edit', $radiologyStudy) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>
                            تعديل
                        </a>
                        <a href="{{ route('radiology-studies.index') }}" class="btn btn-secondary btn-sm">
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
                                        <span class="badge bg-secondary fs-6">{{ $radiologyStudy->code }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="info-label">الحالة:</label>
                                        <span class="badge bg-{{ $radiologyStudy->is_active ? 'success' : 'danger' }} fs-6">
                                            {{ $radiologyStudy->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="info-item mb-3">
                                        <label class="info-label">اسم الفحص:</label>
                                        <div class="info-value">{{ $radiologyStudy->name }}</div>
                                    </div>
                                </div>
                                @if($radiologyStudy->name_en)
                                <div class="col-md-12">
                                    <div class="info-item mb-3">
                                        <label class="info-label">الاسم بالإنجليزية:</label>
                                        <div class="info-value">{{ $radiologyStudy->name_en }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($radiologyStudy->description)
                                <div class="col-md-12">
                                    <div class="info-item mb-3">
                                        <label class="info-label">الوصف:</label>
                                        <div class="info-value">{{ $radiologyStudy->description }}</div>
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
                                            <span>طلبات هذا الشهر:</span>
                                            <strong class="text-info">{{ $stats['this_month_orders'] }}</strong>
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

                    <!-- Study Details -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">الفئة:</label>
                                <span class="badge bg-info fs-6">{{ $radiologyStudy->category_display }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">جزء الجسم:</label>
                                <span class="badge bg-primary fs-6">{{ $radiologyStudy->body_part_display }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">السعر:</label>
                                <div class="info-value text-success fw-bold">{{ number_format($radiologyStudy->price, 2) }} ر.س</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">المدة:</label>
                                <span class="badge bg-warning text-dark fs-6">{{ $radiologyStudy->duration_minutes }} دقيقة</span>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-item mb-3">
                                <label class="info-label">المتطلبات:</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @if($radiologyStudy->requires_contrast)
                                        <span class="badge bg-danger">يتطلب صبغة</span>
                                    @endif
                                    @if($radiologyStudy->requires_fasting)
                                        <span class="badge bg-warning text-dark">يتطلب صيام</span>
                                    @endif
                                    @if($radiologyStudy->is_urgent_capable)
                                        <span class="badge bg-success">قابل للعجل</span>
                                    @endif
                                    @if(!$radiologyStudy->requires_contrast && !$radiologyStudy->requires_fasting && !$radiologyStudy->is_urgent_capable)
                                        <span class="text-muted">لا توجد متطلبات خاصة</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    @if($radiologyStudy->preparation_instructions || $radiologyStudy->contrast_instructions)
                    <hr>
                    <div class="row">
                        @if($radiologyStudy->preparation_instructions)
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">تعليمات التحضير:</label>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ $radiologyStudy->preparation_instructions }}
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($radiologyStudy->contrast_instructions)
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">تعليمات الصبغة:</label>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ $radiologyStudy->contrast_instructions }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Recent Orders -->
                    @if($radiologyStudy->orders->count() > 0)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-history me-2"></i>
                                آخر الطلبات ({{ $radiologyStudy->orders->count() }} من {{ $stats['total_orders'] }})
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
                                            <th>الأولوية</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($radiologyStudy->orders as $order)
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
                                                            'ordered' => 'secondary',
                                                            'scheduled' => 'info',
                                                            'in_progress' => 'warning',
                                                            'completed' => 'success',
                                                            'reported' => 'primary',
                                                            'cancelled' => 'danger'
                                                        ];
                                                        $statusLabels = [
                                                            'ordered' => 'مطلوب',
                                                            'scheduled' => 'مجدول',
                                                            'in_progress' => 'قيد التنفيذ',
                                                            'completed' => 'مكتمل',
                                                            'reported' => 'تم التقرير',
                                                            'cancelled' => 'ملغي'
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $priorityColors = [
                                                            'routine' => 'secondary',
                                                            'urgent' => 'warning',
                                                            'stat' => 'danger'
                                                        ];
                                                        $priorityLabels = [
                                                            'routine' => 'عادي',
                                                            'urgent' => 'عاجل',
                                                            'stat' => 'طارئ'
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $priorityColors[$order->priority] ?? 'secondary' }}">
                                                        {{ $priorityLabels[$order->priority] ?? $order->priority }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('radiology.show', $order) }}" 
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

                            @if($stats['total_orders'] > $radiologyStudy->orders->count())
                                <div class="text-center mt-3">
                                    <a href="{{ route('radiology.index', ['study_id' => $radiologyStudy->id]) }}" class="btn btn-outline-primary">
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

.alert {
    margin-bottom: 0;
}

.requirements-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
</style>
@endpush