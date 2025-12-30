@extends('layouts.app')

@section('title', 'لوحة الخزينة المتقدمة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-credit-card-2-front text-primary me-2"></i>
                        لوحة الخزينة المتقدمة
                    </h1>
                    <p class="text-muted mb-0">إدارة المدفوعات والتأمين والفواتير</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-clock me-1"></i>
                        {{ now()->format('H:i') }}
                    </span>
                    <br>
                    <small class="text-muted">{{ now()->format('Y-m-d') }}</small>
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
                                إجمالي المدفوعات اليوم
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format(0, 2) }} ر.س
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                                المدفوعات النقدية
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format(0, 2) }} ر.س
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
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
                                مدفوعات التأمين
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format(0, 2) }} ر.س
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
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
                                المدفوعات المعلقة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format(0) }}
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

    <!-- Main Content -->
    <div class="row">
        <!-- Payment Processing -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-credit-card me-2"></i>
                        معالجة المدفوعات
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>مرحباً بك في لوحة الخزينة المتقدمة!</strong>
                        <br>
                        هذا النظام يدعم جميع طرق الدفع: نقدي، فيزا، ماستركارد، تحويل بنكي، والتأمين.
                    </div>

                    <!-- Payment Methods -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                                    <h5>الدفع النقدي</h5>
                                    <button class="btn btn-success" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                                        معالجة دفع نقدي
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                                    <h5>الدفع الإلكتروني</h5>
                                    <button class="btn btn-primary" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                                        معالجة دفع إلكتروني
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Insurance Processing -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-shield-alt fa-3x text-info mb-3"></i>
                                    <h5>معالجة التأمين</h5>
                                    <button class="btn btn-info" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                                        حساب نسب التأمين
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-invoice-dollar fa-3x text-warning mb-3"></i>
                                    <h5>الفواتير المتأخرة</h5>
                                    <button class="btn btn-warning" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                                        إدارة المتأخرات
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-lightning me-2"></i>
                        الإجراءات السريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                            <i class="bi bi-search me-2"></i>
                            البحث عن فاتورة
                        </button>
                        <button class="btn btn-outline-success" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                            <i class="bi bi-plus-circle me-2"></i>
                            إنشاء فاتورة جديدة
                        </button>
                        <button class="btn btn-outline-info" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                            <i class="bi bi-shield-check me-2"></i>
                            التحقق من التأمين
                        </button>
                        <button class="btn btn-outline-warning" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                            <i class="bi bi-clock-history me-2"></i>
                            المدفوعات المعلقة
                        </button>
                        <button class="btn btn-outline-danger" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            الفواتير المتأخرة
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>
                        آخر المعاملات
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fa-3x mb-3"></i>
                        <p>لا توجد معاملات حديثة</p>
                        <small>ستظهر المعاملات هنا عند بدء الاستخدام</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Notice -->
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-tools me-2"></i>
                <strong>ملاحظة التطوير:</strong>
                هذه لوحة الخزينة المتقدمة الأساسية. سيتم تطوير الميزات المتقدمة في المرحلة التالية من التطوير.
                <br>
                <small class="text-muted">
                    الميزات المخططة: معالجة المدفوعات الفعلية، تكامل بوابات الدفع، حساب نسب التأمين التلقائي، إدارة المتأخرات المتقدمة.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحديث الوقت كل دقيقة
    setInterval(function() {
        const timeElement = document.querySelector('.badge.bg-success');
        if (timeElement) {
            const now = new Date();
            const timeString = now.toLocaleTimeString('ar-SA', {
                hour: '2-digit',
                minute: '2-digit'
            });
            timeElement.innerHTML = '<i class="bi bi-clock me-1"></i>' + timeString;
        }
    }, 60000);
});
</script>
@endpush