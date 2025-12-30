<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DentalTreatmentRequest extends FormRequest
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
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'treatment_type' => 'required|in:orthodontics,implants,cosmetic,general,surgery,endodontics,periodontics,prosthodontics',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'teeth_involved' => 'nullable|array',
            'teeth_involved.*' => 'integer|between:1,32',
            'total_cost' => 'required|numeric|min:0|max:999999.99',
            'paid_amount' => 'nullable|numeric|min:0|lte:total_cost',
            'total_sessions' => 'required|integer|min:1|max:50',
            'payment_type' => 'required|in:cash,installments,insurance',
            'installment_months' => 'required_if:payment_type,installments|nullable|integer|min:2|max:60',
            'monthly_installment' => 'nullable|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'expected_end_date' => 'required|date|after:start_date',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string|max:1000',
            'treatment_plan' => 'nullable|array',
            'before_photos' => 'nullable|array|max:10',
            'before_photos.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'after_photos' => 'nullable|array|max:10',
            'after_photos.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'status' => 'nullable|in:planned,in_progress,completed,cancelled,on_hold'
        ];

        // Additional validation for update requests
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['actual_end_date'] = 'nullable|date|after_or_equal:start_date';
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'يجب اختيار المريض',
            'patient_id.exists' => 'المريض المحدد غير موجود',
            'doctor_id.required' => 'يجب اختيار الطبيب',
            'doctor_id.exists' => 'الطبيب المحدد غير موجود',
            'treatment_type.required' => 'يجب اختيار نوع العلاج',
            'treatment_type.in' => 'نوع العلاج المحدد غير صحيح',
            'title.required' => 'يجب إدخال عنوان العلاج',
            'title.max' => 'عنوان العلاج يجب ألا يزيد عن 255 حرف',
            'description.required' => 'يجب إدخال وصف العلاج',
            'teeth_involved.array' => 'الأسنان المتضررة يجب أن تكون قائمة',
            'teeth_involved.*.integer' => 'رقم السن يجب أن يكون رقم صحيح',
            'teeth_involved.*.between' => 'رقم السن يجب أن يكون بين 1 و 32',
            'total_cost.required' => 'يجب إدخال التكلفة الإجمالية',
            'total_cost.numeric' => 'التكلفة الإجمالية يجب أن تكون رقم',
            'total_cost.min' => 'التكلفة الإجمالية يجب أن تكون أكبر من أو تساوي صفر',
            'total_cost.max' => 'التكلفة الإجمالية يجب ألا تزيد عن 999,999.99',
            'paid_amount.numeric' => 'المبلغ المدفوع يجب أن يكون رقم',
            'paid_amount.min' => 'المبلغ المدفوع يجب أن يكون أكبر من أو يساوي صفر',
            'paid_amount.lte' => 'المبلغ المدفوع يجب ألا يزيد عن التكلفة الإجمالية',
            'total_sessions.required' => 'يجب إدخال عدد الجلسات',
            'total_sessions.integer' => 'عدد الجلسات يجب أن يكون رقم صحيح',
            'total_sessions.min' => 'عدد الجلسات يجب أن يكون على الأقل 1',
            'total_sessions.max' => 'عدد الجلسات يجب ألا يزيد عن 50',
            'payment_type.required' => 'يجب اختيار نوع الدفع',
            'payment_type.in' => 'نوع الدفع المحدد غير صحيح',
            'installment_months.required_if' => 'يجب إدخال عدد أشهر التقسيط عند اختيار الدفع بالأقساط',
            'installment_months.integer' => 'عدد أشهر التقسيط يجب أن يكون رقم صحيح',
            'installment_months.min' => 'عدد أشهر التقسيط يجب أن يكون على الأقل 2',
            'installment_months.max' => 'عدد أشهر التقسيط يجب ألا يزيد عن 60',
            'monthly_installment.numeric' => 'القسط الشهري يجب أن يكون رقم',
            'monthly_installment.min' => 'القسط الشهري يجب أن يكون أكبر من أو يساوي صفر',
            'start_date.required' => 'يجب إدخال تاريخ بداية العلاج',
            'start_date.date' => 'تاريخ بداية العلاج يجب أن يكون تاريخ صحيح',
            'start_date.after_or_equal' => 'تاريخ بداية العلاج يجب أن يكون اليوم أو بعده',
            'expected_end_date.required' => 'يجب إدخال تاريخ انتهاء العلاج المتوقع',
            'expected_end_date.date' => 'تاريخ انتهاء العلاج المتوقع يجب أن يكون تاريخ صحيح',
            'expected_end_date.after' => 'تاريخ انتهاء العلاج المتوقع يجب أن يكون بعد تاريخ البداية',
            'priority.required' => 'يجب اختيار أولوية العلاج',
            'priority.in' => 'أولوية العلاج المحددة غير صحيحة',
            'notes.max' => 'الملاحظات يجب ألا تزيد عن 1000 حرف',
            'treatment_plan.array' => 'خطة العلاج يجب أن تكون قائمة',
            'before_photos.array' => 'صور ما قبل العلاج يجب أن تكون قائمة',
            'before_photos.max' => 'يمكن رفع حد أقصى 10 صور لما قبل العلاج',
            'before_photos.*.image' => 'ملف صورة ما قبل العلاج يجب أن يكون صورة',
            'before_photos.*.mimes' => 'صورة ما قبل العلاج يجب أن تكون من نوع: jpeg, png, jpg',
            'before_photos.*.max' => 'حجم صورة ما قبل العلاج يجب ألا يزيد عن 5 ميجابايت',
            'after_photos.array' => 'صور ما بعد العلاج يجب أن تكون قائمة',
            'after_photos.max' => 'يمكن رفع حد أقصى 10 صور لما بعد العلاج',
            'after_photos.*.image' => 'ملف صورة ما بعد العلاج يجب أن يكون صورة',
            'after_photos.*.mimes' => 'صورة ما بعد العلاج يجب أن تكون من نوع: jpeg, png, jpg',
            'after_photos.*.max' => 'حجم صورة ما بعد العلاج يجب ألا يزيد عن 5 ميجابايت',
            'status.in' => 'حالة العلاج المحددة غير صحيحة',
            'actual_end_date.date' => 'تاريخ انتهاء العلاج الفعلي يجب أن يكون تاريخ صحيح',
            'actual_end_date.after_or_equal' => 'تاريخ انتهاء العلاج الفعلي يجب أن يكون بعد أو يساوي تاريخ البداية'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'المريض',
            'doctor_id' => 'الطبيب',
            'treatment_type' => 'نوع العلاج',
            'title' => 'عنوان العلاج',
            'description' => 'وصف العلاج',
            'teeth_involved' => 'الأسنان المتضررة',
            'total_cost' => 'التكلفة الإجمالية',
            'paid_amount' => 'المبلغ المدفوع',
            'total_sessions' => 'عدد الجلسات',
            'payment_type' => 'نوع الدفع',
            'installment_months' => 'عدد أشهر التقسيط',
            'monthly_installment' => 'القسط الشهري',
            'start_date' => 'تاريخ بداية العلاج',
            'expected_end_date' => 'تاريخ انتهاء العلاج المتوقع',
            'actual_end_date' => 'تاريخ انتهاء العلاج الفعلي',
            'priority' => 'أولوية العلاج',
            'notes' => 'الملاحظات',
            'treatment_plan' => 'خطة العلاج',
            'before_photos' => 'صور ما قبل العلاج',
            'after_photos' => 'صور ما بعد العلاج',
            'status' => 'حالة العلاج'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation for installment payments
            if ($this->payment_type === 'installments') {
                if (!$this->installment_months) {
                    $validator->errors()->add('installment_months', 'عدد أشهر التقسيط مطلوب عند اختيار الدفع بالأقساط');
                }
                
                if ($this->installment_months && $this->total_cost) {
                    $calculatedMonthly = $this->total_cost / $this->installment_months;
                    if ($this->monthly_installment && abs($this->monthly_installment - $calculatedMonthly) > 0.01) {
                        $validator->errors()->add('monthly_installment', 'القسط الشهري لا يتطابق مع التكلفة الإجمالية وعدد الأشهر');
                    }
                }
            }

            // Validate teeth numbers
            if ($this->teeth_involved && is_array($this->teeth_involved)) {
                foreach ($this->teeth_involved as $tooth) {
                    if (!is_numeric($tooth) || $tooth < 1 || $tooth > 32) {
                        $validator->errors()->add('teeth_involved', 'أرقام الأسنان يجب أن تكون بين 1 و 32');
                        break;
                    }
                }
            }

            // Validate treatment plan structure
            if ($this->treatment_plan && is_array($this->treatment_plan)) {
                foreach ($this->treatment_plan as $index => $step) {
                    if (!is_array($step) || !isset($step['title']) || !isset($step['description'])) {
                        $validator->errors()->add('treatment_plan', 'خطة العلاج يجب أن تحتوي على عنوان ووصف لكل خطوة');
                        break;
                    }
                }
            }
        });
    }
}