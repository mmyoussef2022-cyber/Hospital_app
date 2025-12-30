<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientReview;
use App\Models\Appointment;
use App\Http\Requests\PatientReviewRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:reviews.view')->only(['index', 'show']);
        $this->middleware('permission:reviews.create')->only(['create', 'store']);
        $this->middleware('permission:reviews.edit')->only(['edit', 'update']);
        $this->middleware('permission:reviews.delete')->only(['destroy']);
        $this->middleware('permission:reviews.moderate')->only(['approve', 'reject', 'hide', 'feature']);
    }

    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        $query = PatientReview::with(['patient.user', 'doctor.user', 'appointment']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('review_text', 'like', "%{$search}%")
                  ->orWhereHas('patient.user', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('doctor.user', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured === 'yes');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);
        $doctors = Doctor::with('user')->active()->get();

        return view('reviews.index', compact('reviews', 'doctors'));
    }

    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request)
    {
        $appointmentId = $request->get('appointment_id');
        $doctorId = $request->get('doctor_id');
        
        $appointment = null;
        $doctor = null;

        if ($appointmentId) {
            $appointment = Appointment::with(['doctor.user', 'patient.user'])
                ->where('id', $appointmentId)
                ->where('patient_id', auth()->user()->patient->id ?? null)
                ->where('status', 'completed')
                ->first();
            
            if (!$appointment) {
                return redirect()->back()->with('error', 'الموعد غير صالح أو لم يكتمل بعد');
            }
            
            // Check if review already exists
            $existingReview = PatientReview::where('patient_id', $appointment->patient_id)
                ->where('appointment_id', $appointmentId)
                ->first();
            
            if ($existingReview) {
                return redirect()->route('reviews.show', $existingReview)
                    ->with('info', 'لقد قمت بتقييم هذا الموعد مسبقاً');
            }
            
            $doctor = $appointment->doctor;
        } elseif ($doctorId) {
            $doctor = Doctor::with('user')->findOrFail($doctorId);
            
            // Check if patient has completed appointments with this doctor
            $hasCompletedAppointments = Appointment::where('doctor_id', $doctor->user_id)
                ->where('patient_id', auth()->user()->patient->id ?? null)
                ->where('status', 'completed')
                ->exists();
            
            if (!$hasCompletedAppointments) {
                return redirect()->back()->with('error', 'يجب أن يكون لديك موعد مكتمل مع هذا الطبيب لتتمكن من تقييمه');
            }
        } else {
            return redirect()->back()->with('error', 'يجب تحديد الطبيب أو الموعد');
        }

        return view('reviews.create', compact('appointment', 'doctor'));
    }

    /**
     * Store a newly created review.
     */
    public function store(PatientReviewRequest $request)
    {
        $validated = $request->validated();
        
        // Get patient
        $patient = auth()->user()->patient;
        if (!$patient) {
            return redirect()->back()->with('error', 'يجب أن تكون مريضاً لتتمكن من إضافة تقييم');
        }
        
        $validated['patient_id'] = $patient->id;

        // Process rating aspects
        if ($request->filled('rating_aspects')) {
            $aspects = [];
            foreach ($request->rating_aspects as $aspect => $rating) {
                if ($rating > 0) {
                    $aspects[$aspect] = (int) $rating;
                }
            }
            $validated['rating_aspects'] = $aspects;
        }

        $review = PatientReview::create($validated);

        return redirect()->route('reviews.show', $review)
                        ->with('success', 'تم إضافة تقييمك بنجاح وسيتم مراجعته قبل النشر');
    }

    /**
     * Display the specified review.
     */
    public function show(PatientReview $review)
    {
        $review->load(['patient.user', 'doctor.user', 'appointment', 'approvedBy']);
        
        return view('reviews.show', compact('review'));
    }

    /**
     * Show the form for editing the review.
     */
    public function edit(PatientReview $review)
    {
        // Check if user can edit this review
        if (!$review->canBeEditedBy(auth()->user())) {
            return redirect()->back()->with('error', 'لا يمكنك تعديل هذا التقييم');
        }

        $review->load(['doctor.user', 'appointment']);
        
        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified review.
     */
    public function update(PatientReviewRequest $request, PatientReview $review)
    {
        // Check if user can edit this review
        if (!$review->canBeEditedBy(auth()->user())) {
            return redirect()->back()->with('error', 'لا يمكنك تعديل هذا التقييم');
        }

        $validated = $request->validated();

        // Process rating aspects
        if ($request->filled('rating_aspects')) {
            $aspects = [];
            foreach ($request->rating_aspects as $aspect => $rating) {
                if ($rating > 0) {
                    $aspects[$aspect] = (int) $rating;
                }
            }
            $validated['rating_aspects'] = $aspects;
        }

        // Reset approval status if content changed
        if ($review->review_text !== $validated['review_text'] || $review->rating !== $validated['rating']) {
            $validated['status'] = 'pending';
            $validated['is_approved'] = false;
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;
        }

        $review->update($validated);

        return redirect()->route('reviews.show', $review)
                        ->with('success', 'تم تحديث تقييمك بنجاح');
    }

    /**
     * Remove the specified review.
     */
    public function destroy(PatientReview $review)
    {
        // Check if user can delete this review
        if (!$review->canBeDeletedBy(auth()->user())) {
            return redirect()->back()->with('error', 'لا يمكنك حذف هذا التقييم');
        }

        $review->delete();

        return redirect()->route('reviews.index')
                        ->with('success', 'تم حذف التقييم بنجاح');
    }

    /**
     * Approve a review.
     */
    public function approve(PatientReview $review)
    {
        $review->approve(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'تم اعتماد التقييم بنجاح',
            'status_badge' => $review->status_badge
        ]);
    }

    /**
     * Reject a review.
     */
    public function reject(Request $request, PatientReview $review)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $review->reject($request->admin_notes, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'تم رفض التقييم',
            'status_badge' => $review->status_badge
        ]);
    }

    /**
     * Hide a review.
     */
    public function hide(Request $request, PatientReview $review)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $review->hide($request->admin_notes, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'تم إخفاء التقييم',
            'status_badge' => $review->status_badge
        ]);
    }

    /**
     * Feature/unfeature a review.
     */
    public function toggleFeature(PatientReview $review)
    {
        if ($review->is_featured) {
            $review->unfeature();
            $message = 'تم إلغاء تمييز التقييم';
        } else {
            $review->feature();
            $message = 'تم تمييز التقييم';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_featured' => $review->is_featured
        ]);
    }

    /**
     * Get reviews for a specific doctor.
     */
    public function doctorReviews(Doctor $doctor, Request $request)
    {
        $query = $doctor->approvedReviews()->with(['patient.user']);

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);
        $ratingDistribution = $doctor->getRatingDistribution();
        $aspectRatings = $doctor->getAspectRatings();

        return view('reviews.doctor-reviews', compact('doctor', 'reviews', 'ratingDistribution', 'aspectRatings'));
    }

    /**
     * Get review statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => PatientReview::count(),
            'approved' => PatientReview::approved()->count(),
            'pending' => PatientReview::pending()->count(),
            'featured' => PatientReview::featured()->count(),
            'average_rating' => PatientReview::approved()->avg('rating'),
            'by_rating' => [],
            'recent_count' => PatientReview::recent(7)->count(),
            'monthly_count' => PatientReview::recent(30)->count()
        ];

        // Rating distribution
        for ($i = 1; $i <= 5; $i++) {
            $stats['by_rating'][$i] = PatientReview::approved()->byRating($i)->count();
        }

        return response()->json($stats);
    }

    /**
     * Bulk operations on reviews.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,hide,delete,feature,unfeature',
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:patient_reviews,id',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $reviews = PatientReview::whereIn('id', $request->review_ids);
        $count = $reviews->count();

        switch ($request->action) {
            case 'approve':
                foreach ($reviews->get() as $review) {
                    $review->approve(Auth::id());
                }
                $message = "تم اعتماد {$count} تقييم بنجاح";
                break;
            case 'reject':
                foreach ($reviews->get() as $review) {
                    $review->reject($request->admin_notes, Auth::id());
                }
                $message = "تم رفض {$count} تقييم";
                break;
            case 'hide':
                foreach ($reviews->get() as $review) {
                    $review->hide($request->admin_notes, Auth::id());
                }
                $message = "تم إخفاء {$count} تقييم";
                break;
            case 'delete':
                $reviews->delete();
                $message = "تم حذف {$count} تقييم بنجاح";
                break;
            case 'feature':
                $reviews->update(['is_featured' => true]);
                $message = "تم تمييز {$count} تقييم";
                break;
            case 'unfeature':
                $reviews->update(['is_featured' => false]);
                $message = "تم إلغاء تمييز {$count} تقييم";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'affected_count' => $count
        ]);
    }
}