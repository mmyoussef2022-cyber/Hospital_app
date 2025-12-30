@extends('layouts.app')

@section('title', 'تعديل القسط')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تعديل القسط #{{ $installment->installment_number }}</h3>
                </div>

                <form method="POST" action="{{ route('dental.installments.update', $installment) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dental_treatment_id">العلاج <span class="text-danger">*</span></label>
                                    <select name="dental_treatment_id" id="dental_treatment_id" class="form-control @error('dental_treatment_id') is-invalid @enderror" required>
                                        <option value="">اختر العلاج</option>
                                        @foreach($treatments as $treatment)
                                            <option value="{{ $treatment->id }}" {{ old('dental_treatment_id', $installment->dental_treatment_id) == $treatment->id ? 'selected' : '' }}>
                                                {{ $treatment->patient->name }} - {{ $treatment->title }} ({{ number_format($treatment->total_cost, 2) }} ريال)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dental_treatment_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">المبلغ <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $installment->amount) }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="due_date">تاريخ الاستحقاق <span class="text-danger">*</span></label>
                                    <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $installment->due_date->format('Y-m-d')) }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">الحالة</label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                        <option value="pending" {{ old('status', $installment->status) == 'pending' ? 'selected' : '' }}>معلق</option>
                                        <option value="paid" {{ old('status', $installment->status) == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                        <option value="overdue" {{ old('status', $installment->status) == 'overdue' ? 'selected' : '' }}>متأخر</option>
                                        <option value="cancelled" {{ old('status', $installment->status) == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="payment-fields">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method">طريقة الدفع</label>
                                    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                                        <option value="">اختر طريقة الدفع</option>
                                        <option value="cash" {{ old('payment_method', $installment->payment_method) == 'cash' ? 'selected' : '' }}>نقدي</option>
                                        <option value="card" {{ old('payment_method', $installment->payment_method) == 'card' ? 'selected' : '' }}>بطاقة</option>
                                        <option value="bank_transfer" {{ old('payment_method', $installment->payment_method) == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                                        <option value="check" {{ old('payment_method', $installment->payment_method) == 'check' ? 'selected' : '' }}>شيك</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="paid_date">تاريخ الدفع</label>
                                    <input type="date" name="paid_date" id="paid_date" class="form-control @error('paid_date') is-invalid @enderror" value="{{ old('paid_date', $installment->paid_date?->format('Y-m-d')) }}">
                                    @error('paid_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="payment_notes">ملاحظات</label>
                                    <textarea name="payment_notes" id="payment_notes" class="form-control @error('payment_notes') is-invalid @enderror" rows="3">{{ old('payment_notes', $installment->payment_notes) }}</textarea>
                                    @error('payment_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                        <a href="{{ route('dental.installments.show', $installment) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const paymentFields = document.getElementById('payment-fields');
    
    function togglePaymentFields() {
        if (statusSelect.value === 'paid') {
            paymentFields.style.display = 'block';
        } else {
            paymentFields.style.display = 'none';
        }
    }
    
    statusSelect.addEventListener('change', togglePaymentFields);
    togglePaymentFields(); // Initial check
});
</script>
@endsection