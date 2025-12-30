@extends('layouts.app')

@section('title', 'إدارة المستخدمين المتقدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-users-cog"></i>
                        إدارة المستخدمين المتقدمة
                    </h3>
                    <div>
                        @can('users.create')
                            <a href="{{ route('advanced-users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                إضافة مستخدم جديد
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- فلاتر البحث -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('advanced-users.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">البحث</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="الاسم، البريد، رقم الموظف">
                                </div>
                                <div class="col-md-2">
                                    <label for="department_id" class="form-label">القسم</label>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option value="">جميع الأقسام</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" 
                                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name_ar }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="role_id" class="form-label">الدور</label>
                                    <select class="form-select" id="role_id" name="role_id">
                                        <option value="">جميع الأدوار</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" 
                                                    {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->display_name_ar }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="is_active" class="form-label">الحالة</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="">جميع الحالات</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-search"></i>
                                        بحث
                                    </button>
                                    <a href="{{ route('advanced-users.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                        مسح
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- جدول المستخدمين -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>رقم الموظف</th>
                                    <th>القسم</th>
                                    <th>الأدوار</th>
                                    <th>الحالة</th>
                                    <th>آخر دخول</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-{{ $user->is_active ? 'success' : 'secondary' }} rounded-circle">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->specialization)
                                                        <br><small class="text-muted">{{ $user->specialization }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $user->employee_id }}</span>
                                        </td>
                                        <td>
                                            @if($user->department)
                                                <span class="badge bg-primary">{{ $user->department->name_ar }}</span>
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->roles && $user->roles->count() > 0)
                                                @foreach($user->roles as $role)
                                                    <span class="badge me-1 bg-primary">
                                                        {{ $role->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">لا توجد أدوار</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->last_login_at)
                                                <small>{{ $user->last_login_at->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">لم يسجل دخول</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('users.view')
                                                    <a href="{{ route('advanced-users.show', $user) }}" 
                                                       class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('users.edit')
                                                    <a href="{{ route('advanced-users.edit', $user) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('users.manage')
                                                    <a href="{{ route('advanced-users.manage-roles', $user) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="إدارة الأدوار">
                                                        <i class="fas fa-user-cog"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>لا توجد مستخدمين مطابقين للبحث</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-left: 2px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // تحسين تجربة المستخدم مع الفلاتر
    $('.form-select, .form-control').on('change keyup', function() {
        if ($(this).attr('name') === 'search') {
            // تأخير البحث للنص
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(function() {
                // يمكن إضافة AJAX search هنا
            }, 500);
        }
    });

    // تحسين عرض الجدول على الشاشات الصغيرة
    if ($(window).width() < 768) {
        $('.table-responsive').addClass('table-responsive-sm');
    }
});
</script>
@endpush
@endsection