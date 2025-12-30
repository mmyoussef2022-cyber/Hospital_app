@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus"></i>
                        إضافة مستخدم جديد
                    </h4>
                    <a href="{{ route('advanced-users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i>
                        العودة للقائمة
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('advanced-users.store') }}">
                        @csrf

                        <div class="row">
                            <!-- المعلومات الشخصية -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user-circle"></i>
                                    المعلومات الشخصية
                                </h5>

                                <div class="mb-3">
                                    <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>

                                <div class="mb-3">
                                    <label for="national_id" class="form-label">رقم الهوية <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('national_id') is-invalid @enderror" 
                                           id="national_id" name="national_id" value="{{ old('national_id') }}" required>
                                    @error('national_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">الهاتف</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" value="{{ old('phone') }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mobile" class="form-label">الجوال</label>
                                            <input type="text" class="form-control @error('mobile') is-invalid @enderror" 
                                                   id="mobile" name="mobile" value="{{ old('mobile') }}">
                                            @error('mobile')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gender" class="form-label">الجنس <span class="text-danger">*</span></label>
                                            <select class="form-select @error('gender') is-invalid @enderror" 
                                                    id="gender" name="gender" required>
                                                <option value="">اختر الجنس</option>
                                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                                            </select>
                                            @error('gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label">تاريخ الميلاد</label>
                                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- المعلومات الوظيفية -->
                            <div class="col-md-6">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-briefcase"></i>
                                    المعلومات الوظيفية
                                </h5>

                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">رقم الموظف <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                           id="employee_id" name="employee_id" value="{{ old('employee_id') }}" required>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
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

                                <div class="mb-3">
                                    <label for="job_title" class="form-label">المسمى الوظيفي <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('job_title') is-invalid @enderror" 
                                           id="job_title" name="job_title" value="{{ old('job_title') }}" required>
                                    @error('job_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="specialization" class="form-label">التخصص</label>
                                    <select class="form-select @error('specialization') is-invalid @enderror" 
                                            id="specialization" name="specialization">
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

                                <div class="mb-3">
                                    <label for="license_number" class="form-label">رقم الترخيص</label>
                                    <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                           id="license_number" name="license_number" value="{{ old('license_number') }}">
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hire_date" class="form-label">تاريخ التوظيف <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                                   id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required>
                                            @error('hire_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="employment_status" class="form-label">حالة التوظيف <span class="text-danger">*</span></label>
                                            <select class="form-select @error('employment_status') is-invalid @enderror" 
                                                    id="employment_status" name="employment_status" required>
                                                <option value="">اختر الحالة</option>
                                                <option value="active" {{ old('employment_status') == 'active' ? 'selected' : '' }}>نشط</option>
                                                <option value="inactive" {{ old('employment_status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                                <option value="terminated" {{ old('employment_status') == 'terminated' ? 'selected' : '' }}>منتهي الخدمة</option>
                                            </select>
                                            @error('employment_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="salary" class="form-label">الراتب</label>
                                    <input type="number" step="0.01" class="form-control @error('salary') is-invalid @enderror" 
                                           id="salary" name="salary" value="{{ old('salary') }}">
                                    @error('salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- الأدوار والصلاحيات -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-info mb-3">
                                    <i class="fas fa-shield-alt"></i>
                                    الأدوار والصلاحيات
                                </h5>

                                <div class="mb-3">
                                    <label class="form-label">الأدوار <span class="text-danger">*</span></label>
                                    <div class="row">
                                        @foreach($roles as $role)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input @error('roles') is-invalid @enderror" 
                                                           type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                                           id="role_{{ $role->id }}"
                                                           {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                                        {{ $role->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('roles')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('advanced-users.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        حفظ المستخدم
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