<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $appointmentId = $this->route('appointment')?->id;

        return [
            'patient_id' => [
                'required',
                'exists:patients,id'
            ],
            'doctor_id' => [
                'required',
                'exists:users,id'
            ],
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'appointment_time' => [
                'required',
                'date_format:H:i'
            ],
            'duration' => [
                'nullable',
                'integer',
                'min:15',
                'max:240'
            ],
            'type' => [
                'required',
                'in:consultation,follow_up,emergency,surgery'
            ],
            'status' => [
                'nullable',
                'in:scheduled,confirmed,in_progress,completed,cancelled,no_show'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'يرجى اختيار المريض',
            'patient_id.exists' => 'المريض المحدد غير موجود',
            'doctor_id.required' => 'يرجى اختيار الطبيب',
            'doctor_id.exists' => 'الطبيب المحدد غير موجود',
            'appointment_date.required' => 'يرجى تحديد تاريخ الموعد',
            'appointment_date.date' => 'تاريخ الموعد غير صحيح',
            'appointment_date.after_or_equal' => 'لا يمكن حجز موعد في الماضي',
            'appointment_time.required' => 'يرجى تحديد وقت الموعد',
            'appointment_time.date_format' => 'صيغة الوقت غير صحيحة',
            'duration.integer' => 'مدة الموعد يجب أن تكون رقماً',
            'duration.min' => 'أقل مدة للموعد هي 15 دقيقة',
            'duration.max' => 'أقصى مدة للموعد هي 4 ساعات',
            'type.required' => 'يرجى تحديد نوع الموعد',
            'type.in' => 'نوع الموعد المحدد غير صحيح',
            'status.in' => 'حالة الموعد المحددة غير صحيحة',
            'notes.max' => 'الملاحظات لا يجب أن تتجاوز 1000 حرف'
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
            'appointment_date' => 'تاريخ الموعد',
            'appointment_time' => 'وقت الموعد',
            'duration' => 'مدة الموعد',
            'type' => 'نوع الموعد',
            'status' => 'حالة الموعد',
            'notes' => 'الملاحظات'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default duration if not provided
        if (!$this->has('duration') || empty($this->duration)) {
            $this->merge(['duration' => 30]);
        }

        // Set default status if not provided
        if (!$this->has('status') || empty($this->status)) {
            $this->merge(['status' => 'scheduled']);
        }
    }
}
