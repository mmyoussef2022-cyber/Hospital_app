@extends('layouts.app')

@section('title', 'تعديل السرير ' . $bed->full_bed_number)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تعديل السرير {{ $bed->full_bed_number }}</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('beds.show', $bed) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        <a href="{{ route('beds.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة
                        </a>
                    </div>
                </div>

                <form action="{{ route('beds.update', $bed) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Room Selection -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">الغرفة <span class="text-danger">*</span></label>
                                    <select class="form-select @error('room_id') is-invalid @enderror" 
                                            id="room_id" 
                                            name="room_id" 
                                            required>
                                        <option value="">اختر الغرفة</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}" 
                                                    {{ old('room_id', $bed->room_id) == $room->id ? 'selected' : '' }}>
                                                {{ $room->room_number }} - {{ $room->room_type_display }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('room_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Bed Number -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bed_number" class="form-label">رقم السرير <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('bed_number') is-invalid @enderror" 
                                           id="bed_number" 
                                           name="bed_number" 
                                           value="{{ old('bed_number', $bed->bed_number) }}" 
                                           required>
                                    @error('bed_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Bed Type -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bed_type" class="form-label">نوع السرير <span class="text-danger">*</span></label>
                                    <select class="form-select @error('bed_type') is-invalid @enderror" 
                                            id="bed_type" 
                                            name="bed_type" 
                                            required>
                                        <option value="">اختر نوع السرير</option>
                                        <option value="standard" {{ old('bed_type', $bed->bed_type) == 'standard' ? 'selected' : '' }}>عادي</option>
                                        <option value="icu" {{ old('bed_type', $bed->bed_type) == 'icu' ? 'selected' : '' }}>عناية مركزة</option>
                                        <option value="pediatric" {{ old('bed_type', $bed->bed_type) == 'pediatric' ? 'selected' : '' }}>أطفال</option>
                                        <option value="bariatric" {{ old('bed_type', $bed->bed_type) == 'bariatric' ? 'selected' : '' }}>بدانة</option>
                                    </select>
                                    @error('bed_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">المميزات الإضافية</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="adjustable" id="feature_adjustable"
                                                       {{ in_array('adjustable', old('features', $bed->features ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="feature_adjustable">قابل للتعديل</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="electric" id="feature_electric"
                                                       {{ in_array('electric', old('features', $bed->features ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="feature_electric">كهربائي</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="side_rails" id="feature_side_rails"
                                                       {{ in_array('side_rails', old('features', $bed->features ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="feature_side_rails">حواجز جانبية</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="mattress_overlay" id="feature_mattress_overlay"
                                                       {{ in_array('mattress_overlay', old('features', $bed->features ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="feature_mattress_overlay">مرتبة طبية</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="weighing_scale" id="feature_weighing_scale"
                                                       {{ in_array('weighing_scale', old('features', $bed->features ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="feature_weighing_scale">ميزان مدمج</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="trendelenburg" id="feature_trendelenburg"
                                                       {{ in_array('trendelenburg', old('features', $bed->features ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="feature_trendelenburg">وضعية ترندلنبرغ</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                                               {{ old('is_active', $bed->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            السرير نشط ومتاح للاستخدام
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Status Warning -->
                        @if($bed->status === 'occupied')
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> تنبيه:</h6>
                            <p class="mb-0">هذا السرير مشغول حالياً بالمريض: <strong>{{ $bed->currentAssignment->patient->name ?? 'غير محدد' }}</strong></p>
                            <p class="mb-0">لا يمكن تغيير الغرفة أو إلغاء تفعيل السرير أثناء وجود مريض.</p>
                        </div>
                        @endif

                        <!-- Room Change Warning -->
                        @if($bed->assignments()->count() > 0)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> معلومة:</h6>
                            <p class="mb-0">هذا السرير له تاريخ تخصيصات ({{ $bed->assignments()->count() }} تخصيص).</p>
                            <p class="mb-0">تغيير الغرفة سيؤثر على التقارير والإحصائيات.</p>
                        </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('beds.show', $bed) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" 
                                    @if($bed->status === 'occupied') 
                                        onclick="return confirm('هل أنت متأكد من حفظ التغييرات؟ السرير مشغول حالياً.')"
                                    @endif>
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Prevent room change if bed is occupied
@if($bed->status === 'occupied')
document.getElementById('room_id').addEventListener('change', function() {
    if (this.value != {{ $bed->room_id }}) {
        alert('لا يمكن تغيير الغرفة أثناء وجود مريض في السرير');
        this.value = {{ $bed->room_id }};
    }
});

// Prevent deactivation if bed is occupied
document.getElementById('is_active').addEventListener('change', function() {
    if (!this.checked) {
        alert('لا يمكن إلغاء تفعيل السرير أثناء وجود مريض');
        this.checked = true;
    }
});
@endif

// Auto-select features based on bed type
document.getElementById('bed_type').addEventListener('change', function() {
    const bedType = this.value;
    
    if (confirm('هل تريد تحديث المميزات حسب نوع السرير الجديد؟')) {
        // Clear all features first
        document.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Auto-select features based on bed type
        switch(bedType) {
            case 'icu':
                document.getElementById('feature_adjustable').checked = true;
                document.getElementById('feature_electric').checked = true;
                document.getElementById('feature_side_rails').checked = true;
                document.getElementById('feature_trendelenburg').checked = true;
                break;
            case 'bariatric':
                document.getElementById('feature_adjustable').checked = true;
                document.getElementById('feature_electric').checked = true;
                document.getElementById('feature_weighing_scale').checked = true;
                break;
            case 'pediatric':
                document.getElementById('feature_side_rails').checked = true;
                break;
            case 'standard':
                document.getElementById('feature_adjustable').checked = true;
                document.getElementById('feature_side_rails').checked = true;
                break;
        }
    }
});

// Warn about bed number conflicts
document.getElementById('bed_number').addEventListener('blur', function() {
    const bedNumber = this.value;
    const roomId = document.getElementById('room_id').value;
    
    if (bedNumber && roomId) {
        // This would typically check via AJAX for conflicts
        // For now, we'll just validate format
        if (!/^[0-9A-Za-z]+$/.test(bedNumber)) {
            alert('رقم السرير يجب أن يحتوي على أرقام وحروف فقط');
            this.focus();
        }
    }
});
</script>
@endpush