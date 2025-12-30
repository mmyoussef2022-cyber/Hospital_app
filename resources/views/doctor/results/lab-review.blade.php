@extends('layouts.app')

@section('title', 'مراجعة نتيجة المختبر - ' . $labResult->patient->name)

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-flask me-2"></i>
                        نتيجة المختبر - {{ $labResult->patient->name }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>تاريخ الطلب:</strong> {{ $labResult->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>تاريخ الإنجاز:</strong> {{ $labResult->completed_at ? $labResult->completed_at->format('d/m/Y H:i') : 'لم يكتمل بعد' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>الطبيب الطالب:</strong> د. {{ $labResult->doctor->user->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>الأولوية:</strong> 
                            <span class="badge bg-{{ $labResult->priority === 'stat' ? 'danger' : ($labResult->priority === 'urgent' ? 'warning' : 'info') }}">
                                {{ $labResult->priority === 'stat' ? 'عاجل جداً' : ($labResult->priority === 'urgent' ? 'عاجل' : 'عادي') }}
                            </span>
                        </div>
                    </div>

                    @if($labResult->clinical_notes)
                    <div class="mb-3">
                        <strong>الملاحظات السريرية:</strong>
                        <p class="text-muted">{{ $labResult->clinical_notes }}</p>
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong>التحاليل المطلوبة:</strong>
                        <ul class="list-group">
                            @foreach(json_decode($labResult->test_ids, true) ?? [] as $testId)
                            <li class="list-group-item">تحليل رقم {{ $testId }}</li>
                            @endforeach
                        </ul>
                    </div>

                    @if($labResult->results)
                    <div class="mb-3">
                        <strong>النتائج:</strong>
                        <div class="border p-3 bg-light">
                            {!! nl2br(e($labResult->results)) !!}
                        </div>
                    </div>
                    @endif

                    @if($labResult->is_critical)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تنبيه:</strong> هذه النتيجة تحتوي على قيم حرجة تتطلب اهتماماً فورياً
                    </div>
                    @endif

                    <form id="reviewForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">ملاحظات المراجعة</label>
                            <textarea class="form-control" name="review_notes" rows="4" 
                                      placeholder="اكتب ملاحظاتك على النتيجة..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                <i class="fas fa-arrow-right me-2"></i>رجوع
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>اعتماد النتيجة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">معلومات المريض</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h5>{{ $labResult->patient->name }}</h5>
                        <p class="text-muted">{{ $labResult->patient->age ?? 'غير محدد' }} سنة</p>
                    </div>
                    
                    <div class="mb-2">
                        <strong>رقم الهوية:</strong> {{ $labResult->patient->national_id ?? 'غير محدد' }}
                    </div>
                    <div class="mb-2">
                        <strong>رقم الجوال:</strong> {{ $labResult->patient->phone }}
                    </div>
                    <div class="mb-2">
                        <strong>الجنس:</strong> {{ $labResult->patient->gender === 'male' ? 'ذكر' : 'أنثى' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$('#reviewForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("doctor.results.lab.approve", $labResult->id) }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(function() {
                    window.location.href = '{{ route("doctor.integrated.dashboard") }}';
                }, 1500);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            showAlert('error', 'حدث خطأ أثناء اعتماد النتيجة');
        }
    });
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush