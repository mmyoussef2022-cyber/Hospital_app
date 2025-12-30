<?php

namespace App\Http\Controllers;

use App\Models\RadiologyStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RadiologyStudyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of radiology studies.
     */
    public function index(Request $request)
    {
        $query = RadiologyStudy::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by body part
        if ($request->filled('body_part')) {
            $query->byBodyPart($request->body_part);
        }

        // Filter by active status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        } else {
            // Default to active studies only
            $query->active();
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by urgent capability
        if ($request->filled('urgent_capable')) {
            $query->urgentCapable();
        }

        $studies = $query->orderBy('category')
                        ->orderBy('name')
                        ->paginate(20);

        // Get filter options
        $categories = RadiologyStudy::getCategories();
        $bodyParts = RadiologyStudy::getBodyParts();

        return view('radiology.studies.index', compact('studies', 'categories', 'bodyParts'));
    }

    /**
     * Show the form for creating a new radiology study.
     */
    public function create()
    {
        $categories = RadiologyStudy::getCategories();
        $bodyParts = RadiologyStudy::getBodyParts();

        return view('radiology.studies.create', compact('categories', 'bodyParts'));
    }

    /**
     * Store a newly created radiology study.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:radiology_studies,code',
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:' . implode(',', array_keys(RadiologyStudy::getCategories())),
            'body_part' => 'required|in:' . implode(',', array_keys(RadiologyStudy::getBodyParts())),
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'preparation_instructions' => 'nullable|string',
            'contrast_instructions' => 'nullable|string',
            'requires_contrast' => 'boolean',
            'requires_fasting' => 'boolean',
            'is_urgent_capable' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $study = RadiologyStudy::create($validated);

        return redirect()->route('radiology-studies.show', $study)
                        ->with('success', 'تم إنشاء فحص الأشعة بنجاح');
    }

    /**
     * Display the specified radiology study.
     */
    public function show(RadiologyStudy $radiologyStudy)
    {
        $radiologyStudy->load(['orders' => function ($query) {
            $query->with(['patient', 'doctor'])->latest()->limit(10);
        }]);

        // Get statistics
        $stats = [
            'total_orders' => $radiologyStudy->orders()->count(),
            'completed_orders' => $radiologyStudy->orders()->where('status', 'completed')->count(),
            'this_month_orders' => $radiologyStudy->orders()->whereMonth('ordered_at', now()->month)->count(),
            'average_rating' => 0, // TODO: Implement rating system
            'total_revenue' => $radiologyStudy->orders()->where('is_paid', true)->sum('total_amount')
        ];

        return view('radiology.studies.show', compact('radiologyStudy', 'stats'));
    }

    /**
     * Show the form for editing the radiology study.
     */
    public function edit(RadiologyStudy $radiologyStudy)
    {
        $categories = RadiologyStudy::getCategories();
        $bodyParts = RadiologyStudy::getBodyParts();

        return view('radiology.studies.edit', compact('radiologyStudy', 'categories', 'bodyParts'));
    }

    /**
     * Update the specified radiology study.
     */
    public function update(Request $request, RadiologyStudy $radiologyStudy)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:radiology_studies,code,' . $radiologyStudy->id,
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:' . implode(',', array_keys(RadiologyStudy::getCategories())),
            'body_part' => 'required|in:' . implode(',', array_keys(RadiologyStudy::getBodyParts())),
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'preparation_instructions' => 'nullable|string',
            'contrast_instructions' => 'nullable|string',
            'requires_contrast' => 'boolean',
            'requires_fasting' => 'boolean',
            'is_urgent_capable' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $radiologyStudy->update($validated);

        return redirect()->route('radiology-studies.show', $radiologyStudy)
                        ->with('success', 'تم تحديث فحص الأشعة بنجاح');
    }

    /**
     * Remove the specified radiology study.
     */
    public function destroy(RadiologyStudy $radiologyStudy)
    {
        // Check if study has orders
        if ($radiologyStudy->orders()->exists()) {
            return back()->with('error', 'لا يمكن حذف فحص الأشعة لوجود طلبات مرتبطة به');
        }

        $radiologyStudy->delete();

        return redirect()->route('radiology-studies.index')
                        ->with('success', 'تم حذف فحص الأشعة بنجاح');
    }

    /**
     * Toggle study status.
     */
    public function toggleStatus(RadiologyStudy $radiologyStudy)
    {
        $radiologyStudy->update(['is_active' => !$radiologyStudy->is_active]);

        $status = $radiologyStudy->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        return response()->json([
            'success' => true,
            'message' => $status . ' فحص الأشعة بنجاح',
            'is_active' => $radiologyStudy->is_active
        ]);
    }

    /**
     * Get studies by category.
     */
    public function getByCategory(Request $request)
    {
        $category = $request->get('category');
        
        $studies = RadiologyStudy::active()
                                ->when($category, function ($query, $category) {
                                    return $query->byCategory($category);
                                })
                                ->orderBy('name')
                                ->get(['id', 'name', 'name_en', 'price', 'duration_minutes']);

        return response()->json($studies);
    }

    /**
     * Bulk actions on studies.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'study_ids' => 'required|array',
            'study_ids.*' => 'exists:radiology_studies,id'
        ]);

        $studies = RadiologyStudy::whereIn('id', $request->study_ids);

        switch ($request->action) {
            case 'activate':
                $studies->update(['is_active' => true]);
                $message = 'تم تفعيل الفحوصات المحددة بنجاح';
                break;
            case 'deactivate':
                $studies->update(['is_active' => false]);
                $message = 'تم إلغاء تفعيل الفحوصات المحددة بنجاح';
                break;
            case 'delete':
                // Check if any study has orders
                $studiesWithOrders = $studies->whereHas('orders')->count();
                if ($studiesWithOrders > 0) {
                    return back()->with('error', 'لا يمكن حذف بعض الفحوصات لوجود طلبات مرتبطة بها');
                }
                $studies->delete();
                $message = 'تم حذف الفحوصات المحددة بنجاح';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Export studies to CSV.
     */
    public function export(Request $request)
    {
        $query = RadiologyStudy::query();

        // Apply same filters as index
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('body_part')) {
            $query->byBodyPart($request->body_part);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $studies = $query->orderBy('category')->orderBy('name')->get();

        $filename = 'radiology_studies_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($studies) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'الكود',
                'الاسم',
                'الاسم بالإنجليزية',
                'الفئة',
                'جزء الجسم',
                'السعر',
                'المدة (دقيقة)',
                'يتطلب صبغة',
                'يتطلب صيام',
                'قابل للعجل',
                'نشط'
            ]);

            // Data
            foreach ($studies as $study) {
                fputcsv($file, [
                    $study->code,
                    $study->name,
                    $study->name_en,
                    $study->category_display,
                    $study->body_part,
                    $study->price,
                    $study->duration_minutes,
                    $study->requires_contrast ? 'نعم' : 'لا',
                    $study->requires_fasting ? 'نعم' : 'لا',
                    $study->is_urgent_capable ? 'نعم' : 'لا',
                    $study->is_active ? 'نعم' : 'لا'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get study statistics.
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_studies' => RadiologyStudy::count(),
            'active_studies' => RadiologyStudy::active()->count(),
            'inactive_studies' => RadiologyStudy::where('is_active', false)->count(),
            'urgent_capable_studies' => RadiologyStudy::urgentCapable()->count(),
            'studies_with_contrast' => RadiologyStudy::where('requires_contrast', true)->count(),
            'studies_requiring_fasting' => RadiologyStudy::where('requires_fasting', true)->count()
        ];

        // Studies by category
        $studiesByCategory = RadiologyStudy::select('category', DB::raw('count(*) as count'))
                                          ->groupBy('category')
                                          ->pluck('count', 'category');

        // Most ordered studies
        $popularStudies = RadiologyStudy::withCount(['orders' => function ($query) use ($startDate, $endDate) {
                                        $query->whereBetween('ordered_at', [$startDate, $endDate]);
                                    }])
                                    ->orderBy('orders_count', 'desc')
                                    ->limit(10)
                                    ->get();

        // Revenue by study
        $revenueByStudy = RadiologyStudy::with(['orders' => function ($query) use ($startDate, $endDate) {
                                        $query->whereBetween('ordered_at', [$startDate, $endDate])
                                              ->where('is_paid', true);
                                    }])
                                    ->get()
                                    ->map(function ($study) {
                                        return [
                                            'name' => $study->display_name,
                                            'revenue' => $study->orders->sum('total_amount')
                                        ];
                                    })
                                    ->sortByDesc('revenue')
                                    ->take(10);

        return view('radiology.studies.statistics', compact(
            'stats', 
            'studiesByCategory', 
            'popularStudies', 
            'revenueByStudy', 
            'startDate', 
            'endDate'
        ));
    }
}