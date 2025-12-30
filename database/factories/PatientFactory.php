<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['male', 'female']);
        $arabicNames = [
            'male' => ['أحمد محمد علي', 'محمد عبدالله أحمد', 'علي حسن محمد', 'عبدالرحمن سالم', 'خالد عبدالعزيز', 'سعد محمد علي', 'فهد عبدالله', 'عبدالله أحمد محمد'],
            'female' => ['فاطمة أحمد علي', 'عائشة محمد عبدالله', 'خديجة حسن محمد', 'مريم سالم أحمد', 'نورا عبدالعزيز', 'سارة محمد علي', 'هند عبدالله', 'زينب أحمد محمد']
        ];

        return [
            'name' => $this->faker->randomElement($arabicNames[$gender]),
            'name_en' => $this->faker->name($gender),
            'national_id' => $this->faker->unique()->numerify('##########'),
            'gender' => $gender,
            'date_of_birth' => $this->faker->dateTimeBetween('-80 years', '-1 year'),
            'phone' => $this->faker->optional()->phoneNumber(),
            'mobile' => $this->faker->phoneNumber(),
            'email' => $this->faker->boolean(70) ? $this->faker->unique()->safeEmail() : null,
            'address' => $this->faker->address(),
            'city' => $this->faker->randomElement(['الرياض', 'جدة', 'الدمام', 'مكة المكرمة', 'المدينة المنورة', 'الطائف', 'تبوك', 'بريدة']),
            'country' => 'السعودية',
            'nationality' => $this->faker->randomElement(['سعودي', 'مصري', 'سوري', 'أردني', 'لبناني', 'فلسطيني']),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced', 'widowed']),
            'occupation' => $this->faker->optional()->randomElement(['مهندس', 'طبيب', 'معلم', 'محاسب', 'موظف', 'طالب', 'متقاعد']),
            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'emergency_contact' => [
                'name' => $this->faker->name(),
                'relationship' => $this->faker->randomElement(['الأب', 'الأم', 'الزوج', 'الزوجة', 'الأخ', 'الأخت', 'الابن', 'الابنة']),
                'phone' => $this->faker->phoneNumber(),
            ],
            'insurance_info' => $this->faker->optional()->passthrough([
                'company' => $this->faker->randomElement(['بوبا العربية', 'التعاونية', 'ساب تكافل', 'الأهلي تكافل']),
                'policy_number' => $this->faker->numerify('POL-########'),
                'coverage_percentage' => $this->faker->randomElement([80, 90, 100]),
            ]),
            'allergies' => $this->faker->optional()->passthrough([
                $this->faker->randomElement(['البنسلين', 'الأسبرين', 'المكسرات', 'البيض', 'اللبن'])
            ]),
            'chronic_conditions' => $this->faker->optional()->passthrough([
                $this->faker->randomElement(['السكري', 'ضغط الدم', 'الربو', 'أمراض القلب'])
            ]),
            'medical_notes' => $this->faker->optional()->sentence(),
            'patient_type' => $this->faker->randomElement(['outpatient', 'inpatient', 'emergency']),
            'is_active' => true,
            'first_visit_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now'),
            'last_visit_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'outstanding_balance' => $this->faker->randomFloat(2, 0, 5000),
            'preferences' => [
                'language' => $this->faker->randomElement(['ar', 'en']),
                'communication_method' => $this->faker->randomElement(['phone', 'sms', 'email', 'whatsapp']),
                'appointment_reminders' => $this->faker->boolean(),
            ],
        ];
    }

    /**
     * Indicate that the patient is a family head.
     */
    public function familyHead(): static
    {
        return $this->state(fn (array $attributes) => [
            'family_head_id' => null,
            'family_code' => 'FAM' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'family_relation' => null,
        ]);
    }

    /**
     * Indicate that the patient is a family member.
     */
    public function familyMember($familyHeadId, $relation): static
    {
        return $this->state(fn (array $attributes) => [
            'family_head_id' => $familyHeadId,
            'family_relation' => $relation,
            'family_code' => null,
        ]);
    }

    /**
     * Indicate that the patient is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}