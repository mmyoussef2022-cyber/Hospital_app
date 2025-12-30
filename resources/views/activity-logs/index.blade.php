@extends('layouts.app')

@section('title', 'سجل العمليات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        سجل العمليات
                    </h3>
                    <div>
                        @can('users.manage')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                            <i class="fas fa-trash"></i>
                            حذف السجلات القديمة
                        </button>
                        @endcan
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- إحصائيات سريعة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ number_format($stats['total_logs']) }}</h4>
                                            <p class="mb-0">إجمالي السجلات</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-list fa-2x"></i>
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
                                            <h4>{{ number_format($stats['today_logs']) }}</h4>
                                            <p class="mb-0">سجلات اليوم</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar-day fa-2x"></i>
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
                                            <h4>{{ number_format($stats['this_week_logs']) }}</h4>
                                            <p class="mb-0">سجلات هذا الأسبوع</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar-week fa-2x"></i>
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
                                            <h4>{{ number_format($stats['unique_users']) }}</h4>
                                            <p class="mb-0">المستخدمين النشطين</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- فلاتر البحث -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-filter"></i>
                                فلاتر البحث
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('activity-logs.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">المستخدم</label>
                                        <select name="user_id" class="form-select">
                                            <option value="">جميع المستخدمين</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" 
                                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">نوع السجل</label>
                                        <select name="log_name" class="form-select">
                                            <option value="">جميع الأنواع</option>
                                            @foreach($logTypes as $type)
                                                <option value="{{ $type }}" 
                                                        {{ request('log_name') == $type ? 'selected' : '' }}>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">من تاريخ</label>
                                        <input type="date" name="date_from" class="form-control" 
                                               value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">إلى تاريخ</label>
                                        <input type="date" name="date_to" class="form-control" 
                                               value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                                بحث
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- جدول السجلات -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>التاريخ والوقت</th>
                                    <th>المستخدم</th>
                                    <th>نوع السجل</th>
                                    <th>الوصف</th>
                                    <th>النموذج</th>
                                    <th>عنوان IP</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</small>
                                    </td>
                                    <td>
                                        @if($log->user_name)
                                            <div>
                                                <strong>{{ $log->user_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $log->user_email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">نظام</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->log_name)
                                            <span class="badge bg-info">{{ $log->log_name }}</span>
                                        @else
                                            <span class="badge bg-secondary">عام</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($log->description, 50) }}</td>
                                    <td>
                                        @if($log->subject_type)
                                            <code>{{ class_basename($log->subject_type) }}</code>
                                            @if($log->subject_id)
                                                <br><small class="text-muted">ID: {{ $log->subject_id }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->properties)
                                            @php
                                                $properties = json_decode($log->properties, true);
                                                $ip = $properties['ip'] ?? null;
                                            @endphp
                                            {{ $ip ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('activity-logs.show', $log->id) }}" 
                                           class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-history fa-3x mb-3"></i>
                                        <p>لا توجد سجلات</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal حذف السجلات القديمة -->
@can('users.manage')
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">حذف السجلات القديمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clearLogsForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        تحذير: هذا الإجراء لا يمكن التراجع عنه!
                    </div>
                    <div class="mb-3">
                        <label class="form-label">حذف السجلات الأقدم من (بالأيام)</label>
                        <input type="number" name="days" class="form-control" min="1" max="365" value="30" required>
                        <div class="form-text">سيتم حذف جميع السجلات الأقدم من العدد المحدد من الأيام</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">حذف السجلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تفعيل tooltips
    $('[title]').tooltip();

    // معالجة نموذج حذف السجلات القديمة
    $('#clearLogsForm').on('submit', function(e) {
        e.preventDefault();
        
        const days = $(this).find('input[name="days"]').val();
        
        if (!confirm(`هل أنت متأكد من حذف جميع السجلات الأقدم من ${days} يوم؟`)) {
            return;
        }

        $.ajax({
            url: '{{ route("activity-logs.clear-old") }}',
            method: 'DELETE',
            data: {
                days: days,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                }
            },
            error: function() {
                alert('حدث خطأ أثناء حذف السجلات');
            }
        });
    });
});
</script>
@endpush