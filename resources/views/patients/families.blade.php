@extends('layouts.app')

@section('title', 'إدارة العائلات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        إدارة العائلات
                    </h3>
                    <div>
                        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i>
                            العودة للمرضى
                        </a>
                        <a href="{{ route('patients.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة عائلة جديدة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('patients.families') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="البحث بالاسم، رمز العائلة، أو الهاتف"
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-funnel"></i>
                                        بحث
                                    </button>
                                    <a href="{{ route('patients.families') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                        إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Families Grid -->
                    <div class="row">
                        @forelse($families as $family)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light border-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 text-primary">
                                                <i class="bi bi-house-door me-1"></i>
                                                {{ $family->family_code }}
                                            </h6>
                                            <span class="badge bg-info">
                                                {{ $family->familyMembers->count() + 1 }} أفراد
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <!-- Family Head -->
                                        <div class="d-flex align-items-center mb-3">
                                            @if($family->profile_photo)
                                                <img src="{{ asset('storage/' . $family->profile_photo) }}" 
                                                     alt="صورة رب الأسرة" 
                                                     class="rounded-circle me-3" 
                                                     width="50" height="50">
                                            @else
                                                <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="bi bi-person text-white fs-4"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $family->name }}</h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-crown text-warning me-1"></i>
                                                    رب الأسرة
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Family Members Preview -->
                                        @if($family->familyMembers->count() > 0)
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-2">أفراد العائلة:</small>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($family->familyMembers->take(3) as $member)
                                                        <span class="badge bg-light text-dark border">
                                                            {{ $member->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($family->familyMembers->count() > 3)
                                                        <span class="badge bg-secondary">
                                                            +{{ $family->familyMembers->count() - 3 }} آخرين
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Contact Info -->
                                        <div class="mb-3">
                                            <small class="text-muted d-block">معلومات الاتصال:</small>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-telephone text-muted me-2"></i>
                                                <span class="small">{{ $family->phone ?? 'غير محدد' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer bg-transparent border-0">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('patients.show-family', $family->family_code) }}" 
                                               class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="bi bi-eye me-1"></i>
                                                عرض العائلة
                                            </a>
                                            <a href="{{ route('patients.show', $family) }}" 
                                               class="btn btn-outline-info btn-sm">
                                                <i class="bi bi-person"></i>
                                            </a>
                                            <a href="{{ route('patients.edit', $family) }}" 
                                               class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-house-x display-1 d-block mb-3"></i>
                                        <h5>لا توجد عائلات مسجلة</h5>
                                        <p>لم يتم العثور على أي عائلات في النظام</p>
                                        <a href="{{ route('patients.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            إضافة عائلة جديدة
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($families->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $families->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">إجمالي العائلات</h6>
                        <h3 class="mb-0">{{ $families->total() }}</h3>
                    </div>
                    <i class="bi bi-houses display-6"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">إجمالي الأفراد</h6>
                        <h3 class="mb-0">{{ $families->sum(function($family) { return $family->familyMembers->count() + 1; }) }}</h3>
                    </div>
                    <i class="bi bi-people display-6"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">متوسط حجم العائلة</h6>
                        <h3 class="mb-0">
                            @if($families->count() > 0)
                                {{ number_format($families->sum(function($family) { return $family->familyMembers->count() + 1; }) / $families->count(), 1) }}
                            @else
                                0
                            @endif
                        </h3>
                    </div>
                    <i class="bi bi-bar-chart display-6"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">أكبر عائلة</h6>
                        <h3 class="mb-0">
                            @if($families->count() > 0)
                                {{ $families->max(function($family) { return $family->familyMembers->count() + 1; }) }}
                            @else
                                0
                            @endif
                            أفراد
                        </h3>
                    </div>
                    <i class="bi bi-award display-6"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush