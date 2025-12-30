@extends('layouts.app')

@section('page-title', 'إعدادات صفحة الهبوط')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">
                    <i class="fas fa-cog me-2"></i>
                    إعدادات صفحة الهبوط
                </h2>
                <div class="btn-group">
                    <a href="{{ route('admin.landing-page.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للوحة التحكم
                    </a>
                    <a href="{{ route('admin.landing-page.preview') }}" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-eye me-1"></i>
                        معاينة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.landing-page.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Navigation Tabs -->
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                    <i class="fas fa-info-circle me-1"></i>
                                    معلومات عامة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="hero-tab" data-bs-toggle="tab" data-bs-target="#hero" type="button" role="tab">
                                    <i class="fas fa-image me-1"></i>
                                    قسم البطل
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sections-tab" data-bs-toggle="tab" data-bs-target="#sections" type="button" role="tab">
                                    <i class="fas fa-th-large me-1"></i>
                                    الأقسام
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                    <i class="fas fa-phone me-1"></i>
                                    معلومات الاتصال
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                                    <i class="fas fa-share-alt me-1"></i>
                                    وسائل التواصل
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                                    <i class="fas fa-search me-1"></i>
                                    SEO
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="design-tab" data-bs-toggle="tab" data-bs-target="#design" type="button" role="tab">
                                    <i class="fas fa-palette me-1"></i>
                                    التصميم
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="settingsTabsContent">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hospital_name" class="form-label">اسم المستشفى *</label>
                                            <input type="text" class="form-control" id="hospital_name" name="hospital_name" 
                                                   value="{{ old('hospital_name', $settings->hospital_name) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hospital_tagline" class="form-label">شعار المستشفى</label>
                                            <input type="text" class="form-control" id="hospital_tagline" name="hospital_tagline" 
                                                   value="{{ old('hospital_tagline', $settings->hospital_tagline) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="hospital_description" class="form-label">وصف المستشفى</label>
                                    <textarea class="form-control" id="hospital_description" name="hospital_description" rows="4">{{ old('hospital_description', $settings->hospital_description) }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hospital_logo" class="form-label">شعار المستشفى</label>
                                            <input type="file" class="form-control" id="hospital_logo" name="hospital_logo" accept="image/*">
                                            @if($settings->hospital_logo)
                                                <div class="mt-2">
                                                    <img src="{{ Storage::url($settings->hospital_logo) }}" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hero Section -->
                            <div class="tab-pane fade" id="hero" role="tabpanel">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="hero_section_enabled" name="hero_section_enabled" 
                                               {{ old('hero_section_enabled', $settings->hero_section_enabled) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hero_section_enabled">
                                            تفعيل قسم البطل
                                        </label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hero_title" class="form-label">عنوان قسم البطل</label>
                                            <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                                   value="{{ old('hero_title', $settings->hero_title) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hero_subtitle" class="form-label">العنوان الفرعي</label>
                                            <input type="text" class="form-control" id="hero_subtitle" name="hero_subtitle" 
                                                   value="{{ old('hero_subtitle', $settings->hero_subtitle) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hero_cta_primary_text" class="form-label">نص الزر الأساسي</label>
                                            <input type="text" class="form-control" id="hero_cta_primary_text" name="hero_cta_primary_text" 
                                                   value="{{ old('hero_cta_primary_text', $settings->hero_cta_primary_text) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hero_cta_secondary_text" class="form-label">نص الزر الثانوي</label>
                                            <input type="text" class="form-control" id="hero_cta_secondary_text" name="hero_cta_secondary_text" 
                                                   value="{{ old('hero_cta_secondary_text', $settings->hero_cta_secondary_text) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="hero_background_image" class="form-label">صورة خلفية قسم البطل</label>
                                    <input type="file" class="form-control" id="hero_background_image" name="hero_background_image" accept="image/*">
                                    @if($settings->hero_background_image)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($settings->hero_background_image) }}" alt="Hero Background" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Sections -->
                            <div class="tab-pane fade" id="sections" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>تفعيل الأقسام</h5>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="about_section_enabled" name="about_section_enabled" 
                                                       {{ old('about_section_enabled', $settings->about_section_enabled) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="about_section_enabled">
                                                    قسم عن المستشفى
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="services_section_enabled" name="services_section_enabled" 
                                                       {{ old('services_section_enabled', $settings->services_section_enabled) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="services_section_enabled">
                                                    قسم الخدمات
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="doctors_section_enabled" name="doctors_section_enabled" 
                                                       {{ old('doctors_section_enabled', $settings->doctors_section_enabled) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="doctors_section_enabled">
                                                    قسم الأطباء
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="offers_section_enabled" name="offers_section_enabled" 
                                                       {{ old('offers_section_enabled', $settings->offers_section_enabled) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="offers_section_enabled">
                                                    قسم العروض
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="location_section_enabled" name="location_section_enabled" 
                                                       {{ old('location_section_enabled', $settings->location_section_enabled) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="location_section_enabled">
                                                    قسم الموقع
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>إعدادات الأقسام</h5>
                                        <div class="mb-3">
                                            <label for="featured_doctors_count" class="form-label">عدد الأطباء المميزين</label>
                                            <input type="number" class="form-control" id="featured_doctors_count" name="featured_doctors_count" 
                                                   value="{{ old('featured_doctors_count', $settings->featured_doctors_count ?? 6) }}" min="1" max="20">
                                        </div>
                                        <div class="mb-3">
                                            <label for="services_title" class="form-label">عنوان قسم الخدمات</label>
                                            <input type="text" class="form-control" id="services_title" name="services_title" 
                                                   value="{{ old('services_title', $settings->services_title) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="doctors_title" class="form-label">عنوان قسم الأطباء</label>
                                            <input type="text" class="form-control" id="doctors_title" name="doctors_title" 
                                                   value="{{ old('doctors_title', $settings->doctors_title) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="offers_title" class="form-label">عنوان قسم العروض</label>
                                            <input type="text" class="form-control" id="offers_title" name="offers_title" 
                                                   value="{{ old('offers_title', $settings->offers_title) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="tab-pane fade" id="contact" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone_primary" class="form-label">الهاتف الرئيسي</label>
                                            <input type="text" class="form-control" id="phone_primary" name="phone_primary" 
                                                   value="{{ old('phone_primary', $settings->phone_primary) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone_emergency" class="form-label">هاتف الطوارئ</label>
                                            <input type="text" class="form-control" id="phone_emergency" name="phone_emergency" 
                                                   value="{{ old('phone_emergency', $settings->phone_emergency) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="whatsapp_number" class="form-label">رقم واتساب</label>
                                            <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" 
                                                   value="{{ old('whatsapp_number', $settings->whatsapp_number) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_primary" class="form-label">البريد الإلكتروني الرئيسي</label>
                                            <input type="email" class="form-control" id="email_primary" name="email_primary" 
                                                   value="{{ old('email_primary', $settings->email_primary) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="email_appointments" class="form-label">بريد المواعيد</label>
                                            <input type="email" class="form-control" id="email_appointments" name="email_appointments" 
                                                   value="{{ old('email_appointments', $settings->email_appointments) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address_text" class="form-label">العنوان</label>
                                    <textarea class="form-control" id="address_text" name="address_text" rows="3">{{ old('address_text', $settings->address_text) }}</textarea>
                                </div>
                            </div>

                            <!-- Social Media -->
                            <div class="tab-pane fade" id="social" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="facebook_url" class="form-label">
                                                <i class="fab fa-facebook text-primary me-1"></i>
                                                رابط فيسبوك
                                            </label>
                                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                                   value="{{ old('facebook_url', $settings->facebook_url) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="twitter_url" class="form-label">
                                                <i class="fab fa-twitter text-info me-1"></i>
                                                رابط تويتر
                                            </label>
                                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                                   value="{{ old('twitter_url', $settings->twitter_url) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="instagram_url" class="form-label">
                                                <i class="fab fa-instagram text-danger me-1"></i>
                                                رابط إنستغرام
                                            </label>
                                            <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                                   value="{{ old('instagram_url', $settings->instagram_url) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="youtube_url" class="form-label">
                                                <i class="fab fa-youtube text-danger me-1"></i>
                                                رابط يوتيوب
                                            </label>
                                            <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                                   value="{{ old('youtube_url', $settings->youtube_url) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="linkedin_url" class="form-label">
                                                <i class="fab fa-linkedin text-primary me-1"></i>
                                                رابط لينكد إن
                                            </label>
                                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                                   value="{{ old('linkedin_url', $settings->linkedin_url) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Settings -->
                            <div class="tab-pane fade" id="seo" role="tabpanel">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">عنوان الصفحة (Meta Title)</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                           value="{{ old('meta_title', $settings->meta_title) }}" maxlength="60">
                                    <div class="form-text">يُفضل أن يكون أقل من 60 حرف</div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">وصف الصفحة (Meta Description)</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="160">{{ old('meta_description', $settings->meta_description) }}</textarea>
                                    <div class="form-text">يُفضل أن يكون أقل من 160 حرف</div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">الكلمات المفتاحية</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                           value="{{ old('meta_keywords', $settings->meta_keywords) }}">
                                    <div class="form-text">افصل الكلمات بفاصلة</div>
                                </div>
                            </div>

                            <!-- Design Settings -->
                            <div class="tab-pane fade" id="design" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="primary_color" class="form-label">اللون الأساسي</label>
                                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" 
                                                   value="{{ old('primary_color', $settings->primary_color ?? '#007bff') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="secondary_color" class="form-label">اللون الثانوي</label>
                                            <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" 
                                                   value="{{ old('secondary_color', $settings->secondary_color ?? '#6c757d') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="accent_color" class="form-label">لون التمييز</label>
                                            <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" 
                                                   value="{{ old('accent_color', $settings->accent_color ?? '#28a745') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.landing-page.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ الإعدادات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection