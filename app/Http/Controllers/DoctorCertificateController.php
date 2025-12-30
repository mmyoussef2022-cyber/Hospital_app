<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorCertificate;
use App\Http\Requests\DoctorCertificateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DoctorCertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:doctors.view')->only(['index', 'show']);
        $this->middleware('permission:doctors.create')->only(['create', 'store']);
        $this->middleware('permission:doctors.edit')->only(['edit', 'update']);
        $this->middleware('permission:doctors.delete')->only(['destroy']);
    }

    /**
     * Display a listing of certificates.
     */
    public function index(Request $request)
    {
        $query = DoctorCertificate::with(['doctor.user', 'verifiedBy']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('institution', 'like', "%{$search}%")
                  ->orWhere('certificate_number', 'like', "%{$search}%")
                  ->orWhereHas('doctor.user', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('verification_status')) {
            if ($request->verification_status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->verification_status === 'pending') {
                $query->where('is_verified', false);
            }
        }

        if ($request->filled('expiry_status')) {
            if ($request->expiry_status === 'expiring_soon') {
                $query->expiringSoon(30);
            } elseif ($request->expiry_status === 'expired') {
                $query->expired();
            }
        }

        $certificates = $query->orderBy('created_at', 'desc')->paginate(15);
        $types = DoctorCertificate::getTypes();

        return view('doctors.certificates.index', compact('certificates', 'types'));
    }

    /**
     * Show the form for creating a new certificate.
     */
    public function create(Doctor $doctor)
    {
        $types = DoctorCertificate::getTypes();
        
        return view('doctors.certificates.create', compact('doctor', 'types'));
    }

    /**
     * Store a newly created certificate.
     */
    public function store(DoctorCertificateRequest $request, Doctor $doctor)
    {
        $validated = $request->validated();
        $validated['doctor_id'] = $doctor->id;

        // Handle file upload
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('certificates', $filename, 'public');
            
            $validated['file_path'] = $path;
            $validated['file_type'] = $file->getClientMimeType();
            $validated['file_size'] = $file->getSize();
        }

        $certificate = DoctorCertificate::create($validated);

        return redirect()->route('doctors.show', $doctor)
                        ->with('success', 'تم إضافة الشهادة بنجاح');
    }

    /**
     * Display the specified certificate.
     */
    public function show(Doctor $doctor, DoctorCertificate $certificate)
    {
        $certificate->load(['doctor.user', 'verifiedBy']);
        
        return view('doctors.certificates.show', compact('doctor', 'certificate'));
    }

    /**
     * Show the form for editing the certificate.
     */
    public function edit(Doctor $doctor, DoctorCertificate $certificate)
    {
        $types = DoctorCertificate::getTypes();
        
        return view('doctors.certificates.edit', compact('doctor', 'certificate', 'types'));
    }

    /**
     * Update the specified certificate.
     */
    public function update(DoctorCertificateRequest $request, Doctor $doctor, DoctorCertificate $certificate)
    {
        $validated = $request->validated();

        // Handle file upload
        if ($request->hasFile('certificate_file')) {
            // Delete old file
            if ($certificate->file_path) {
                Storage::disk('public')->delete($certificate->file_path);
            }
            
            $file = $request->file('certificate_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('certificates', $filename, 'public');
            
            $validated['file_path'] = $path;
            $validated['file_type'] = $file->getClientMimeType();
            $validated['file_size'] = $file->getSize();
        }

        $certificate->update($validated);

        return redirect()->route('doctors.certificates.show', [$doctor, $certificate])
                        ->with('success', 'تم تحديث الشهادة بنجاح');
    }

    /**
     * Remove the specified certificate.
     */
    public function destroy(Doctor $doctor, DoctorCertificate $certificate)
    {
        // Delete file if exists
        if ($certificate->file_path) {
            Storage::disk('public')->delete($certificate->file_path);
        }

        $certificate->delete();

        return redirect()->route('doctors.show', $doctor)
                        ->with('success', 'تم حذف الشهادة بنجاح');
    }

    /**
     * Verify a certificate.
     */
    public function verify(DoctorCertificate $certificate)
    {
        $certificate->verify(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق من الشهادة بنجاح',
            'is_verified' => true,
            'verified_at' => $certificate->verified_at->format('Y-m-d H:i:s'),
            'verified_by' => $certificate->verifiedBy->name
        ]);
    }

    /**
     * Unverify a certificate.
     */
    public function unverify(DoctorCertificate $certificate)
    {
        $certificate->unverify();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التحقق من الشهادة',
            'is_verified' => false
        ]);
    }

    /**
     * Download certificate file.
     */
    public function download(DoctorCertificate $certificate)
    {
        if (!$certificate->file_path || !Storage::disk('public')->exists($certificate->file_path)) {
            abort(404, 'الملف غير موجود');
        }

        $filePath = Storage::disk('public')->path($certificate->file_path);
        $fileName = $certificate->title . '_' . $certificate->doctor->user->name . '.' . pathinfo($certificate->file_path, PATHINFO_EXTENSION);

        return response()->download($filePath, $fileName);
    }

    /**
     * Get certificates expiring soon.
     */
    public function expiringSoon()
    {
        $certificates = DoctorCertificate::with(['doctor.user'])
            ->expiringSoon(30)
            ->orderBy('expiry_date')
            ->get();

        return response()->json([
            'certificates' => $certificates,
            'count' => $certificates->count()
        ]);
    }

    /**
     * Bulk verify certificates.
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'certificate_ids' => 'required|array',
            'certificate_ids.*' => 'exists:doctor_certificates,id'
        ]);

        $certificates = DoctorCertificate::whereIn('id', $request->certificate_ids)->get();
        
        foreach ($certificates as $certificate) {
            $certificate->verify(Auth::id());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق من ' . $certificates->count() . ' شهادة بنجاح',
            'verified_count' => $certificates->count()
        ]);
    }

    /**
     * Get certificate statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => DoctorCertificate::count(),
            'verified' => DoctorCertificate::where('is_verified', true)->count(),
            'pending' => DoctorCertificate::where('is_verified', false)->count(),
            'expiring_soon' => DoctorCertificate::expiringSoon(30)->count(),
            'expired' => DoctorCertificate::expired()->count(),
            'by_type' => DoctorCertificate::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray()
        ];

        return response()->json($stats);
    }
}