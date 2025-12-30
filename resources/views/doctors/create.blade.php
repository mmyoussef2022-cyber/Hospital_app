@extends('layouts.app')

@section('page-title', 'إضافة طبيب جديد')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus text-facebook"></i>
                        إضافة طبيب جديد
                    </h5>
                    <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right"></i>
                        العودة للقائمة
                    </a>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('doctors.store') }}" enctype="multipart/form-data" id="doctorForm">
                        @csrf
                        
                        <!-- Personal Information Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-circle"></i>
                                    المعلومات الشخصية
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">اسم الطبيب <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" name="password_confirmation" required>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="national_id" class="form-label">الرقم القومي <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('national_id') is-invalid @enderror" 
                                       id="national_id" name="national_id" value="{{ old('national_id') }}" 
                                       pattern="[0-9]{10}" maxlength="10" required>
                                @error('national_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">10 أرقام فقط</div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">الجنس <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                    <option value="">اختر الجنس</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="date_of_birth" class="form-label">تاريخ الميلاد</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">رقم الجوال</label>
                                <input type="tel" class="form-control @error('mobile') is-invalid @enderror" 
                                       id="mobile" name="mobile" value="{{ old('mobile') }}">
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">العنوان</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="2">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-award"></i>
                                    المعلومات المهنية
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">القسم <span class="text-danger">*</span></label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" name="department_id" required>
                                    <option value="">اختر القسم</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="job_title" class="form-label">المسمى الوظيفي</label>
                                <input type="text" class="form-control @error('job_title') is-invalid @enderror" 
                                       id="job_title" name="job_title" value="{{ old('job_title', 'طبيب') }}">
                                @error('job_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="license_number" class="form-label">رقم الترخيص <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                       id="license_number" name="license_number" value="{{ old('license_number') }}" required>
                                @error('license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="specialization" class="form-label">التخصص <span class="text-danger">*</span></label>
                                <select class="form-select @error('specialization') is-invalid @enderror" 
                                        id="specialization" name="specialization" required>
                                    <option value="">اختر التخصص</option>
                                    @foreach($specializations as $key => $value)
                                        <option value="{{ $key }}" 
                                                {{ old('specialization') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('specialization')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="degree" class="form-label">الدرجة العلمية <span class="text-danger">*</span></label>
                                <select class="form-select @error('degree') is-invalid @enderror" id="degree" name="degree" required>
                                    <option value="">اختر الدرجة العلمية</option>
                                    @foreach($degrees as $key => $value)
                                        <option value="{{ $key }}" 
                                                {{ old('degree') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('degree')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="university" class="form-label">الجامعة</label>
                                <input type="text" class="form-control @error('university') is-invalid @enderror" 
                                       id="university" name="university" value="{{ old('university') }}">
                                @error('university')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="experience_years" class="form-label">سنوات الخبرة <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                       id="experience_years" name="experience_years" value="{{ old('experience_years', 0) }}" 
                                       min="0" max="50" required>
                                @error('experience_years')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="languages" class="form-label">اللغات</label>
                                <input type="text" class="form-control @error('languages') is-invalid @enderror" 
                                       id="languages" name="languages" value="{{ old('languages') }}" 
                                       placeholder="العربية، الإنجليزية، الفرنسية">
                                @error('languages')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">افصل بين اللغات بفاصلة</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="sub_specializations" class="form-label">التخصصات الفرعية</label>
                                <input type="text" class="form-control @error('sub_specializations') is-invalid @enderror" 
                                       id="sub_specializations" name="sub_specializations" value="{{ old('sub_specializations') }}" 
                                       placeholder="جراحة القلب، قسطرة القلب">
                                @error('sub_specializations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">افصل بين التخصصات بفاصلة</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="biography" class="form-label">السيرة الذاتية</label>
                                <textarea class="form-control @error('biography') is-invalid @enderror" 
                                          id="biography" name="biography" rows="4" maxlength="2000">{{ old('biography') }}</textarea>
                                @error('biography')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">حد أقصى 2000 حرف</div>
                            </div>
                        </div>

                        <!-- Contact & Fees Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-telephone"></i>
                                    معلومات الاتصال والرسوم
                                </h6>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="consultation_fee" class="form-label">رسوم الاستشارة (ريال) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('consultation_fee') is-invalid @enderror" 
                                       id="consultation_fee" name="consultation_fee" value="{{ old('consultation_fee', 0) }}" 
                                       min="0" step="0.01" required>
                                @error('consultation_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="follow_up_fee" class="form-label">رسوم المتابعة (ريال) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('follow_up_fee') is-invalid @enderror" 
                                       id="follow_up_fee" name="follow_up_fee" value="{{ old('follow_up_fee', 0) }}" 
                                       min="0" step="0.01" required>
                                @error('follow_up_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="room_number" class="form-label">رقم الغرفة</label>
                                <input type="text" class="form-control @error('room_number') is-invalid @enderror" 
                                       id="room_number" name="room_number" value="{{ old('room_number') }}">
                                @error('room_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="doctor_phone" class="form-label">هاتف الطبيب المباشر</label>
                                <input type="tel" class="form-control @error('doctor_phone') is-invalid @enderror" 
                                       id="doctor_phone" name="doctor_phone" value="{{ old('doctor_phone') }}">
                                @error('doctor_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="doctor_email" class="form-label">إيميل الطبيب المباشر</label>
                                <input type="email" class="form-control @error('doctor_email') is-invalid @enderror" 
                                       id="doctor_email" name="doctor_email" value="{{ old('doctor_email') }}">
                                @error('doctor_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="profile_photo" class="form-label">الصورة الشخصية</label>
                                <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                                       id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg">
                                @error('profile_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">الحد الأقصى: 2 ميجابايت، الأنواع المدعومة: JPG, PNG</div>
                            </div>
                        </div>

                        <!-- Working Hours Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-clock"></i>
                                    ساعات العمل
                                </h6>
                            </div>
                            
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
                            @endphp
                            
                            @foreach($days as $dayKey => $dayName)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="working_{{ $dayKey }}" 
                                                       name="working_hours[{{ $dayKey }}][is_working]" 
                                                       value="1" 
                                                       {{ in_array($dayKey, ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']) ? 'checked' : '' }}
                                                       onchange="toggleWorkingHours('{{ $dayKey }}')">
                                                <label class="form-check-label fw-bold" for="working_{{ $dayKey }}">
                                                    {{ $dayName }}
                                                </label>
                                            </div>
                                            <div id="hours_{{ $dayKey }}" class="working-hours" 
                                                 style="{{ in_array($dayKey, ['friday', 'saturday']) ? 'display: none;' : '' }}">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <label class="form-label small">من</label>
                                                        <input type="time" class="form-control form-control-sm" 
                                                               name="working_hours[{{ $dayKey }}][start]" 
                                                               value="08:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small">إلى</label>
                                                        <input type="time" class="form-control form-control-sm" 
                                                               name="working_hours[{{ $dayKey }}][end]" 
                                                               value="16:00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Status Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-toggle-on"></i>
                                    حالة الطبيب
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        نشط في النظام
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_available" 
                                           name="is_available" value="1" checked>
                                    <label class="form-check-label" for="is_available">
                                        متاح لاستقبال المواعيد
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-facebook">
                                        <i class="bi bi-check-circle"></i>
                                        حفظ الطبيب
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

@push('scripts')
<script>
function toggleWorkingHours(day) {
    const checkbox = document.getElementById('working_' + day);
    const hoursDiv = document.getElementById('hours_' + day);
    
    if (checkbox.checked) {
        hoursDiv.style.display = 'block';
    } else {
        hoursDiv.style.display = 'none';
    }
}

// National ID validation
document.getElementById('national_id').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});

// Form validation
document.getElementById('doctorForm').addEventListener('submit', function(e) {
    const nationalId = document.getElementById('national_id').value;
    if (nationalId.length !== 10) {
        e.preventDefault();
        alert('الرقم القومي يجب أن يكون 10 أرقام بالضبط');
        document.getElementById('national_id').focus();
        return false;
    }
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('كلمة المرور وتأكيد كلمة المرور غير متطابقتين');
        document.getElementById('password_confirmation').focus();
        return false;
    }
});

// Preview profile photo
document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create preview if doesn't exist
            let preview = document.getElementById('photo_preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'photo_preview';
                preview.className = 'img-thumbnail mt-2';
                preview.style.maxWidth = '150px';
                preview.style.maxHeight = '150px';
                document.getElementById('profile_photo').parentNode.appendChild(preview);
            }
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection