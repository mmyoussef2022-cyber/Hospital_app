@extends('layouts.app')

@section('page-title', 'لوحة تحكم صفحة الهبوط')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">
                    <i class="fas fa-globe me-2"></i>
                    لوحة تحكم صفحة الهبوط
                </h2>
                <div class="btn-group">
                    <a href="{{ route('admin.landing-page.preview') }}" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-eye me-1"></i>
                        معاينة الصفحة
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearCache()">
                        <i class="fas fa-sync me-1"></i>
                        مسح الذاكرة المؤقتة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي العروض
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['total_offers'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                العروض النشطة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['active_offers'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                العروض المميزة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['featured_offers'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                العروض المنتهية
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['expired_offers'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        الإجراءات السريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.landing-page.settings') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-cog me-2"></i>
                                إعدادات الصفحة
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.landing-page.offers') }}" class="btn btn-success btn-block">
                                <i class="fas fa-tags me-2"></i>
                                إدارة العروض
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.landing-page.offers.create') }}" class="btn btn-info btn-block">
                                <i class="fas fa-plus me-2"></i>
                                إضافة عرض جديد
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.landing-page.analytics') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-chart-bar me-2"></i>
                                التحليلات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Settings Overview -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        نظرة عامة على الإعدادات الحالية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">معلومات المستشفى</h6>
                            <p class="mb-1"><strong>الاسم:</strong> {{ $settings->hospital_name ?? 'غير محدد' }}</p>
                            <p class="mb-1"><strong>الشعار:</strong> {{ $settings->hospital_tagline ?? 'غير محدد' }}</p>
                            <p class="mb-3"><strong>الهاتف الرئيسي:</strong> {{ $settings->phone_primary ?? 'غير محدد' }}</p>
                            
                            <h6 class="font-weight-bold">الأقسام المفعلة</h6>
                            <div class="mb-3">
                                <span class="badge badge-{{ $settings->hero_section_enabled ? 'success' : 'secondary' }} me-1">
                                    قسم البطل
                                </span>
                                <span class="badge badge-{{ $settings->about_section_enabled ? 'success' : 'secondary' }} me-1">
                                    قسم عن المستشفى
                                </span>
                                <span class="badge badge-{{ $settings->services_section_enabled ? 'success' : 'secondary' }} me-1">
                                    قسم الخدمات
                                </span>
                                <span class="badge badge-{{ $settings->doctors_section_enabled ? 'success' : 'secondary' }} me-1">
                                    قسم الأطباء
                                </span>
                                <span class="badge badge-{{ $settings->offers_section_enabled ? 'success' : 'secondary' }} me-1">
                                    قسم العروض
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">معلومات الاتصال</h6>
                            <p class="mb-1"><strong>البريد الإلكتروني:</strong> {{ $settings->email_primary ?? 'غير محدد' }}</p>
                            <p class="mb-1"><strong>واتساب:</strong> {{ $settings->whatsapp_number ?? 'غير محدد' }}</p>
                            <p class="mb-3"><strong>العنوان:</strong> {{ Str::limit($settings->address_text ?? 'غير محدد', 50) }}</p>
                            
                            <h6 class="font-weight-bold">وسائل التواصل الاجتماعي</h6>
                            <div class="mb-3">
                                @if($settings->facebook_url)
                                    <a href="{{ $settings->facebook_url }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                @endif
                                @if($settings->twitter_url)
                                    <a href="{{ $settings->twitter_url }}" target="_blank" class="btn btn-sm btn-outline-info me-1">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                @endif
                                @if($settings->instagram_url)
                                    <a href="{{ $settings->instagram_url }}" target="_blank" class="btn btn-sm btn-outline-danger me-1">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                @endif
                                @if($settings->youtube_url)
                                    <a href="{{ $settings->youtube_url }}" target="_blank" class="btn btn-sm btn-outline-danger me-1">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-link me-2"></i>
                        روابط مفيدة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('public.landing') }}" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-external-link-alt me-2"></i>
                            عرض صفحة الهبوط
                        </a>
                        <a href="{{ route('admin.landing-page.settings') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i>
                            تعديل الإعدادات
                        </a>
                        <a href="{{ route('admin.landing-page.offers') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-tags me-2"></i>
                            إدارة العروض
                        </a>
                        <a href="{{ route('admin.landing-page.analytics') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-line me-2"></i>
                            عرض التحليلات
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb me-2"></i>
                        نصائح سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            تأكد من تفعيل الأقسام المهمة في الإعدادات
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            أضف عروض جذابة لزيادة التفاعل
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-check text-success me-1"></i>
                            راجع التحليلات بانتظام لتحسين الأداء
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-check text-success me-1"></i>
                            امسح الذاكرة المؤقتة بعد التحديثات
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>
@endsection