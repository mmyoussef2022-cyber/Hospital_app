@extends('layouts.app')

@section('title', 'لوحة تحكم العمليات الجراحية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">لوحة تحكل العمليات الجراحية</h1>
            <p class="mb-0 text-muted">إدارة ومتابعة العمليات الجراحية اليومية</p>
        </div>
        <div>
            <a href="{{ route('surgeries.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> جدولة عملية جديدة
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
                                عمليات اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today_total'] }}</div>
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
                                مكتملة اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today_completed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                جارية الآن
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today_in_progress'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-procedures fa-2x text-gray-300"></i>
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
                                طوارئ في الانتظار
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['emergency_queue'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Surgeries -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">عمليات اليوم</h6>
                    <a href="{{ route('surgeries.today') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    @if($todaySurgeries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>الوقت</th>
                                        <th>المريض</th>
                                        <th>الجراح</th>
                                        <th>العملية</th>
                                        <th>غرفة العمليات</th>
                                        <th>الحالة</th>
                                        <th>الأولوية</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaySurgeries->take(10) as $surgery)
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                {{ $surgery->scheduled_start_time->format('H:i') }} - 
                                                {{ $surgery->scheduled_end_time->format('H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('patients.show', $surgery->patient) }}" class="text-decoration-none">
                                                {{ $surgery->patient->name }}
                                            </a>
                                        </td>
                                        <td>{{ $surgery->primarySurgeon->name }}</td>
                                        <td>{{ $surgery->surgicalProcedure->display_name }}</td>
                                        <td>{{ $surgery->operatingRoom->or_number }}</td>
                                        <td>
                                            <span class="badge badge-{{ $surgery->status_color }}">
                                                {{ $surgery->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $surgery->priority_color }}">
                                                {{ $surgery->priority_display }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">لا توجد عمليات مجدولة لليوم</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Operating Rooms Status -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">حالة غرف العمليات</h6>
                </div>
                <div class="card-body">
                    @foreach($operatingRooms as $room)
                    <div class="d-flex align-items-center mb-3 p-2 border rounded">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $room->or_number }}</h6>
                            <small class="text-muted">{{ $room->display_name }}</small>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-{{ $room->status_color }}">
                                {{ $room->status_display }}
                            </span>
                            @if($room->currentSurgery)
                                <div class="mt-1">
                                    <small class="text-muted">
                                        {{ $room->currentSurgery->patient->name }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    <div class="mt-3">
                        <a href="{{ route('operating-rooms.dashboard') }}" class="btn btn-sm btn-outline-primary btn-block">
                            عرض تفاصيل غرف العمليات
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Queue -->
    @if($emergencyQueue->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-exclamation-triangle"></i> طوارئ في الانتظار
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>الجراح</th>
                                    <th>العملية</th>
                                    <th>الأولوية</th>
                                    <th>الوقت المجدول</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($emergencyQueue as $surgery)
                                <tr>
                                    <td>{{ $surgery->patient->name }}</td>
                                    <td>{{ $surgery->primarySurgeon->name }}</td>
                                    <td>{{ $surgery->surgicalProcedure->display_name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $surgery->priority_color }}">
                                            {{ $surgery->priority_display }}
                                        </span>
                                    </td>
                                    <td>{{ $surgery->scheduled_start_time->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('surgeries.show', $surgery) }}" class="btn btn-sm btn-primary">
                                            عرض التفاصيل
                                        </a>
                                    </td>
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

@push('scripts')
<script>
// Auto refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endpush
@endsection