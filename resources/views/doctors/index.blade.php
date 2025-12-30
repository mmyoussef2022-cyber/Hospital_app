@extends('layouts.app')

@section('page-title', 'إدارة الأطباء')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-facebook"></i>
                        قائمة الأطباء
                    </h5>
                    <a href="{{ route('doctors.create') }}" class="btn btn-facebook">
                        <i class="bi bi-plus-circle"></i>
                        إضافة طبيب جديد
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="اسم الطبيب، الإيميل، رقم الطبيب..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">التخصص</label>
                            <select name="specialization" class="form-select">
                                <option value="">جميع التخصصات</option>
                                @foreach($specializations as $key => $value)
                                    <option value="{{ $key }}" {{ request('specialization') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">القسم</label>
                            <select name="department_id" class="form-select">
                                <option value="">جميع الأقسام</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-facebook">
                                    <i class="bi bi-search"></i>
                                    بحث
                                </button>
                                <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">إجمالي الأطباء</h6>
                                            <h3 class="mb-0">{{ $doctors->total() }}</h3>
                                        </div>
                                        <i class="bi bi-people-fill" style="font-size: 2rem; opacity: 0.7;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">الأطباء النشطون</h6>
                                            <h3 class="mb-0">{{ $doctors->where('is_active', true)->count() }}</h3>
                                        </div>
                                        <i class="bi bi-check-circle-fill" style="font-size: 2rem; opacity: 0.7;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">متاحون اليوم</h6>
                                            <h3 class="mb-0">{{ $doctors->where('is_available', true)->count() }}</h3>
                                        </div>
                                        <i class="bi bi-calendar-check-fill" style="font-size: 2rem; opacity: 0.7;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">التخصصات</h6>
                                            <h3 class="mb-0">{{ count($specializations) }}</h3>
                                        </div>
                                        <i class="bi bi-award-fill" style="font-size: 2rem; opacity: 0.7;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Doctors Table -->
                    @if($doctors->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>الطبيب</th>
                                        <th>التخصص</th>
                                        <th>القسم</th>
                                        <th>الخبرة</th>
                                        <th>رسوم الاستشارة</th>
                                        <th>الحالة</th>
                                        <th>التقييم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($doctors as $doctor)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $doctor->profile_photo_url }}" 
                                                         alt="{{ $doctor->user->name }}" 
                                                         class="rounded-circle me-3" 
                                                         width="50" height="50"
                                                         style="object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold">{{ $doctor->full_name }}</div>
                                                        <small class="text-muted">{{ $doctor->doctor_number }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $doctor->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $specializations[$doctor->specialization] ?? $doctor->specialization }}
                                                </span>
                                                @if($doctor->sub_specializations)
                                                    <br>
                                                    @foreach($doctor->sub_specializations as $sub)
                                                        <small class="badge bg-light text-dark">{{ $sub }}</small>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $doctor->user->department->name ?? 'غير محدد' }}</td>
                                            <td>{{ $doctor->experience_display }}</td>
                                            <td>{{ number_format($doctor->consultation_fee, 2) }} ريال</td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge bg-{{ $doctor->is_active ? 'success' : 'secondary' }}">
                                                        {{ $doctor->is_active ? 'نشط' : 'غير نشط' }}
                                                    </span>
                                                    <span class="badge bg-{{ $doctor->is_available ? 'primary' : 'warning' }}">
                                                        {{ $doctor->is_available ? 'متاح' : 'غير متاح' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($doctor->total_reviews > 0)
                                                    <div class="text-warning">
                                                        {{ $doctor->rating_display }}
                                                    </div>
                                                    <small class="text-muted">({{ $doctor->total_reviews }} تقييم)</small>
                                                @else
                                                    <small class="text-muted">لا توجد تقييمات</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('doctors.show', $doctor) }}" 
                                                       class="btn btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('doctors.edit', $doctor) }}" 
                                                       class="btn btn-outline-warning" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-{{ $doctor->is_available ? 'secondary' : 'success' }}" 
                                                            onclick="toggleAvailability({{ $doctor->id }})"
                                                            title="{{ $doctor->is_available ? 'جعل غير متاح' : 'جعل متاح' }}">
                                                        <i class="bi bi-{{ $doctor->is_available ? 'pause' : 'play' }}-circle"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('doctors.destroy', $doctor) }}" 
                                                          class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطبيب؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
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

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $doctors->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">لا توجد أطباء</h5>
                            <p class="text-muted">لم يتم العثور على أطباء تطابق معايير البحث</p>
                            <a href="{{ route('doctors.create') }}" class="btn btn-facebook">
                                <i class="bi bi-plus-circle"></i>
                                إضافة طبيب جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAvailability(doctorId) {
    fetch(`/doctors/${doctorId}/toggle-availability`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تحديث حالة الطبيب');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث حالة الطبيب');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterInputs = document.querySelectorAll('select[name="specialization"], select[name="department_id"], select[name="status"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
@endsection