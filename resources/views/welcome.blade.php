<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المستشفى</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .welcome-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="welcome-card p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <i class="bi bi-hospital text-primary" style="font-size: 4rem;"></i>
                        <h1 class="display-4 fw-bold text-primary mt-3">نظام إدارة المستشفى</h1>
                        <p class="lead text-muted">{{ config('app.hospital_name', 'مركز محمد يوسف لطب الاسنان') }}</p>
                    </div>

                    <!-- Features -->
                    <div class="row mb-5">
                        <div class="col-md-4 mb-4">
                            <div class="card feature-card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-hospital text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">لوحة الاستقبال الشاملة</h5>
                                    <p class="card-text text-muted">إدارة جميع عمليات المستشفى من مكان واحد</p>
                                    <span class="badge bg-success">مكتمل</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card feature-card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-credit-card-2-front text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">لوحة الخزينة المتقدمة</h5>
                                    <p class="card-text text-muted">إدارة المدفوعات والتأمين والفواتير</p>
                                    <span class="badge bg-success">مكتمل</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card feature-card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-stethoscope text-info" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">لوحة الطبيب المتكاملة</h5>
                                    <p class="card-text text-muted">الكشف والوصفات والتحاليل</p>
                                    <span class="badge bg-warning">قيد التطوير</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Login Section -->
                    <div class="text-center">
                        <h3 class="mb-4">تسجيل الدخول</h3>
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="d-grid gap-3">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        دخول الموظفين
                                    </a>
                                    <div class="text-muted">
                                        <small>
                                            للحصول على بيانات الدخول، يرجى التواصل مع إدارة النظام
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-5 pt-4 border-top">
                        <p class="text-muted mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            نظام آمن ومحمي - جميع البيانات مشفرة
                        </p>
                        <small class="text-muted">
                            © {{ date('Y') }} جميع الحقوق محفوظة
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>