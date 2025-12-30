@extends('layouts.app')

@section('page-title', 'معاينة صفحة الهبوط')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">
                    <i class="fas fa-eye me-2"></i>
                    معاينة صفحة الهبوط
                </h2>
                <div class="btn-group">
                    <a href="{{ route('public.landing') }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>
                        عرض الصفحة الفعلية
                    </a>
                    <a href="{{ route('admin.landing-page.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Container -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-desktop me-2"></i>
                        معاينة تفاعلية
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="setPreviewMode('desktop')">
                            <i class="fas fa-desktop"></i> سطح المكتب
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="setPreviewMode('tablet')">
                            <i class="fas fa-tablet-alt"></i> تابلت
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="setPreviewMode('mobile')">
                            <i class="fas fa-mobile-alt"></i> موبايل
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="preview-container" id="previewContainer">
                        <iframe 
                            id="previewFrame" 
                            src="{{ route('public.landing') }}" 
                            frameborder="0" 
                            style="width: 100%; height: 800px; border: none;">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Summary -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog me-2"></i>
                        ملخص الإعدادات الحالية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-secondary">معلومات أساسية</h6>
                            <p class="mb-1"><strong>اسم المستشفى:</strong> {{ $settings->hospital_name ?? 'غير محدد' }}</p>
                            <p class="mb-1"><strong>الشعار:</strong> {{ $settings->hospital_tagline ?? 'غير محدد' }}</p>
                            <p class="mb-3"><strong>الهاتف:</strong> {{ $settings->phone_primary ?? 'غير محدد' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-secondary">الأقسام المفعلة</h6>
                            <div class="mb-3">
                                <span class="badge badge-{{ $settings->hero_section_enabled ? 'success' : 'secondary' }} me-1">
                                    قسم البطل
                                </span>
                                <span class="badge badge-{{ $settings->about_section_enabled ? 'success' : 'secondary' }} me-1">
                                    عن المستشفى
                                </span>
                                <span class="badge badge-{{ $settings->services_section_enabled ? 'success' : 'secondary' }} me-1">
                                    الخدمات
                                </span>
                                <span class="badge badge-{{ $settings->doctors_section_enabled ? 'success' : 'secondary' }} me-1">
                                    الأطباء
                                </span>
                                <span class="badge badge-{{ $settings->offers_section_enabled ? 'success' : 'secondary' }} me-1">
                                    العروض
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tags me-2"></i>
                        العروض النشطة
                    </h6>
                </div>
                <div class="card-body">
                    @if($offers->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($offers as $offer)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="mb-1">{{ $offer->title }}</h6>
                                        <small class="text-muted">
                                            صالح حتى: {{ $offer->valid_until?->format('Y-m-d') }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($offer->is_featured)
                                            <span class="badge bg-warning">مميز</span>
                                        @endif
                                        <span class="badge bg-success">نشط</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-tags fa-2x mb-2"></i>
                            <p class="mb-0">لا توجد عروض نشطة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.landing-page.settings') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-cog me-2"></i>
                                تعديل الإعدادات
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.landing-page.offers') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-tags me-2"></i>
                                إدارة العروض
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-info btn-block" onclick="refreshPreview()">
                                <i class="fas fa-sync me-2"></i>
                                تحديث المعاينة
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-warning btn-block" onclick="clearCache()">
                                <i class="fas fa-broom me-2"></i>
                                مسح الذاكرة المؤقتة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.preview-container {
    transition: all 0.3s ease;
}

.preview-container.tablet {
    max-width: 768px;
    margin: 0 auto;
}

.preview-container.mobile {
    max-width: 375px;
    margin: 0 auto;
}

.preview-container.tablet #previewFrame,
.preview-container.mobile #previewFrame {
    border: 1px solid #ddd;
    border-radius: 8px;
}

.btn-group .btn.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}
</style>

<script>
function setPreviewMode(mode) {
    const container = document.getElementById('previewContainer');
    const frame = document.getElementById('previewFrame');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    // إزالة الفئات النشطة
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // إزالة فئات الوضع
    container.classList.remove('tablet', 'mobile');
    
    // تطبيق الوضع الجديد
    if (mode === 'tablet') {
        container.classList.add('tablet');
        frame.style.height = '600px';
    } else if (mode === 'mobile') {
        container.classList.add('mobile');
        frame.style.height = '700px';
    } else {
        frame.style.height = '800px';
    }
    
    // تفعيل الزر المناسب
    event.target.classList.add('active');
}

function refreshPreview() {
    const frame = document.getElementById('previewFrame');
    frame.src = frame.src;
    
    Swal.fire({
        icon: 'success',
        title: 'تم التحديث',
        text: 'تم تحديث المعاينة بنجاح',
        timer: 1500,
        showConfirmButton: false
    });
}

function clearCache() {
    fetch('{{ route("admin.landing-page.clear-cache") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            }).then(() => {
                refreshPreview();
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: 'حدث خطأ أثناء مسح الذاكرة المؤقتة'
        });
    });
}

// تحديث المعاينة كل 30 ثانية
setInterval(function() {
    const frame = document.getElementById('previewFrame');
    if (frame) {
        frame.contentWindow.location.reload();
    }
}, 30000);
</script>
@endsection