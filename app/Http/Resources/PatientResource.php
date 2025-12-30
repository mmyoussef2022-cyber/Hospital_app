<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_number' => $this->patient_number,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'gender' => $this->gender,
            'age' => $this->age,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'nationality' => $this->nationality,
            'marital_status' => $this->marital_status,
            'occupation' => $this->occupation,
            'blood_type' => $this->blood_type,
            'emergency_contact' => $this->emergency_contact,
            'insurance_info' => $this->insurance_info,
            'allergies' => $this->allergies,
            'chronic_conditions' => $this->chronic_conditions,
            'medical_notes' => $this->medical_notes,
            'family_code' => $this->family_code,
            'family_relation' => $this->family_relation,
            'barcode' => $this->barcode,
            'profile_photo' => $this->profile_photo ? asset('storage/' . $this->profile_photo) : null,
            'is_active' => $this->is_active,
            'patient_type' => $this->patient_type,
            'first_visit_date' => $this->first_visit_date?->format('Y-m-d H:i:s'),
            'last_visit_date' => $this->last_visit_date?->format('Y-m-d H:i:s'),
            'outstanding_balance' => $this->outstanding_balance,
            'preferences' => $this->preferences,
            'family_head' => $this->whenLoaded('familyHead', function () {
                return [
                    'id' => $this->familyHead->id,
                    'name' => $this->familyHead->name,
                    'patient_number' => $this->familyHead->patient_number,
                ];
            }),
            'family_members' => $this->whenLoaded('familyMembers', function () {
                return $this->familyMembers->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'patient_number' => $member->patient_number,
                        'family_relation' => $member->family_relation,
                        'age' => $member->age,
                        'gender' => $member->gender,
                    ];
                });
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}