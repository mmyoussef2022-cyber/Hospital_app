@extends('layouts.app')

@section('title', 'إدارة الورديات')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clock me-2"></i>
            إدارة الورديات
        </h1>
        <div>
            <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                إضافة وردية جديدة
            </a>
            <a href="{{ route('shifts.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-tachometer-alt me-1"></i>
                لوحة التحكم
            </a>
            <a href="{{ route('shifts.calendar') }}" class="btn btn-outline-secondary">
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
                                إجمالي الورديات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_shifts'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                إيرادات اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_revenue_today'], 2) }} ريال
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

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">البحث والتصفية</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('shifts.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">البحث</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="رقم الوردية أو اسم الموظف">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">الحالة</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">جميع الحالات</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                            <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>غياب</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="shift_type" class="form-label">نوع الوردية</label>
                        <select class="form-control" id="shift_type" name="shift_type">
                            <option value="">جميع الأنواع</option>
                            <option value="morning" {{ request('shift_type') == 'morning' ? 'selected' : '' }}>صباحية</option>
                            <option value="afternoon" {{ request('shift_type') == 'afternoon' ? 'selected' : '' }}>بعد الظهر</option>
                            <option value="evening" {{ request('shift_type') == 'evening' ? 'selected' : '' }}>مسائية</option>
                            <option value="night" {{ request('shift_type') == 'night' ? 'selected' : '' }}>ليلية</option>
                            <option value="emergency" {{ request('shift_type') == 'emergency' ? 'selected' : '' }}>طوارئ</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="department_id" class="form-label">القسم</label>
                        <select class="form-control" id="department_id" name="department_id">
                            <option value="">جميع الأقسام</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_from" class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="date_to" class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-9 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>
                            بحث
                        </button>
                        <a href="{{ route('shifts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo me-1"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Shifts Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">قائمة الورديات</h6>
        </div>
        <div class="card-body">
            @if($shifts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>رقم الوردية</th>
                                <th>الموظف</th>
                                <th>القسم</th>
                                <th>النوع</th>
                                <th>التاريخ</th>
                                <th>الوقت المجدول</th>
                                <th>الحالة</th>
                                <th>الإيرادات</th>
                                <th>المعاملات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shifts as $shift)
                            <tr>
                                <td>
                                    <a href="{{ route('shifts.show', $shift) }}" class="text-decoration-none">
                                        {{ $shift->shift_number }}
                                    </a>
                                </td>
                                <td>{{ $shift->user->name }}</td>
                                <td>{{ $shift->department->name }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $shift->shift_type_display }}</span>
                                </td>
                                <td>{{ $shift->shift_date->format('Y-m-d') }}</td>
                                <td>{{ $shift->scheduled_start }} - {{ $shift->scheduled_end }}</td>
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
                                <td>{{ number_format($shift->total_revenue, 2) }} ريال</td>
                                <td>{{ $shift->total_transactions }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($shift->status, ['scheduled', 'active']))
                                        <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-sm btn-outline-secondary" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @if($shift->can_start)
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="startShift({{ $shift->id }})" title="بدء الوردية">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        @endif
                                        @if($shift->can_end)
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="endShift({{ $shift->id }})" title="إنهاء الوردية">
                                            <i class="fas fa-stop"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $shifts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">لا توجد ورديات</h5>
                    <p class="text-muted">لم يتم العثور على ورديات تطابق معايير البحث</p>
                    <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة وردية جديدة
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Start Shift Modal -->
<div class="modal fade" id="startShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">بدء الوردية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="startShiftForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="opening_balance" class="form-label">الرصيد الافتتاحي</label>
                        <input type="number" class="form-control" id="opening_balance" name="opening_balance" 
                               step="0.01" min="0" required>
                        <div class="form-text">أدخل الرصيد النقدي في بداية الوردية</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">بدء الوردية</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- End Shift Modal -->
<div class="modal fade" id="endShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إنهاء الوردية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="endShiftForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="closing_balance" class="form-label">الرصيد الختامي</label>
                        <input type="number" class="form-control" id="closing_balance" name="closing_balance" 
                               step="0.01" min="0" required>
                        <div class="form-text">أدخل الرصيد النقدي في نهاية الوردية</div>
                    </div>
                    <div class="mb-3">
                        <label for="shift_notes" class="form-label">ملاحظات الوردية</label>
                        <textarea class="form-control" id="shift_notes" name="shift_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">إنهاء الوردية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function startShift(shiftId) {
    const form = document.getElementById('startShiftForm');
    form.action = `/shifts/${shiftId}/start`;
    new bootstrap.Modal(document.getElementById('startShiftModal')).show();
}

function endShift(shiftId) {
    const form = document.getElementById('endShiftForm');
    form.action = `/shifts/${shiftId}/end`;
    new bootstrap.Modal(document.getElementById('endShiftModal')).show();
}
</script>
@endpush