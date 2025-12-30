@extends('layouts.app')

@section('title', 'إدارة طلبات الأشعة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-x-ray me-2"></i>
                        إدارة طلبات الأشعة
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('radiology.today') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-calendar-day me-1"></i>
                            طلبات اليوم
                        </a>
                        <a href="{{ route('radiology.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            طلب جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>مطلوب</option>
                                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>مجدول</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                        <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>تم التقرير</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="priority" class="form-select">
                                        <option value="">جميع الأولويات</option>
                                        <option value="routine" {{ request('priority') == 'routine' ? 'selected' : '' }}>عادي</option>
                                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجل</option>
                                        <option value="stat" {{ request('priority') == 'stat' ? 'selected' : '' }}>طارئ</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date" class="form-control" value="{{ request('date') }}" placeholder="التاريخ">
                                </div>
                                <div class="col-md-2">
                                    <select name="patient_id" class="form-select">
                                        <option value="">جميع المرضى</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="urgent_findings" value="1" 
                                               {{ request('urgent_findings') ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            نتائج عاجلة فقط
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> بحث
                                    </button>
                                    <a href="{{ route('radiology.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>المريض</th>
                                        <th>الطبيب</th>
                                        <th>نوع الفحص</th>
                                        <th>الأولوية</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th>المبلغ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $order->order_number }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->patient->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $order->patient->national_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->doctor->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $order->doctor->department->name ?? 'غير محدد' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->radiologyStudy->display_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $order->radiologyStudy->category_display }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->priority_color }}">
                                                    {{ $order->priority_display }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->status_color }}">
                                                    {{ $order->status_display }}
                                                </span>
                                                @if($order->has_urgent_findings)
                                                    <br>
                                                    <span class="badge bg-danger mt-1">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        نتائج عاجلة
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $order->ordered_at->format('Y-m-d') }}
                                                    <br>
                                                    <small class="text-muted">{{ $order->ordered_at->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ number_format($order->total_amount, 2) }} ريال</strong>
                                                @if($order->is_paid)
                                                    <br>
                                                    <span class="badge bg-success">مدفوع</span>
                                                @else
                                                    <br>
                                                    <span class="badge bg-warning">غير مدفوع</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('radiology.show', $order) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($order->canBeCancelled())
                                                        <a href="{{ route('radiology.edit', $order) }}" 
                                                           class="btn btn-sm btn-outline-warning" 
                                                           title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($order->hasReport())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                title="عرض التقرير">
                                                            <i class="fas fa-file-medical"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-x-ray fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد طلبات أشعة</h5>
                            <p class="text-muted">يمكنك إنشاء طلب جديد من الزر أعلاه</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection