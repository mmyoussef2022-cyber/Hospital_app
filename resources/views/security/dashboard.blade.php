@extends('layouts.app')

@section('title', 'لوحة تحكم الأمان')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-shield-alt me-2"></i>
                        لوحة تحكم الأمان والمراقبة
                    </h2>
                    <p class="text-muted mb-0">مراقبة وإدارة أمان النظام</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="performHealthCheck()">
                        <i class="fas fa-heartbeat me-1"></i>
                        فحص صحة النظام
                    </button>
                    <button type="button" class="btn btn-success" onclick="createBackup()">
                        <i class="fas fa-download me-1"></i>
                        نسخة احتياطية
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-exclamation-triangle text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">الأحداث الأمنية</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_security_events']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-times-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">الأحداث الحرجة اليوم</h6>
                            <h3 class="mb-0 text-danger">{{ number_format($stats['critical_events_today']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-user-times text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">محاولات دخول فاشلة اليوم</h6>
                            <h3 class="mb-0 text-warning">{{ number_format($stats['failed_logins_today']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-globe text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">عناوين IP فريدة اليوم</h6>
                            <h3 class="mb-0 text-info">{{ number_format($stats['unique_ips_today']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        إحصائيات الأسبوع الماضي
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyStatsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-network-wired me-2 text-primary"></i>
                        أكثر عناوين IP نشاطاً
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($topIps as $ip)
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-medium">{{ $ip->ip_address }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary">{{ $ip->attempts }}</span>
                                    <button class="btn btn-sm btn-outline-danger" onclick="blockIp('{{ $ip->ip_address }}')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-info-circle me-1"></i>
                            لا توجد بيانات متاحة
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                            الأحداث الحرجة الأخيرة
                        </h5>
                        <a href="{{ route('security.logs') }}" class="btn btn-sm btn-outline-primary">
                            عرض الكل
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($criticalEvents as $event)
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-exclamation text-danger"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $event->event_type }}</h6>
                                    <p class="text-muted mb-1 small">{{ $event->description }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $event->user ? $event->user->name : 'غير محدد' }}
                                        </small>
                                        <small class="text-muted">
                                            {{ $event->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle me-1 text-success"></i>
                            لا توجد أحداث حرجة
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-times me-2 text-warning"></i>
                            محاولات الدخول الفاشلة
                        </h5>
                        <a href="{{ route('security.login-attempts') }}" class="btn btn-sm btn-outline-primary">
                            عرض الكل
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($failedLogins as $attempt)
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-times text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $attempt->email }}</h6>
                                    <p class="text-muted mb-1 small">
                                        <i class="fas fa-globe me-1"></i>
                                        {{ $attempt->ip_address }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            {{ $attempt->failure_reason ?? 'بيانات خاطئة' }}
                                        </small>
                                        <small class="text-muted">
                                            {{ $attempt->attempted_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle me-1 text-success"></i>
                            لا توجد محاولات فاشلة
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
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

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تنظيف السجلات القديمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cleanupForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الاحتفاظ بالسجلات لعدد الأيام</label>
                        <input type="number" class="form-control" id="daysToKeep" value="90" min="30" max="365" required>
                        <div class="form-text">سيتم حذف السجلات الأقدم من هذا التاريخ</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">تنظيف السجلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Weekly Stats Chart
const ctx = document.getElementById('weeklyStatsChart').getContext('2d');
const weeklyStatsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($weeklyStats['days']),
        datasets: [{
            label: 'الأحداث الأمنية',
            data: @json($weeklyStats['security_events']),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4
        }, {
            label: 'محاولات تسجيل الدخول',
            data: @json($weeklyStats['login_attempts']),
            borderColor: '#fd7e14',
            backgroundColor: 'rgba(253, 126, 20, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Functions
function performHealthCheck() {
    fetch('{{ route("security.health-check") }}')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'healthy') {
                Swal.fire({
                    icon: 'success',
                    title: 'النظام سليم',
                    text: 'لم يتم العثور على مشاكل أمنية'
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'تم العثور على مشاكل',
                    html: data.issues.join('<br>')
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'فشل في فحص صحة النظام'
            });
        });
}

function createBackup() {
    Swal.fire({
        title: 'إنشاء نسخة احتياطية',
        text: 'هل تريد إنشاء نسخة احتياطية من بيانات الأمان؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'نعم، إنشاء',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("security.create-backup") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح',
                        text: data.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: data.message
                    });
                }
            });
        }
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

// Cleanup Form
document.getElementById('cleanupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const days = document.getElementById('daysToKeep').value;
    
    fetch('{{ route("security.cleanup-logs") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            days: days
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
            bootstrap.Modal.getInstance(document.getElementById('cleanupModal')).hide();
            location.reload();
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