@extends('layouts.app')

@section('title', 'إضافة صندوق نقدي جديد')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">إضافة صندوق نقدي جديد</h1>
            <p class="text-muted">إنشاء صندوق نقدي جديد في النظام</p>
        </div>
        <div>
            <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-secondary">
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
                    <h5 class="card-title mb-0">معلومات الصندوق النقدي</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-registers.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Register Name -->
                            <div class="col-md-6">
                                <label for="register_name" class="form-label">اسم الصندوق <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('register_name') is-invalid @enderror" 
                                       id="register_name" name="register_name" 
                                       value="{{ old('register_name') }}" required
                                       placeholder="مثال: صندوق الاستقبال الرئيسي">
                                @error('register_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department -->
                            <div class="col-md-6">
                                <label for="department_id" class="form-label">القسم <span class="text-danger">*</span></label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" name="department_id" required>
                                    <option value="">اختر القسم</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div class="col-md-6">
                                <label for="location" class="form-label">الموقع</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" 
                                       value="{{ old('location') }}"
                                       placeholder="مثال: الطابق الأول - مكتب الاستقبال">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Opening Balance -->
                            <div class="col-md-6">
                                <label for="opening_balance" class="form-label">الرصيد الافتتاحي <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('opening_balance') is-invalid @enderror" 
                                           id="opening_balance" name="opening_balance" 
                                           value="{{ old('opening_balance', '0') }}" 
                                           step="0.01" min="0" required>
                                    <span class="input-group-text">ريال</span>
                                </div>
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">الرصيد النقدي الأولي في الصندوق</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ الصندوق
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Guidelines -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">إرشادات إنشاء الصندوق</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <strong>اسم الصندوق:</strong>
                            <ul class="mt-1 mb-0">
                                <li>يجب أن يكون واضحاً ومميزاً</li>
                                <li>يفضل تضمين اسم القسم أو الموقع</li>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <strong>القسم:</strong>
                            <ul class="mt-1 mb-0">
                                <li>اختر القسم المسؤول عن الصندوق</li>
                                <li>سيحدد هذا صلاحيات الوصول</li>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <strong>الموقع:</strong>
                            <ul class="mt-1 mb-0">
                                <li>حدد الموقع الفيزيائي للصندوق</li>
                                <li>يساعد في تحديد الصندوق بسهولة</li>
                            </ul>
                        </div>
                        
                        <div>
                            <strong>الرصيد الافتتاحي:</strong>
                            <ul class="mt-1 mb-0">
                                <li>المبلغ النقدي الأولي</li>
                                <li>يمكن أن يكون صفر للصناديق الجديدة</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">معلومات الحالة</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="mb-2">
                            <span class="badge bg-success me-2">نشط</span>
                            سيتم إنشاء الصندوق بحالة نشطة افتراضياً
                        </p>
                        <p class="mb-2">
                            سيتم توليد رقم الصندوق تلقائياً
                        </p>
                        <p class="mb-0">
                            يمكن تعديل معلومات الصندوق لاحقاً من صفحة التفاصيل
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>
                            عرض جميع الصناديق
                        </a>
                        <a href="{{ route('cash-registers.dashboard') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            لوحة التحكم
                        </a>
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
    // Auto-generate register name based on department and location
    const departmentSelect = document.getElementById('department_id');
    const locationInput = document.getElementById('location');
    const nameInput = document.getElementById('register_name');
    
    function updateRegisterName() {
        const departmentText = departmentSelect.options[departmentSelect.selectedIndex]?.text;
        const location = locationInput.value;
        
        if (departmentText && departmentText !== 'اختر القسم') {
            let suggestedName = 'صندوق ' + departmentText;
            if (location) {
                suggestedName += ' - ' + location;
            }
            
            // Only update if the field is empty or contains a previously generated name
            if (!nameInput.value || nameInput.value.startsWith('صندوق ')) {
                nameInput.value = suggestedName;
            }
        }
    }
    
    departmentSelect.addEventListener('change', updateRegisterName);
    locationInput.addEventListener('blur', updateRegisterName);
    
    // Format opening balance input
    const balanceInput = document.getElementById('opening_balance');
    balanceInput.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value)) {
            this.value = value.toFixed(2);
        }
    });
});
</script>
@endpush