@extends('layouts.app')

@section('title', 'إدارة الفحوصات المخبرية')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-vials me-2"></i>
                        إدارة الفحوصات المخبرية
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('lab-tests.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i>
                            تصدير
                        </a>
                        <a href="{{ route('lab-tests.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            فحص جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <select name="category" class="form-select">
                                        <option value="">جميع الفئات</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="is_active" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="البحث بالاسم أو الكود..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search me-1"></i>
                                            بحث
                                        </button>
                                        <a href="{{ route('lab-tests.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            إلغاء
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form id="bulk-action-form" method="POST" action="{{ route('lab-tests.bulk-action') }}">
                                @csrf
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                        <label class="form-check-label" for="select-all">
                                            تحديد الكل
                                        </label>
                                    </div>
                                    <select name="action" class="form-select" style="width: auto;" disabled>
                                        <option value="">اختر إجراء...</option>
                                        <option value="activate">تفعيل</option>
                                        <option value="deactivate">إلغاء تفعيل</option>
                                        <option value="delete">حذف</option>
                                    </select>
                                    <button type="submit" class="btn btn-warning btn-sm" disabled>
                                        <i class="fas fa-play me-1"></i>
                                        تنفيذ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tests Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select-all-header">
                                    </th>
                                    <th>الكود</th>
                                    <th>اسم الفحص</th>
                                    <th>الاسم بالإنجليزية</th>
                                    <th>الفئة</th>
                                    <th>السعر</th>
                                    <th>المدة</th>
                                    <th>نوع العينة</th>
                                    <th>الحالة</th>
                                    <th width="200">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tests as $test)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="test_ids[]" value="{{ $test->id }}" class="test-checkbox">
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $test->code }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $test->name }}</strong>
                                            @if($test->description)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($test->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $test->name_en ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $test->category }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($test->price, 2) }} ر.س</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ $test->duration_minutes }} دقيقة</span>
                                        </td>
                                        <td>{{ $test->specimen_type }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" 
                                                       type="checkbox" 
                                                       data-test-id="{{ $test->id }}"
                                                       {{ $test->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    <span class="badge bg-{{ $test->is_active ? 'success' : 'danger' }}">
                                                        {{ $test->is_active ? 'نشط' : 'غير نشط' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('lab-tests.show', $test) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('lab-tests.edit', $test) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger delete-test" 
                                                        data-test-id="{{ $test->id }}"
                                                        data-test-name="{{ $test->name }}"
                                                        title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-vials fa-3x mb-3"></i>
                                                <h5>لا توجد فحوصات مخبرية</h5>
                                                <p>لم يتم العثور على أي فحوصات مخبرية.</p>
                                                <a href="{{ route('lab-tests.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i>
                                                    إضافة فحص جديد
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($tests->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $tests->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف الفحص المخبري: <strong id="test-name"></strong>؟</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    لا يمكن التراجع عن هذا الإجراء.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.status-toggle {
    cursor: pointer;
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

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.table-responsive {
    border-radius: 0.5rem;
    overflow: hidden;
}

.empty-state {
    padding: 3rem 1rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#select-all, #select-all-header').change(function() {
        const isChecked = $(this).is(':checked');
        $('.test-checkbox').prop('checked', isChecked);
        toggleBulkActions();
    });

    // Individual checkbox change
    $('.test-checkbox').change(function() {
        const totalCheckboxes = $('.test-checkbox').length;
        const checkedCheckboxes = $('.test-checkbox:checked').length;
        
        $('#select-all, #select-all-header').prop('checked', totalCheckboxes === checkedCheckboxes);
        toggleBulkActions();
    });

    // Toggle bulk action controls
    function toggleBulkActions() {
        const hasChecked = $('.test-checkbox:checked').length > 0;
        $('select[name="action"]').prop('disabled', !hasChecked);
        $('button[type="submit"]').prop('disabled', !hasChecked);
    }

    // Status toggle
    $('.status-toggle').change(function() {
        const testId = $(this).data('test-id');
        const isActive = $(this).is(':checked');
        
        $.ajax({
            url: `/lab-tests/${testId}/toggle-status`,
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update badge
                    const badge = $(`.status-toggle[data-test-id="${testId}"]`)
                        .closest('td')
                        .find('.badge');
                    
                    if (response.is_active) {
                        badge.removeClass('bg-danger').addClass('bg-success').text('نشط');
                    } else {
                        badge.removeClass('bg-success').addClass('bg-danger').text('غير نشط');
                    }
                    
                    // Show success message
                    showAlert('success', response.message);
                } else {
                    // Revert toggle
                    $(`.status-toggle[data-test-id="${testId}"]`).prop('checked', !isActive);
                    showAlert('error', 'حدث خطأ أثناء تحديث الحالة');
                }
            },
            error: function() {
                // Revert toggle
                $(`.status-toggle[data-test-id="${testId}"]`).prop('checked', !isActive);
                showAlert('error', 'حدث خطأ أثناء تحديث الحالة');
            }
        });
    });

    // Delete test
    $('.delete-test').click(function() {
        const testId = $(this).data('test-id');
        const testName = $(this).data('test-name');
        
        $('#test-name').text(testName);
        $('#delete-form').attr('action', `/lab-tests/${testId}`);
        $('#deleteModal').modal('show');
    });

    // Bulk action form submission
    $('#bulk-action-form').submit(function(e) {
        const action = $('select[name="action"]').val();
        const selectedCount = $('.test-checkbox:checked').length;
        
        if (!action) {
            e.preventDefault();
            showAlert('warning', 'يرجى اختيار إجراء للتنفيذ');
            return;
        }
        
        if (selectedCount === 0) {
            e.preventDefault();
            showAlert('warning', 'يرجى تحديد فحص واحد على الأقل');
            return;
        }
        
        let confirmMessage = '';
        switch(action) {
            case 'activate':
                confirmMessage = `هل أنت متأكد من تفعيل ${selectedCount} فحص؟`;
                break;
            case 'deactivate':
                confirmMessage = `هل أنت متأكد من إلغاء تفعيل ${selectedCount} فحص؟`;
                break;
            case 'delete':
                confirmMessage = `هل أنت متأكد من حذف ${selectedCount} فحص؟ لا يمكن التراجع عن هذا الإجراء.`;
                break;
        }
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
        }
    });

    // Show alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-danger';
        
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.card-body').prepend(alert);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush