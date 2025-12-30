@extends('layouts.app')

@section('title', 'تفاصيل القسط')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تفاصيل القسط #{{ $installment->installment_number }}</h3>
                    <div>
                        <a href="{{ route('dental.installments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة
                        </a>
                        <a href="{{ route('dental.installments.edit', $installment) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        @if($installment->status === 'pending')
                            <form method="POST" action="{{ route('dental.installments.mark-paid', $installment) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success" onclick="return confirm('هل أنت متأكد من تحديد هذا القسط كمدفوع؟')">
                                    <i class="fas fa-check"></i> تحديد كمدفوع
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Installment Details -->
                        <div class="col-md-6">
                            <h5>تفاصيل القسط</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>رقم القسط</th>
                                    <td>{{ $installment->installment_number }}</td>
                                </tr>
                                <tr>
                                    <th>المبلغ</th>
                                    <td>{{ number_format($installment->amount, 2) }} ريال</td>
                                </tr>
                                <tr>
                                    <th>تاريخ الاستحقاق</th>
                                    <td>{{ $installment->due_date->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th>الحالة</th>
                                    <td>
                                        <span class="badge badge-{{ $installment->status_color }}">
                                            {{ $installment->status_display }}
                                        </span>
                                    </td>
                                </tr>
                                @if($installment->paid_date)
                                    <tr>
                                        <th>تاريخ الدفع</th>
                                        <td>{{ $installment->paid_date->format('Y-m-d') }}</td>
                                    </tr>
                                @endif
                                @if($installment->payment_method)
                                    <tr>
                                        <th>طريقة الدفع</th>
                                        <td>{{ $installment->payment_method_display }}</td>
                                    </tr>
                                @endif
                                @if($installment->payment_notes)
                                    <tr>
                                        <th>ملاحظات الدفع</th>
                                        <td>{{ $installment->payment_notes }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>

                        <!-- Treatment Details -->
                        <div class="col-md-6">
                            <h5>تفاصيل العلاج</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>المريض</th>
                                    <td>
                                        <a href="{{ route('patients.show', $installment->dentalTreatment->patient) }}">
                                            {{ $installment->dentalTreatment->patient->name }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>الطبيب</th>
                                    <td>{{ $installment->dentalTreatment->doctor->name }}</td>
                                </tr>
                                <tr>
                                    <th>نوع العلاج</th>
                                    <td>{{ $installment->dentalTreatment->title }}</td>
                                </tr>
                                <tr>
                                    <th>التكلفة الإجمالية</th>
                                    <td>{{ number_format($installment->dentalTreatment->total_cost, 2) }} ريال</td>
                                </tr>
                                <tr>
                                    <th>المبلغ المدفوع</th>
                                    <td>{{ number_format($installment->dentalTreatment->paid_amount, 2) }} ريال</td>
                                </tr>
                                <tr>
                                    <th>المبلغ المتبقي</th>
                                    <td>{{ number_format($installment->dentalTreatment->total_cost - $installment->dentalTreatment->paid_amount, 2) }} ريال</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Payment History -->
                    @if($installment->payment_history)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>تاريخ المدفوعات</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>التاريخ</th>
                                                <th>المبلغ</th>
                                                <th>الطريقة</th>
                                                <th>المرجع</th>
                                                <th>الملاحظات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($installment->payment_history as $payment)
                                                <tr>
                                                    <td>{{ $payment['date'] }}</td>
                                                    <td>{{ number_format($payment['amount'], 2) }} ريال</td>
                                                    <td>{{ $payment['method'] ?? 'غير محدد' }}</td>
                                                    <td>{{ $payment['reference'] ?? '-' }}</td>
                                                    <td>{{ $payment['notes'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection