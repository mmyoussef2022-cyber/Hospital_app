<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->can('doctors.create') || 
            auth()->user()->can('doctors.edit')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'service_name' => 'required|string|max:255',
            'service_name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:consultation,surgery,procedure,examination,treatment,follow_up,emergency,other',
            'price' => 'required|numeric|min:0|max:999999.99',
            'duration_minutes' => 'required|integer|min:1|max:1440', // Max 24 hours
            'requirements_list' => 'nullable|string|max:2000',
            'preparation_list' => 'nullable|string|max:2000',
            'requires_appointment' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:1'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'service_name' => 'اسم الخدمة',
            'service_name_en' => 'اسم الخدمة بالإنجليزية',
            'description' => 'وصف الخدمة',
            'category' => 'فئة الخدمة',
            'price' => 'سعر الخدمة',
            'duration_minutes' => 'مدة الخدمة بالدقائق',
            'requirements_list' => 'متطلبات الخدمة',
            'preparation_list' => 'تعليمات التحضير',
            'requires_appointment' => 'يتطلب موعد مسبق',
            'is_active' => 'حالة الخدمة',
            'sort_order' => 'ترتيب الخدمة'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'service_name.required' => 'اسم الخدمة مطلوب',
            'service_name.max' => 'اسم الخدمة لا يمكن أن يتجاوز 255 حرف',
            'service_name_en.max' => 'اسم الخدمة بالإنجليزية لا يمكن أن يتجاوز 255 حرف',
            'description.max' => 'وصف الخدمة لا يمكن أن يتجاوز 1000 حرف',
            'category.required' => 'فئة الخدمة مطلوبة',
            'category.in' => 'فئة الخدمة غير صحيحة',
            'price.required' => 'سعر الخدمة مطلوب',
            'price.numeric' => 'سعر الخدمة يجب أن يكون رقم',
            'price.min' => 'سعر الخدمة لا يمكن أن يكون أقل من صفر',
            'price.max' => 'سعر الخدمة لا يمكن أن يتجاوز 999,999.99',
            'duration_minutes.required' => 'مدة الخدمة مطلوبة',
            'duration_minutes.integer' => 'مدة الخدمة يجب أن تكون رقم صحيح',
            'duration_minutes.min' => 'مدة الخدمة يجب أن تكون دقيقة واحدة على الأقل',
            'duration_minutes.max' => 'مدة الخدمة لا يمكن أن تتجاوز 24 ساعة (1440 دقيقة)',
            'requirements_list.max' => 'متطلبات الخدمة لا يمكن أن تتجاوز 2000 حرف',
            'preparation_list.max' => 'تعليمات التحضير لا يمكن أن تتجاوز 2000 حرف',
            'requires_appointment.boolean' => 'حقل يتطلب موعد مسبق يجب أن يكون صحيح أو خطأ',
            'is_active.boolean' => 'حالة الخدمة يجب أن تكون نشط أو غير نشط',
            'sort_order.integer' => 'ترتيب الخدمة يجب أن يكون رقم صحيح',
            'sort_order.min' => 'ترتيب الخدمة يجب أن يكون 1 على الأقل'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the data
        if ($this->has('service_name')) {
            $this->merge([
                'service_name' => trim($this->service_name)
            ]);
        }

        if ($this->has('service_name_en')) {
            $this->merge([
                'service_name_en' => trim($this->service_name_en)
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description)
            ]);
        }

        if ($this->has('requirements_list')) {
            $this->merge([
                'requirements_list' => trim($this->requirements_list)
            ]);
        }

        if ($this->has('preparation_list')) {
            $this->merge([
                'preparation_list' => trim($this->preparation_list)
            ]);
        }

        // Convert checkboxes to boolean
        $this->merge([
            'requires_appointment' => $this->boolean('requires_appointment'),
            'is_active' => $this->boolean('is_active', true) // Default to active
        ]);

        // Convert price to float
        if ($this->has('price')) {
            $this->merge([
                'price' => (float) str_replace(',', '', $this->price)
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation for duration based on category
            if ($this->category === 'surgery' && $this->duration_minutes < 30) {
                $validator->errors()->add('duration_minutes', 'العمليات الجراحية يجب أن تكون 30 دقيقة على الأقل');
            }

            if ($this->category === 'consultation' && $this->duration_minutes > 120) {
                $validator->errors()->add('duration_minutes', 'الاستشارات عادة لا تتجاوز ساعتين');
            }

            // Validate price based on category
            if ($this->category === 'emergency' && $this->price < 100) {
                $validator->errors()->add('price', 'خدمات الطوارئ يجب أن تكون 100 ريال على الأقل');
            }
        });
    }
}