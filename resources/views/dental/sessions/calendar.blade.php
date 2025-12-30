@extends('layouts.app')

@section('title', 'تقويم الجلسات السنية')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تقويم الجلسات السنية</h3>
                    <div>
                        <a href="{{ route('dental.sessions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> عرض القائمة
                        </a>
                        <a href="{{ route('dental.sessions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة جلسة جديدة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Calendar Legend -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap">
                                <div class="mr-3 mb-2">
                                    <span class="badge" style="background-color: #007bff; color: white;">مجدولة</span>
                                </div>
                                <div class="mr-3 mb-2">
                                    <span class="badge" style="background-color: #28a745; color: white;">مكتملة</span>
                                </div>
                                <div class="mr-3 mb-2">
                                    <span class="badge" style="background-color: #dc3545; color: white;">ملغية</span>
                                </div>
                                <div class="mr-3 mb-2">
                                    <span class="badge" style="background-color: #ffc107; color: black;">لم يحضر</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Container -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">تفاصيل الجلسة</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                <a href="#" id="viewEventBtn" class="btn btn-primary">عرض التفاصيل</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
    }
    .fc-event:hover {
        opacity: 0.8;
    }
    .fc-toolbar-title {
        font-size: 1.2em !important;
    }
    .fc-button {
        font-size: 0.9em !important;
    }
    .fc-daygrid-event {
        font-size: 0.85em;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/ar.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ar',
        direction: 'rtl',
        initialView: 'dayGridMonth',
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
        events: {
            url: '{{ route("dental.sessions.calendar-data") }}',
            method: 'GET',
            failure: function() {
                alert('حدث خطأ في تحميل الجلسات');
            }
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            
            // Show event details in modal
            showEventDetails(info.event);
        },
        dateClick: function(info) {
            // Redirect to create new session with selected date
            var createUrl = '{{ route("dental.sessions.create") }}?date=' + info.dateStr;
            window.location.href = createUrl;
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.setAttribute('title', info.event.title);
        }
    });
    
    calendar.render();
    
    function showEventDetails(event) {
        var modalBody = document.getElementById('eventModalBody');
        var viewBtn = document.getElementById('viewEventBtn');
        
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-sm-4"><strong>العنوان:</strong></div>
                <div class="col-sm-8">${event.title}</div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4"><strong>التاريخ:</strong></div>
                <div class="col-sm-8">${event.start.toLocaleDateString('ar-SA')}</div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4"><strong>الوقت:</strong></div>
                <div class="col-sm-8">${event.start.toLocaleTimeString('ar-SA', {hour: '2-digit', minute: '2-digit'})}</div>
            </div>
        `;
        
        if (event.url) {
            viewBtn.href = event.url;
            viewBtn.style.display = 'inline-block';
        } else {
            viewBtn.style.display = 'none';
        }
        
        $('#eventModal').modal('show');
    }
});
</script>
@endpush