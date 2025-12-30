@extends('layouts.app')

@section('title', 'لوحة تحكم الورديات')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clock me-2"></i>
            لوحة تحكم الورديات
        </h1>
        <div>
            <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                إضافة وردية جديدة
            </a>
            <a href="{{ route('shifts.calendar') }}" class="btn btn-outline-primary">
                <i class="fas fa-calendar me-1"></i>
                التقويم
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ورديات اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today_shifts'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                                الورديات النشطة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_shifts'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
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
                                الورديات المتأخرة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue_shifts'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                إيرادات اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['today_revenue'], 2) }} ريال
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Shifts -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">الورديات النشطة</h6>
                    <a href="{{ route('shifts.index', ['status' => 'active']) }}" class="btn btn-sm btn-outline-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($activeShifts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>الموظف</th>
                                        <th>القسم</th>
                                        <th>النوع</th>
                                        <th>بدأت</th>
                                        <th>الإيرادات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeShifts as $shift)
                                    <tr>
                                        <td>{{ $shift->user->name }}</td>
                                        <td>{{ $shift->department->name }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $shift->shift_type_display }}</span>
                                        </td>
                                        <td>{{ $shift->actual_start->format('H:i') }}</td>
                                        <td>{{ number_format($shift->total_revenue, 2) }} ريال</td>
                                        <td>
                                            <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">لا توجد ورديات نشطة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Today's Shifts -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">ورديات اليوم</h6>
                    <a href="{{ route('shifts.index', ['date_from' => today()->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($todayShifts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>الموظف</th>
                                        <th>النوع</th>
                                        <th>الحالة</th>
                                        <th>الوقت</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayShifts->take(10) as $shift)
                                    <tr>
                                        <td>{{ $shift->user->name }}</td>
                                        <td>{{ $shift->shift_type_display }}</td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'scheduled' => 'secondary',
                                                    'active' => 'success',
                                                    'completed' => 'primary',
                                                    'cancelled' => 'danger',
                                                    'no_show' => 'warning'
                                                ][$shift->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $statusClass }}">{{ $shift->status_display }}</span>
                                        </td>
                                        <td>{{ $shift->scheduled_start }} - {{ $shift->scheduled_end }}</td>
                                        <td>
                                            <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-day fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">لا توجد ورديات مجدولة لليوم</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Shifts Alert -->
    @if($overdueShifts->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تنبيه: ورديات متأخرة
                </h5>
                <p class="mb-2">يوجد {{ $overdueShifts->count() }} وردية متأخرة تحتاج إلى متابعة:</p>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            @foreach($overdueShifts->take(5) as $shift)
                            <tr>
                                <td>{{ $shift->user->name }}</td>
                                <td>{{ $shift->department->name }}</td>
                                <td>{{ $shift->shift_date->format('Y-m-d') }}</td>
                                <td>{{ $shift->scheduled_start }}</td>
                                <td>
                                    <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-warning">
                                        متابعة
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($overdueShifts->count() > 5)
                <hr>
                <a href="{{ route('shifts.index', ['status' => 'scheduled']) }}" class="btn btn-warning">
                    عرض جميع الورديات المتأخرة ({{ $overdueShifts->count() }})
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Department Summary -->
    @if($departmentSummary->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ملخص الأقسام - اليوم</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>القسم</th>
                                    <th>إجمالي الورديات</th>
                                    <th>النشطة</th>
                                    <th>المكتملة</th>
                                    <th>الإيرادات</th>
                                    <th>المعاملات</th>
                                    <th>المرضى</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departmentSummary as $summary)
                                <tr>
                                    <td>{{ $summary['department'] }}</td>
                                    <td>{{ $summary['total_shifts'] }}</td>
                                    <td>
                                        <span class="badge badge-success">{{ $summary['active_shifts'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $summary['completed_shifts'] }}</span>
                                    </td>
                                    <td>{{ number_format($summary['total_revenue'], 2) }} ريال</td>
                                    <td>{{ $summary['total_transactions'] }}</td>
                                    <td>{{ $summary['total_patients_served'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endpush