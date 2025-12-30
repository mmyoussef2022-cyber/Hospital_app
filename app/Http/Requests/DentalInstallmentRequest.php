<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DentalInstallmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'treatment_id' => 'required|exists:dental_treatments,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'due_date' => 'required|date|after_or_equal:today',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,check',
            'paid_date' => 'nullable|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'late_fee' => 'nullable|numeric|min:0|max:99999.99',
            'discount' => 'nullable|numeric|min:0|max:99999.99'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'treatment_id.required' => 'يجب اختيار خطة العلاج',
            'treatment_id.exists' => 'خطة العلاج المحددة غير موجودة',
            'amount.required' => 'يجب تحديد مبلغ القسط',
            'amount.numeric' => 'مبلغ القسط يجب أن يكون رقم',
            'amount.min' => 'مبلغ القسط يجب أن يكون أكبر من صفر',
            'amount.max' => 'مبلغ القسط مرتفع جداً',
            'due_date.required' => 'يجب تحديد تاريخ الاستحقاق',
            'due_date.date' => 'تاريخ الاستحقاق غير صحيح',
            'due_date.after_or_equal' => 'تاريخ الاستحقاق لا يمكن أن يكون في الماضي',
            'status.required' => 'يجب تحديد حالة القسط',
            'status.in' => 'حالة القسط غير صحيحة',
            'payment_method.in' => 'طريقة الدفع غير صحيحة',
            'paid_date.date' => 'تاريخ الدفع غير صحيح',
            'paid_date.before_or_equal' => 'تاريخ الدفع لا يمكن أن يكون في المستقبل',
            'notes.max' => 'الملاحظات لا يجب أن تتجاوز 1000 حرف',
            'late_fee.numeric' => 'رسوم التأخير يجب أن تكون رقم',
            'late_fee.min' => 'رسوم التأخير لا يمكن أن تكون سالبة',
            'late_fee.max' => 'رسوم التأخير مرتفعة جداً',
            'discount.numeric' => 'الخصم يجب أن يكون رقم',
            'discount.min' => 'الخصم لا يمكن أن يكون سالب',
            'discount.max' => 'الخصم مرتفع جداً'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'treatment_id' => 'خطة العلاج',
            'amount' => 'مبلغ القسط',
            'due_date' => 'تاريخ الاستحقاق',
            'status' => 'حالة القسط',
            'payment_method' => 'طريقة الدفع',
            'paid_date' => 'تاريخ الدفع',
            'notes' => 'الملاحظات',
            'late_fee' => 'رسوم التأخير',
            'discount' => 'الخصم'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate paid_date is required when status is paid
            if ($this->status === 'paid' && empty($this->paid_date)) {
                $validator->errors()->add('paid_date', 'يجب تحديد تاريخ الدفع للأقساط المدفوعة');
            }

            // Validate payment_method is required when status is paid
            if ($this->status === 'paid' && empty($this->payment_method)) {
                $validator->errors()->add('payment_method', 'يجب تحديد طريقة الدفع للأقساط المدفوعة');
            }

            // Validate amount doesn't exceed remaining treatment amount
            if ($this->treatment_id && $this->amount) {
                $treatment = \App\Models\DentalTreatment::find($this->treatment_id);
                if ($treatment) {
                    $totalInstallments = $treatment->installments()
                        ->where('id', '!=', $this->route('installment')?->id ?? 0)
                        ->sum('amount');
                    
                    $remainingAmount = $treatment->total_cost - $treatment->paid_amount;
                    
                    if (($totalInstallments + $this->amount) > $remainingAmount) {
                        $validator->errors()->add('amount', 'مبلغ القسط يتجاوز المبلغ المتبقي من العلاج');
                    }
                }
            }

            // Validate discount doesn't exceed installment amount
            if ($this->discount && $this->amount && $this->discount > $this->amount) {
                $validator->errors()->add('discount', 'الخصم لا يمكن أن يتجاوز مبلغ القسط');
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set paid_date to today if status is paid and no date provided
        if ($this->status === 'paid' && empty($this->paid_date)) {
            $this->merge([
                'paid_date' => now()->format('Y-m-d')
            ]);
        }

        // Set default payment method if status is paid
        if ($this->status === 'paid' && empty($this->payment_method)) {
            $this->merge([
                'payment_method' => 'cash'
            ]);
        }
    }
}