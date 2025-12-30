@extends('layouts.app')

@section('title', 'جلسات العلاج - نظام أقساط الأسنان')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">🦷 جلسات العلاج</h2>
                    <p class="text-muted mb-0">إدارة جلسات علاج الأسنان والمتابعة</p>
                </div>
                <div>
                    @can('create', App\Models\DentalSession::class)
                        <a href="{{ route('dental.sessions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i>
                            إضافة جلسة جديدة
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $stats['total'] }}</h3>
                    <small>إجمالي الجلسات</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $stats['scheduled'] }}</h3>
                    <small>مجدولة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $stats['completed'] }}</h3>
                    <small>مكتملة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $stats['cancelled'] }}</h3>
                    <small>ملغية</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $stats['today'] }}</h3>
                    <small>اليوم</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $stats['this_week'] ?? 0 }}</h3>
                    <small>هذا الأسبوع</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dental.sessions.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">البحث</label>
                        <input type="text" name="search" class="form-control" 
                               value="{{ request('search') }}" 
                               placeholder="اسم المريض، رقم الهاتف، أو عنوان الجلسة">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">جميع الحالات</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                            <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>لم يحضر</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">خطة العلاج</label>
                        <select name="dental_treatment_id" class="form-select">
                            <option value="">جميع خطط العلاج</option>
                            @foreach($treatments as $treatment)
                                <option value="{{ $treatment->id }}" 
                                        {{ request('dental_treatment_id') == $treatment->id ? 'selected' : '' }}>
                                    {{ $treatment->title }} - {{ $treatment->patient->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> بحث
                        </button>
                        <a href="{{ route('dental.sessions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="card">
        <div class="card-body">
            @if($sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الجلسة</th>
                                <th>المريض</th>
                                <th>الطبيب</th>
                                <th>عنوان الجلسة</th>
                                <th>التاريخ المجدول</th>
                                <th>التكلفة</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr>
                                    <td>
                                        <strong>{{ $session->session_number }}</strong>
                                        <br>
                                        <small class="text-muted">الجلسة {{ $session->session_order }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $session->dentalTreatment->patient->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $session->dentalTreatment->patient->phone }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $session->dentalTreatment->doctor->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $session->dentalTreatment->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $session->session_title }}</strong>
                                            @if($session->session_description)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($session->session_description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $session->scheduled_date ? $session->scheduled_date->format('Y-m-d') : 'غير محدد' }}</strong>
                                            @if($session->duration)
                                                <br>
                                                <small class="text-muted">المدة: {{ $session->duration_display }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ number_format($session->session_cost, 2) }} ر.س</strong>
                                            @if($session->session_payment > 0)
                                                <br>
                                                <small class="text-{{ $session->payment_status_color }}">
                                                    {{ $session->payment_status }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $session->status_color }}">
                                            {{ $session->status_display }}
                                        </span>
                                        @if($session->is_overdue)
                                            <br>
                                            <small class="text-danger">متأخرة</small>
                                        @elseif($session->is_today)
                                            <br>
                                            <small class="text-warning">اليوم</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('dental.sessions.show', $session) }}" 
                                               class="btn btn-sm btn-outline-primary" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('update', $session)
                                                <a href="{{ route('dental.sessions.edit', $session) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="تعديل">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @if($session->canBeCompleted())
                                                <form method="POST" action="{{ route('dental.sessions.complete', $session) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success" 
                                                            title="تحديد كمكتملة"
                                                            onclick="return confirm('هل أنت متأكد من تحديد هذه الجلسة كمكتملة؟')">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $sessions->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h4 class="mt-3">لا توجد جلسات</h4>
                    <p class="text-muted">لم يتم العثور على جلسات تطابق معايير البحث</p>
                    @can('create', App\Models\DentalSession::class)
                        <a href="{{ route('dental.sessions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i>
                            إضافة جلسة جديدة
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-left: 2px;
}

.badge {
    font-size: 0.75em;
}

.table-responsive {
    border-radius: 0.5rem;
}
</style>
@endpush
@endsection