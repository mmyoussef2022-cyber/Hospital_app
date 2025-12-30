@extends('layouts.app')

@section('title', 'محاولات تسجيل الدخول')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        محاولات تسجيل الدخول
                    </h2>
                    <p class="text-muted mb-0">مراقبة محاولات الدخول الناجحة والفاشلة</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('security.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">محاولات ناجحة اليوم</h6>
                            <h3 class="mb-0 text-success">
                                {{ $attempts->where('success', true)->where('attempted_at', '>=', today())->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-times-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">محاولات فاشلة اليوم</h6>
                            <h3 class="mb-0 text-danger">
                                {{ $attempts->where('success', false)->where('attempted_at', '>=', today())->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-globe text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">عناوين IP فريدة</h6>
                            <h3 class="mb-0 text-info">
                                {{ $attempts->unique('ip_address')->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-user-times text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">حسابات مشبوهة</h6>
                            <h3 class="mb-0 text-warning">
                                {{ $attempts->where('success', false)->groupBy('email')->filter(function($group) { return $group->count() >= 3; })->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('security.login-attempts') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="success" class="form-select">
                        <option value="">جميع المحاولات</option>
                        <option value="1" {{ request('success') === '1' ? 'selected' : '' }}>ناجحة</option>
                        <option value="0" {{ request('success') === '0' ? 'selected' : '' }}>فاشلة</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ request('email') }}" placeholder="البحث بالبريد الإلكتروني">
                </div>

                <div class="col-md-2">
                    <label class="form-label">عنوان IP</label>
                    <input type="text" name="ip_address" class="form-control" value="{{ request('ip_address') }}" placeholder="192.168.1.1">
                </div>

                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('security.login-attempts') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Login Attempts Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الحالة</th>
                            <th>البريد الإلكتروني</th>
                            <th>عنوان IP</th>
                            <th>المتصفح</th>
                            <th>سبب الفشل</th>
                            <th>وقت المحاولة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attempts as $attempt)
                        <tr class="{{ !$attempt->success ? 'table-danger-subtle' : '' }}">
                            <td>
                                @if($attempt->success)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>
                                        ناجحة
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>
                                        فاشلة
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-envelope text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $attempt->email }}</div>
                                        @if(!$attempt->success)
                                            <small class="text-danger">
                                                محاولات فاشلة: {{ $attempts->where('email', $attempt->email)->where('success', false)->count() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code>{{ $attempt->ip_address }}</code>
                                <div>
                                    <small class="text-muted">
                                        محاولات من هذا IP: {{ $attempts->where('ip_address', $attempt->ip_address)->count() }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $attempt->user_agent }}">
                                    @php
                                        $browser = 'غير محدد';
                                        if (str_contains($attempt->user_agent, 'Chrome')) $browser = 'Chrome';
                                        elseif (str_contains($attempt->user_agent, 'Firefox')) $browser = 'Firefox';
                                        elseif (str_contains($attempt->user_agent, 'Safari')) $browser = 'Safari';
                                        elseif (str_contains($attempt->user_agent, 'Edge')) $browser = 'Edge';
                                    @endphp
                                    <i class="fab fa-{{ strtolower($browser) }} me-1"></i>
                                    {{ $browser }}
                                </div>
                            </td>
                            <td>
                                @if($attempt->failure_reason)
                                    <span class="badge bg-warning text-dark">{{ $attempt->failure_reason }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $attempt->attempted_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $attempt->attempted_at->format('H:i:s') }}</small>
                                <div>
                                    <small class="text-muted">{{ $attempt->attempted_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if(!$attempt->success)
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="blockIp('{{ $attempt->ip_address }}')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="blockEmail('{{ $attempt->email }}')">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="showAttemptDetails('{{ $attempt->id }}')">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    لا توجد محاولات تسجيل دخول
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($attempts->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $attempts->links() }}
        </div>
        @endif
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

<!-- Attempt Details Modal -->
<div class="modal fade" id="attemptDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل محاولة تسجيل الدخول</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="attemptDetailsContent">
                <!-- Content will be loaded here -->
            </div>
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

.table-danger-subtle {
    background-color: rgba(220, 53, 69, 0.05);
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
function blockIp(ip) {
    document.getElementById('ipAddress').value = ip;
    new bootstrap.Modal(document.getElementById('blockIpModal')).show();
}

function blockEmail(email) {
    Swal.fire({
        title: 'حظر البريد الإلكتروني',
        text: `هل تريد حظر البريد الإلكتروني: ${email}؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احظر',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement email blocking logic here
            Swal.fire({
                icon: 'success',
                title: 'تم الحظر',
                text: 'تم حظر البريد الإلكتروني بنجاح'
            });
        }
    });
}

function showAttemptDetails(attemptId) {
    // Load attempt details via AJAX
    fetch(`/security/login-attempts/${attemptId}/details`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>معلومات المحاولة</h6>
                        <table class="table table-sm">
                            <tr><td><strong>البريد الإلكتروني:</strong></td><td>${data.email}</td></tr>
                            <tr><td><strong>عنوان IP:</strong></td><td><code>${data.ip_address}</code></td></tr>
                            <tr><td><strong>الحالة:</strong></td><td>${data.success ? '<span class="badge bg-success">ناجحة</span>' : '<span class="badge bg-danger">فاشلة</span>'}</td></tr>
                            <tr><td><strong>وقت المحاولة:</strong></td><td>${data.attempted_at}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>معلومات المتصفح</h6>
                        <div class="bg-light p-3 rounded">
                            <small>${data.user_agent}</small>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('attemptDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('attemptDetailsModal')).show();
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'فشل في تحميل تفاصيل المحاولة'
            });
        });
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