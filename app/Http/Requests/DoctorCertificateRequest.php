<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorCertificateRequest extends FormRequest
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
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:degree,certificate,course,license,fellowship,board,other',
            'institution' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'issue_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:issue_date',
            'certificate_number' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB max
        ];

        // For updates, make file optional if it already exists
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['certificate_file'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان الشهادة',
            'type' => 'نوع الشهادة',
            'institution' => 'المؤسسة المانحة',
            'country' => 'البلد',
            'issue_date' => 'تاريخ الإصدار',
            'expiry_date' => 'تاريخ الانتهاء',
            'certificate_number' => 'رقم الشهادة',
            'description' => 'الوصف',
            'certificate_file' => 'ملف الشهادة'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الشهادة مطلوب',
            'title.max' => 'عنوان الشهادة لا يمكن أن يتجاوز 255 حرف',
            'type.required' => 'نوع الشهادة مطلوب',
            'type.in' => 'نوع الشهادة غير صحيح',
            'institution.required' => 'المؤسسة المانحة مطلوبة',
            'institution.max' => 'اسم المؤسسة لا يمكن أن يتجاوز 255 حرف',
            'country.max' => 'اسم البلد لا يمكن أن يتجاوز 100 حرف',
            'issue_date.required' => 'تاريخ الإصدار مطلوب',
            'issue_date.date' => 'تاريخ الإصدار غير صحيح',
            'issue_date.before_or_equal' => 'تاريخ الإصدار لا يمكن أن يكون في المستقبل',
            'expiry_date.date' => 'تاريخ الانتهاء غير صحيح',
            'expiry_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ الإصدار',
            'certificate_number.max' => 'رقم الشهادة لا يمكن أن يتجاوز 100 حرف',
            'description.max' => 'الوصف لا يمكن أن يتجاوز 1000 حرف',
            'certificate_file.file' => 'يجب أن يكون ملف صحيح',
            'certificate_file.mimes' => 'نوع الملف يجب أن يكون: PDF, JPG, JPEG, PNG',
            'certificate_file.max' => 'حجم الملف لا يمكن أن يتجاوز 5 ميجابايت'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the data
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title)
            ]);
        }

        if ($this->has('institution')) {
            $this->merge([
                'institution' => trim($this->institution)
            ]);
        }

        if ($this->has('certificate_number')) {
            $this->merge([
                'certificate_number' => trim($this->certificate_number)
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description)
            ]);
        }
    }
}