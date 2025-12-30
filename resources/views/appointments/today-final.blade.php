<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مواعيد اليوم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-calendar-day"></i>
                            مواعيد اليوم - {{ today()->format('Y/m/d') }}
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($appointments->count() > 0)
                            <div class="alert alert-info">
                                <strong>عدد المواعيد:</strong> {{ $appointments->count() }}
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>الوقت</th>
                                            <th>المريض</th>
                                            <th>الطبيب</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($appointments as $appointment)
                                            <tr>
                                                <td>{{ $appointment->appointment_time ? $appointment->appointment_time->format('H:i') : 'غير محدد' }}</td>
                                                <td>{{ $appointment->patient->name ?? 'غير محدد' }}</td>
                                                <td>{{ $appointment->doctor->name ?? 'غير محدد' }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $appointment->status ?? 'غير محدد' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning text-center">
                                <h5><i class="bi bi-calendar-x"></i> لا توجد مواعيد اليوم</h5>
                                <p>لم يتم جدولة أي مواعيد لتاريخ {{ today()->format('Y/m/d') }}</p>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                العودة لجميع المواعيد
                            </a>
                            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i>
                                حجز موعد جديد
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>