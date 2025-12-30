<?php

namespace App\Http\Controllers;

use App\Models\SurgicalProcedure;
use Illuminate\Http\Request;

class SurgicalProcedureController extends Controller
{
    public function index(Request $request)
    {
        $query = SurgicalProcedure::query();

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', $request->specialty);
        }

        if ($request->filled('complexity')) {
            $query->where('complexity', $request->complexity);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $procedures = $query->orderBy('name')->paginate(15);

        // Get filter options
        $categories = SurgicalProcedure::getByCategory();
        $specialties = SurgicalProcedure::getBySpecialty();

        return view('surgical-procedures.index', compact('procedures', 'categories', 'specialties'));
    }

    public function create()
    {
        return view('surgical-procedures.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:surgical_procedures,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'specialty' => 'required|string|max:100',
            'complexity' => 'required|in:minor,moderate,major,complex',
            'urgency_level' => 'required|in:elective,urgent,emergency',
            'estimated_duration' => 'required|integer|min:15|max:720',
            'min_duration' => 'nullable|integer|min:15',
            'max_duration' => 'nullable|integer|min:15',
            'base_cost' => 'required|numeric|min:0',
            'surgeon_fee' => 'nullable|numeric|min:0',
            'anesthesia_fee' => 'nullable|numeric|min:0',
            'facility_fee' => 'nullable|numeric|min:0',
            'required_equipment' => 'nullable|array',
            'required_team_roles' => 'nullable|array',
            'pre_operative_requirements' => 'nullable|array',
            'post_operative_care' => 'nullable|array',
            'requires_icu' => 'boolean',
            'requires_blood_bank' => 'boolean',
            'requires_anesthesia' => 'boolean',
            'is_outpatient' => 'boolean',
            'is_active' => 'boolean',
            'contraindications' => 'nullable|string|max:1000',
            'complications' => 'nullable|string|max:1000',
            'recovery_notes' => 'nullable|string|max:1000',
        ]);

        $procedure = SurgicalProcedure::create($validated);

        return redirect()->route('surgical-procedures.show', $procedure)
            ->with('success', 'تم إنشاء الإجراء الجراحي بنجاح');
    }

    public function show(SurgicalProcedure $surgicalProcedure)
    {
        $surgicalProcedure->loadCount('surgeries');
        
        // Get recent surgeries for this procedure
        $recentSurgeries = $surgicalProcedure->surgeries()
            ->with(['patient', 'primarySurgeon', 'operatingRoom'])
            ->orderBy('scheduled_start_time', 'desc')
            ->take(10)
            ->get();

        // Statistics
        $stats = [
            'total_surgeries' => $surgicalProcedure->surgeries_count,
            'completed_surgeries' => $surgicalProcedure->surgeries()->where('status', 'completed')->count(),
            'average_duration' => $surgicalProcedure->surgeries()
                ->whereNotNull('actual_start_time')
                ->whereNotNull('actual_end_time')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, actual_start_time, actual_end_time)) as avg_duration')
                ->value('avg_duration'),
            'success_rate' => $surgicalProcedure->surgeries_count > 0 
                ? ($surgicalProcedure->surgeries()->where('status', 'completed')->count() / $surgicalProcedure->surgeries_count) * 100 
                : 0,
        ];

        return view('surgical-procedures.show', compact('surgicalProcedure', 'recentSurgeries', 'stats'));
    }

    public function edit(SurgicalProcedure $surgicalProcedure)
    {
        return view('surgical-procedures.edit', compact('surgicalProcedure'));
    }

    public function update(Request $request, SurgicalProcedure $surgicalProcedure)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:surgical_procedures,code,' . $surgicalProcedure->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'specialty' => 'required|string|max:100',
            'complexity' => 'required|in:minor,moderate,major,complex',
            'urgency_level' => 'required|in:elective,urgent,emergency',
            'estimated_duration' => 'required|integer|min:15|max:720',
            'min_duration' => 'nullable|integer|min:15',
            'max_duration' => 'nullable|integer|min:15',
            'base_cost' => 'required|numeric|min:0',
            'surgeon_fee' => 'nullable|numeric|min:0',
            'anesthesia_fee' => 'nullable|numeric|min:0',
            'facility_fee' => 'nullable|numeric|min:0',
            'required_equipment' => 'nullable|array',
            'required_team_roles' => 'nullable|array',
            'pre_operative_requirements' => 'nullable|array',
            'post_operative_care' => 'nullable|array',
            'requires_icu' => 'boolean',
            'requires_blood_bank' => 'boolean',
            'requires_anesthesia' => 'boolean',
            'is_outpatient' => 'boolean',
            'is_active' => 'boolean',
            'contraindications' => 'nullable|string|max:1000',
            'complications' => 'nullable|string|max:1000',
            'recovery_notes' => 'nullable|string|max:1000',
        ]);

        $surgicalProcedure->update($validated);

        return redirect()->route('surgical-procedures.show', $surgicalProcedure)
            ->with('success', 'تم تحديث الإجراء الجراحي بنجاح');
    }

    public function destroy(SurgicalProcedure $surgicalProcedure)
    {
        if ($surgicalProcedure->surgeries()->exists()) {
            return back()->with('error', 'لا يمكن حذف إجراء جراحي مرتبط بعمليات');
        }

        $surgicalProcedure->delete();

        return redirect()->route('surgical-procedures.index')
            ->with('success', 'تم حذف الإجراء الجراحي بنجاح');
    }

    public function toggleStatus(SurgicalProcedure $surgicalProcedure)
    {
        $surgicalProcedure->update([
            'is_active' => !$surgicalProcedure->is_active
        ]);

        $status = $surgicalProcedure->is_active ? 'تفعيل' : 'إلغاء تفعيل';
        
        return back()->with('success', "تم {$status} الإجراء الجراحي بنجاح");
    }

    // AJAX endpoints
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $procedures = SurgicalProcedure::active()
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->select('id', 'code', 'name', 'name_ar', 'estimated_duration', 'base_cost')
            ->limit(10)
            ->get();

        return response()->json($procedures);
    }

    public function getDetails(SurgicalProcedure $surgicalProcedure)
    {
        return response()->json([
            'id' => $surgicalProcedure->id,
            'code' => $surgicalProcedure->code,
            'name' => $surgicalProcedure->display_name,
            'estimated_duration' => $surgicalProcedure->estimated_duration,
            'total_cost' => $surgicalProcedure->total_cost,
            'complexity' => $surgicalProcedure->complexity,
            'requires_icu' => $surgicalProcedure->requires_icu,
            'requires_blood_bank' => $surgicalProcedure->requires_blood_bank,
            'requires_anesthesia' => $surgicalProcedure->requires_anesthesia,
            'required_equipment' => $surgicalProcedure->required_equipment,
            'required_team_roles' => $surgicalProcedure->getRequiredTeamRoles(),
        ]);
    }
}