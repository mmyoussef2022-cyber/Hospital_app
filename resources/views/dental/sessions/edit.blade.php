@extends('layouts.app')

@section('title', 'تعديل الجلسة - ' . $session->session_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">🦷 تعديل الجلسة {{ $session->session_number }}</h2>
                    <p class="text-muted mb-0">{{ $session->session_title }}</p>
                </div>
                <div>
                    <a href="{{ route('dental.sessions.show', $session) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للجلسة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('dental.sessions.update', $session) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
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
                                                    {{ (old('dental_treatment_id', $session->dental_treatment_id) == $treatment->id) ? 'selected' : '' }}>
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
                                           value="{{ old('session_order', $session->session_order) }}" min="1" required>
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
                                           value="{{ old('session_title', $session->session_title) }}" required>
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
                                              rows="3">{{ old('session_description', $session->session_description) }}</textarea>
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
                                           value="{{ old('scheduled_date', $session->scheduled_date ? $session->scheduled_date->format('Y-m-d') : '') }}">
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
                                           value="{{ old('duration', $session->duration) }}">
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
                                        <option value="scheduled" {{ old('status', $session->status) == 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                                        <option value="completed" {{ old('status', $session->status) == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                        <option value="cancelled" {{ old('status', $session->status) == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                                        <option value="no_show" {{ old('status', $session->status) == 'no_show' ? 'selected' : '' }}>لم يحضر</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="completed_date_field" style="{{ old('status', $session->status) == 'completed' ? 'display: block;' : 'display: none;' }}">
                                <div class="mb-3">
                                    <label for="completed_date" class="form-label">تاريخ الإكمال</label>
                                    <input type="date" name="completed_date" id="completed_date" 
                                           class="form-control @error('completed_date') is-invalid @enderror" 
                                           value="{{ old('completed_date', $session->completed_date ? $session->completed_date->format('Y-m-d') : '') }}">
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
                                           value="{{ old('session_cost', $session->session_cost) }}" min="0" step="0.01" required>
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
                                           value="{{ old('session_payment', $session->session_payment) }}" min="0" step="0.01">
                                    @error('session_payment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Procedures and Materials -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">الإجراءات والمواد</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="procedures_performed" class="form-label">الإجراءات المنفذة</label>
                                    <textarea name="procedures_performed" id="procedures_performed" 
                                              class="form-control @error('procedures_performed') is-invalid @enderror" 
                                              rows="4" placeholder="اكتب كل إجراء في سطر منفصل">{{ old('procedures_performed', is_array($session->procedures_performed) ? implode("\n", $session->procedures_performed) : '') }}</textarea>
                                    @error('procedures_performed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">اكتب كل إجراء في سطر منفصل</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="materials_used" class="form-label">المواد المستخدمة</label>
                                    <textarea name="materials_used" id="materials_used" 
                                              class="form-control @error('materials_used') is-invalid @enderror" 
                                              rows="4" placeholder="اكتب كل مادة في سطر منفصل">{{ old('materials_used', is_array($session->materials_used) ? implode("\n", $session->materials_used) : '') }}</textarea>
                                    @error('materials_used')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">اكتب كل مادة في سطر منفصل</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Treatment Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">معلومات خطة العلاج</h5>
                    </div>
                    <div class="card-body">
                        <h6>المريض</h6>
                        <p class="mb-1"><strong>{{ $session->dentalTreatment->patient->name }}</strong></p>
                        <p class="text-muted mb-3">{{ $session->dentalTreatment->patient->phone }}</p>

                        <h6>الطبيب</h6>
                        <p class="mb-1"><strong>{{ $session->dentalTreatment->doctor->name }}</strong></p>
                        <p class="text-muted mb-3">{{ $session->dentalTreatment->doctor->specialization }}</p>

                        <h6>خطة العلاج</h6>
                        <p class="mb-1"><strong>{{ $session->dentalTreatment->title }}</strong></p>
                        <p class="text-muted">{{ Str::limit($session->dentalTreatment->description, 100) }}</p>
                    </div>
                </div>

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
                                   value="{{ old('pain_level_before', $session->pain_level_before) }}" min="0" max="10" step="0.1">
                            @error('pain_level_before')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pain_level_after" class="form-label">مستوى الألم بعد الجلسة (0-10)</label>
                            <input type="number" name="pain_level_after" id="pain_level_after" 
                                   class="form-control @error('pain_level_after') is-invalid @enderror" 
                                   value="{{ old('pain_level_after', $session->pain_level_after) }}" min="0" max="10" step="0.1">
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
                                       {{ old('follow_up_required', $session->follow_up_required) ? 'checked' : '' }}>
                                <label for="follow_up_required" class="form-check-label">
                                    متابعة مطلوبة
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="follow_up_date_field" style="{{ old('follow_up_required', $session->follow_up_required) ? 'display: block;' : 'display: none;' }}">
                            <label for="follow_up_date" class="form-label">تاريخ المتابعة</label>
                            <input type="date" name="follow_up_date" id="follow_up_date" 
                                   class="form-control @error('follow_up_date') is-invalid @enderror" 
                                   value="{{ old('follow_up_date', $session->follow_up_date ? $session->follow_up_date->format('Y-m-d') : '') }}">
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
                                      rows="4">{{ old('session_notes', $session->session_notes) }}</textarea>
                            @error('session_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="next_session_plan" class="form-label">خطة الجلسة القادمة</label>
                            <textarea name="next_session_plan" id="next_session_plan" 
                                      class="form-control @error('next_session_plan') is-invalid @enderror" 
                                      rows="3">{{ old('next_session_plan', $session->next_session_plan) }}</textarea>
                            @error('next_session_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Complications -->
                @if($session->complications && count($session->complications) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0 text-warning">مضاعفات مسجلة</h5>
                        </div>
                        <div class="card-body">
                            @foreach($session->complications as $complication)
                                <div class="alert alert-warning alert-sm mb-2">
                                    <small>{{ $complication['description'] ?? $complication }}</small>
                                    @if(isset($complication['timestamp']))
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($complication['timestamp'])->format('Y-m-d H:i') }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dental.sessions.show', $session) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                حفظ التغييرات
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
            if (!document.getElementById('completed_date').value) {
                document.getElementById('completed_date').value = new Date().toISOString().split('T')[0];
            }
        } else {
            completedDateField.style.display = 'none';
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
        }
    }
    
    followUpCheckbox.addEventListener('change', toggleFollowUpDate);
    toggleFollowUpDate(); // Initial check
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

.alert-sm {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-left: 0.25rem;
}
</style>
@endpush
@endsection