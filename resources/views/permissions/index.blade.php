@extends('layouts.app')

@section('title', 'إدارة الصلاحيات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-key"></i>
                        إدارة الصلاحيات
                    </h3>
                    <div>
                        @can('users.manage')
                        <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            إضافة صلاحية جديدة
                        </a>
                        @endcan
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- إحصائيات سريعة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $permissions->total() }}</h4>
                                            <p class="mb-0">إجمالي الصلاحيات</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-key fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $groupedPermissions->count() }}</h4>
                                            <p class="mb-0">الوحدات</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-layer-group fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $roles->count() }}</h4>
                                            <p class="mb-0">الأدوار</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-tag fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $permissions->sum('users_count') }}</h4>
                                            <p class="mb-0">المستخدمين المرتبطين</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- الصلاحيات مجمعة حسب الوحدة -->
                    @foreach($groupedPermissions as $module => $modulePermissions)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-folder"></i>
                                وحدة {{ ucfirst($module) }}
                                <span class="badge bg-secondary">{{ $modulePermissions->count() }} صلاحية</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>اسم الصلاحية</th>
                                            <th>الاسم المعروض</th>
                                            <th>الوصف</th>
                                            <th>الأدوار</th>
                                            <th>المستخدمين</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modulePermissions as $permission)
                                        <tr>
                                            <td>
                                                <code>{{ $permission->name }}</code>
                                            </td>
                                            <td>{{ $permission->display_name ?? $permission->name }}</td>
                                            <td>{{ Str::limit($permission->description ?? 'لا يوجد وصف', 30) }}</td>
                                            <td>
                                                @foreach($permission->roles as $role)
                                                    <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $permission->users_count }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('permissions.show', $permission) }}" 
                                                       class="btn btn-sm btn-outline-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @can('users.manage')
                                                    <a href="{{ route('permissions.edit', $permission) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    @if($permission->users_count == 0 && $permission->roles->count() == 0)
                                                    <form method="POST" action="{{ route('permissions.destroy', $permission) }}" 
                                                          class="d-inline" 
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه الصلاحية؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-center">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تفعيل tooltips
    $('[title]').tooltip();
});
</script>
@endpush