@extends('layouts.app')

@section('title', 'إدارة ساعات العمل - ' . $doctor->user->name)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-clock text-primary"></i>
                        إدارة ساعات العمل
                    </h1>
                    <p class="text-muted mb-0">تحديد أوقات العمل للطبيب {{ $doctor->user->name }}</p>
                </div>
                <div>
                    <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للملف الشخصي
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctor Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @if($doctor->profile_photo)
                                <img src="{{ $doctor->profile_photo_url }}" 
                                     alt="{{ $doctor->user->name }}" 
                                     class="rounded-circle" 
                                     width="60" height="60">
                            @else
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-person-fill text-white fs-4"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col">
                            <h5 class="mb-1">{{ $doctor->user->name }}</h5>
                            <p class="text-muted mb-0">
                                {{ $doctor->specialization }} - 
                                {{ $doctor->user->department->name ?? 'غير محدد' }}
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <span class="me-2">الحالة:</span>
                                <span class="badge bg-{{ $doctor->is_available ? 'success' : 'danger' }}">
                                    {{ $doctor->is_available ? 'متاح' : 'غير متاح' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Working Hours Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week"></i>
                        ساعات العمل الأسبوعية
                    </h5>
                </div>
                <div class="card-body">
                    <form id="workingHoursForm">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            @php
                                $days = [
                                    'sunday' => 'الأحد',
                                    'monday' => 'الاثنين',
                                    'tuesday' => 'الثلاثاء',
                                    'wednesday' => 'الأربعاء',
                                    'thursday' => 'الخميس',
                                    'friday' => 'الجمعة',
                                    'saturday' => 'السبت'
                                ];
                                $workingHours = $doctor->working_hours ?? [];
                            @endphp
                            
                            @foreach($days as $dayKey => $dayName)
                                @php
                                    $dayHours = $workingHours[$dayKey] ?? ['is_working' => false, 'start' => '08:00', 'end' => '17:00'];
                                @endphp
                                
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ $dayName }}</h6>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input day-toggle" 
                                                           type="checkbox" 
                                                           id="working_{{ $dayKey }}"
                                                           name="working_hours[{{ $dayKey }}][is_working]"
                                                           value="1"
                                                           {{ $dayHours['is_working'] ? 'checked' : '' }}
                                                           data-day="{{ $dayKey }}">
                                                    <label class="form-check-label" for="working_{{ $dayKey }}">
                                                        <small>يوم عمل</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body py-3">
                                            <div class="time-inputs" id="times_{{ $dayKey }}" 
                                                 style="{{ $dayHours['is_working'] ? '' : 'display: none;' }}">
                                                <div class="mb-3">
                                                    <label for="start_{{ $dayKey }}" class="form-label">
                                                        <i class="bi bi-clock"></i>
                                                        بداية العمل
                                                    </label>
                                                    <input type="time" 
                                                           class="form-control" 
                                                           id="start_{{ $dayKey }}"
                                                           name="working_hours[{{ $dayKey }}][start]"
                                                           value="{{ $dayHours['start'] ?? '08:00' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="end_{{ $dayKey }}" class="form-label">
                                                        <i class="bi bi-clock-fill"></i>
                                                        نهاية العمل
                                                    </label>
                                                    <input type="time" 
                                                           class="form-control" 
                                                           id="end_{{ $dayKey }}"
                                                           name="working_hours[{{ $dayKey }}][end]"
                                                           value="{{ $dayHours['end'] ?? '17:00' }}">
                                                </div>
                                                <div class="text-center">
                                                    <small class="text-muted duration-display" id="duration_{{ $dayKey }}">
                                                        <!-- Duration will be calculated by JS -->
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="non-working-day" id="nonworking_{{ $dayKey }}" 
                                                 style="{{ $dayHours['is_working'] ? 'display: none;' : '' }}">
                                                <div class="text-center text-muted py-4">
                                                    <i class="bi bi-x-circle fs-1"></i>
                                                    <p class="mb-0">يوم راحة</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">إعدادات سريعة</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="setWeekdays">
                                                أيام الأسبوع (الأحد - الخميس)
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="setAllDays">
                                                جميع الأيام
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm" id="clearAll">
                                                إلغاء جميع الأيام
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" id="setMorningShift">
                                                وردية صباحية (8:00 - 14:00)
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm" id="setEveningShift">
                                                وردية مسائية (14:00 - 20:00)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary" id="previewSchedule">
                                            <i class="bi bi-eye"></i>
                                            معاينة الجدول
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                            إلغاء
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i>
                                            حفظ ساعات العمل
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">معاينة جدول العمل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>اليوم</th>
                                <th>الحالة</th>
                                <th>بداية العمل</th>
                                <th>نهاية العمل</th>
                                <th>إجمالي الساعات</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Content will be populated by JavaScript -->
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4">إجمالي ساعات العمل الأسبوعية</th>
                                <th id="totalWeeklyHours">0 ساعة</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('workingHoursForm');
    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const dayNames = {
        'sunday': 'الأحد',
        'monday': 'الاثنين', 
        'tuesday': 'الثلاثاء',
        'wednesday': 'الأربعاء',
        'thursday': 'الخميس',
        'friday': 'الجمعة',
        'saturday': 'السبت'
    };
    
    // Initialize duration calculations
    days.forEach(day => {
        calculateDuration(day);
        
        // Add event listeners for time changes
        const startInput = document.getElementById(`start_${day}`);
        const endInput = document.getElementById(`end_${day}`);
        
        if (startInput && endInput) {
            startInput.addEventListener('change', () => calculateDuration(day));
            endInput.addEventListener('change', () => calculateDuration(day));
        }
    });
    
    // Day toggle handlers
    document.querySelectorAll('.day-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const day = this.dataset.day;
            const timesDiv = document.getElementById(`times_${day}`);
            const nonWorkingDiv = document.getElementById(`nonworking_${day}`);
            
            if (this.checked) {
                timesDiv.style.display = 'block';
                nonWorkingDiv.style.display = 'none';
                calculateDuration(day);
            } else {
                timesDiv.style.display = 'none';
                nonWorkingDiv.style.display = 'block';
            }
        });
    });
    
    // Quick action buttons
    document.getElementById('setWeekdays').addEventListener('click', function() {
        const weekdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        setDaysWorking(weekdays, '08:00', '17:00');
    });
    
    document.getElementById('setAllDays').addEventListener('click', function() {
        setDaysWorking(days, '08:00', '17:00');
    });
    
    document.getElementById('clearAll').addEventListener('click', function() {
        days.forEach(day => {
            const toggle = document.getElementById(`working_${day}`);
            toggle.checked = false;
            toggle.dispatchEvent(new Event('change'));
        });
    });
    
    document.getElementById('setMorningShift').addEventListener('click', function() {
        const workingDays = getWorkingDays();
        setWorkingTimes(workingDays, '08:00', '14:00');
    });
    
    document.getElementById('setEveningShift').addEventListener('click', function() {
        const workingDays = getWorkingDays();
        setWorkingTimes(workingDays, '14:00', '20:00');
    });
    
    // Preview button
    document.getElementById('previewSchedule').addEventListener('click', showPreview);
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveWorkingHours();
    });
    
    function calculateDuration(day) {
        const startInput = document.getElementById(`start_${day}`);
        const endInput = document.getElementById(`end_${day}`);
        const durationDisplay = document.getElementById(`duration_${day}`);
        
        if (!startInput || !endInput || !durationDisplay) return;
        
        const start = startInput.value;
        const end = endInput.value;
        
        if (start && end) {
            const startTime = new Date(`2000-01-01T${start}:00`);
            const endTime = new Date(`2000-01-01T${end}:00`);
            
            if (endTime > startTime) {
                const diffMs = endTime - startTime;
                const diffHours = diffMs / (1000 * 60 * 60);
                durationDisplay.textContent = `${diffHours} ساعة`;
            } else {
                durationDisplay.textContent = 'وقت غير صحيح';
            }
        } else {
            durationDisplay.textContent = '';
        }
    }
    
    function setDaysWorking(targetDays, startTime, endTime) {
        targetDays.forEach(day => {
            const toggle = document.getElementById(`working_${day}`);
            const startInput = document.getElementById(`start_${day}`);
            const endInput = document.getElementById(`end_${day}`);
            
            toggle.checked = true;
            startInput.value = startTime;
            endInput.value = endTime;
            
            toggle.dispatchEvent(new Event('change'));
            calculateDuration(day);
        });
    }
    
    function setWorkingTimes(targetDays, startTime, endTime) {
        targetDays.forEach(day => {
            const startInput = document.getElementById(`start_${day}`);
            const endInput = document.getElementById(`end_${day}`);
            
            startInput.value = startTime;
            endInput.value = endTime;
            calculateDuration(day);
        });
    }
    
    function getWorkingDays() {
        return days.filter(day => {
            const toggle = document.getElementById(`working_${day}`);
            return toggle.checked;
        });
    }
    
    function showPreview() {
        const tableBody = document.getElementById('previewTableBody');
        let totalHours = 0;
        let html = '';
        
        days.forEach(day => {
            const toggle = document.getElementById(`working_${day}`);
            const startInput = document.getElementById(`start_${day}`);
            const endInput = document.getElementById(`end_${day}`);
            
            const isWorking = toggle.checked;
            const start = startInput.value;
            const end = endInput.value;
            
            let duration = 0;
            let durationText = '-';
            
            if (isWorking && start && end) {
                const startTime = new Date(`2000-01-01T${start}:00`);
                const endTime = new Date(`2000-01-01T${end}:00`);
                
                if (endTime > startTime) {
                    duration = (endTime - startTime) / (1000 * 60 * 60);
                    durationText = `${duration} ساعة`;
                    totalHours += duration;
                }
            }
            
            html += `
                <tr>
                    <td>${dayNames[day]}</td>
                    <td>
                        <span class="badge bg-${isWorking ? 'success' : 'secondary'}">
                            ${isWorking ? 'يوم عمل' : 'راحة'}
                        </span>
                    </td>
                    <td>${isWorking ? start : '-'}</td>
                    <td>${isWorking ? end : '-'}</td>
                    <td>${durationText}</td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
        document.getElementById('totalWeeklyHours').textContent = `${totalHours} ساعة`;
        
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }
    
    function saveWorkingHours() {
        const formData = new FormData(form);
        const data = {};
        
        // Process form data
        days.forEach(day => {
            const toggle = document.getElementById(`working_${day}`);
            const startInput = document.getElementById(`start_${day}`);
            const endInput = document.getElementById(`end_${day}`);
            
            data[`working_hours[${day}][is_working]`] = toggle.checked;
            if (toggle.checked) {
                data[`working_hours[${day}][start]`] = startInput.value;
                data[`working_hours[${day}][end]`] = endInput.value;
            }
        });
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري الحفظ...';
        submitBtn.disabled = true;
        
        fetch(`{{ route('doctors.update-working-hours', $doctor) }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                working_hours: Object.fromEntries(
                    days.map(day => [
                        day,
                        {
                            is_working: document.getElementById(`working_${day}`).checked,
                            start: document.getElementById(`start_${day}`).value,
                            end: document.getElementById(`end_${day}`).value
                        }
                    ])
                )
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Optionally redirect back to doctor profile
                setTimeout(() => {
                    window.location.href = `{{ route('doctors.show', $doctor) }}`;
                }, 1500);
            } else {
                showAlert('error', data.message || 'حدث خطأ أثناء الحفظ');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'حدث خطأ أثناء الحفظ');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
@endpush