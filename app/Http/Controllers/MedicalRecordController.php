<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Models\Prescription;
use App\Models\MedicalRecordAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the medical records.
     */
    public function index(Request $request)
    {
        $query = MedicalRecord::with(['patient', 'doctor', 'prescriptions', 'attachments'])
                              ->orderBy('visit_date', 'desc');

        // Filter by patient if specified
        if ($request->has('patient_id') && $request->patient_id) {
            $query->forPatient($request->patient_id);
        }

        // Filter by doctor if specified
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->byDoctor($request->doctor_id);
        }

        // Filter by visit type
        if ($request->has('visit_type') && $request->visit_type) {
            $query->visitType($request->visit_type);
        }

        // Filter by emergency status
        if ($request->has('is_emergency')) {
            $query->where('is_emergency', $request->boolean('is_emergency'));
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->where('visit_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('visit_date', '<=', $request->date_to);
        }

        $records = $query->paginate(20);
        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();

        return view('medical-records.index', compact('records', 'patients', 'doctors'));
    }

    /**
     * Show the form for creating a new medical record.
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();

        $selectedPatient = null;
        if ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        return view('medical-records.create', compact('patients', 'doctors', 'selectedPatient'));
    }

    /**
     * Store a newly created medical record in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'required|string|max:1000',
            'chief_complaint_ar' => 'nullable|string|max:1000',
            'diagnosis' => 'required|array',
            'diagnosis_ar' => 'nullable|array',
            'treatment' => 'required|string',
            'treatment_ar' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:visit_date',
            'is_emergency' => 'boolean',
            'visit_type' => 'required|in:consultation,follow_up,emergency,routine_checkup,procedure',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $record = MedicalRecord::create($request->only([
            'patient_id', 'doctor_id', 'visit_date', 'chief_complaint', 'chief_complaint_ar',
            'diagnosis', 'diagnosis_ar', 'treatment', 'treatment_ar', 'vital_signs',
            'notes', 'notes_ar', 'follow_up_date', 'is_emergency', 'visit_type'
        ]));

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($record, $file, $request);
            }
        }

        return redirect()->route('medical-records.show', $record)
                        ->with('success', __('app.medical_record_created_successfully'));
    }

    /**
     * Display the specified medical record.
     */
    public function show(MedicalRecord $medicalRecord)
    {
        // Check access permissions
        if (!$medicalRecord->hasAccess()) {
            abort(403, __('app.access_denied'));
        }

        // Log access
        $medicalRecord->logAccess('viewed');

        $medicalRecord->load([
            'patient', 
            'doctor', 
            'prescriptions.doctor', 
            'attachments.uploader',
            'audits.user'
        ]);

        return view('medical-records.show', compact('medicalRecord'));
    }

    /**
     * Show the form for editing the specified medical record.
     */
    public function edit(MedicalRecord $medicalRecord)
    {
        // Check access permissions for editing
        if (!$medicalRecord->hasAccess(null, 'edit')) {
            abort(403, __('app.access_denied_edit'));
        }

        $patients = Patient::orderBy('first_name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();

        return view('medical-records.edit', compact('medicalRecord', 'patients', 'doctors'));
    }

    /**
     * Update the specified medical record in storage.
     */
    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        // Check access permissions for updating
        if (!$medicalRecord->hasAccess(null, 'update')) {
            abort(403, __('app.access_denied_edit'));
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'required|string|max:1000',
            'chief_complaint_ar' => 'nullable|string|max:1000',
            'diagnosis' => 'required|array',
            'diagnosis_ar' => 'nullable|array',
            'treatment' => 'required|string',
            'treatment_ar' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:visit_date',
            'is_emergency' => 'boolean',
            'visit_type' => 'required|in:consultation,follow_up,emergency,routine_checkup,procedure',
            'status' => 'required|in:active,completed,cancelled,pending',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $oldValues = $medicalRecord->toArray();
        
        $medicalRecord->update($request->only([
            'patient_id', 'doctor_id', 'visit_date', 'chief_complaint', 'chief_complaint_ar',
            'diagnosis', 'diagnosis_ar', 'treatment', 'treatment_ar', 'vital_signs',
            'notes', 'notes_ar', 'follow_up_date', 'is_emergency', 'visit_type', 'status'
        ]));

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($medicalRecord, $file, $request);
            }
        }

        return redirect()->route('medical-records.show', $medicalRecord)
                        ->with('success', __('app.medical_record_updated_successfully'));
    }

    /**
     * Remove the specified medical record from storage.
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        // Check access permissions for deletion
        if (!$medicalRecord->hasAccess(null, 'delete')) {
            abort(403, __('app.access_denied_delete'));
        }

        $medicalRecord->delete();

        return redirect()->route('medical-records.index')
                        ->with('success', __('app.medical_record_deleted_successfully'));
    }

    /**
     * Store attachment for medical record
     */
    private function storeAttachment(MedicalRecord $record, $file, Request $request)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $filePath = $file->storeAs('medical-records/' . $record->id, $fileName, 'public');

        MedicalRecordAttachment::create([
            'medical_record_id' => $record->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $originalName,
            'file_name_ar' => $request->input('file_name_ar'),
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->input('attachment_description'),
            'description_ar' => $request->input('attachment_description_ar'),
            'category' => $request->input('attachment_category', 'document'),
            'is_confidential' => $request->boolean('is_confidential', false)
        ]);
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(MedicalRecordAttachment $attachment)
    {
        // Check access permissions
        if (!$attachment->medicalRecord->hasAccess()) {
            abort(403, __('app.access_denied'));
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, __('app.file_not_found'));
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(MedicalRecordAttachment $attachment)
    {
        // Check access permissions
        if (!$attachment->medicalRecord->hasAccess()) {
            abort(403, __('app.access_denied'));
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()
                        ->with('success', __('app.attachment_deleted_successfully'));
    }

    /**
     * Get patient medical history
     */
    public function patientHistory(Patient $patient)
    {
        $records = MedicalRecord::forPatient($patient->id)
                               ->with(['doctor', 'prescriptions', 'attachments'])
                               ->orderBy('visit_date', 'desc')
                               ->paginate(10);

        return view('medical-records.patient-history', compact('patient', 'records'));
    }

    /**
     * Get doctor's medical records
     */
    public function doctorRecords(User $doctor)
    {
        $records = MedicalRecord::byDoctor($doctor->id)
                               ->with(['patient', 'prescriptions', 'attachments'])
                               ->orderBy('visit_date', 'desc')
                               ->paginate(10);

        return view('medical-records.doctor-records', compact('doctor', 'records'));
    }
}
