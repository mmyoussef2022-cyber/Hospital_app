<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>طلبات المختبر اليوم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-flask"></i>
                            طلبات المختبر اليوم - {{ today()->format('Y/m/d') }}
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($orders->count() > 0)
                            <div class="alert alert-info">
                                <strong>عدد الطلبات:</strong> {{ $orders->count() }}
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>رقم الطلب</th>
                                            <th>المريض</th>
                                            <th>الطبيب</th>
                                            <th>الفحص</th>
                                            <th>الأولوية</th>
                                            <th>الحالة</th>
                                            <th>وقت الطلب</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orders as $order)
                                            <tr>
                                                <td>{{ $order->order_number }}</td>
                                                <td>{{ $order->patient->name ?? 'غير محدد' }}</td>
                                                <td>{{ $order->doctor->name ?? 'غير محدد' }}</td>
                                                <td>{{ $order->labTest->name ?? 'غير محدد' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $order->priority_color }}">
                                                        {{ $order->priority_display }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $order->status_color }}">
                                                        {{ $order->status_display }}
                                                    </span>
                                                </td>
                                                <td>{{ $order->ordered_at ? $order->ordered_at->format('H:i') : 'غير محدد' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning text-center">
                                <h5><i class="bi bi-flask"></i> لا توجد طلبات مختبر اليوم</h5>
                                <p>لم يتم طلب أي فحوصات مخبرية لتاريخ {{ today()->format('Y/m/d') }}</p>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <a href="{{ route('lab.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                العودة لجميع الطلبات
                            </a>
                            <a href="{{ route('lab.create') }}" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i>
                                طلب فحص جديد
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