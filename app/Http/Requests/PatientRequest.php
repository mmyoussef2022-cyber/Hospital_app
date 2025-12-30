<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
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
        $patientId = $this->route('patient') ? $this->route('patient')->id : null;

        return [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'national_id' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                Rule::unique('patients', 'national_id')->ignore($patientId)
            ],
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date|before:today',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'required|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->ignore($patientId)
            ],
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'occupation' => 'nullable|string|max:255',
            'blood_type' => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'emergency_contact' => 'required|array',
            'emergency_contact.name' => 'required|string|max:255',
            'emergency_contact.relationship' => 'required|string|max:100',
            'emergency_contact.phone' => 'required|string|max:20',
            'insurance_info' => 'nullable|array',
            'insurance_info.company' => 'nullable|string|max:255',
            'insurance_info.policy_number' => 'nullable|string|max:100',
            'insurance_info.coverage_percentage' => 'nullable|numeric|min:0|max:100',
            'allergies' => 'nullable|array',
            'allergies.*' => 'string|max:255',
            'chronic_conditions' => 'nullable|array',
            'chronic_conditions.*' => 'string|max:255',
            'medical_notes' => 'nullable|string|max:1000',
            'family_head_id' => 'nullable|exists:patients,id',
            'family_relation' => 'nullable|string|max:100',
            'patient_type' => 'required|in:outpatient,inpatient,emergency',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'preferences' => 'nullable|array',
            'preferences.language' => 'nullable|in:ar,en',
            'preferences.communication_method' => 'nullable|in:phone,sms,email,whatsapp',
            'preferences.appointment_reminders' => 'nullable|boolean',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المريض مطلوب',
            'name.max' => 'اسم المريض يجب ألا يتجاوز 255 حرف',
            'national_id.required' => 'رقم الهوية الوطنية مطلوب',
            'national_id.regex' => 'رقم الهوية الوطنية يجب أن يكون 10 أرقام',
            'national_id.unique' => 'رقم الهوية الوطنية مسجل مسبقاً',
            'gender.required' => 'الجنس مطلوب',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
            'date_of_birth.required' => 'تاريخ الميلاد مطلوب',
            'date_of_birth.date' => 'تاريخ الميلاد يجب أن يكون تاريخ صحيح',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم',
            'mobile.required' => 'رقم الجوال مطلوب',
            'email.email' => 'البريد الإلكتروني يجب أن يكون صحيح',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً',
            'address.required' => 'العنوان مطلوب',
            'city.required' => 'المدينة مطلوبة',
            'country.required' => 'الدولة مطلوبة',
            'nationality.required' => 'الجنسية مطلوبة',
            'marital_status.required' => 'الحالة الاجتماعية مطلوبة',
            'blood_type.required' => 'فصيلة الدم مطلوبة',
            'emergency_contact.required' => 'جهة الاتصال في حالات الطوارئ مطلوبة',
            'emergency_contact.name.required' => 'اسم جهة الاتصال في حالات الطوارئ مطلوب',
            'emergency_contact.relationship.required' => 'صلة القرابة مطلوبة',
            'emergency_contact.phone.required' => 'رقم هاتف جهة الاتصال في حالات الطوارئ مطلوب',
            'patient_type.required' => 'نوع المريض مطلوب',
            'profile_photo.image' => 'الصورة الشخصية يجب أن تكون صورة',
            'profile_photo.mimes' => 'الصورة الشخصية يجب أن تكون من نوع jpeg, png, jpg',
            'profile_photo.max' => 'حجم الصورة الشخصية يجب ألا يتجاوز 2 ميجابايت',
            'family_head_id.exists' => 'رب الأسرة المحدد غير موجود'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert checkbox values to boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active')
            ]);
        }

        // Set default values
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Clean and format national ID
        if ($this->has('national_id')) {
            $this->merge([
                'national_id' => preg_replace('/[^0-9]/', '', $this->national_id)
            ]);
        }
    }
}