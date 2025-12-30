@extends('layouts.public')

@section('title', 'قائمة الأطباء - مركز محمد يوسف لطب الأسنان')
@section('meta-description', 'تصفح قائمة أطبائنا المتخصصين في مختلف المجالات الطبية واحجز موعدك مع الطبيب المناسب')

@section('content')
<!-- Page Header -->
<section class="py-5 bg-primary text-white" style="margin-top: 80px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="display-4 mb-3">أطباؤنا المتخصصون</h1>
                <p class="lead">
                    فريق من أمهر الأطباء والمتخصصين في مختلف المجالات الطبية
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('public.index') }}" class="text-white-50">الرئيسية</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">الأطباء</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-4 text-center" data-aos="fade-left">
                <div class="bg-white bg-opacity-10 p-4 rounded-3">
                    <h3 class="mb-2">{{ $doctors->total() }}</h3>
                    <p class="mb-0">طبيب متخصص</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search and Filter Section -->
<section class="py-4 bg-light">
    <div class="container">
        <form method="GET" action="{{ route('public.doctors.index') }}" class="row g-3" id="doctorSearchForm">
            <div class="col-lg-4 col-md-6">
                <label for="search" class="form-label">البحث بالاسم أو التخصص</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="ابحث عن طبيب...">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <label for="department" class="form-label">القسم الطبي</label>
                <select class="form-select" id="department" name="department">
                    <option value="">جميع الأقسام</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" 
                                {{ request('department') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <label for="specialization" class="form-label">التخصص</label>
                <input type="text" class="form-control" id="specialization" name="specialization" 
                       value="{{ request('specialization') }}" placeholder="مثل: جراحة القلب">
            </div>
            
            <div class="col-lg-2 col-md-6">
                <label for="sort" class="form-label">ترتيب حسب</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>الاسم</option>
                    <option value="experience" {{ request('sort') == 'experience' ? 'selected' : '' }}>الخبرة</option>
                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>التقييم</option>
                </select>
            </div>
        </form>
        
        @if(request()->hasAny(['search', 'department', 'specialization', 'sort']))
        <div class="mt-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted">البحث النشط:</span>
                @if(request('search'))
                    <span class="badge bg-primary">البحث: {{ request('search') }}</span>
                @endif
                @if(request('department'))
                    <span class="badge bg-success">القسم: {{ $departments->find(request('department'))->name ?? 'غير محدد' }}</span>
                @endif
                @if(request('specialization'))
                    <span class="badge bg-info">التخصص: {{ request('specialization') }}</span>
                @endif
                <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i> مسح الفلاتر
                </a>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Doctors Grid -->
<section class="py-5">
    <div class="container">
        @if($doctors->count() > 0)
            <div class="row">
                @foreach($doctors as $doctor)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
                    <div class="doctor-card">
                        <div class="doctor-image d-flex align-items-center justify-content-center">
                            @if($doctor->photo)
                                <img src="{{ asset('storage/' . $doctor->photo) }}" alt="{{ $doctor->name }}" class="doctor-image">
                            @else
                                <i class="fas fa-user-md text-white" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        
                        <div class="doctor-info">
                            <h5 class="doctor-name">{{ $doctor->name }}</h5>
                            <p class="doctor-specialty">{{ $doctor->specialization }}</p>
                            
                            @if($doctor->department)
                            <p class="text-muted small mb-2">
                                <i class="fas fa-hospital me-1"></i>
                                {{ $doctor->department->name }}
                            </p>
                            @endif
                            
                            <div class="doctor-rating mb-2">
                                <div class="rating-stars">
                                    @php
                                        $rating = $doctor->reviews->avg('rating') ?? 0;
                                        $fullStars = floor($rating);
                                        $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    @endphp
                                    
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $fullStars)
                                            <i class="fas fa-star"></i>
                                        @elseif($i == $fullStars + 1 && $hasHalfStar)
                                            <i class="fas fa-star-half-alt"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="rating-text">
                                    ({{ $doctor->reviews->count() }} تقييم)
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted d-block">سنوات الخبرة</small>
                                        <strong class="text-primary">{{ $doctor->years_of_experience }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">الخدمات</small>
                                        <strong class="text-success">{{ $doctor->services->count() }}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            @if($doctor->services->count() > 0)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">الخدمات المتاحة:</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($doctor->services->take(3) as $service)
                                        <span class="badge bg-light text-dark">{{ $service->name }}</span>
                                    @endforeach
                                    @if($doctor->services->count() > 3)
                                        <span class="badge bg-secondary">+{{ $doctor->services->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('public.doctors.profile', $doctor->id) }}" 
                                   class="btn btn-outline-facebook btn-sm flex-fill">
                                    <i class="fas fa-eye"></i>
                                    عرض الملف
                                </a>
                                <a href="{{ route('public.booking.form', $doctor->id) }}" 
                                   class="btn btn-facebook btn-sm flex-fill">
                                    <i class="fas fa-calendar-plus"></i>
                                    احجز موعد
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $doctors->appends(request()->query())->links() }}
            </div>
        @else
            <!-- No Results -->
            <div class="text-center py-5" data-aos="fade-up">
                <div class="mb-4">
                    <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                </div>
                <h3 class="mb-3">لم يتم العثور على أطباء</h3>
                <p class="text-muted mb-4">
                    عذراً، لم نجد أي أطباء يطابقون معايير البحث الخاصة بك.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('public.doctors.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i>
                        عرض جميع الأطباء
                    </a>
                    <a href="{{ route('public.booking.form') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-plus"></i>
                        احجز موعد عام
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Quick Booking CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h3 class="mb-3">لم تجد الطبيب المناسب؟</h3>
                <p class="mb-0">
                    اتصل بنا وسنساعدك في العثور على الطبيب المناسب لحالتك أو احجز موعد عام وسنوجهك للتخصص المناسب
                </p>
            </div>
            <div class="col-lg-4 text-center" data-aos="fade-left">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="tel:+966123456789" class="btn btn-success">
                        <i class="fas fa-phone"></i>
                        اتصل بنا
                    </a>
                    <a href="{{ route('public.booking.form') }}" class="btn btn-outline-light">
                        <i class="fas fa-calendar-plus"></i>
                        احجز موعد
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Auto-submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('doctorSearchForm');
        const selects = form.querySelectorAll('select');
        
        selects.forEach(select => {
            select.addEventListener('change', function() {
                form.submit();
            });
        });
        
        // Search input with debounce
        const searchInput = document.getElementById('search');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    form.submit();
                }
            }, 500);
        });
    });

    // Smooth scroll for pagination
    document.addEventListener('DOMContentLoaded', function() {
        const paginationLinks = document.querySelectorAll('.pagination a');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Let the default behavior happen, but scroll to top after page load
                setTimeout(() => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }, 100);
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .pagination {
        --bs-pagination-padding-x: 1rem;
        --bs-pagination-padding-y: 0.75rem;
        --bs-pagination-border-radius: 0.5rem;
        --bs-pagination-color: var(--facebook-blue);
        --bs-pagination-bg: white;
        --bs-pagination-border-color: var(--facebook-light-gray);
        --bs-pagination-hover-color: white;
        --bs-pagination-hover-bg: var(--facebook-blue);
        --bs-pagination-hover-border-color: var(--facebook-blue);
        --bs-pagination-active-color: white;
        --bs-pagination-active-bg: var(--facebook-blue);
        --bs-pagination-active-border-color: var(--facebook-blue);
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.5);
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .rating-stars {
        color: #FFD700;
        font-size: 0.9rem;
    }
    
    .rating-stars .far {
        color: #E5E7EB;
    }
    
    .doctor-card:hover .doctor-image {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: var(--facebook-blue);
        box-shadow: 0 0 0 0.2rem rgba(24, 119, 242, 0.25);
    }
</style>
@endpush