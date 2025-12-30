<?php

namespace App\Http\Controllers;

use App\Models\DentalSession;
use App\Models\DentalTreatment;
use App\Http\Requests\DentalSessionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DentalSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of dental sessions
     */
    public function index(Request $request)
    {
        $query = DentalSession::with(['dentalTreatment.patient', 'dentalTreatment.doctor'])
            ->orderBy('scheduled_date', 'desc');

        // Filter by treatment
        if ($request->filled('dental_treatment_id')) {
            $query->where('dental_treatment_id', $request->dental_treatment_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('dentalTreatment', function ($q) use ($search) {
                $q->whereHas('patient', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('title', 'like', "%{$search}%");
            });
        }

        $sessions = $query->paginate(15);

        // Statistics
        $stats = [
            'total' => DentalSession::count(),
            'scheduled' => DentalSession::where('status', 'scheduled')->count(),
            'completed' => DentalSession::where('status', 'completed')->count(),
            'cancelled' => DentalSession::where('status', 'cancelled')->count(),
            'today' => DentalSession::whereDate('scheduled_date', today())->count(),
            'this_week' => DentalSession::whereBetween('scheduled_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'total_revenue' => DentalSession::where('status', 'completed')->sum('session_cost')
        ];

        $treatments = DentalTreatment::with('patient')->get();

        return view('dental.sessions.index', compact('sessions', 'stats', 'treatments'));
    }

    /**
     * Show the form for creating a new dental session
     */
    public function create(Request $request)
    {
        $treatment = null;
        if ($request->filled('dental_treatment_id')) {
            $treatment = DentalTreatment::with(['patient', 'doctor'])->findOrFail($request->dental_treatment_id);
        }

        $treatments = DentalTreatment::with(['patient', 'doctor'])
            ->whereIn('status', ['planned', 'in_progress'])
            ->get();

        return view('dental.sessions.create', compact('treatment', 'treatments'));
    }

    /**
     * Store a newly created dental session
     */
    public function store(DentalSessionRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle file uploads
            if ($request->hasFile('session_images')) {
                $images = [];
                foreach ($request->file('session_images') as $file) {
                    $path = $file->store('dental/sessions/images', 'public');
                    $images[] = $path;
                }
                $data['session_images'] = $images;
            }

            if ($request->hasFile('session_notes_file')) {
                $data['session_notes_file'] = $request->file('session_notes_file')
                    ->store('dental/sessions/notes', 'public');
            }

            // Auto-generate session number
            $treatment = DentalTreatment::findOrFail($data['dental_treatment_id']);
            $data['session_number'] = $treatment->sessions()->count() + 1;

            $session = DentalSession::create($data);

            // Update treatment progress if session is completed
            if ($session->status === 'completed') {
                $this->updateTreatmentProgress($treatment);
            }

            DB::commit();

            return redirect()->route('dental.sessions.show', $session)
                ->with('success', 'تم إنشاء الجلسة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified dental session
     */
    public function show(DentalSession $session)
    {
        $session->load(['dentalTreatment.patient', 'dentalTreatment.doctor']);
        
        return view('dental.sessions.show', compact('session'));
    }

    /**
     * Show the form for editing the specified dental session
     */
    public function edit(DentalSession $session)
    {
        $session->load(['dentalTreatment.patient', 'dentalTreatment.doctor']);
        
        $treatments = DentalTreatment::with(['patient', 'doctor'])
            ->whereIn('status', ['planned', 'in_progress'])
            ->get();

        return view('dental.sessions.edit', compact('session', 'treatments'));
    }

    /**
     * Update the specified dental session
     */
    public function update(DentalSessionRequest $request, DentalSession $session)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $oldStatus = $session->status;

            // Handle file uploads
            if ($request->hasFile('session_images')) {
                // Delete old images
                if ($session->session_images) {
                    foreach ($session->session_images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }

                $images = [];
                foreach ($request->file('session_images') as $file) {
                    $path = $file->store('dental/sessions/images', 'public');
                    $images[] = $path;
                }
                $data['session_images'] = $images;
            }

            if ($request->hasFile('session_notes_file')) {
                // Delete old file
                if ($session->session_notes_file) {
                    Storage::disk('public')->delete($session->session_notes_file);
                }

                $data['session_notes_file'] = $request->file('session_notes_file')
                    ->store('dental/sessions/notes', 'public');
            }

            $session->update($data);

            // Update treatment progress if status changed
            if ($oldStatus !== $session->status) {
                $this->updateTreatmentProgress($session->dentalTreatment);
            }

            DB::commit();

            return redirect()->route('dental.sessions.show', $session)
                ->with('success', 'تم تحديث الجلسة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified dental session
     */
    public function destroy(DentalSession $session)
    {
        try {
            DB::beginTransaction();

            // Delete associated files
            if ($session->session_images) {
                foreach ($session->session_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            if ($session->session_notes_file) {
                Storage::disk('public')->delete($session->session_notes_file);
            }

            $treatment = $session->dentalTreatment;
            $session->delete();

            // Update treatment progress
            $this->updateTreatmentProgress($treatment);

            DB::commit();

            return redirect()->route('dental.sessions.index')
                ->with('success', 'تم حذف الجلسة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * Mark session as completed
     */
    public function markCompleted(DentalSession $session)
    {
        try {
            DB::beginTransaction();

            $session->update([
                'status' => 'completed',
                'actual_end_time' => now()
            ]);

            $this->updateTreatmentProgress($session->dentalTreatment);

            DB::commit();

            return back()->with('success', 'تم تحديد الجلسة كمكتملة');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Mark session as cancelled
     */
    public function markCancelled(DentalSession $session)
    {
        try {
            DB::beginTransaction();

            $session->update([
                'status' => 'cancelled',
                'cancellation_reason' => request('reason', 'تم الإلغاء')
            ]);

            $this->updateTreatmentProgress($session->dentalTreatment);

            DB::commit();

            return back()->with('success', 'تم إلغاء الجلسة');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Show calendar view
     */
    public function calendarView()
    {
        return view('dental.sessions.calendar');
    }

    /**
     * Get sessions for calendar
     */
    public function calendar(Request $request)
    {
        $query = DentalSession::with(['dentalTreatment.patient', 'dentalTreatment.doctor']);

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('scheduled_date', [$request->start, $request->end]);
        }

        $sessions = $query->get();

        $events = $sessions->map(function ($session) {
            $statusColors = [
                'scheduled' => '#007bff',
                'completed' => '#28a745',
                'cancelled' => '#dc3545',
                'no_show' => '#ffc107'
            ];

            return [
                'id' => $session->id,
                'title' => $session->dentalTreatment->patient->name . ' - جلسة ' . $session->session_order,
                'start' => $session->scheduled_date->format('Y-m-d') . 'T09:00:00',
                'end' => $session->scheduled_date->format('Y-m-d') . 'T10:00:00',
                'backgroundColor' => $statusColors[$session->status] ?? '#6c757d',
                'borderColor' => $statusColors[$session->status] ?? '#6c757d',
                'url' => route('dental.sessions.show', $session)
            ];
        });

        return response()->json($events);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:complete,cancel,delete',
            'sessions' => 'required|array',
            'sessions.*' => 'exists:dental_sessions,id'
        ]);

        try {
            DB::beginTransaction();

            $sessions = DentalSession::whereIn('id', $request->sessions)->get();
            $count = 0;

            foreach ($sessions as $session) {
                switch ($request->action) {
                    case 'complete':
                        if ($session->status === 'scheduled') {
                            $session->update([
                                'status' => 'completed',
                                'actual_end_time' => now()
                            ]);
                            $this->updateTreatmentProgress($session->dentalTreatment);
                            $count++;
                        }
                        break;

                    case 'cancel':
                        if ($session->status === 'scheduled') {
                            $session->update([
                                'status' => 'cancelled',
                                'cancellation_reason' => 'إلغاء جماعي'
                            ]);
                            $this->updateTreatmentProgress($session->dentalTreatment);
                            $count++;
                        }
                        break;

                    case 'delete':
                        // Delete associated files
                        if ($session->session_images) {
                            foreach ($session->session_images as $image) {
                                Storage::disk('public')->delete($image);
                            }
                        }
                        if ($session->session_notes_file) {
                            Storage::disk('public')->delete($session->session_notes_file);
                        }
                        
                        $treatment = $session->dentalTreatment;
                        $session->delete();
                        $this->updateTreatmentProgress($treatment);
                        $count++;
                        break;
                }
            }

            DB::commit();

            $actionNames = [
                'complete' => 'إكمال',
                'cancel' => 'إلغاء',
                'delete' => 'حذف'
            ];

            return back()->with('success', "تم {$actionNames[$request->action]} {$count} جلسة بنجاح");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage());
        }
    }

    /**
     * Update treatment progress based on completed sessions
     */
    private function updateTreatmentProgress(DentalTreatment $treatment)
    {
        $completedSessions = $treatment->sessions()->where('status', 'completed')->count();
        $totalSessions = $treatment->total_sessions;

        if ($totalSessions > 0) {
            $progressPercentage = min(100, ($completedSessions / $totalSessions) * 100);
            
            $status = $treatment->status;
            if ($progressPercentage >= 100) {
                $status = 'completed';
            } elseif ($progressPercentage > 0) {
                $status = 'in_progress';
            }

            $treatment->update([
                'progress_percentage' => $progressPercentage,
                'status' => $status,
                'actual_end_date' => $progressPercentage >= 100 ? now() : null
            ]);
        }
    }
}