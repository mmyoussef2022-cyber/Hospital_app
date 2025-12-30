@extends('layouts.app')

@section('title', 'إدارة تقييمات المرضى')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-facebook mb-1">
                        <i class="bi bi-star-fill"></i>
                        إدارة تقييمات المرضى
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">تقييمات المرضى</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-facebook me-2" onclick="loadStatistics()">
                        <i class="bi bi-bar-chart"></i>
                        الإحصائيات
                    </button>
                    <button type="button" class="btn btn-facebook" data-bs-toggle="modal" data-bs-target="#bulkActionsModal">
                        <i class="bi bi-list-check"></i>
                        عمليات مجمعة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statisticsCards" style="display: none;">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">إجمالي التقييمات</h6>
                            <h3 class="mb-0" id="totalReviews">0</h3>
                        </div>
                        <i class="bi bi-star-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">التقييمات المعتمدة</h6>
                            <h3 class="mb-0" id="approvedReviews">0</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">في انتظار المراجعة</h6>
                            <h3 class="mb-0" id="pendingReviews">0</h3>
                        </div>
                        <i class="bi bi-clock fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">متوسط التقييم</h6>
                            <h3 class="mb-0" id="averageRating">0.0</h3>
                        </div>
                        <i class="bi bi-star-half fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i>
                البحث والتصفية
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reviews.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">البحث</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="نص التقييم أو اسم المريض...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">الحالة</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">جميع الحالات</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>في انتظار المراجعة</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>مخفي</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="rating" class="form-label">التقييم</label>
                        <select class="form-select" id="rating" name="rating">
                            <option value="">جميع التقييمات</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                    {{ $i }} نجوم
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="doctor_id" class="form-label">الطبيب</label>
                        <select class="form-select" id="doctor_id" name="doctor_id">
                            <option value="">جميع الأطباء</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->user->name }} - {{ $doctor->specialization }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="is_featured" class="form-label">مميز</label>
                        <select class="form-select" id="is_featured" name="is_featured">
                            <option value="">الكل</option>
                            <option value="yes" {{ request('is_featured') == 'yes' ? 'selected' : '' }}>مميز</option>
                            <option value="no" {{ request('is_featured') == 'no' ? 'selected' : '' }}>غير مميز</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="date_from" class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_to" class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-facebook me-2">
                            <i class="bi bi-search"></i>
                            بحث
                        </button>
                        <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-list"></i>
                    قائمة التقييمات ({{ $reviews->total() }})
                </h6>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn" style="display: none;">
                        <i class="bi bi-trash"></i>
                        حذف المحدد
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="bulkApproveBtn" style="display: none;">
                        <i class="bi bi-check-circle"></i>
                        اعتماد المحدد
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($reviews->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>المريض</th>
                                <th>الطبيب</th>
                                <th>التقييم</th>
                                <th>التعليق</th>
                                <th>التاريخ</th>
                                <th>الحالة</th>
                                <th width="150">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input review-checkbox" 
                                               value="{{ $review->id }}">
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $review->patient_name }}</div>
                                            @if($review->appointment)
                                                <small class="text-muted">موعد #{{ $review->appointment->id }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $review->doctor->user->name }}</div>
                                            <small class="text-muted">{{ $review->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">{{ $review->rating }}</span>
                                            {!! $review->rating_stars !!}
                                        </div>
                                        @if($review->is_featured)
                                            <span class="badge bg-warning mt-1">مميز</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            {{ $review->review_summary }}
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div>{{ $review->created_at->format('Y-m-d') }}</div>
                                            <small class="text-muted">{{ $review->time_ago }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        {!! $review->status_badge !!}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('reviews.show', $review) }}" 
                                               class="btn btn-outline-info" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($review->status === 'pending')
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="approveReview({{ $review->id }})" title="اعتماد">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="rejectReview({{ $review->id }})" title="رفض">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="toggleFeature({{ $review->id }})" 
                                                    title="{{ $review->is_featured ? 'إلغاء التمييز' : 'تمييز' }}">
                                                <i class="bi bi-star{{ $review->is_featured ? '-fill' : '' }}"></i>
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
                    {{ $reviews->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-star text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">لا توجد تقييمات</h5>
                    <p class="text-muted">لم يتم العثور على تقييمات تطابق معايير البحث</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">العمليات المجمعة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>اختر العملية المطلوب تطبيقها على التقييمات المحددة:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="bulkAction('approve')">
                        <i class="bi bi-check-circle"></i>
                        اعتماد التقييمات المحددة
                    </button>
                    <button type="button" class="btn btn-warning" onclick="bulkAction('reject')">
                        <i class="bi bi-x-circle"></i>
                        رفض التقييمات المحددة
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="bulkAction('hide')">
                        <i class="bi bi-eye-slash"></i>
                        إخفاء التقييمات المحددة
                    </button>
                    <button type="button" class="btn btn-info" onclick="bulkAction('feature')">
                        <i class="bi bi-star"></i>
                        تمييز التقييمات المحددة
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkAction('delete')">
                        <i class="bi bi-trash"></i>
                        حذف التقييمات المحددة
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Load statistics
function loadStatistics() {
    fetch('{{ route("reviews.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalReviews').textContent = data.total;
            document.getElementById('approvedReviews').textContent = data.approved;
            document.getElementById('pendingReviews').textContent = data.pending;
            document.getElementById('averageRating').textContent = (data.average_rating || 0).toFixed(1);
            
            document.getElementById('statisticsCards').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
            alert('حدث خطأ في تحميل الإحصائيات');
        });
}

// Approve review
function approveReview(reviewId) {
    if (confirm('هل أنت متأكد من اعتماد هذا التقييم؟')) {
        fetch(`/reviews/${reviewId}/approve`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ في اعتماد التقييم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في اعتماد التقييم');
        });
    }
}

// Reject review
function rejectReview(reviewId) {
    const notes = prompt('سبب الرفض (اختياري):');
    if (notes !== null) {
        fetch(`/reviews/${reviewId}/reject`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ admin_notes: notes })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ في رفض التقييم');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في رفض التقييم');
        });
    }
}

// Toggle feature
function toggleFeature(reviewId) {
    fetch(`/reviews/${reviewId}/toggle-feature`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تغيير حالة التمييز');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تغيير حالة التمييز');
    });
}

// Bulk actions
function bulkAction(action) {
    const selectedReviews = Array.from(document.querySelectorAll('.review-checkbox:checked')).map(cb => cb.value);
    
    if (selectedReviews.length === 0) {
        alert('يرجى اختيار تقييم واحد على الأقل');
        return;
    }

    let confirmMessage = '';
    let adminNotes = null;
    
    switch (action) {
        case 'approve':
            confirmMessage = `هل أنت متأكد من اعتماد ${selectedReviews.length} تقييم؟`;
            break;
        case 'reject':
            adminNotes = prompt('سبب الرفض (اختياري):');
            if (adminNotes === null) return;
            confirmMessage = `هل أنت متأكد من رفض ${selectedReviews.length} تقييم؟`;
            break;
        case 'hide':
            adminNotes = prompt('سبب الإخفاء (اختياري):');
            if (adminNotes === null) return;
            confirmMessage = `هل أنت متأكد من إخفاء ${selectedReviews.length} تقييم؟`;
            break;
        case 'feature':
            confirmMessage = `هل أنت متأكد من تمييز ${selectedReviews.length} تقييم؟`;
            break;
        case 'delete':
            confirmMessage = `هل أنت متأكد من حذف ${selectedReviews.length} تقييم؟ هذا الإجراء لا يمكن التراجع عنه.`;
            break;
    }

    if (confirm(confirmMessage)) {
        fetch('{{ route("reviews.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                review_ids: selectedReviews,
                admin_notes: adminNotes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('حدث خطأ في تنفيذ العملية');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تنفيذ العملية');
        });
    }

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal'));
    modal.hide();
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkButtons();
});

// Update bulk buttons visibility
function updateBulkButtons() {
    const selectedCount = document.querySelectorAll('.review-checkbox:checked').length;
    const bulkButtons = document.querySelectorAll('#bulkDeleteBtn, #bulkApproveBtn');
    
    bulkButtons.forEach(btn => {
        btn.style.display = selectedCount > 0 ? 'inline-block' : 'none';
    });
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.review-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkButtons);
    });
});
</script>
@endpush