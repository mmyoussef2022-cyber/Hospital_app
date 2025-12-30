@extends('layouts.app')

@section('title', 'إضافة وردية جديدة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">إضافة وردية جديدة</h1>
            <p class="text-muted">إنشاء وردية جديدة للموظفين</p>
        </div>
        <div>
            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">معلومات الوردية</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('shifts.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- User Selection -->
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                                <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                    <option value="">اختر الموظف</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $preselected['user_id']) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department Selection -->
                            <div class="col-md-6">
                                <label for="department_id" class="form-label">القسم <span class="text-danger">*</span></label>
                                <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                                    <option value="">اختر القسم</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id', $preselected['department_id']) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shift Type -->
                            <div class="col-md-6">
                                <label for="shift_type" class="form-label">نوع الوردية <span class="text-danger">*</span></label>
                                <select class="form-select @error('shift_type') is-invalid @enderror" id="shift_type" name="shift_type" required>
                                    <option value="">اختر نوع الوردية</option>
                                    <option value="morning" {{ old('shift_type', $preselected['shift_type']) == 'morning' ? 'selected' : '' }}>صباحية</option>
                                    <option value="afternoon" {{ old('shift_type', $preselected['shift_type']) == 'afternoon' ? 'selected' : '' }}>بعد الظهر</option>
                                    <option value="evening" {{ old('shift_type', $preselected['shift_type']) == 'evening' ? 'selected' : '' }}>مسائية</option>
                                    <option value="night" {{ old('shift_type', $preselected['shift_type']) == 'night' ? 'selected' : '' }}>ليلية</option>
                                    <option value="emergency" {{ old('shift_type', $preselected['shift_type']) == 'emergency' ? 'selected' : '' }}>طوارئ</option>
                                </select>
                                @error('shift_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shift Date -->
                            <div class="col-md-6">
                                <label for="shift_date" class="form-label">تاريخ الوردية <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('shift_date') is-invalid @enderror" 
                                       id="shift_date" name="shift_date" 
                                       value="{{ old('shift_date', $preselected['shift_date']) }}" required>
                                @error('shift_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Scheduled Start -->
                            <div class="col-md-6">
                                <label for="scheduled_start" class="form-label">وقت البداية <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('scheduled_start') is-invalid @enderror" 
                                       id="scheduled_start" name="scheduled_start" 
                                       value="{{ old('scheduled_start') }}" required>
                                @error('scheduled_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Scheduled End -->
                            <div class="col-md-6">
                                <label for="scheduled_end" class="form-label">وقت النهاية <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('scheduled_end') is-invalid @enderror" 
                                       id="scheduled_end" name="scheduled_end" 
                                       value="{{ old('scheduled_end') }}" required>
                                @error('scheduled_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cash Register -->
                            <div class="col-md-6">
                                <label for="cash_register_id" class="form-label">الصندوق</label>
                                <select class="form-select @error('cash_register_id') is-invalid @enderror" id="cash_register_id" name="cash_register_id">
                                    <option value="">اختر الصندوق (اختياري)</option>
                                    @foreach($cashRegisters as $register)
                                        <option value="{{ $register->id }}" {{ old('cash_register_id') == $register->id ? 'selected' : '' }}>
                                            {{ $register->name }} - {{ $register->location }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cash_register_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Supervisor -->
                            <div class="col-md-6">
                                <label for="supervisor_id" class="form-label">المشرف</label>
                                <select class="form-select @error('supervisor_id') is-invalid @enderror" id="supervisor_id" name="supervisor_id">
                                    <option value="">اختر المشرف (اختياري)</option>
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                            {{ $supervisor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supervisor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shift Notes -->
                            <div class="col-12">
                                <label for="shift_notes" class="form-label">ملاحظات الوردية</label>
                                <textarea class="form-control @error('shift_notes') is-invalid @enderror" 
                                          id="shift_notes" name="shift_notes" rows="3" 
                                          placeholder="أدخل أي ملاحظات خاصة بالوردية">{{ old('shift_notes') }}</textarea>
                                @error('shift_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ الوردية
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" id="check_availability">
                            <i class="fas fa-search me-1"></i>
                            فحص التوفر
                        </button>
                        <button type="button" class="btn btn-outline-info" id="suggest_times">
                            <i class="fas fa-clock me-1"></i>
                            اقتراح أوقات
                        </button>
                        <a href="{{ route('shifts.calendar') }}" class="btn btn-outline-success">
                            <i class="fas fa-calendar me-1"></i>
                            عرض التقويم
                        </a>
                    </div>
                </div>
            </div>

            <!-- Shift Type Guide -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">دليل أنواع الورديات</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong>صباحية:</strong> 06:00 - 14:00
                        </div>
                        <div class="mb-2">
                            <strong>بعد الظهر:</strong> 14:00 - 22:00
                        </div>
                        <div class="mb-2">
                            <strong>مسائية:</strong> 18:00 - 02:00
                        </div>
                        <div class="mb-2">
                            <strong>ليلية:</strong> 22:00 - 06:00
                        </div>
                        <div>
                            <strong>طوارئ:</strong> حسب الحاجة
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill times based on shift type
    document.getElementById('shift_type').addEventListener('change', function() {
        const shiftType = this.value;
        const startInput = document.getElementById('scheduled_start');
        const endInput = document.getElementById('scheduled_end');
        
        const times = {
            'morning': { start: '06:00', end: '14:00' },
            'afternoon': { start: '14:00', end: '22:00' },
            'evening': { start: '18:00', end: '02:00' },
            'night': { start: '22:00', end: '06:00' }
        };
        
        if (times[shiftType]) {
            startInput.value = times[shiftType].start;
            endInput.value = times[shiftType].end;
        }
    });

    // Check availability
    document.getElementById('check_availability').addEventListener('click', function() {
        const date = document.getElementById('shift_date').value;
        const departmentId = document.getElementById('department_id').value;
        const userId = document.getElementById('user_id').value;
        
        if (!date || !departmentId) {
            alert('يرجى اختيار التاريخ والقسم أولاً');
            return;
        }
        
        // Make AJAX request to check availability
        fetch('{{ route("shifts.available-slots") }}?' + new URLSearchParams({
            date: date,
            department_id: departmentId,
            user_id: userId
        }))
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                alert('لا توجد أوقات متاحة في هذا التاريخ');
            } else {
                let message = 'الأوقات المتاحة:\n';
                data.forEach(slot => {
                    message += `${slot.display}: ${slot.start} - ${slot.end}\n`;
                });
                alert(message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في فحص التوفر');
        });
    });

    // Suggest times
    document.getElementById('suggest_times').addEventListener('click', function() {
        const shiftType = document.getElementById('shift_type').value;
        if (!shiftType) {
            alert('يرجى اختيار نوع الوردية أولاً');
            return;
        }
        
        // Trigger the change event to auto-fill times
        document.getElementById('shift_type').dispatchEvent(new Event('change'));
    });
});
</script>
@endpush