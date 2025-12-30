@extends('layouts.app')

@section('title', 'تفاصيل المستخدم - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user"></i>
                        تفاصيل المستخدم: {{ $user->name }}
                    </h4>
                    <div>
                        @can('users.edit')
                            <a href="{{ route('advanced-users.edit', $user) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>
                        @endcan
                        @can('users.manage')
                            <a href="{{ route('advanced-users.manage-roles', $user) }}" class="btn btn-warning">
                                <i class="fas fa-user-cog"></i>
                                إدارة الأدوار
                            </a>
                        @endcan
                        <a href="{{ route('advanced-users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- معلومات شخصية -->
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-user-circle"></i>
                                المعلومات الشخصية
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>الاسم:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>البريد الإلكتروني:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>رقم الهوية:</strong></td>
                                    <td>{{ $user->national_id ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الهاتف:</strong></td>
                                    <td>{{ $user->phone ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الجوال:</strong></td>
                                    <td>{{ $user->mobile ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الجنس:</strong></td>
                                    <td>{{ $user->gender == 'male' ? 'ذكر' : 'أنثى' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>تاريخ الميلاد:</strong></td>
                                    <td>{{ $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : 'غير محدد' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- معلومات وظيفية -->
                        <div class="col-md-6">
                            <h5 class="text-success mb-3">
                                <i class="fas fa-briefcase"></i>
                                المعلومات الوظيفية
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>رقم الموظف:</strong></td>
                                    <td>{{ $user->employee_id ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>القسم:</strong></td>
                                    <td>{{ $user->department->name ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>المسمى الوظيفي:</strong></td>
                                    <td>{{ $user->job_title ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>التخصص:</strong></td>
                                    <td>{{ $user->specialization ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>رقم الترخيص:</strong></td>
                                    <td>{{ $user->license_number ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>تاريخ التوظيف:</strong></td>
                                    <td>{{ $user->hire_date ? $user->hire_date->format('Y-m-d') : 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>حالة التوظيف:</strong></td>
                                    <td>
                                        @if($user->employment_status == 'active')
                                            <span class="badge bg-success">نشط</span>
                                        @elseif($user->employment_status == 'inactive')
                                            <span class="badge bg-warning">غير نشط</span>
                                        @else
                                            <span class="badge bg-danger">منتهي الخدمة</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- الأدوار والصلاحيات -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-shield-alt"></i>
                                الأدوار والصلاحيات
                            </h5>
                            
                            @if($user->roles->count() > 0)
                                <div class="row">
                                    @foreach($user->roles as $role)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border-info">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="fas fa-user-tag"></i>
                                                        {{ $role->name }}
                                                    </h6>
                                                    <p class="card-text small text-muted">
                                                        {{ $role->guard_name ?? 'web' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    لم يتم تعيين أي أدوار لهذا المستخدم
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- حالة النشاط -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-warning mb-3">
                                <i class="fas fa-toggle-on"></i>
                                حالة النشاط
                            </h5>
                            <div class="alert {{ $user->is_active ? 'alert-success' : 'alert-danger' }}">
                                @if($user->is_active)
                                    <i class="fas fa-check-circle"></i>
                                    المستخدم نشط ويمكنه الوصول للنظام
                                @else
                                    <i class="fas fa-times-circle"></i>
                                    المستخدم غير نشط ولا يمكنه الوصول للنظام
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection