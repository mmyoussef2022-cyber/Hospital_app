@extends('layouts.app')

@section('title', 'سجلات الأمان')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-list-alt me-2"></i>
                        سجلات الأمان
                    </h2>
                    <p class="text-muted mb-0">عرض وفلترة سجلات الأحداث الأمنية</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-download me-1"></i>
                        تصدير السجلات
                    </button>
                    <a href="{{ route('security.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('security.logs') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">المستوى</label>
                    <select name="level" class="form-select">
                        <option value="">جميع المستويات</option>
                        <option value="info" {{ request('level') == 'info' ? 'selected' : '' }}>معلومات</option>
                        <option value="warning" {{ request('level') == 'warning' ? 'selected' : '' }}>تحذير</option>
                        <option value="error" {{ request('level') == 'error' ? 'selected' : '' }}>خطأ</option>
                        <option value="critical" {{ request('level') == 'critical' ? 'selected' : '' }}>حرج</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">نوع الحدث</label>
                    <select name="event_type" class="form-select">
                        <option value="">جميع الأنواع</option>
                        @foreach($eventTypes as $type)
                        <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                        @endforeach
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

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('security.logs') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>المستوى</th>
                            <th>نوع الحدث</th>
                            <th>الوصف</th>
                            <th>المستخدم</th>
                            <th>عنوان IP</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                @switch($log->level)
                                    @case('info')
                                        <span class="badge bg-info">معلومات</span>
                                        @break
                                    @case('warning')
                                        <span class="badge bg-warning">تحذير</span>
                                        @break
                                    @case('error')
                                        <span class="badge bg-danger">خطأ</span>
                                        @break
                                    @case('critical')
                                        <span class="badge bg-dark">حرج</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $log->level }}</span>
                                @endswitch
                            </td>
                            <td>
                                <code class="text-primary">{{ $log->event_type }}</code>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 300px;" title="{{ $log->description }}">
                                    {{ $log->description }}
                                </div>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $log->user->name }}</div>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td>
                                <code>{{ $log->ip_address }}</code>
                            </td>
                            <td>
                                <div>{{ $log->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showEventDetails({{ $log->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($log->ip_address)
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="blockIp('{{ $log->ip_address }}')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    لا توجد سجلات متاحة
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($logs->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الحدث الأمني</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تصدير السجلات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="exportForm" action="{{ route('security.export-logs') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">تنسيق التصدير</label>
                        <select name="format" class="form-select" required>
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تصدير</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div class="modal fade" id="blockIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">حظر عنوان IP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="blockIpForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">عنوان IP</label>
                        <input type="text" class="form-control" id="ipAddress" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب الحظر</label>
                        <textarea class="form-control" id="blockReason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">حظر IP</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.table-responsive {
    border-radius: 0.375rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endpush

@push('scripts')
<script>
function showEventDetails(eventId) {
    fetch(`/security/events/${eventId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('eventDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'فشل في تحميل تفاصيل الحدث'
            });
        });
}

function blockIp(ip) {
    document.getElementById('ipAddress').value = ip;
    new bootstrap.Modal(document.getElementById('blockIpModal')).show();
}

// Block IP Form
document.getElementById('blockIpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const ip = document.getElementById('ipAddress').value;
    const reason = document.getElementById('blockReason').value;
    
    fetch('{{ route("security.block-ip") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            ip_address: ip,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'تم بنجاح',
                text: data.message
            });
            bootstrap.Modal.getInstance(document.getElementById('blockIpModal')).hide();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: data.message
            });
        }
    });
});
</script>
@endpush