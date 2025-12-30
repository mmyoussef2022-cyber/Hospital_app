@extends('layouts.app')

@section('title', 'إدارة الأدوار')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-user-tag"></i>
                        إدارة الأدوار
                    </h3>
                    <div>
                        @can('users.manage')
                        <a href="{{ route('roles.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            إضافة دور جديد
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>اسم الدور</th>
                                    <th>الاسم المعروض</th>
                                    <th>الوصف</th>
                                    <th>عدد الصلاحيات</th>
                                    <th>عدد المستخدمين</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                    </td>
                                    <td>{{ $role->display_name ?? $role->name }}</td>
                                    <td>{{ Str::limit($role->description ?? 'لا يوجد وصف', 50) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $role->users_count }}</span>
                                    </td>
                                    <td>{{ $role->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('roles.show', $role) }}" 
                                               class="btn btn-sm btn-outline-info" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @can('users.manage')
                                            <a href="{{ route('roles.edit', $role) }}" 
                                               class="btn btn-sm btn-outline-warning" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if($role->users_count == 0)
                                            <form method="POST" action="{{ route('roles.destroy', $role) }}" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الدور؟')">
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
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-user-tag fa-3x mb-3"></i>
                                        <p>لا توجد أدوار مسجلة</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $roles->links() }}
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