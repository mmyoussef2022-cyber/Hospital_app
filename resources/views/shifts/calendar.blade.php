@extends('layouts.app')

@section('title', 'تقويم الورديات')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">تقويم الورديات</h1>
            <p class="text-muted">عرض وإدارة الورديات في التقويم</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                إضافة وردية جديدة
            </a>
            <a href="{{ route('shifts.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-1"></i>
                لوحة التحكم
            </a>
            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i>
                قائمة الورديات
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="department_filter" class="form-label">القسم</label>
                    <select class="form-select" id="department_filter">
                        <option value="">جميع الأقسام</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="user_filter" class="form-label">الموظف</label>
                    <select class="form-select" id="user_filter">
                        <option value="">جميع الموظفين</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">الحالة</label>
                    <select class="form-select" id="status_filter">
                        <option value="">جميع الحالات</option>
                        <option value="scheduled">مجدولة</option>
                        <option value="active">نشطة</option>
                        <option value="completed">مكتملة</option>
                        <option value="cancelled">ملغية</option>
                        <option value="no_show">غياب</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-primary w-100" id="apply_filters">
                        <i class="fas fa-filter me-1"></i>
                        تطبيق الفلاتر
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Shift Details Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1" aria-labelledby="shiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shiftModalLabel">تفاصيل الوردية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="shiftModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
    }
    .fc-event:hover {
        opacity: 0.8;
    }
    .fc-toolbar-title {
        font-size: 1.2rem !important;
    }
    .fc-button {
        font-size: 0.875rem !important;
    }
    .fc-daygrid-event {
        font-size: 0.8rem;
    }
    .shift-status-scheduled { background-color: #007bff !important; border-color: #007bff !important; }
    .shift-status-active { background-color: #28a745 !important; border-color: #28a745 !important; }
    .shift-status-completed { background-color: #6c757d !important; border-color: #6c757d !important; }
    .shift-status-cancelled { background-color: #dc3545 !important; border-color: #dc3545 !important; }
    .shift-status-no_show { background-color: #fd7e14 !important; border-color: #fd7e14 !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    let calendar;

    // Initialize calendar
    function initCalendar() {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'ar',
            direction: 'rtl',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'اليوم',
                month: 'شهر',
                week: 'أسبوع',
                day: 'يوم'
            },
            height: 'auto',
            events: function(fetchInfo, successCallback, failureCallback) {
                loadEvents(fetchInfo.startStr, fetchInfo.endStr, successCallback, failureCallback);
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                showShiftDetails(info.event.id);
            },
            eventDidMount: function(info) {
                // Add status class to event element
                const status = info.event.extendedProps.status;
                if (status) {
                    info.el.classList.add('shift-status-' + status.toLowerCase().replace(' ', '_'));
                }
                
                // Add tooltip
                info.el.setAttribute('title', 
                    info.event.title + '\n' +
                    'الحالة: ' + (info.event.extendedProps.status || '') + '\n' +
                    'القسم: ' + (info.event.extendedProps.department || '') + '\n' +
                    'الإيرادات: ' + (info.event.extendedProps.revenue || '0') + ' ريال'
                );
            },
            dateClick: function(info) {
                // Redirect to create shift with pre-filled date
                window.location.href = '{{ route("shifts.create") }}?shift_date=' + info.dateStr;
            }
        });

        calendar.render();
    }

    // Load events from server
    function loadEvents(start, end, successCallback, failureCallback) {
        const params = new URLSearchParams({
            start: start,
            end: end
        });

        // Add filters
        const departmentFilter = document.getElementById('department_filter').value;
        const userFilter = document.getElementById('user_filter').value;
        const statusFilter = document.getElementById('status_filter').value;

        if (departmentFilter) params.append('department_id', departmentFilter);
        if (userFilter) params.append('user_id', userFilter);
        if (statusFilter) params.append('status', statusFilter);

        fetch('{{ route("shifts.calendar") }}?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            successCallback(data);
        })
        .catch(error => {
            console.error('Error loading events:', error);
            failureCallback(error);
        });
    }

    // Show shift details in modal
    function showShiftDetails(shiftId) {
        const modal = new bootstrap.Modal(document.getElementById('shiftModal'));
        const modalBody = document.getElementById('shiftModalBody');
        
        modalBody.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>';
        modal.show();

        fetch(`/shifts/${shiftId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Extract the main content from the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const content = doc.querySelector('.container-fluid');
            if (content) {
                modalBody.innerHTML = content.innerHTML;
            } else {
                modalBody.innerHTML = '<p>حدث خطأ في تحميل البيانات</p>';
            }
        })
        .catch(error => {
            console.error('Error loading shift details:', error);
            modalBody.innerHTML = '<p>حدث خطأ في تحميل البيانات</p>';
        });
    }

    // Apply filters
    document.getElementById('apply_filters').addEventListener('click', function() {
        if (calendar) {
            calendar.refetchEvents();
        }
    });

    // Initialize calendar
    initCalendar();
});
</script>
@endpush