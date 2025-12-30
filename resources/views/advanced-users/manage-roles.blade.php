@extends('layouts.app')

@section('page-title', 'إدارة أدوار المستخدم')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-gear"></i>
                        إدارة أدوار المستخدم: {{ $user->name }}
                    </h5>
                    <a href="{{ route('advanced-users.show', $user) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i>
                        العودة لملف المستخدم
                    </a>
                </div>
                <div class="card-body">
                    <!-- معلومات المستخدم -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">معلومات المستخدم</h6>
                                    <p class="mb-1"><strong>الاسم:</strong> {{ $user->name }}</p>
                                    <p class="mb-1"><strong>البريد الإلكتروني:</strong> {{ $user->email }}</p>
                                    <p class="mb-1"><strong>المسمى الوظيفي:</strong> {{ $user->job_title ?? 'غير محدد' }}</p>
                                    <p class="mb-0"><strong>القسم:</strong> {{ $user->department ?? 'غير محدد' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">إحصائيات الأدوار</h6>
                                    <p class="mb-1"><strong>عدد الأدوار الحالية:</strong> {{ $user->userRoles->count() }}</p>
                                    <p class="mb-1"><strong>الأدوار النشطة:</strong> {{ $user->userRoles->where('is_active', true)->count() }}</p>
                                    <p class="mb-0"><strong>الأدوار المؤقتة:</strong> {{ $user->userRoles->whereNotNull('expires_at')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- الأدوار الحالية -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>الأدوار الحالية</h6>
                            @if($user->userRoles->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>الدور</th>
                                                <th>القسم</th>
                                                <th>تاريخ التعيين</th>
                                                <th>تاريخ الانتهاء</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->userRoles as $userRole)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $userRole->role->name }}</span>
                                                    </td>
                                                    <td>{{ $userRole->department->name ?? 'عام' }}</td>
                                                    <td>{{ $userRole->assigned_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        @if($userRole->expires_at)
                                                            <span class="badge bg-warning">{{ $userRole->expires_at->format('Y-m-d') }}</span>
                                                        @else
                                                            <span class="badge bg-success">دائم</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($userRole->is_active)
                                                            <span class="badge bg-success">نشط</span>
                                                        @else
                                                            <span class="badge bg-secondary">غير نشط</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            @if($userRole->is_active)
                                                                <form method="POST" action="{{ route('advanced-users.deactivate-role', [$user, $userRole]) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من إلغاء تفعيل هذا الدور؟')">
                                                                        <i class="bi bi-pause"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <form method="POST" action="{{ route('advanced-users.activate-role', [$user, $userRole]) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-success">
                                                                        <i class="bi bi-play"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <form method="POST" action="{{ route('advanced-users.remove-role', [$user, $userRole]) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الدور؟')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    لا توجد أدوار مُعيَّنة لهذا المستخدم حالياً.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- إضافة دور جديد -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-plus-circle"></i>
                                        إضافة دور جديد
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('advanced-users.assign-role', $user) }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="role_id" class="form-label">الدور <span class="text-danger">*</span></label>
                                                    <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                                        <option value="">اختر الدور</option>
                                                        @foreach($availableRoles as $role)
                                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                                {{ $role->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('role_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="department_id" class="form-label">القسم</label>
                                                    <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                                        <option value="">عام (جميع الأقسام)</option>
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
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="expires_at" class="form-label">تاريخ الانتهاء</label>
                                                    <input type="date" name="expires_at" id="expires_at" 
                                                           class="form-control @error('expires_at') is-invalid @enderror" 
                                                           value="{{ old('expires_at') }}"
                                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                                    @error('expires_at')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted">اتركه فارغاً للدور الدائم</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label for="reason" class="form-label">سبب التعيين <span class="text-danger">*</span></label>
                                                    <textarea name="reason" id="reason" rows="3" 
                                                              class="form-control @error('reason') is-invalid @enderror" 
                                                              placeholder="اذكر سبب تعيين هذا الدور للمستخدم..." required>{{ old('reason') }}</textarea>
                                                    @error('reason')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle"></i>
                                                    إضافة الدور
                                                </button>
                                                <a href="{{ route('advanced-users.show', $user) }}" class="btn btn-secondary">
                                                    <i class="bi bi-x-circle"></i>
                                                    إلغاء
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
    // تحديث القائمة المنسدلة للأقسام بناءً على الدور المختار
    const roleSelect = document.getElementById('role_id');
    const departmentSelect = document.getElementById('department_id');
    
    roleSelect.addEventListener('change', function() {
        // يمكن إضافة منطق لتصفية الأقسام بناءً على الدور المختار
        // هذا مثال بسيط - يمكن تطويره أكثر
        console.log('تم اختيار الدور:', this.value);
    });
});
</script>
@endpush