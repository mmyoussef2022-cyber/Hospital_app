@extends('layouts.app')

@section('page-title', 'إدارة العروض')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">
                    <i class="fas fa-tags me-2"></i>
                    إدارة العروض
                </h2>
                <div class="btn-group">
                    <a href="{{ route('admin.landing-page.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للوحة التحكم
                    </a>
                    <a href="{{ route('admin.landing-page.offers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة عرض جديد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">الحالة</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">جميع الحالات</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>منتهي الصلاحية</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="featured" class="form-label">مميز</label>
                            <select class="form-select" id="featured" name="featured">
                                <option value="">الكل</option>
                                <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>مميز</option>
                                <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>غير مميز</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">البحث</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="البحث في العنوان أو الوصف">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i>
                                    بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Offers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        قائمة العروض ({{ $offers->total() }} عرض)
                    </h6>
                </div>
                <div class="card-body">
                    @if($offers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">#</th>
                                        <th width="80">الصورة</th>
                                        <th>العنوان</th>
                                        <th width="120">نوع الخصم</th>
                                        <th width="100">قيمة الخصم</th>
                                        <th width="120">تاريخ البداية</th>
                                        <th width="120">تاريخ النهاية</th>
                                        <th width="80">الحالة</th>
                                        <th width="80">مميز</th>
                                        <th width="150">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($offers as $offer)
                                        <tr>
                                            <td>{{ $offer->id }}</td>
                                            <td>
                                                @if($offer->image)
                                                    <img src="{{ Storage::url($offer->image) }}" 
                                                         alt="{{ $offer->title }}" 
                                                         class="img-thumbnail" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $offer->title }}</strong>
                                                @if($offer->description)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($offer->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($offer->discount_type)
                                                    @case('percentage')
                                                        <span class="badge bg-info">نسبة مئوية</span>
                                                        @break
                                                    @case('fixed')
                                                        <span class="badge bg-success">مبلغ ثابت</span>
                                                        @break
                                                    @case('free')
                                                        <span class="badge bg-warning">مجاني</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($offer->discount_type === 'percentage')
                                                    {{ $offer->discount_value }}%
                                                @elseif($offer->discount_type === 'fixed')
                                                    {{ $offer->discount_value }} ريال
                                                @else
                                                    مجاناً
                                                @endif
                                            </td>
                                            <td>{{ $offer->valid_from->format('Y-m-d') }}</td>
                                            <td>{{ $offer->valid_until->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="status_{{ $offer->id }}" 
                                                           {{ $offer->is_active ? 'checked' : '' }}
                                                           onchange="toggleOfferStatus({{ $offer->id }})">
                                                </div>
                                            </td>
                                            <td>
                                                @if($offer->is_featured)
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-star"></i>
                                                        مميز
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">عادي</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.landing-page.offers.edit', $offer) }}" 
                                                       class="btn btn-outline-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteOffer({{ $offer->id }})" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $offers->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد عروض</h5>
                            <p class="text-muted">لم يتم العثور على أي عروض. ابدأ بإضافة عرض جديد.</p>
                            <a href="{{ route('admin.landing-page.offers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                إضافة عرض جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOfferStatus(offerId) {
    fetch(`/admin/landing-page/offers/${offerId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'تم بنجاح',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            // Revert checkbox state
            const checkbox = document.getElementById(`status_${offerId}`);
            checkbox.checked = !checkbox.checked;
            
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'حدث خطأ أثناء تغيير حالة العرض'
            });
        }
    })
    .catch(error => {
        // Revert checkbox state
        const checkbox = document.getElementById(`status_${offerId}`);
        checkbox.checked = !checkbox.checked;
        
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: 'حدث خطأ أثناء تغيير حالة العرض'
        });
    });
}

function deleteOffer(offerId) {
    Swal.fire({
        title: 'تأكيد الحذف',
        text: 'هل أنت متأكد من حذف هذا العرض؟ لا يمكن التراجع عن هذا الإجراء.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/landing-page/offers/${offerId}`;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(methodInput);
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endsection