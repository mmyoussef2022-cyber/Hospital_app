@extends('layouts.app')

@section('title', 'تفاصيل طلب المختبر')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-flask me-2"></i>
                        تفاصيل طلب المختبر
                    </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('lab.index') }}">المختبر</a></li>
                            <li class="breadcrumb-item active">{{ $lab->order_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    @if($lab->canBeEdited())
                        <a href="{{ route('lab.edit', $lab) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>
                            تعديل
                        </a>
                    @endif
                    <a href="{{ route('lab.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Order Information -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                معلومات الطلب
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">رقم الطلب:</td>
                                            <td>{{ $lab->order_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">تاريخ الطلب:</td>
                                            <td>{{ $lab->ordered_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">الحالة:</td>
                                            <td>
                                                <span class="badge bg-{{ $lab->status_color }} fs-6">
                                                    {{ $lab->status_display }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">الأولوية:</td>
                                            <td>
                                                <span class="badge bg-{{ $lab->priority_color }} fs-6">
                                                    {{ $lab->priority_display }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        @if($lab->collected_at)
                                        <tr>
                                            <td class="fw-bold">تاريخ جمع العينة:</td>
                                            <td>{{ $lab->collected_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        @endif
                                        @if($lab->completed_at)
                                        <tr>
                                            <td class="fw-bold">تاريخ الإكمال:</td>
                                            <td>{{ $lab->completed_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold">المبلغ الإجمالي:</td>
                                            <td>
                                                <strong class="text-success">{{ number_format($lab->total_amount, 2) }} ريال</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">حالة الدفع:</td>
                                            <td>
                                                @if($lab->is_paid)
                                                    <span class="badge bg-success">مدفوع</span>
                                                @else
                                                    <span class="badge bg-warning">غير مدفوع</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if($lab->clinical_notes)
                            <div class="mt-3">
                                <h6 class="fw-bold">الملاحظات السريرية:</h6>
                                <div class="alert alert-info">
                                    {{ $lab->clinical_notes }}
                                </div>
                            </div>
                            @endif

                            @if($lab->collection_notes)
                            <div class="mt-3">
                                <h6 class="fw-bold">ملاحظات جمع العينة:</h6>
                                <div class="alert alert-secondary">
                                    {{ $lab->collection_notes }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Test Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-vial me-2"></i>
                                معلومات الفحص
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="text-primary">{{ $lab->labTest->name }}</h5>
                                    <p class="text-muted mb-2">{{ $lab->labTest->description }}</p>
                                    
                                    @if($lab->labTest->category)
                                    <div class="mb-2">
                                        <span class="badge bg-info">{{ $lab->labTest->category }}</span>
                                    </div>
                                    @endif

                                    @if($lab->labTest->sample_type)
                                    <p class="mb-1">
                                        <strong>نوع العينة:</strong> {{ $lab->labTest->sample_type }}
                                    </p>
                                    @endif

                                    @if($lab->labTest->preparation_instructions)
                                    <div class="alert alert-warning mt-3">
                                        <h6 class="alert-heading">تعليمات التحضير:</h6>
                                        {{ $lab->labTest->preparation_instructions }}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <div class="text-end">
                                        <h4 class="text-success mb-2">{{ number_format($lab->labTest->price, 2) }} ريال</h4>
                                        @if($lab->labTest->duration_minutes)
                                        <p class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $lab->labTest->duration_minutes }} دقيقة
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Section -->
                    @if($lab->results->count() > 0)
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                النتائج
                            </h5>
                            @if($lab->canBeVerified())
                            <button type="button" class="btn btn-success btn-sm" onclick="verifyAllResults()">
                                <i class="fas fa-check-circle me-1"></i>
                                التحقق من جميع النتائج
                            </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>المعامل</th>
                                            <th>القيمة</th>
                                            <th>الوحدة</th>
                                            <th>المدى المرجعي</th>
                                            <th>الحالة</th>
                                            <th>التحقق</th>
                                            <th>الملاحظات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lab->results as $result)
                                        <tr class="{{ $result->is_critical ? 'table-danger' : '' }}">
                                            <td class="fw-bold">{{ $result->parameter_name }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $result->value }}</span>
                                                @if($result->is_critical)
                                                    <i class="fas fa-exclamation-triangle text-danger ms-1" title="قيمة حرجة"></i>
                                                @endif
                                            </td>
                                            <td>{{ $result->unit }}</td>
                                            <td class="text-muted">{{ $result->reference_range }}</td>
                                            <td>
                                                <span class="badge bg-{{ $result->flag_color }}">
                                                    {{ $result->flag_display }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($result->verified_at)
                                                    <div>
                                                        <span class="badge bg-success">تم التحقق</span>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $result->verifiedBy->name ?? 'غير معروف' }}
                                                            <br>
                                                            {{ $result->verified_at->format('Y-m-d H:i') }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="badge bg-warning">في الانتظار</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($result->notes)
                                                    <small class="text-muted">{{ $result->notes }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @elseif($lab->canAddResults())
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد نتائج بعد</h5>
                            <p class="text-muted">يمكنك إضافة النتائج الآن</p>
                            <button type="button" class="btn btn-primary" onclick="addResults({{ $lab->id }})">
                                <i class="fas fa-plus me-1"></i>
                                إضافة النتائج
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Patient & Doctor Information -->
                <div class="col-lg-4">
                    <!-- Patient Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>
                                معلومات المريض
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-primary text-white me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $lab->patient->name }}</h6>
                                    <small class="text-muted">{{ $lab->patient->national_id }}</small>
                                </div>
                            </div>
                            
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold">العمر:</td>
                                    <td>{{ $lab->patient->age ?? 'غير محدد' }} سنة</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">الجنس:</td>
                                    <td>{{ $lab->patient->gender === 'male' ? 'ذكر' : 'أنثى' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">الهاتف:</td>
                                    <td>{{ $lab->patient->phone ?? 'غير محدد' }}</td>
                                </tr>
                            </table>

                            <div class="d-grid">
                                <a href="{{ route('patients.show', $lab->patient) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    عرض ملف المريض
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-md me-2"></i>
                                الطبيب المعالج
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-success text-white me-3">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $lab->doctor->name }}</h6>
                                    <small class="text-muted">{{ $lab->doctor->department->name ?? 'غير محدد' }}</small>
                                </div>
                            </div>
                            
                            @if($lab->doctor->specialization)
                            <p class="mb-2">
                                <strong>التخصص:</strong> {{ $lab->doctor->specialization }}
                            </p>
                            @endif

                            <div class="d-grid">
                                <a href="{{ route('doctors.show', $lab->doctor) }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    عرض ملف الطبيب
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Status Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i>
                                إجراءات سريعة
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($lab->canBeCollected())
                                <button type="button" class="btn btn-info" onclick="updateStatus('collected')">
                                    <i class="fas fa-vial me-1"></i>
                                    تم جمع العينة
                                </button>
                                @endif

                                @if($lab->canBeProcessed())
                                <button type="button" class="btn btn-primary" onclick="updateStatus('processing')">
                                    <i class="fas fa-cog me-1"></i>
                                    بدء المعالجة
                                </button>
                                @endif

                                @if($lab->canAddResults())
                                <button type="button" class="btn btn-success" onclick="addResults({{ $lab->id }})">
                                    <i class="fas fa-plus-circle me-1"></i>
                                    إضافة النتائج
                                </button>
                                @endif

                                @if($lab->canBeCompleted())
                                <button type="button" class="btn btn-warning" onclick="updateStatus('completed')">
                                    <i class="fas fa-check-circle me-1"></i>
                                    إكمال الفحص
                                </button>
                                @endif

                                @if($lab->canBeCancelled())
                                <button type="button" class="btn btn-danger" onclick="cancelOrder()">
                                    <i class="fas fa-times-circle me-1"></i>
                                    إلغاء الطلب
                                </button>
                                @endif

                                <hr>

                                <button type="button" class="btn btn-outline-secondary" onclick="printReport()">
                                    <i class="fas fa-print me-1"></i>
                                    طباعة التقرير
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.table-danger {
    --bs-table-bg: rgba(220, 53, 69, 0.1);
}
</style>
@endpush

@push('scripts')
<script>
function updateStatus(status) {
    const notes = prompt('ملاحظات (اختيارية):');
    
    fetch(`/lab/{{ $lab->id }}/status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status: status,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ في تحديث الحالة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في تحديث الحالة');
    });
}

function addResults(orderId) {
    window.location.href = `/lab/${orderId}/edit#results`;
}

function verifyAllResults() {
    if (confirm('هل أنت متأكد من التحقق من جميع النتائج؟')) {
        const resultIds = @json($lab->results->pluck('id'));
        
        fetch(`/lab/{{ $lab->id }}/verify`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                result_ids: resultIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ في التحقق من النتائج');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في التحقق من النتائج');
        });
    }
}

function cancelOrder() {
    if (confirm('هل أنت متأكد من إلغاء هذا الطلب؟')) {
        fetch(`/lab/{{ $lab->id }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/lab';
            } else {
                alert('حدث خطأ في إلغاء الطلب');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في إلغاء الطلب');
        });
    }
}

function printReport() {
    window.print();
}
</script>
@endpush