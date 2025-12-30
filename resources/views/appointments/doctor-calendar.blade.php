@extends('layouts.app')

@section('title', 'تقويم مواعيد الطبيب')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-calendar3 text-primary"></i>
                        تقويم المواعيد
                    </h1>
                    <p class="text-muted mb-0">إدارة مواعيد الطبيب مع إمكانية السحب والإفلات</p>
                </div>
                <div class="d-flex gap-2">
                    @can('appointments.create')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickCreateModal" {{ !$doctor ? 'disabled' : '' }}>
                        <i class="bi bi-plus-circle"></i>
                        موعد سريع
                    </button>
                    @endcan
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-list"></i>
                        عرض القائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(isset($error))
        <!-- Error State -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ $error }}</h4>
                    <p class="mb-0">يرجى التواصل مع المدير لإضافة أطباء إلى النظام.</p>
                    <a href="{{ route('appointments.index') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-right"></i>
                        العودة إلى قائمة المواعيد
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Doctor Selection and Info -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="doctorSelect" class="form-label">اختيار الطبيب</label>
                                <select id="doctorSelect" class="form-select">
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" 
                                                {{ $doc->id == $doctor->id ? 'selected' : '' }}
                                                data-specialization="{{ $doc->doctor?->specialization ?? '' }}">
                                            {{ $doc->name }} - {{ $doc->doctor?->specialization ?? 'غير محدد' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($doctor->doctor?->profile_photo)
                                            <img src="{{ $doctor->doctor->profile_photo_url }}" 
                                                 alt="{{ $doctor->name }}" 
                                                 class="rounded-circle" 
                                                 width="50" height="50">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-person-fill text-white"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $doctor->name }}</h6>
                                        <small class="text-muted">{{ $doctor->doctor?->specialization ?? 'غير محدد' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">إحصائيات اليوم</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-primary">
                                    <i class="bi bi-calendar-check" style="font-size: 1.5rem;"></i>
                                    <div class="mt-1">
                                        <strong id="todayCount">0</strong>
                                        <small class="d-block">مواعيد اليوم</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-success">
                                    <i class="bi bi-clock" style="font-size: 1.5rem;"></i>
                                    <div class="mt-1">
                                        <strong id="availableSlots">0</strong>
                                        <small class="d-block">فترات متاحة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar3"></i>
                                التقويم التفاعلي
                            </h5>
                            <div class="d-flex gap-2">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="monthView">شهر</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="weekView">أسبوع</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="dayView">يوم</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Calendar Legend -->
                        <div class="mb-3">
                            <small class="text-muted">الألوان:</small>
                            <span class="badge bg-primary ms-2">مجدول</span>
                            <span class="badge bg-success ms-2">مؤكد</span>
                            <span class="badge bg-warning ms-2">جاري</span>
                            <span class="badge bg-info ms-2">مكتمل</span>
                            <span class="badge bg-danger ms-2">ملغي</span>
                            <span class="badge bg-secondary ms-2">لم يحضر</span>
                        </div>
                        
                        <!-- Calendar Container -->
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Quick Create Appointment Modal -->
<div class="modal fade" id="quickCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إنشاء موعد سريع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCreateForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quickPatient" class="form-label">المريض *</label>
                            <select id="quickPatient" name="patient_id" class="form-select" required>
                                <option value="">اختر المريض</option>
                                @foreach(\App\Models\Patient::all() as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }} - {{ $patient->phone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quickType" class="form-label">نوع الموعد</label>
                            <select id="quickType" name="type" class="form-select">
                                <option value="consultation">استشارة</option>
                                <option value="follow_up">متابعة</option>
                                <option value="emergency">طوارئ</option>
                                <option value="surgery">جراحة</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quickDate" class="form-label">التاريخ *</label>
                            <input type="date" id="quickDate" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quickTime" class="form-label">الوقت *</label>
                            <input type="time" id="quickTime" name="time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="quickDuration" class="form-label">المدة (دقيقة)</label>
                        <select id="quickDuration" name="duration" class="form-select">
                            <option value="15">15 دقيقة</option>
                            <option value="30" selected>30 دقيقة</option>
                            <option value="45">45 دقيقة</option>
                            <option value="60">60 دقيقة</option>
                            <option value="90">90 دقيقة</option>
                            <option value="120">120 دقيقة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quickNotes" class="form-label">ملاحظات</label>
                        <textarea id="quickNotes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i>
                        إنشاء الموعد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الموعد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentDetails">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" id="editAppointmentBtn">
                    <i class="bi bi-pencil"></i>
                    تعديل
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
    }
    
    .fc-event:hover {
        opacity: 0.8;
    }
    
    .fc-daygrid-event {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .fc-timegrid-event {
        border-radius: 3px;
    }
    
    .fc-toolbar-title {
        font-size: 1.2rem !important;
    }
    
    .fc-button {
        font-size: 0.875rem;
    }
    
    .fc-today-button {
        background-color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
    }
    
    .fc-business-hours {
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .fc-non-business {
        background-color: rgba(108, 117, 125, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let calendar;
    let currentDoctorId = {{ $doctor->id }};
    
    // Initialize calendar
    initializeCalendar();
    
    // Doctor selection change
    document.getElementById('doctorSelect').addEventListener('change', function() {
        currentDoctorId = this.value;
        if (currentDoctorId) {
            loadDoctorWorkingHours();
            calendar.refetchEvents();
            updateDoctorInfo();
        }
    });
    
    // View buttons
    document.getElementById('monthView').addEventListener('click', () => calendar.changeView('dayGridMonth'));
    document.getElementById('weekView').addEventListener('click', () => calendar.changeView('timeGridWeek'));
    document.getElementById('dayView').addEventListener('click', () => calendar.changeView('timeGridDay'));
    
    // Quick create form
    document.getElementById('quickCreateForm').addEventListener('submit', handleQuickCreate);
    
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
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
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            slotDuration: '00:30:00',
            snapDuration: '00:15:00',
            height: 'auto',
            editable: true,
            droppable: true,
            eventResizableFromStart: true,
            eventDurationEditable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            
            events: function(info, successCallback, failureCallback) {
                fetch(`{{ route('appointments.calendar') }}?start=${info.startStr}&end=${info.endStr}&doctor_id=${currentDoctorId}`)
                    .then(response => response.json())
                    .then(data => {
                        successCallback(data);
                        updateTodayStats(data);
                    })
                    .catch(error => {
                        console.error('Error loading events:', error);
                        failureCallback(error);
                    });
            },
            
            eventClick: function(info) {
                showAppointmentDetails(info.event);
            },
            
            eventDrop: function(info) {
                moveAppointment(info.event, info.event.start);
            },
            
            eventResize: function(info) {
                resizeAppointment(info.event, info.event.start, info.event.end);
            },
            
            select: function(info) {
                openQuickCreateModal(info.start);
            },
            
            eventDidMount: function(info) {
                // Add tooltip
                info.el.setAttribute('title', 
                    `${info.event.extendedProps.patient_name}\n` +
                    `النوع: ${info.event.extendedProps.type}\n` +
                    `الحالة: ${info.event.extendedProps.status}`
                );
            }
        });
        
        calendar.render();
        loadDoctorWorkingHours();
    }
    
    function loadDoctorWorkingHours() {
        fetch(`{{ route('appointments.doctor-working-hours') }}?doctor_id=${currentDoctorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.setOption('businessHours', data.working_hours);
                }
            })
            .catch(error => console.error('Error loading working hours:', error));
    }
    
    function moveAppointment(event, newStart) {
        const appointmentId = event.extendedProps.appointment_id;
        
        fetch(`/appointments/${appointmentId}/move`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                start: newStart.toISOString(),
                doctor_id: currentDoctorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert the event
                event.revert();
                showAlert('error', data.message);
            } else {
                showAlert('success', data.message);
            }
        })
        .catch(error => {
            event.revert();
            showAlert('error', 'حدث خطأ أثناء نقل الموعد');
        });
    }
    
    function resizeAppointment(event, start, end) {
        const appointmentId = event.extendedProps.appointment_id;
        
        fetch(`/appointments/${appointmentId}/resize`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                start: start.toISOString(),
                end: end.toISOString()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                event.revert();
                showAlert('error', data.message);
            } else {
                showAlert('success', data.message);
            }
        })
        .catch(error => {
            event.revert();
            showAlert('error', 'حدث خطأ أثناء تغيير مدة الموعد');
        });
    }
    
    function openQuickCreateModal(selectedDate) {
        const modal = new bootstrap.Modal(document.getElementById('quickCreateModal'));
        
        // Set default date and time
        const date = selectedDate.toISOString().split('T')[0];
        const time = selectedDate.toTimeString().slice(0, 5);
        
        document.getElementById('quickDate').value = date;
        document.getElementById('quickTime').value = time;
        
        modal.show();
    }
    
    function handleQuickCreate(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const dateTime = new Date(`${formData.get('date')}T${formData.get('time')}`);
        
        const data = {
            doctor_id: currentDoctorId,
            patient_id: formData.get('patient_id'),
            start: dateTime.toISOString(),
            duration: formData.get('duration'),
            type: formData.get('type'),
            notes: formData.get('notes')
        };
        
        fetch('{{ route("appointments.quick-create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add event to calendar
                calendar.addEvent(data.appointment);
                
                // Close modal and reset form
                bootstrap.Modal.getInstance(document.getElementById('quickCreateModal')).hide();
                e.target.reset();
                
                showAlert('success', data.message);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showAlert('error', 'حدث خطأ أثناء إنشاء الموعد');
        });
    }
    
    function showAppointmentDetails(event) {
        const props = event.extendedProps;
        const start = event.start;
        const end = event.end;
        
        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6>معلومات المريض</h6>
                    <p><strong>الاسم:</strong> ${props.patient_name}</p>
                    <p><strong>الهاتف:</strong> ${props.patient_phone || 'غير محدد'}</p>
                </div>
                <div class="col-md-6">
                    <h6>معلومات الموعد</h6>
                    <p><strong>النوع:</strong> ${props.type}</p>
                    <p><strong>الحالة:</strong> <span class="badge bg-${getStatusColor(event.backgroundColor)}">${props.status}</span></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>التاريخ:</strong> ${start.toLocaleDateString('ar-SA')}</p>
                    <p><strong>الوقت:</strong> ${start.toLocaleTimeString('ar-SA', {hour: '2-digit', minute: '2-digit'})}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>المدة:</strong> ${props.duration} دقيقة</p>
                    <p><strong>الطبيب:</strong> ${props.doctor_name}</p>
                </div>
            </div>
            ${props.notes ? `<div class="row"><div class="col-12"><h6>الملاحظات</h6><p>${props.notes}</p></div></div>` : ''}
        `;
        
        document.getElementById('appointmentDetails').innerHTML = detailsHtml;
        document.getElementById('editAppointmentBtn').onclick = () => {
            window.location.href = `/appointments/${props.appointment_id}/edit`;
        };
        
        new bootstrap.Modal(document.getElementById('appointmentModal')).show();
    }
    
    function updateTodayStats(events) {
        const today = new Date().toDateString();
        const todayEvents = events.filter(event => 
            new Date(event.start).toDateString() === today
        );
        
        document.getElementById('todayCount').textContent = todayEvents.length;
        
        // Calculate available slots (simplified)
        const workingHours = 8; // 8 hours
        const slotDuration = 0.5; // 30 minutes
        const totalSlots = workingHours / slotDuration;
        const availableSlots = Math.max(0, totalSlots - todayEvents.length);
        
        document.getElementById('availableSlots').textContent = Math.floor(availableSlots);
    }
    
    function updateDoctorInfo() {
        const select = document.getElementById('doctorSelect');
        const selectedOption = select.options[select.selectedIndex];
        
        // Update doctor info display if needed
        // This would require additional AJAX call to get doctor details
    }
    
    function getStatusColor(backgroundColor) {
        const colorMap = {
            '#007bff': 'primary',
            '#28a745': 'success',
            '#ffc107': 'warning',
            '#17a2b8': 'info',
            '#dc3545': 'danger',
            '#6c757d': 'secondary'
        };
        return colorMap[backgroundColor] || 'primary';
    }
    
    function showAlert(type, message) {
        // Create and show alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Load initial working hours
    loadDoctorWorkingHours();
});
</script>
@endpush