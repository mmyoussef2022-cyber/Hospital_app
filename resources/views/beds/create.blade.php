@extends('layouts.app')

@section('title', 'إضافة سرير جديد')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إضافة سرير جديد</h3>
                    <a href="{{ route('beds.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للقائمة
                    </a>
                </div>

                <form action="{{ route('beds.store') }}" method="POST">
                    @csrf
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
                                                    data-capacity="{{ $room->capacity }}" 
                                                    data-current-beds="{{ $room->beds->count() }}"
                                                    {{ old('room_id', request('room_id')) == $room->id ? 'selected' : '' }}>
                                                {{ $room->room_number }} - {{ $room->room_type_display }} 
                                                ({{ $room->beds->count() }}/{{ $room->capacity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('room_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text" id="room-info"></div>
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
                                           value="{{ old('bed_number') }}" 
                                           required>
                                    @error('bed_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">سيتم إنشاء رقم تلقائي إذا تُرك فارغاً</div>
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
                                        <option value="standard" {{ old('bed_type') == 'standard' ? 'selected' : '' }}>عادي</option>
                                        <option value="icu" {{ old('bed_type') == 'icu' ? 'selected' : '' }}>عناية مركزة</option>
                                        <option value="pediatric" {{ old('bed_type') == 'pediatric' ? 'selected' : '' }}>أطفال</option>
                                        <option value="bariatric" {{ old('bed_type') == 'bariatric' ? 'selected' : '' }}>بدانة</option>
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
                                                <input class="form-check-input" type="checkbox" name="features[]" value="adjustable" id="feature_adjustable">
                                                <label class="form-check-label" for="feature_adjustable">قابل للتعديل</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="electric" id="feature_electric">
                                                <label class="form-check-label" for="feature_electric">كهربائي</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="side_rails" id="feature_side_rails">
                                                <label class="form-check-label" for="feature_side_rails">حواجز جانبية</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="mattress_overlay" id="feature_mattress_overlay">
                                                <label class="form-check-label" for="feature_mattress_overlay">مرتبة طبية</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="weighing_scale" id="feature_weighing_scale">
                                                <label class="form-check-label" for="feature_weighing_scale">ميزان مدمج</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="features[]" value="trendelenburg" id="feature_trendelenburg">
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
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            السرير نشط ومتاح للاستخدام
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Room Capacity Warning -->
                        <div class="alert alert-info d-none" id="capacity-warning">
                            <i class="fas fa-info-circle"></i>
                            <span id="capacity-message"></span>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('beds.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-save"></i> حفظ السرير
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
// Handle room selection
document.getElementById('room_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const roomInfo = document.getElementById('room-info');
    const capacityWarning = document.getElementById('capacity-warning');
    const capacityMessage = document.getElementById('capacity-message');
    const submitBtn = document.getElementById('submit-btn');
    const bedNumberField = document.getElementById('bed_number');
    
    if (selectedOption.value) {
        const capacity = parseInt(selectedOption.dataset.capacity);
        const currentBeds = parseInt(selectedOption.dataset.currentBeds);
        const available = capacity - currentBeds;
        
        roomInfo.innerHTML = `السعة: ${capacity} | الأسرة الحالية: ${currentBeds} | المتاح: ${available}`;
        
        if (available <= 0) {
            capacityWarning.classList.remove('d-none');
            capacityWarning.classList.remove('alert-info');
            capacityWarning.classList.add('alert-danger');
            capacityMessage.textContent = 'تحذير: هذه الغرفة وصلت للحد الأقصى من الأسرة!';
            submitBtn.disabled = true;
        } else if (available <= 2) {
            capacityWarning.classList.remove('d-none');
            capacityWarning.classList.remove('alert-danger');
            capacityWarning.classList.add('alert-warning');
            capacityMessage.textContent = `تنبيه: يمكن إضافة ${available} سرير فقط لهذه الغرفة.`;
            submitBtn.disabled = false;
        } else {
            capacityWarning.classList.add('d-none');
            submitBtn.disabled = false;
        }
        
        // Auto-generate bed number
        if (!bedNumberField.value) {
            bedNumberField.value = (currentBeds + 1).toString();
        }
    } else {
        roomInfo.innerHTML = '';
        capacityWarning.classList.add('d-none');
        submitBtn.disabled = false;
        bedNumberField.value = '';
    }
});

// Auto-suggest bed type based on room type
document.getElementById('room_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const bedTypeField = document.getElementById('bed_type');
    
    if (selectedOption.value && selectedOption.textContent.includes('ICU')) {
        bedTypeField.value = 'icu';
    } else if (selectedOption.textContent.includes('أطفال') || selectedOption.textContent.includes('Pediatric')) {
        bedTypeField.value = 'pediatric';
    } else if (!bedTypeField.value) {
        bedTypeField.value = 'standard';
    }
});

// Auto-select features based on bed type
document.getElementById('bed_type').addEventListener('change', function() {
    const bedType = this.value;
    
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
});

// Trigger room selection if pre-selected
if (document.getElementById('room_id').value) {
    document.getElementById('room_id').dispatchEvent(new Event('change'));
}
</script>
@endpush