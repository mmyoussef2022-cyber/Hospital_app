@extends('layouts.app')

@section('title', 'الإشعارات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i>
                        الإشعارات
                    </h3>
                    <div class="card-tools">
                        @can('notifications.send')
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                                <i class="fas fa-plus"></i>
                                إرسال إشعار
                            </button>
                        @endcan
                        <button type="button" class="btn btn-success btn-sm" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i>
                            تحديد الكل كمقروء
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- إحصائيات سريعة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-bell"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">إجمالي الإشعارات</span>
                                    <span class="info-box-number">{{ $stats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">غير مقروءة</span>
                                    <span class="info-box-number">{{ $stats['unread'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">حرجة</span>
                                    <span class="info-box-number">{{ $stats['critical'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-cog"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">الإعدادات</span>
                                    <a href="{{ route('notifications.preferences') }}" class="btn btn-sm btn-outline-primary">
                                        تخصيص
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- فلاتر -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" class="d-flex gap-2">
                                <select name="type" class="form-select" style="width: auto;">
                                    <option value="">جميع الأنواع</option>
                                    @foreach(\App\Models\Notification::TYPES as $key => $value)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                <select name="status" class="form-select" style="width: auto;">
                                    <option value="">جميع الحالات</option>
                                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>غير مقروءة</option>
                                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>مقروءة</option>
                                </select>
                                
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-filter"></i>
                                    فلترة
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- قائمة الإشعارات -->
                    <div class="notifications-list">
                        @forelse($notifications as $notification)
                            <div class="notification-item {{ $notification->read_at ? '' : 'unread' }} priority-{{ $notification->priority }}" 
                                 data-notification-id="{{ $notification->id }}">
                                <div class="d-flex align-items-start">
                                    <div class="notification-icon me-3">
                                        @switch($notification->type)
                                            @case('appointment_reminder')
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                                @break
                                            @case('lab_critical')
                                                <i class="fas fa-flask text-danger"></i>
                                                @break
                                            @case('payment_reminder')
                                                <i class="fas fa-money-bill text-warning"></i>
                                                @break
                                            @default
                                                <i class="fas fa-bell text-info"></i>
                                        @endswitch
                                    </div>
                                    
                                    <div class="notification-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="notification-title mb-1">
                                                {{ $notification->title }}
                                                @if($notification->priority === 'critical')
                                                    <span class="badge bg-danger">حرج</span>
                                                @elseif($notification->priority === 'high')
                                                    <span class="badge bg-warning">عالي</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        
                                        <p class="notification-message mb-2">
                                            {{ Str::limit($notification->message, 100) }}
                                        </p>
                                        
                                        <div class="notification-actions">
                                            <a href="{{ route('notifications.show', $notification) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                عرض
                                            </a>
                                            
                                            @if(!$notification->read_at)
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="markAsRead({{ $notification->id }})">
                                                    تحديد كمقروء
                                                </button>
                                            @endif
                                            
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteNotification({{ $notification->id }})">
                                                حذف
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">لا توجد إشعارات</h5>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f8ff;
    border-right: 4px solid #007bff;
}

.notification-item.priority-critical {
    border-right: 4px solid #dc3545;
}

.notification-item.priority-high {
    border-right: 4px solid #fd7e14;
}

.notification-title {
    font-weight: 600;
}

.notification-message {
    color: #666;
    line-height: 1.4;
}

.notification-icon {
    font-size: 1.2em;
    width: 30px;
    text-align: center;
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteNotification(notificationId) {
    if (confirm('هل أنت متأكد من حذف هذا الإشعار؟')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}
</script>
@endsection