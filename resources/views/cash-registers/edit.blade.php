@extends('layouts.app')

@section('title', 'تعديل الصندوق النقدي')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">تعديل الصندوق النقدي</h1>
            <p class="text-muted">تعديل معلومات الصندوق #{{ $cashRegister->register_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cash-registers.show', $cashRegister) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>
                العودة لتفاصيل الصندوق
            </a>
            <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i>
                قائمة الصناديق
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل معلومات الصندوق
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-registers.update', $cashRegister) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Current Register Info -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>رقم الصندوق:</strong> {{ $cashRegister->register_number }}
                                </div>
                                <div class="col-md-6">
                                    <strong>الحالة:</strong> 
                                    <span class="badge bg-primary">
                                        {{ $cashRegister->status_display }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Register Name -->
                            <div class="col-md-6">
                                <label for="register_name" class="form-label">
                                    اسم الصندوق <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('register_name') is-invalid @enderror" 
                                       id="register_name" 
                                       name="register_name" 
                                       value="{{ old('register_name', $cashRegister->register_name) }}" 
                                       required>
                                @error('register_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department -->
                            <div class="col-md-6">
                                <label for="department_id" class="form-label">
                                    القسم <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" 
                                        name="department_id" 
                                        required>
                                    <option value="">اختر القسم</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ old('department_id', $cashRegister->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div class="col-12">
                                <label for="location" class="form-label">الموقع</label>
                                <input type="text" 
                                       class="form-control @error('location') is-invalid @enderror" 
                                       id="location" 
                                       name="location" 
                                       value="{{ old('location', $cashRegister->location) }}" 
                                       placeholder="مثال: الطابق الأول - مكتب الاستقبال">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <div>
                                <a href="{{ route('cash-registers.show', $cashRegister) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    إلغاء
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    حفظ التغييرات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection