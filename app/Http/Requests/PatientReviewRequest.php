<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->patient || 
            auth()->user()->can('reviews.create') || 
            auth()->user()->can('reviews.edit')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:2000',
            'rating_aspects' => 'nullable|array',
            'rating_aspects.professionalism' => 'nullable|integer|min:1|max:5',
            'rating_aspects.communication' => 'nullable|integer|min:1|max:5',
            'rating_aspects.punctuality' => 'nullable|integer|min:1|max:5',
            'rating_aspects.cleanliness' => 'nullable|integer|min:1|max:5',
            'rating_aspects.effectiveness' => 'nullable|integer|min:1|max:5',
            'rating_aspects.staff_behavior' => 'nullable|integer|min:1|max:5',
            'rating_aspects.waiting_time' => 'nullable|integer|min:1|max:5',
            'rating_aspects.value_for_money' => 'nullable|integer|min:1|max:5',
            'is_anonymous' => 'boolean'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'doctor_id' => 'الطبيب',
            'appointment_id' => 'الموعد',
            'rating' => 'التقييم العام',
            'review_text' => 'نص التقييم',
            'rating_aspects.professionalism' => 'الاحترافية',
            'rating_aspects.communication' => 'التواصل',
            'rating_aspects.punctuality' => 'الالتزام بالمواعيد',
            'rating_aspects.cleanliness' => 'النظافة',
            'rating_aspects.effectiveness' => 'فعالية العلاج',
            'rating_aspects.staff_behavior' => 'تعامل الطاقم',
            'rating_aspects.waiting_time' => 'وقت الانتظار',
            'rating_aspects.value_for_money' => 'القيمة مقابل المال',
            'is_anonymous' => 'تقييم مجهول'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'doctor_id.required' => 'يجب تحديد الطبيب',
            'doctor_id.exists' => 'الطبيب المحدد غير موجود',
            'appointment_id.exists' => 'الموعد المحدد غير موجود',
            'rating.required' => 'التقييم العام مطلوب',
            'rating.integer' => 'التقييم يجب أن يكون رقم صحيح',
            'rating.min' => 'التقييم يجب أن يكون من 1 إلى 5 نجوم',
            'rating.max' => 'التقييم يجب أن يكون من 1 إلى 5 نجوم',
            'review_text.max' => 'نص التقييم لا يمكن أن يتجاوز 2000 حرف',
            'rating_aspects.array' => 'تقييمات الجوانب يجب أن تكون مصفوفة',
            'rating_aspects.*.integer' => 'تقييم الجانب يجب أن يكون رقم صحيح',
            'rating_aspects.*.min' => 'تقييم الجانب يجب أن يكون من 1 إلى 5 نجوم',
            'rating_aspects.*.max' => 'تقييم الجانب يجب أن يكون من 1 إلى 5 نجوم',
            'is_anonymous.boolean' => 'خيار التقييم المجهول يجب أن يكون صحيح أو خطأ'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the data
        if ($this->has('review_text')) {
            $this->merge([
                'review_text' => trim($this->review_text)
            ]);
        }

        // Convert checkbox to boolean
        $this->merge([
            'is_anonymous' => $this->boolean('is_anonymous', false)
        ]);

        // Convert rating to integer
        if ($this->has('rating')) {
            $this->merge([
                'rating' => (int) $this->rating
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if patient has completed appointment with this doctor
            if ($this->appointment_id) {
                $appointment = \App\Models\Appointment::find($this->appointment_id);
                if ($appointment && $appointment->status !== 'completed') {
                    $validator->errors()->add('appointment_id', 'يجب أن يكون الموعد مكتملاً لتتمكن من تقييمه');
                }
            }

            // Check for duplicate review
            if ($this->appointment_id && auth()->user()->patient) {
                $existingReview = \App\Models\PatientReview::where('patient_id', auth()->user()->patient->id)
                    ->where('appointment_id', $this->appointment_id)
                    ->where('id', '!=', $this->route('review')->id ?? null)
                    ->first();
                
                if ($existingReview) {
                    $validator->errors()->add('appointment_id', 'لقد قمت بتقييم هذا الموعد مسبقاً');
                }
            }

            // Validate review text for low ratings
            if ($this->rating <= 2 && empty(trim($this->review_text))) {
                $validator->errors()->add('review_text', 'يرجى كتابة تعليق عند إعطاء تقييم منخفض لمساعدتنا على التحسين');
            }
        });
    }
}