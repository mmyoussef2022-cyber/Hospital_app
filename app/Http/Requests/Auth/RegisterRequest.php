<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'national_id' => 'required|string|size:10|unique:users,national_id',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'employee_id' => 'nullable|string|max:20|unique:users,employee_id',
            'department_id' => 'nullable|exists:departments,id',
            'job_title' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date|before_or_equal:today',
            'salary' => 'nullable|numeric|min:0|max:999999.99',
            'emergency_contact' => 'nullable|array',
            'emergency_contact.name' => 'nullable|string|max:255',
            'emergency_contact.phone' => 'nullable|string|max:20',
            'emergency_contact.relationship' => 'nullable|string|max:100',
            'preferred_language' => 'in:ar,en'
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم الكامل مطلوب',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'national_id.required' => 'الرقم الوطني مطلوب',
            'national_id.size' => 'الرقم الوطني يجب أن يكون 10 أرقام بالضبط',
            'national_id.unique' => 'الرقم الوطني مستخدم بالفعل',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 20 رقم',
            'mobile.max' => 'رقم الجوال يجب ألا يتجاوز 20 رقم',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
            'date_of_birth.date' => 'تاريخ الميلاد غير صحيح',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون في الماضي',
            'address.max' => 'العنوان يجب ألا يتجاوز 500 حرف',
            'employee_id.max' => 'رقم الموظف يجب ألا يتجاوز 20 حرف',
            'employee_id.unique' => 'رقم الموظف مستخدم بالفعل',
            'department_id.exists' => 'القسم المحدد غير موجود',
            'job_title.required' => 'المسمى الوظيفي مطلوب',
            'job_title.max' => 'المسمى الوظيفي يجب ألا يتجاوز 255 حرف',
            'specialization.max' => 'التخصص يجب ألا يتجاوز 255 حرف',
            'license_number.max' => 'رقم الترخيص يجب ألا يتجاوز 50 حرف',
            'hire_date.date' => 'تاريخ التوظيف غير صحيح',
            'hire_date.before_or_equal' => 'تاريخ التوظيف لا يمكن أن يكون في المستقبل',
            'salary.numeric' => 'الراتب يجب أن يكون رقم',
            'salary.min' => 'الراتب لا يمكن أن يكون سالب',
            'salary.max' => 'الراتب يجب ألا يتجاوز 999,999.99',
            'preferred_language.in' => 'اللغة المفضلة يجب أن تكون العربية أو الإنجليزية'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'preferred_language' => $this->preferred_language ?? 'ar',
            'is_active' => true
        ]);

        // Prepare emergency contact as JSON
        if ($this->has('emergency_contact')) {
            $emergencyContact = $this->emergency_contact;
            if (is_array($emergencyContact) && !empty(array_filter($emergencyContact))) {
                $this->merge(['emergency_contact' => $emergencyContact]);
            } else {
                $this->merge(['emergency_contact' => null]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate national ID format (Saudi format)
            if ($this->national_id && !$this->isValidSaudiNationalId($this->national_id)) {
                $validator->errors()->add('national_id', 'الرقم الوطني غير صحيح');
            }

            // Validate phone numbers format
            if ($this->phone && !$this->isValidPhoneNumber($this->phone)) {
                $validator->errors()->add('phone', 'رقم الهاتف غير صحيح');
            }

            if ($this->mobile && !$this->isValidPhoneNumber($this->mobile)) {
                $validator->errors()->add('mobile', 'رقم الجوال غير صحيح');
            }
        });
    }

    /**
     * Validate Saudi National ID format
     */
    private function isValidSaudiNationalId(string $nationalId): bool
    {
        // Basic validation for Saudi National ID
        if (!preg_match('/^[12]\d{9}$/', $nationalId)) {
            return false;
        }

        // Additional checksum validation can be added here
        return true;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // Check if it's a valid Saudi phone number
        return preg_match('/^(966|0)?5\d{8}$/', $cleanPhone) || 
               preg_match('/^(966|0)?1\d{7}$/', $cleanPhone);
    }
}
