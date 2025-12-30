<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of the prescriptions.
     */
    public function index(Request $request)
    {
        $query = Prescription::with(['patient', 'doctor', 'medicalRecord'])
                             ->orderBy('created_at', 'desc');

        // Filter by medical record if specified
        if ($request->has('medical_record_id') && $request->medical_record_id) {
            $query->where('medical_record_id', $request->medical_record_id);
        }

        // Filter by patient if specified
        if ($request->has('patient_id') && $request->patient_id) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by doctor if specified
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by controlled substances
        if ($request->has('controlled_only')) {
            $query->controlledSubstances();
        }

        // Filter by active prescriptions
        if ($request->has('active_only')) {
            $query->active();
        }

        // Filter by expired prescriptions
        if ($request->has('expired_only')) {
            $query->expired();
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $prescriptions = $query->paginate(20);
        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();

        return view('prescriptions.index', compact('prescriptions', 'patients', 'doctors'));
    }

    /**
     * Show the form for creating a new prescription.
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();

        $selectedPatient = null;
        $selectedMedicalRecord = null;

        if ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        if ($request->has('medical_record_id')) {
            $selectedMedicalRecord = MedicalRecord::find($request->medical_record_id);
            $selectedPatient = $selectedMedicalRecord->patient;
        }

        return view('prescriptions.create', compact('patients', 'doctors', 'selectedPatient', 'selectedMedicalRecord'));
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'medication_name' => 'required|string|max:255',
            'medication_name_ar' => 'nullable|string|max:255',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'frequency_ar' => 'nullable|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'instructions' => 'required|string',
            'instructions_ar' => 'nullable|string',
            'warnings' => 'nullable|string',
            'warnings_ar' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_controlled_substance' => 'boolean',
            'pharmacy_notes' => 'nullable|string|max:255',
            'pharmacy_notes_ar' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $prescription = Prescription::create($request->all());

        return redirect()->route('prescriptions.show', $prescription)
                        ->with('success', __('app.prescription_created_successfully'));
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription)
    {
        $prescription->load(['patient', 'doctor', 'medicalRecord']);

        return view('prescriptions.show', compact('prescription'));
    }

    /**
     * Show the form for editing the specified prescription.
     */
    public function edit(Prescription $prescription)
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();

        return view('prescriptions.edit', compact('prescription', 'patients', 'doctors'));
    }

    /**
     * Update the specified prescription in storage.
     */
    public function update(Request $request, Prescription $prescription)
    {
        $validator = Validator::make($request->all(), [
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'medication_name' => 'required|string|max:255',
            'medication_name_ar' => 'nullable|string|max:255',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'frequency_ar' => 'nullable|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'instructions' => 'required|string',
            'instructions_ar' => 'nullable|string',
            'warnings' => 'nullable|string',
            'warnings_ar' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,completed,cancelled,expired',
            'is_controlled_substance' => 'boolean',
            'pharmacy_notes' => 'nullable|string|max:255',
            'pharmacy_notes_ar' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $prescription->update($request->all());

        return redirect()->route('prescriptions.show', $prescription)
                        ->with('success', __('app.prescription_updated_successfully'));
    }

    /**
     * Remove the specified prescription from storage.
     */
    public function destroy(Prescription $prescription)
    {
        $prescription->delete();

        return redirect()->route('prescriptions.index')
                        ->with('success', __('app.prescription_deleted_successfully'));
    }

    /**
     * Get patient prescriptions
     */
    public function patientPrescriptions(Patient $patient)
    {
        $prescriptions = Prescription::where('patient_id', $patient->id)
                                   ->with(['doctor', 'medicalRecord'])
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(10);

        return view('prescriptions.patient-prescriptions', compact('patient', 'prescriptions'));
    }

    /**
     * Get doctor's prescriptions
     */
    public function doctorPrescriptions(User $doctor)
    {
        $prescriptions = Prescription::where('doctor_id', $doctor->id)
                                   ->with(['patient', 'medicalRecord'])
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(10);

        return view('prescriptions.doctor-prescriptions', compact('doctor', 'prescriptions'));
    }

    /**
     * Mark prescription as completed
     */
    public function markCompleted(Prescription $prescription)
    {
        $prescription->update(['status' => 'completed']);

        return redirect()->back()
                        ->with('success', __('app.prescription_marked_completed'));
    }

    /**
     * Cancel prescription
     */
    public function cancel(Prescription $prescription)
    {
        $prescription->update(['status' => 'cancelled']);

        return redirect()->back()
                        ->with('success', __('app.prescription_cancelled'));
    }

    /**
     * Print prescription
     */
    public function print(Prescription $prescription)
    {
        $prescription->load(['patient', 'doctor', 'medicalRecord']);

        return view('prescriptions.print', compact('prescription'));
    }
}