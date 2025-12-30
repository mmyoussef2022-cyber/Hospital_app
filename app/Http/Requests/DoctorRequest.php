<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoctorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('doctors.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $doctorId = $this->route('doctor') ? $this->route('doctor')->id : null;
        $userId = $this->route('doctor') ? $this->route('doctor')->user_id : null;

        return [
            // User data
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => $this->isMethod('post') ? 'required|string|min:8|confirmed' : 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id',
            'job_title' => 'nullable|string|max:255',
            
            // Doctor specific data
            'national_id' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                Rule::unique('doctors', 'national_id')->ignore($doctorId)
            ],
            'license_number' => [
                'required',
                'string',
                Rule::unique('doctors', 'license_number')->ignore($doctorId)
            ],
            'specialization' => 'required|string',
            'sub_specializations' => 'nullable|array',
            'sub_specializations.*' => 'string|max:255',
            'degree' => 'required|string',
            'university' => 'nullable|string|max:255',
            'experience_years' => 'required|integer|min:0|max:50',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:100',
            'biography' => 'nullable|string|max:2000',
            'consultation_fee' => 'required|numeric|min:0|max:9999.99',
            'follow_up_fee' => 'required|numeric|min:0|max:9999.99',
            'room_number' => 'nullable|string|max:50',
            'doctor_phone' => 'nullable|string|max:20',
            'doctor_email' => 'nullable|email|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            
            // Working hours
            'working_hours' => 'nullable|array',
            'working_hours.*.is_working' => 'boolean',
            'working_hours.*.start' => 'nullable|date_format:H:i',
            'working_hours.*.end' => 'nullable|date_format:H:i|after:working_hours.*.start'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الطبيب',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',
            'phone' => 'رقم الهاتف',
            'mobile' => 'رقم الجوال',
            'gender' => 'الجنس',
            'date_of_birth' => 'تاريخ الميلاد',
            'address' => 'العنوان',
            'department_id' => 'القسم',
            'job_title' => 'المسمى الوظيفي',
            'national_id' => 'الرقم القومي',
            'license_number' => 'رقم الترخيص',
            'specialization' => 'التخصص',
            'sub_specializations' => 'التخصصات الفرعية',
            'degree' => 'الدرجة العلمية',
            'university' => 'الجامعة',
            'experience_years' => 'سنوات الخبرة',
            'languages' => 'اللغات',
            'biography' => 'السيرة الذاتية',
            'consultation_fee' => 'رسوم الاستشارة',
            'follow_up_fee' => 'رسوم المتابعة',
            'room_number' => 'رقم الغرفة',
            'doctor_phone' => 'هاتف الطبيب',
            'doctor_email' => 'إيميل الطبيب',
            'profile_photo' => 'الصورة الشخصية',
            'is_active' => 'الحالة النشطة',
            'is_available' => 'الإتاحة',
            'working_hours' => 'ساعات العمل'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'national_id.regex' => 'الرقم القومي يجب أن يكون 10 أرقام فقط',
            'national_id.unique' => 'هذا الرقم القومي مسجل مسبقاً',
            'license_number.unique' => 'رقم الترخيص مسجل مسبقاً',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً',
            'consultation_fee.max' => 'رسوم الاستشارة لا يمكن أن تتجاوز 9999.99',
            'follow_up_fee.max' => 'رسوم المتابعة لا يمكن أن تتجاوز 9999.99',
            'experience_years.max' => 'سنوات الخبرة لا يمكن أن تتجاوز 50 سنة',
            'profile_photo.image' => 'الملف يجب أن يكون صورة',
            'profile_photo.mimes' => 'الصورة يجب أن تكون من نوع: jpeg, png, jpg',
            'profile_photo.max' => 'حجم الصورة لا يمكن أن يتجاوز 2 ميجابايت',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون في الماضي',
            'working_hours.*.end.after' => 'وقت الانتهاء يجب أن يكون بعد وقت البداية'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_available' => $this->boolean('is_available')
        ]);

        // Process working hours
        if ($this->has('working_hours')) {
            $workingHours = [];
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            
            foreach ($days as $day) {
                $workingHours[$day] = [
                    'is_working' => $this->boolean("working_hours.{$day}.is_working"),
                    'start' => $this->input("working_hours.{$day}.start", '08:00'),
                    'end' => $this->input("working_hours.{$day}.end", '16:00')
                ];
            }
            
            $this->merge(['working_hours' => $workingHours]);
        }

        // Process arrays
        if ($this->has('sub_specializations') && is_string($this->sub_specializations)) {
            $this->merge([
                'sub_specializations' => array_filter(explode(',', $this->sub_specializations))
            ]);
        }

        if ($this->has('languages') && is_string($this->languages)) {
            $this->merge([
                'languages' => array_filter(explode(',', $this->languages))
            ]);
        }
    }
}