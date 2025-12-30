<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientInsurance;
use App\Models\InsuranceCompany;
use App\Models\InsurancePolicy;
use App\Http\Requests\PatientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    /**
     * Display a listing of patients
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by blood type
        if ($request->filled('blood_type')) {
            $query->where('blood_type', $request->blood_type);
        }

        // Filter by patient type
        if ($request->filled('patient_type')) {
            $query->where('patient_type', $request->patient_type);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $patients = $query->with(['familyHead', 'familyMembers'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient
     */
    public function create()
    {
        $familyHeads = Patient::whereNull('family_head_id')
                             ->where('is_active', true)
                             ->get(['id', 'name', 'patient_number']);

        return view('patients.create', compact('familyHeads'));
    }

    /**
     * Store a newly created patient
     */
    public function store(PatientRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $data['profile_photo'] = $request->file('profile_photo')
                    ->store('patients/photos', 'public');
            }

            // Generate family code if this is a family head
            if (empty($data['family_head_id']) && empty($data['family_code'])) {
                $data['family_code'] = $this->generateFamilyCode();
            }

            $patient = Patient::create($data);

            Log::info('Patient created successfully', [
                'patient_id' => $patient->id,
                'patient_number' => $patient->patient_number,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('patients.show', $patient)
                           ->with('success', 'تم إنشاء ملف المريض بنجاح');

        } catch (\Exception $e) {
            Log::error('Error creating patient', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء ملف المريض');
        }
    }

    /**
     * Display the specified patient with advanced profile
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'familyHead', 
            'familyMembers',
            'patientInsurances.insuranceCompany',
            'patientInsurances.insurancePolicy',
            'medicalRecords' => function($q) {
                $q->latest()->limit(5);
            },
            'prescriptions' => function($q) {
                $q->latest()->limit(5);
            },
            'appointments' => function($q) {
                $q->latest()->limit(10);
            }
        ]);

        // Get active insurance companies for potential insurance assignment
        $activeInsuranceCompanies = InsuranceCompany::active()
                                                   ->with(['policies' => function($q) {
                                                       $q->active();
                                                   }])
                                                   ->get();

        return view('patients.show', compact('patient', 'activeInsuranceCompanies'));
    }

    /**
     * Show the form for editing the specified patient
     */
    public function edit(Patient $patient)
    {
        $familyHeads = Patient::whereNull('family_head_id')
                             ->where('is_active', true)
                             ->where('id', '!=', $patient->id)
                             ->get(['id', 'name', 'patient_number']);

        return view('patients.edit', compact('patient', 'familyHeads'));
    }

    /**
     * Update the specified patient
     */
    public function update(PatientRequest $request, Patient $patient)
    {
        try {
            $data = $request->validated();

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo
                if ($patient->profile_photo) {
                    Storage::disk('public')->delete($patient->profile_photo);
                }
                
                $data['profile_photo'] = $request->file('profile_photo')
                    ->store('patients/photos', 'public');
            }

            $patient->update($data);

            Log::info('Patient updated successfully', [
                'patient_id' => $patient->id,
                'patient_number' => $patient->patient_number,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('patients.show', $patient)
                           ->with('success', 'تم تحديث بيانات المريض بنجاح');

        } catch (\Exception $e) {
            Log::error('Error updating patient', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث بيانات المريض');
        }
    }

    /**
     * Remove the specified patient from storage
     */
    public function destroy(Patient $patient)
    {
        try {
            // Check if patient has family members
            if ($patient->familyMembers()->count() > 0) {
                return back()->with('error', 'لا يمكن حذف المريض لأنه رب أسرة ولديه أفراد عائلة مرتبطين به');
            }

            // Delete profile photo
            if ($patient->profile_photo) {
                Storage::disk('public')->delete($patient->profile_photo);
            }

            $patientNumber = $patient->patient_number;
            $patient->delete();

            Log::info('Patient deleted successfully', [
                'patient_number' => $patientNumber,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->route('patients.index')
                           ->with('success', 'تم حذف ملف المريض بنجاح');

        } catch (\Exception $e) {
            Log::error('Error deleting patient', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'حدث خطأ أثناء حذف ملف المريض');
        }
    }

    /**
     * Search patients by barcode
     */
    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $patient = Patient::where('barcode', $request->barcode)
                         ->where('is_active', true)
                         ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على مريض بهذا الباركود'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'patient' => [
                'id' => $patient->id,
                'patient_number' => $patient->patient_number,
                'name' => $patient->name,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'phone' => $patient->phone,
                'blood_type' => $patient->blood_type
            ]
        ]);
    }

    /**
     * Toggle patient active status
     */
    public function toggleStatus(Patient $patient)
    {
        try {
            $patient->update(['is_active' => !$patient->is_active]);

            $status = $patient->is_active ? 'مفعل' : 'غير مفعل';
            
            Log::info('Patient status toggled', [
                'patient_id' => $patient->id,
                'new_status' => $patient->is_active,
                'updated_by' => auth()->id()
            ]);

            return back()->with('success', "تم تغيير حالة المريض إلى {$status}");

        } catch (\Exception $e) {
            Log::error('Error toggling patient status', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'حدث خطأ أثناء تغيير حالة المريض');
        }
    }

    /**
     * Get family members for a patient
     */
    public function getFamilyMembers(Patient $patient)
    {
        $familyMembers = $patient->familyMembers()
                               ->where('is_active', true)
                               ->get(['id', 'name', 'patient_number', 'family_relation', 'age']);

        return response()->json([
            'success' => true,
            'family_members' => $familyMembers
        ]);
    }

    /**
     * Generate unique family code
     */
    private function generateFamilyCode()
    {
        do {
            $code = 'FAM' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Patient::where('family_code', $code)->exists());
        
        return $code;
    }

    /**
     * Display families management page
     */
    public function families(Request $request)
    {
        $query = Patient::whereNotNull('family_code')
                        ->whereNull('family_head_id'); // Only family heads

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('family_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $families = $query->with(['familyMembers' => function($q) {
                            $q->where('is_active', true);
                        }])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('patients.families', compact('families'));
    }

    /**
     * Show specific family details
     */
    public function showFamily($familyCode)
    {
        $familyHead = Patient::where('family_code', $familyCode)
                            ->whereNull('family_head_id')
                            ->first();

        if (!$familyHead) {
            abort(404, 'العائلة غير موجودة');
        }

        $familyMembers = Patient::where('family_code', $familyCode)
                               ->where('is_active', true)
                               ->orderBy('date_of_birth', 'desc')
                               ->get();

        return view('patients.show-family', compact('familyHead', 'familyMembers', 'familyCode'));
    }

    /**
     * Assign insurance to patient
     */
    public function assignInsurance(Request $request, Patient $patient)
    {
        $request->validate([
            'insurance_company_id' => 'required|exists:insurance_companies,id',
            'insurance_policy_id' => 'required|exists:insurance_policies,id',
            'member_id' => 'required|string|max:50',
            'card_number' => 'nullable|string|max:50',
            'policy_holder_name' => 'required|string|max:100',
            'policy_holder_relation' => 'required|in:self,spouse,child,parent,sibling,other',
            'coverage_start_date' => 'required|date',
            'coverage_end_date' => 'nullable|date|after:coverage_start_date',
            'is_primary' => 'boolean'
        ]);

        try {
            // If this is set as primary, make other insurances secondary
            if ($request->boolean('is_primary')) {
                $patient->patientInsurances()->update(['is_primary' => false]);
            }

            $patientInsurance = $patient->patientInsurances()->create([
                'insurance_company_id' => $request->insurance_company_id,
                'insurance_policy_id' => $request->insurance_policy_id,
                'member_id' => $request->member_id,
                'card_number' => $request->card_number,
                'policy_holder_name' => $request->policy_holder_name,
                'policy_holder_relation' => $request->policy_holder_relation,
                'coverage_start_date' => $request->coverage_start_date,
                'coverage_end_date' => $request->coverage_end_date,
                'is_primary' => $request->boolean('is_primary'),
                'status' => 'active'
            ]);

            // Initialize annual limits
            $policy = InsurancePolicy::find($request->insurance_policy_id);
            if ($policy && $policy->max_coverage_per_year) {
                $patientInsurance->update([
                    'annual_limit_remaining' => $policy->max_coverage_per_year
                ]);
            }

            Log::info('Insurance assigned to patient', [
                'patient_id' => $patient->id,
                'insurance_company_id' => $request->insurance_company_id,
                'assigned_by' => auth()->id()
            ]);

            return back()->with('success', 'تم ربط التأمين بالمريض بنجاح');

        } catch (\Exception $e) {
            Log::error('Error assigning insurance to patient', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->with('error', 'حدث خطأ أثناء ربط التأمين بالمريض');
        }
    }

    /**
     * Remove insurance from patient
     */
    public function removeInsurance(Patient $patient, PatientInsurance $patientInsurance)
    {
        try {
            $patientInsurance->cancel('Removed by user request');

            Log::info('Insurance removed from patient', [
                'patient_id' => $patient->id,
                'patient_insurance_id' => $patientInsurance->id,
                'removed_by' => auth()->id()
            ]);

            return back()->with('success', 'تم إلغاء ربط التأمين بالمريض بنجاح');

        } catch (\Exception $e) {
            Log::error('Error removing insurance from patient', [
                'patient_id' => $patient->id,
                'patient_insurance_id' => $patientInsurance->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'حدث خطأ أثناء إلغاء ربط التأمين');
        }
    }

    /**
     * Get insurance policies for a company (AJAX)
     */
    public function getInsurancePolicies(Request $request)
    {
        $companyId = $request->get('company_id');
        
        $policies = InsurancePolicy::where('insurance_company_id', $companyId)
                                  ->active()
                                  ->get(['id', 'policy_name_ar', 'policy_number', 'coverage_percentage']);

        return response()->json([
            'success' => true,
            'policies' => $policies
        ]);
    }

    /**
     * Calculate insurance coverage for amount (AJAX)
     */
    public function calculateCoverage(Request $request, Patient $patient)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'service_type' => 'nullable|string'
        ]);

        try {
            $coverage = $patient->calculateInsuranceCoverage(
                $request->amount,
                $request->service_type
            );

            return response()->json([
                'success' => true,
                'coverage' => $coverage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب التغطية التأمينية'
            ], 500);
        }
    }
}