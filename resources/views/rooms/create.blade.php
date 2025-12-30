@extends('layouts.app')

@section('title', 'إضافة غرفة جديدة')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إضافة غرفة جديدة</h3>
                    <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للقائمة
                    </a>
                </div>

                <form action="{{ route('rooms.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Room Number -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_number" class="form-label">رقم الغرفة <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('room_number') is-invalid @enderror" 
                                           id="room_number" 
                                           name="room_number" 
                                           value="{{ old('room_number') }}" 
                                           required>
                                    @error('room_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Room Type -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_type" class="form-label">نوع الغرفة <span class="text-danger">*</span></label>
                                    <select class="form-select @error('room_type') is-invalid @enderror" 
                                            id="room_type" 
                                            name="room_type" 
                                            required>
                                        <option value="">اختر نوع الغرفة</option>
                                        <option value="ward" {{ old('room_type') == 'ward' ? 'selected' : '' }}>جناح عام</option>
                                        <option value="icu" {{ old('room_type') == 'icu' ? 'selected' : '' }}>العناية المركزة</option>
                                        <option value="emergency" {{ old('room_type') == 'emergency' ? 'selected' : '' }}>طوارئ</option>
                                        <option value="surgery" {{ old('room_type') == 'surgery' ? 'selected' : '' }}>جراحة</option>
                                        <option value="private" {{ old('room_type') == 'private' ? 'selected' : '' }}>خاصة</option>
                                        <option value="semi_private" {{ old('room_type') == 'semi_private' ? 'selected' : '' }}>شبه خاصة</option>
                                    </select>
                                    @error('room_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Department -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">القسم <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('department') is-invalid @enderror" 
                                           id="department" 
                                           name="department" 
                                           value="{{ old('department') }}" 
                                           required>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Floor -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="floor" class="form-label">الطابق <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('floor') is-invalid @enderror" 
                                           id="floor" 
                                           name="floor" 
                                           value="{{ old('floor') }}" 
                                           min="1" 
                                           max="50" 
                                           required>
                                    @error('floor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Wing -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="wing" class="form-label">الجناح</label>
                                    <input type="text" 
                                           class="form-control @error('wing') is-invalid @enderror" 
                                           id="wing" 
                                           name="wing" 
                                           value="{{ old('wing') }}">
                                    @error('wing')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Capacity -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">السعة (عدد الأسرة) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('capacity') is-invalid @enderror" 
                                           id="capacity" 
                                           name="capacity" 
                                           value="{{ old('capacity', 1) }}" 
                                           min="1" 
                                           max="20" 
                                           required>
                                    @error('capacity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Daily Rate -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="daily_rate" class="form-label">السعر اليومي (ريال) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('daily_rate') is-invalid @enderror" 
                                           id="daily_rate" 
                                           name="daily_rate" 
                                           value="{{ old('daily_rate') }}" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                    @error('daily_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Amenities -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">المرافق</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="tv" id="amenity_tv">
                                                <label class="form-check-label" for="amenity_tv">تلفزيون</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="wifi" id="amenity_wifi">
                                                <label class="form-check-label" for="amenity_wifi">واي فاي</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="ac" id="amenity_ac">
                                                <label class="form-check-label" for="amenity_ac">تكييف</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="bathroom" id="amenity_bathroom">
                                                <label class="form-check-label" for="amenity_bathroom">حمام خاص</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="fridge" id="amenity_fridge">
                                                <label class="form-check-label" for="amenity_fridge">ثلاجة</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="balcony" id="amenity_balcony">
                                                <label class="form-check-label" for="amenity_balcony">شرفة</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Equipment -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">المعدات الطبية</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipment[]" value="oxygen" id="equipment_oxygen">
                                                <label class="form-check-label" for="equipment_oxygen">أكسجين</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipment[]" value="suction" id="equipment_suction">
                                                <label class="form-check-label" for="equipment_suction">شفط</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipment[]" value="monitor" id="equipment_monitor">
                                                <label class="form-check-label" for="equipment_monitor">مراقب حيوي</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipment[]" value="ventilator" id="equipment_ventilator">
                                                <label class="form-check-label" for="equipment_ventilator">جهاز تنفس</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipment[]" value="defibrillator" id="equipment_defibrillator">
                                                <label class="form-check-label" for="equipment_defibrillator">مزيل رجفان</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipment[]" value="iv_pump" id="equipment_iv_pump">
                                                <label class="form-check-label" for="equipment_iv_pump">مضخة وريدية</label>
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
                                            الغرفة نشطة ومتاحة للاستخدام
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ الغرفة
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
// Auto-generate room number based on floor and type
document.getElementById('floor').addEventListener('change', generateRoomNumber);
document.getElementById('room_type').addEventListener('change', generateRoomNumber);

function generateRoomNumber() {
    const floor = document.getElementById('floor').value;
    const roomType = document.getElementById('room_type').value;
    const roomNumberField = document.getElementById('room_number');
    
    if (floor && roomType && !roomNumberField.value) {
        let prefix = '';
        switch(roomType) {
            case 'icu': prefix = 'ICU'; break;
            case 'emergency': prefix = 'ER'; break;
            case 'surgery': prefix = 'OR'; break;
            case 'private': prefix = 'PVT'; break;
            case 'semi_private': prefix = 'SP'; break;
            default: prefix = 'W'; break;
        }
        
        roomNumberField.value = `${prefix}${floor}01`;
    }
}

// Update capacity based on room type
document.getElementById('room_type').addEventListener('change', function() {
    const capacityField = document.getElementById('capacity');
    const roomType = this.value;
    
    switch(roomType) {
        case 'icu':
            capacityField.value = 1;
            capacityField.max = 2;
            break;
        case 'private':
            capacityField.value = 1;
            capacityField.max = 1;
            break;
        case 'semi_private':
            capacityField.value = 2;
            capacityField.max = 2;
            break;
        case 'emergency':
            capacityField.value = 1;
            capacityField.max = 4;
            break;
        default:
            capacityField.value = 4;
            capacityField.max = 20;
            break;
    }
});

// Update daily rate based on room type
document.getElementById('room_type').addEventListener('change', function() {
    const dailyRateField = document.getElementById('daily_rate');
    const roomType = this.value;
    
    if (!dailyRateField.value) {
        switch(roomType) {
            case 'icu':
                dailyRateField.value = 1500;
                break;
            case 'private':
                dailyRateField.value = 800;
                break;
            case 'semi_private':
                dailyRateField.value = 500;
                break;
            case 'emergency':
                dailyRateField.value = 300;
                break;
            case 'surgery':
                dailyRateField.value = 1000;
                break;
            default:
                dailyRateField.value = 200;
                break;
        }
    }
});
</script>
@endpush