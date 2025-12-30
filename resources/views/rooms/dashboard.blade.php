@extends('layouts.app')

@section('title', 'لوحة تحكم الغرف')

@section('content')
<div class="container-fluid">
    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ $stats['total_rooms'] ?? 0 }}</h3>
                            <p class="mb-0">إجمالي الغرف</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bed fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ $stats['available_rooms'] ?? 0 }}</h3>
                            <p class="mb-0">غرف متاحة</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ $stats['occupied_rooms'] ?? 0 }}</h3>
                            <p class="mb-0">غرف مشغولة</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-injured fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3>{{ number_format($stats['occupancy_rate'] ?? 0, 1) }}%</h3>
                            <p class="mb-0">معدل الإشغال</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-percentage fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Room Status Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">توزيع الغرف حسب الحالة</h5>
                </div>
                <div class="card-body">
                    <canvas id="roomStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Room Type Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">توزيع الغرف حسب النوع</h5>
                </div>
                <div class="card-body">
                    <canvas id="roomTypeChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent Assignments -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">آخر التخصيصات</h5>
                    <a href="{{ route('rooms.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>الغرفة</th>
                                    <th>السرير</th>
                                    <th>تاريخ الدخول</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAssignments as $assignment)
                                    <tr>
                                        <td>
                                            <strong>{{ $assignment->patient->name ?? 'غير محدد' }}</strong>
                                            <small class="text-muted d-block">{{ $assignment->patient->national_id ?? '' }}</small>
                                        </td>
                                        <td>{{ $assignment->room->room_number ?? 'غير محدد' }}</td>
                                        <td>{{ $assignment->bed->bed_number ?? 'غير محدد' }}</td>
                                        <td>{{ $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d H:i') : 'غير محدد' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $assignment->status_color }}">
                                                {{ $assignment->status_display }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">لا توجد تخصيصات حديثة</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Assignments -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        تخصيصات متأخرة
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($overdueAssignments as $assignment)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong>{{ $assignment->patient->name ?? 'غير محدد' }}</strong>
                                <small class="text-muted d-block">غرفة {{ $assignment->room->room_number ?? 'غير محدد' }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-danger">
                                    متأخر {{ $assignment->expected_discharge_at ? $assignment->expected_discharge_at->diffForHumans() : '' }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p>لا توجد تخصيصات متأخرة</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('rooms.create') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus"></i> إضافة غرفة جديدة
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('beds.create') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-bed"></i> إضافة سرير جديد
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('rooms.index', ['availability' => 'available']) }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-search"></i> عرض الغرف المتاحة
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-warning w-100 mb-2" onclick="refreshDashboard()">
                                <i class="fas fa-sync-alt"></i> تحديث البيانات
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
// Room Status Chart
const roomStatusCtx = document.getElementById('roomStatusChart').getContext('2d');
const roomStatusChart = new Chart(roomStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['متاحة', 'مشغولة', 'صيانة', 'تنظيف'],
        datasets: [{
            data: [
                {{ $roomsByStatus['available'] ?? 0 }},
                {{ $roomsByStatus['occupied'] ?? 0 }},
                {{ $roomsByStatus['maintenance'] ?? 0 }},
                {{ $roomsByStatus['cleaning'] ?? 0 }}
            ],
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#17a2b8'
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

// Room Type Chart
const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
const roomTypeChart = new Chart(roomTypeCtx, {
    type: 'bar',
    data: {
        labels: [
            @foreach($roomsByType as $type => $count)
                '{{ ucfirst($type) }}',
            @endforeach
        ],
        datasets: [{
            label: 'عدد الغرف',
            data: [
                @foreach($roomsByType as $count)
                    {{ $count }},
                @endforeach
            ],
            backgroundColor: '#007bff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

function refreshDashboard() {
    location.reload();
}

// Auto-refresh every 5 minutes
setInterval(refreshDashboard, 300000);
</script>
@endpush