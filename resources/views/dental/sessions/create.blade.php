@extends('layouts.app')

@section('title', 'إضافة جلسة جديدة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">🦷 إضافة جلسة جديدة</h2>
                    <p class="text-muted mb-0">إنشاء جلسة علاج أسنان جديدة</p>
                </div>
                <div>
                    <a href="{{ route('dental.sessions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('dental.sessions.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">المعلومات الأساسية</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dental_treatment_id" class="form-label">خطة العلاج <span class="text-danger">*</span></label>
                                    <select name="dental_treatment_id" id="dental_treatment_id" class="form-select @error('dental_treatment_id') is-invalid @enderror" required>
                                        <option value="">اختر خطة العلاج</option>
                                        @foreach($treatments as $treatment)
                                            <option value="{{ $treatment->id }}" 
                                                    {{ (old('dental_treatment_id') == $treatment->id || (isset($treatment) && $treatment->id == $treatment->id)) ? 'selected' : '' }}>
                                                {{ $treatment->title }} - {{ $treatment->patient->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dental_treatment_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="session_order" class="form-label">ترتيب الجلسة <span class="text-danger">*</span></label>
                                    <input type="number" name="session_order" id="session_order" 
                                           class="form-control @error('session_order') is-invalid @enderror" 
                                           value="{{ old('session_order', 1) }}" min="1" required>
                                    @error('session_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="session_title" class="form-label">عنوان الجلسة <span class="text-danger">*</span></label>
                                    <input type="text" name="session_title" id="session_title" 
                                           class="form-control @error('session_title') is-invalid @enderror" 
                                           value="{{ old('session_title') }}" required>
                                    @error('session_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="session_description" class="form-label">وصف الجلسة</label>
                                    <textarea name="session_description" id="session_description" 
                                              class="form-control @error('session_description') is-invalid @enderror" 
                                              rows="3">{{ old('session_description') }}</textarea>
                                    @error('session_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">الجدولة والتوقيت</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scheduled_date" class="form-label">التاريخ المجدول</label>
                                    <input type="date" name="scheduled_date" id="scheduled_date" 
                                           class="form-control @error('scheduled_date') is-invalid @enderror" 
                                           value="{{ old('scheduled_date') }}">
                                    @error('scheduled_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">المدة المتوقعة</label>
                                    <input type="time" name="duration" id="duration" 
                                           class="form-control @error('duration') is-invalid @enderror" 
                                           value="{{ old('duration', '01:00') }}">
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">الحالة</label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="scheduled" {{ old('status', 'scheduled') == 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="completed_date_field" style="display: none;">
                                <div class="mb-3">
                                    <label for="completed_date" class="form-label">تاريخ الإكمال</label>
                                    <input type="date" name="completed_date" id="completed_date" 
                                           class="form-control @error('completed_date') is-invalid @enderror" 
                                           value="{{ old('completed_date') }}">
                                    @error('completed_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">المعلومات المالية</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="session_cost" class="form-label">تكلفة الجلسة (ر.س) <span class="text-danger">*</span></label>
                                    <input type="number" name="session_cost" id="session_cost" 
                                           class="form-control @error('session_cost') is-invalid @enderror" 
                                           value="{{ old('session_cost', 0) }}" min="0" step="0.01" required>
                                    @error('session_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="session_payment" class="form-label">المبلغ المدفوع (ر.س)</label>
                                    <input type="number" name="session_payment" id="session_payment" 
                                           class="form-control @error('session_payment') is-invalid @enderror" 
                                           value="{{ old('session_payment', 0) }}" min="0" step="0.01">
                                    @error('session_payment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Treatment Information -->
                @if(isset($treatment))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">معلومات خطة العلاج</h5>
                        </div>
                        <div class="card-body">
                            <h6>المريض</h6>
                            <p class="mb-1"><strong>{{ $treatment->patient->name }}</strong></p>
                            <p class="text-muted mb-3">{{ $treatment->patient->phone }}</p>

                            <h6>الطبيب</h6>
                            <p class="mb-1"><strong>{{ $treatment->doctor->name }}</strong></p>
                            <p class="text-muted mb-3">{{ $treatment->doctor->specialization }}</p>

                            <h6>خطة العلاج</h6>
                            <p class="mb-1"><strong>{{ $treatment->title }}</strong></p>
                            <p class="text-muted">{{ Str::limit($treatment->description, 100) }}</p>
                        </div>
                    </div>
                @endif

                <!-- Pain Assessment -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">تقييم الألم</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="pain_level_before" class="form-label">مستوى الألم قبل الجلسة (0-10)</label>
                            <input type="number" name="pain_level_before" id="pain_level_before" 
                                   class="form-control @error('pain_level_before') is-invalid @enderror" 
                                   value="{{ old('pain_level_before') }}" min="0" max="10" step="0.1">
                            @error('pain_level_before')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pain_level_after" class="form-label">مستوى الألم بعد الجلسة (0-10)</label>
                            <input type="number" name="pain_level_after" id="pain_level_after" 
                                   class="form-control @error('pain_level_after') is-invalid @enderror" 
                                   value="{{ old('pain_level_after') }}" min="0" max="10" step="0.1">
                            @error('pain_level_after')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Follow-up -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">المتابعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="follow_up_required" id="follow_up_required" 
                                       class="form-check-input" value="1" 
                                       {{ old('follow_up_required') ? 'checked' : '' }}>
                                <label for="follow_up_required" class="form-check-label">
                                    متابعة مطلوبة
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="follow_up_date_field" style="display: none;">
                            <label for="follow_up_date" class="form-label">تاريخ المتابعة</label>
                            <input type="date" name="follow_up_date" id="follow_up_date" 
                                   class="form-control @error('follow_up_date') is-invalid @enderror" 
                                   value="{{ old('follow_up_date') }}">
                            @error('follow_up_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Session Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ملاحظات إضافية</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="session_notes" class="form-label">ملاحظات الجلسة</label>
                            <textarea name="session_notes" id="session_notes" 
                                      class="form-control @error('session_notes') is-invalid @enderror" 
                                      rows="4">{{ old('session_notes') }}</textarea>
                            @error('session_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="next_session_plan" class="form-label">خطة الجلسة القادمة</label>
                            <textarea name="next_session_plan" id="next_session_plan" 
                                      class="form-control @error('next_session_plan') is-invalid @enderror" 
                                      rows="3">{{ old('next_session_plan') }}</textarea>
                            @error('next_session_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dental.sessions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                حفظ الجلسة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide completed date field based on status
    const statusSelect = document.getElementById('status');
    const completedDateField = document.getElementById('completed_date_field');
    
    function toggleCompletedDate() {
        if (statusSelect.value === 'completed') {
            completedDateField.style.display = 'block';
            document.getElementById('completed_date').value = new Date().toISOString().split('T')[0];
        } else {
            completedDateField.style.display = 'none';
            document.getElementById('completed_date').value = '';
        }
    }
    
    statusSelect.addEventListener('change', toggleCompletedDate);
    toggleCompletedDate(); // Initial check

    // Show/hide follow-up date field
    const followUpCheckbox = document.getElementById('follow_up_required');
    const followUpDateField = document.getElementById('follow_up_date_field');
    
    function toggleFollowUpDate() {
        if (followUpCheckbox.checked) {
            followUpDateField.style.display = 'block';
        } else {
            followUpDateField.style.display = 'none';
            document.getElementById('follow_up_date').value = '';
        }
    }
    
    followUpCheckbox.addEventListener('change', toggleFollowUpDate);
    toggleFollowUpDate(); // Initial check

    // Auto-calculate session order based on treatment
    const treatmentSelect = document.getElementById('dental_treatment_id');
    const sessionOrderInput = document.getElementById('session_order');
    
    treatmentSelect.addEventListener('change', function() {
        if (this.value) {
            // You could make an AJAX call here to get the next session order
            // For now, we'll just increment from 1
            sessionOrderInput.value = 1;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-left: 0.25rem;
}
</style>
@endpush
@endsection