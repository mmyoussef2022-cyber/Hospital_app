<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DentalSessionRequest extends FormRequest
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
            'treatment_id' => 'required|exists:dental_treatments,id',
            'session_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'procedures_performed' => 'nullable|string|max:1000',
            'session_notes' => 'nullable|string|max:2000',
            'session_cost' => 'required|numeric|min:0|max:999999.99',
            'materials_used' => 'nullable|string|max:1000',
            'next_session_notes' => 'nullable|string|max:1000',
            'patient_feedback' => 'nullable|string|max:1000',
            'complications' => 'nullable|string|max:1000',
            'cancellation_reason' => 'nullable|string|max:500',
            'session_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'session_notes_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240'
        ];

        // Additional validation for update requests
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // Allow past dates for completed sessions
            if ($this->status === 'completed') {
                $rules['session_date'] = 'required|date';
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'treatment_id.required' => 'يجب اختيار خطة العلاج',
            'treatment_id.exists' => 'خطة العلاج المحددة غير موجودة',
            'session_date.required' => 'يجب تحديد تاريخ الجلسة',
            'session_date.date' => 'تاريخ الجلسة غير صحيح',
            'session_date.after_or_equal' => 'لا يمكن تحديد تاريخ في الماضي',
            'start_time.required' => 'يجب تحديد وقت بداية الجلسة',
            'start_time.date_format' => 'صيغة وقت البداية غير صحيحة',
            'end_time.required' => 'يجب تحديد وقت نهاية الجلسة',
            'end_time.date_format' => 'صيغة وقت النهاية غير صحيحة',
            'end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية',
            'status.required' => 'يجب تحديد حالة الجلسة',
            'status.in' => 'حالة الجلسة غير صحيحة',
            'procedures_performed.max' => 'الإجراءات المنفذة لا يجب أن تتجاوز 1000 حرف',
            'session_notes.max' => 'ملاحظات الجلسة لا يجب أن تتجاوز 2000 حرف',
            'session_cost.required' => 'يجب تحديد تكلفة الجلسة',
            'session_cost.numeric' => 'تكلفة الجلسة يجب أن تكون رقم',
            'session_cost.min' => 'تكلفة الجلسة لا يمكن أن تكون سالبة',
            'session_cost.max' => 'تكلفة الجلسة مرتفعة جداً',
            'materials_used.max' => 'المواد المستخدمة لا يجب أن تتجاوز 1000 حرف',
            'next_session_notes.max' => 'ملاحظات الجلسة القادمة لا يجب أن تتجاوز 1000 حرف',
            'patient_feedback.max' => 'تعليقات المريض لا يجب أن تتجاوز 1000 حرف',
            'complications.max' => 'المضاعفات لا يجب أن تتجاوز 1000 حرف',
            'cancellation_reason.max' => 'سبب الإلغاء لا يجب أن يتجاوز 500 حرف',
            'session_images.*.image' => 'يجب أن تكون الملفات المرفوعة صور',
            'session_images.*.mimes' => 'صيغة الصورة غير مدعومة (المدعوم: jpeg, png, jpg, gif)',
            'session_images.*.max' => 'حجم الصورة لا يجب أن يتجاوز 5 ميجابايت',
            'session_notes_file.file' => 'يجب أن يكون الملف المرفوع ملف صحيح',
            'session_notes_file.mimes' => 'صيغة الملف غير مدعومة (المدعوم: pdf, doc, docx)',
            'session_notes_file.max' => 'حجم الملف لا يجب أن يتجاوز 10 ميجابايت'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'treatment_id' => 'خطة العلاج',
            'session_date' => 'تاريخ الجلسة',
            'start_time' => 'وقت البداية',
            'end_time' => 'وقت النهاية',
            'status' => 'حالة الجلسة',
            'procedures_performed' => 'الإجراءات المنفذة',
            'session_notes' => 'ملاحظات الجلسة',
            'session_cost' => 'تكلفة الجلسة',
            'materials_used' => 'المواد المستخدمة',
            'next_session_notes' => 'ملاحظات الجلسة القادمة',
            'patient_feedback' => 'تعليقات المريض',
            'complications' => 'المضاعفات',
            'cancellation_reason' => 'سبب الإلغاء',
            'session_images' => 'صور الجلسة',
            'session_notes_file' => 'ملف الملاحظات'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate session time doesn't conflict with other sessions
            if ($this->treatment_id && $this->session_date && $this->start_time && $this->end_time) {
                $conflictQuery = \App\Models\DentalSession::where('treatment_id', $this->treatment_id)
                    ->where('session_date', $this->session_date)
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($query) {
                        $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                              ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                              ->orWhere(function ($q) {
                                  $q->where('start_time', '<=', $this->start_time)
                                    ->where('end_time', '>=', $this->end_time);
                              });
                    });

                // Exclude current session for updates
                if ($this->route('session')) {
                    $conflictQuery->where('id', '!=', $this->route('session')->id);
                }

                if ($conflictQuery->exists()) {
                    $validator->errors()->add('start_time', 'يوجد تعارض مع جلسة أخرى في نفس الوقت');
                }
            }

            // Validate required fields based on status
            if ($this->status === 'completed') {
                if (empty($this->procedures_performed)) {
                    $validator->errors()->add('procedures_performed', 'يجب تحديد الإجراءات المنفذة للجلسات المكتملة');
                }
            }

            if ($this->status === 'cancelled') {
                if (empty($this->cancellation_reason)) {
                    $validator->errors()->add('cancellation_reason', 'يجب تحديد سبب الإلغاء');
                }
            }
        });
    }
}