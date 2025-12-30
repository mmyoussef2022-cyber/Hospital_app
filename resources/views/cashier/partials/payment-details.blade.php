<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">معلومات الدفع</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>رقم الدفع:</strong></td>
                <td>#{{ $payment->id }}</td>
            </tr>
            <tr>
                <td><strong>المبلغ:</strong></td>
                <td>{{ number_format($payment->amount, 2) }} ر.س</td>
            </tr>
            <tr>
                <td><strong>طريقة الدفع:</strong></td>
                <td>
                    @switch($payment->payment_method)
                        @case('cash')
                            <span class="badge bg-success">نقدي</span>
                            @break
                        @case('visa')
                            <span class="badge bg-primary">فيزا</span>
                            @break
                        @case('mastercard')
                            <span class="badge bg-warning">ماستركارد</span>
                            @break
                        @case('bank_transfer')
                            <span class="badge bg-info">تحويل بنكي</span>
                            @break
                        @case('insurance')
                            <span class="badge bg-secondary">تأمين</span>
                            @break
                    @endswitch
                </td>
            </tr>
            <tr>
                <td><strong>الحالة:</strong></td>
                <td>
                    @switch($payment->status)
                        @case('pending')
                            <span class="badge bg-warning">معلق</span>
                            @break
                        @case('completed')
                            <span class="badge bg-success">مكتمل</span>
                            @break
                        @case('rejected')
                            <span class="badge bg-danger">مرفوض</span>
                            @break
                    @endswitch
                </td>
            </tr>
            <tr>
                <td><strong>تاريخ الإنشاء:</strong></td>
                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>معالج بواسطة:</strong></td>
                <td>{{ $payment->processedBy->name }}</td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">معلومات المريض والفاتورة</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>اسم المريض:</strong></td>
                <td>{{ $payment->invoice->patient->name }}</td>
            </tr>
            <tr>
                <td><strong>رقم الهوية:</strong></td>
                <td>{{ $payment->invoice->patient->national_id }}</td>
            </tr>
            <tr>
                <td><strong>رقم الهاتف:</strong></td>
                <td>{{ $payment->invoice->patient->phone }}</td>
            </tr>
            <tr>
                <td><strong>نوع المريض:</strong></td>
                <td>
                    @if($payment->invoice->patient->insurancePolicy)
                        <span class="badge bg-info">مؤمن</span>
                        <small class="d-block">{{ $payment->invoice->patient->insurancePolicy->company->name }}</small>
                    @else
                        <span class="badge bg-success">نقدي</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>رقم الفاتورة:</strong></td>
                <td>
                    <a href="{{ route('invoices.show', $payment->invoice->id) }}" target="_blank">
                        #{{ $payment->invoice->invoice_number }}
                    </a>
                </td>
            </tr>
            <tr>
                <td><strong>إجمالي الفاتورة:</strong></td>
                <td>{{ number_format($payment->invoice->total_amount, 2) }} ر.س</td>
            </tr>
        </table>
    </div>
</div>

@if($payment->transaction_reference)
<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-primary mb-3">تفاصيل المعاملة</h6>
        <div class="alert alert-info">
            <strong>رقم المرجع:</strong> {{ $payment->transaction_reference }}
            
            @if($payment->metadata)
                @php $metadata = json_decode($payment->metadata, true); @endphp
                @if($metadata)
                    <div class="mt-2">
                        @foreach($metadata as $key => $value)
                            <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endif

@if($payment->gateway_response)
<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-primary mb-3">استجابة بوابة الدفع</h6>
        <div class="alert alert-secondary">
            @php $response = json_decode($payment->gateway_response, true); @endphp
            @if($response)
                @foreach($response as $key => $value)
                    <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif

@if($payment->notes)
<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-primary mb-3">ملاحظات</h6>
        <div class="alert alert-light">
            {{ $payment->notes }}
        </div>
    </div>
</div>
@endif