@extends('layouts.app')

@section('title', 'إضافة مريض جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>
                        إضافة مريض جديد
                    </h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Personal Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-circle me-2"></i>
                                    المعلومات الشخصية
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">الاسم بالعربية <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name_en" class="form-label">الاسم بالإنجليزية</label>
                                <input type="text" 
                                       class="form-control @error('name_en') is-invalid @enderror" 
                                       id="name_en" 
                                       name="name_en" 
                                       value="{{ old('name_en') }}">
                                @error('name_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="national_id" class="form-label">رقم الهوية الوطنية <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('national_id') is-invalid @enderror" 
                                       id="national_id" 
                                       name="national_id" 
                                       value="{{ old('national_id') }}" 
                                       maxlength="10" 
                                       required>
                                @error('national_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">الجنس <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" 
                                        id="gender" 
                                        name="gender" 
                                        required>
                                    <option value="">اختر الجنس</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="date_of_birth" class="form-label">تاريخ الميلاد <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" 
                                       name="date_of_birth" 
                                       value="{{ old('date_of_birth') }}" 
                                       required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">رقم الجوال <span class="text-danger">*</span></label>
                                <input type="tel" 
                                       class="form-control @error('mobile') is-invalid @enderror" 
                                       id="mobile" 
                                       name="mobile" 
                                       value="{{ old('mobile') }}" 
                                       required>
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">رقم الهاتف الثابت</label>
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="profile_photo" class="form-label">الصورة الشخصية</label>
                                <input type="file" 
                                       class="form-control @error('profile_photo') is-invalid @enderror" 
                                       id="profile_photo" 
                                       name="profile_photo" 
                                       accept="image/*">
                                @error('profile_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    معلومات العنوان
                                </h5>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">العنوان <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3" 
                                          required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">المدينة <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('city') is-invalid @enderror" 
                                       id="city" 
                                       name="city" 
                                       value="{{ old('city') }}" 
                                       required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">الدولة <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('country') is-invalid @enderror" 
                                       id="country" 
                                       name="country" 
                                       value="{{ old('country', 'السعودية') }}" 
                                       required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="nationality" class="form-label">الجنسية <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nationality') is-invalid @enderror" 
                                       id="nationality" 
                                       name="nationality" 
                                       value="{{ old('nationality', 'سعودي') }}" 
                                       required>
                                @error('nationality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Personal Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    التفاصيل الشخصية
                                </h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="marital_status" class="form-label">الحالة الاجتماعية <span class="text-danger">*</span></label>
                                <select class="form-select @error('marital_status') is-invalid @enderror" 
                                        id="marital_status" 
                                        name="marital_status" 
                                        required>
                                    <option value="">اختر الحالة الاجتماعية</option>
                                    <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>أعزب</option>
                                    <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>متزوج</option>
                                    <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>مطلق</option>
                                    <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>أرمل</option>
                                </select>
                                @error('marital_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="occupation" class="form-label">المهنة</label>
                                <input type="text" 
                                       class="form-control @error('occupation') is-invalid @enderror" 
                                       id="occupation" 
                                       name="occupation" 
                                       value="{{ old('occupation') }}">
                                @error('occupation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="blood_type" class="form-label">فصيلة الدم <span class="text-danger">*</span></label>
                                <select class="form-select @error('blood_type') is-invalid @enderror" 
                                        id="blood_type" 
                                        name="blood_type" 
                                        required>
                                    <option value="">اختر فصيلة الدم</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                                        <option value="{{ $bloodType }}" {{ old('blood_type') == $bloodType ? 'selected' : '' }}>
                                            {{ $bloodType }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('blood_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="patient_type" class="form-label">نوع المريض <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_type') is-invalid @enderror" 
                                        id="patient_type" 
                                        name="patient_type" 
                                        required>
                                    <option value="">اختر نوع المريض</option>
                                    <option value="outpatient" {{ old('patient_type') == 'outpatient' ? 'selected' : '' }}>خارجي</option>
                                    <option value="inpatient" {{ old('patient_type') == 'inpatient' ? 'selected' : '' }}>داخلي</option>
                                    <option value="emergency" {{ old('patient_type') == 'emergency' ? 'selected' : '' }}>طوارئ</option>
                                </select>
                                @error('patient_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        حساب مفعل
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-telephone-fill me-2"></i>
                                    جهة الاتصال في حالات الطوارئ
                                </h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="emergency_contact_name" class="form-label">الاسم <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('emergency_contact.name') is-invalid @enderror" 
                                       id="emergency_contact_name" 
                                       name="emergency_contact[name]" 
                                       value="{{ old('emergency_contact.name') }}" 
                                       required>
                                @error('emergency_contact.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="emergency_contact_relationship" class="form-label">صلة القرابة <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('emergency_contact.relationship') is-invalid @enderror" 
                                       id="emergency_contact_relationship" 
                                       name="emergency_contact[relationship]" 
                                       value="{{ old('emergency_contact.relationship') }}" 
                                       required>
                                @error('emergency_contact.relationship')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="emergency_contact_phone" class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="tel" 
                                       class="form-control @error('emergency_contact.phone') is-invalid @enderror" 
                                       id="emergency_contact_phone" 
                                       name="emergency_contact[phone]" 
                                       value="{{ old('emergency_contact.phone') }}" 
                                       required>
                                @error('emergency_contact.phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Family Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-people me-2"></i>
                                    معلومات العائلة
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="family_head_id" class="form-label">رب الأسرة</label>
                                <select class="form-select @error('family_head_id') is-invalid @enderror" 
                                        id="family_head_id" 
                                        name="family_head_id">
                                    <option value="">اختر رب الأسرة (اختياري)</option>
                                    @foreach($familyHeads as $familyHead)
                                        <option value="{{ $familyHead->id }}" {{ old('family_head_id') == $familyHead->id ? 'selected' : '' }}>
                                            {{ $familyHead->name }} - {{ $familyHead->patient_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('family_head_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="family_relation" class="form-label">صلة القرابة</label>
                                <input type="text" 
                                       class="form-control @error('family_relation') is-invalid @enderror" 
                                       id="family_relation" 
                                       name="family_relation" 
                                       value="{{ old('family_relation') }}" 
                                       placeholder="مثل: الابن، الزوجة، الأب">
                                @error('family_relation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-heart-pulse me-2"></i>
                                    المعلومات الطبية
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="allergies" class="form-label">الحساسية</label>
                                <textarea class="form-control @error('allergies') is-invalid @enderror" 
                                          id="allergies" 
                                          name="allergies" 
                                          rows="3" 
                                          placeholder="اكتب أنواع الحساسية مفصولة بفواصل">{{ old('allergies') }}</textarea>
                                @error('allergies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="chronic_conditions" class="form-label">الأمراض المزمنة</label>
                                <textarea class="form-control @error('chronic_conditions') is-invalid @enderror" 
                                          id="chronic_conditions" 
                                          name="chronic_conditions" 
                                          rows="3" 
                                          placeholder="اكتب الأمراض المزمنة مفصولة بفواصل">{{ old('chronic_conditions') }}</textarea>
                                @error('chronic_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="medical_notes" class="form-label">ملاحظات طبية</label>
                                <textarea class="form-control @error('medical_notes') is-invalid @enderror" 
                                          id="medical_notes" 
                                          name="medical_notes" 
                                          rows="4" 
                                          placeholder="أي ملاحظات طبية إضافية">{{ old('medical_notes') }}</textarea>
                                @error('medical_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        العودة
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        حفظ المريض
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Show/hide family relation field based on family head selection
document.getElementById('family_head_id').addEventListener('change', function() {
    const familyRelationField = document.getElementById('family_relation').parentElement;
    if (this.value) {
        familyRelationField.style.display = 'block';
        document.getElementById('family_relation').required = true;
    } else {
        familyRelationField.style.display = 'none';
        document.getElementById('family_relation').required = false;
    }
});

// Convert allergies and chronic conditions to arrays
document.querySelector('form').addEventListener('submit', function(e) {
    const allergiesField = document.getElementById('allergies');
    const chronicConditionsField = document.getElementById('chronic_conditions');
    
    if (allergiesField.value) {
        const allergiesArray = allergiesField.value.split(',').map(item => item.trim()).filter(item => item);
        allergiesField.value = JSON.stringify(allergiesArray);
    }
    
    if (chronicConditionsField.value) {
        const chronicArray = chronicConditionsField.value.split(',').map(item => item.trim()).filter(item => item);
        chronicConditionsField.value = JSON.stringify(chronicArray);
    }
});
</script>
@endpush