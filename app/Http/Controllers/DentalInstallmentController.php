<?php

namespace App\Http\Controllers;

use App\Models\DentalInstallment;
use App\Models\DentalTreatment;
use App\Http\Requests\DentalInstallmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DentalInstallmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of dental installments
     */
    public function index(Request $request)
    {
        $query = DentalInstallment::with(['dentalTreatment.patient', 'dentalTreatment.doctor'])
            ->orderBy('due_date', 'asc');

        // Filter by treatment
        if ($request->filled('treatment_id')) {
            $query->where('dental_treatment_id', $request->treatment_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by due date range
        if ($request->filled('due_from')) {
            $query->whereDate('due_date', '>=', $request->due_from);
        }
        if ($request->filled('due_to')) {
            $query->whereDate('due_date', '<=', $request->due_to);
        }

        // Filter overdue
        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->where('due_date', '<', now())
                  ->where('status', 'pending');
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

        $installments = $query->paginate(15);

        // Statistics
        $stats = [
            'total' => DentalInstallment::count(),
            'pending' => DentalInstallment::where('status', 'pending')->count(),
            'paid' => DentalInstallment::where('status', 'paid')->count(),
            'overdue' => DentalInstallment::where('due_date', '<', now())
                ->where('status', 'pending')->count(),
            'due_this_week' => DentalInstallment::whereBetween('due_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->where('status', 'pending')->count(),
            'total_pending_amount' => DentalInstallment::where('status', 'pending')->sum('amount'),
            'total_paid_amount' => DentalInstallment::where('status', 'paid')->sum('amount')
        ];

        $treatments = DentalTreatment::with('patient')
            ->where('payment_type', 'installments')
            ->get();

        return view('dental.installments.index', compact('installments', 'stats', 'treatments'));
    }

    /**
     * Show the form for creating a new dental installment
     */
    public function create(Request $request)
    {
        $treatment = null;
        if ($request->filled('treatment')) {
            $treatment = DentalTreatment::with(['patient', 'doctor'])->findOrFail($request->treatment);
        }

        $treatments = DentalTreatment::with(['patient', 'doctor'])
            ->where('payment_type', 'installments')
            ->get();

        return view('dental.installments.create', compact('treatment', 'treatments'));
    }

    /**
     * Store a newly created dental installment
     */
    public function store(DentalInstallmentRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Auto-generate installment number
            $treatment = DentalTreatment::findOrFail($data['dental_treatment_id']);
            $data['installment_number'] = $treatment->installments()->count() + 1;

            $installment = DentalInstallment::create($data);

            // Update treatment paid amount if installment is paid
            if ($installment->status === 'paid') {
                $this->updateTreatmentPaidAmount($treatment);
            }

            DB::commit();

            return redirect()->route('dental.installments.show', $installment)
                ->with('success', 'تم إنشاء القسط بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء القسط: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified dental installment
     */
    public function show(DentalInstallment $installment)
    {
        $installment->load(['dentalTreatment.patient', 'dentalTreatment.doctor']);
        
        return view('dental.installments.show', compact('installment'));
    }

    /**
     * Show the form for editing the specified dental installment
     */
    public function edit(DentalInstallment $installment)
    {
        $installment->load(['dentalTreatment.patient', 'dentalTreatment.doctor']);
        
        $treatments = DentalTreatment::with(['patient', 'doctor'])
            ->where('payment_type', 'installments')
            ->get();

        return view('dental.installments.edit', compact('installment', 'treatments'));
    }

    /**
     * Update the specified dental installment
     */
    public function update(DentalInstallmentRequest $request, DentalInstallment $installment)
    {
        try {
            DB::beginTransaction();

            $oldStatus = $installment->status;
            $installment->update($request->validated());

            // Update treatment paid amount if status changed
            if ($oldStatus !== $installment->status) {
                $this->updateTreatmentPaidAmount($installment->dentalTreatment);
            }

            DB::commit();

            return redirect()->route('dental.installments.show', $installment)
                ->with('success', 'تم تحديث القسط بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث القسط: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified dental installment
     */
    public function destroy(DentalInstallment $installment)
    {
        try {
            DB::beginTransaction();

            $treatment = $installment->dentalTreatment;
            $installment->delete();

            // Update treatment paid amount
            $this->updateTreatmentPaidAmount($treatment);

            DB::commit();

            return redirect()->route('dental.installments.index')
                ->with('success', 'تم حذف القسط بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف القسط: ' . $e->getMessage());
        }
    }

    /**
     * Mark installment as paid
     */
    public function markPaid(DentalInstallment $installment)
    {
        try {
            DB::beginTransaction();

            $installment->update([
                'status' => 'paid',
                'paid_date' => now(),
                'payment_method' => request('payment_method', 'cash'),
                'notes' => request('notes')
            ]);

            $this->updateTreatmentPaidAmount($installment->dentalTreatment);

            DB::commit();

            return back()->with('success', 'تم تحديد القسط كمدفوع');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Mark installment as overdue
     */
    public function markOverdue(DentalInstallment $installment)
    {
        try {
            $installment->update([
                'status' => 'overdue',
                'overdue_days' => now()->diffInDays($installment->due_date)
            ]);

            return back()->with('success', 'تم تحديد القسط كمتأخر');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Generate installments for treatment
     */
    public function generateInstallments(Request $request)
    {
        $request->validate([
            'treatment_id' => 'required|exists:dental_treatments,id',
            'months' => 'required|integer|min:2|max:24',
            'first_payment_date' => 'required|date|after_or_equal:today'
        ]);

        try {
            DB::beginTransaction();

            $treatment = DentalTreatment::findOrFail($request->treatment_id);

            // Delete existing installments if any
            $treatment->installments()->delete();

            $remainingAmount = $treatment->total_cost - $treatment->paid_amount;
            $monthlyAmount = $remainingAmount / $request->months;

            for ($i = 0; $i < $request->months; $i++) {
                $dueDate = \Carbon\Carbon::parse($request->first_payment_date)
                    ->addMonths($i);

                DentalInstallment::create([
                    'dental_treatment_id' => $treatment->id,
                    'installment_number' => $i + 1,
                    'amount' => round($monthlyAmount, 2),
                    'due_date' => $dueDate,
                    'status' => 'pending'
                ]);
            }

            // Update treatment installment info
            $treatment->update([
                'installment_months' => $request->months,
                'monthly_installment' => round($monthlyAmount, 2)
            ]);

            DB::commit();

            return back()->with('success', "تم إنشاء {$request->months} قسط بنجاح");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء إنشاء الأقساط: ' . $e->getMessage());
        }
    }

    /**
     * Send payment reminder
     */
    public function sendReminder(DentalInstallment $installment)
    {
        try {
            // Here you would implement the actual reminder sending logic
            // For now, just mark as reminder sent
            $installment->update([
                'reminder_sent' => true,
                'reminder_sent_at' => now()
            ]);

            return back()->with('success', 'تم إرسال تذكير الدفع');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إرسال التذكير: ' . $e->getMessage());
        }
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_paid,mark_overdue,send_reminder,delete',
            'installments' => 'required|array',
            'installments.*' => 'exists:dental_installments,id'
        ]);

        try {
            DB::beginTransaction();

            $installments = DentalInstallment::whereIn('id', $request->installments)->get();
            $count = 0;

            foreach ($installments as $installment) {
                switch ($request->action) {
                    case 'mark_paid':
                        if ($installment->status === 'pending') {
                            $installment->update([
                                'status' => 'paid',
                                'paid_date' => now(),
                                'payment_method' => 'cash'
                            ]);
                            $this->updateTreatmentPaidAmount($installment->dentalTreatment);
                            $count++;
                        }
                        break;

                    case 'mark_overdue':
                        if ($installment->status === 'pending') {
                            $installment->update([
                                'status' => 'overdue',
                                'overdue_days' => now()->diffInDays($installment->due_date)
                            ]);
                            $count++;
                        }
                        break;

                    case 'send_reminder':
                        $installment->update([
                            'reminder_sent' => true,
                            'reminder_sent_at' => now()
                        ]);
                        $count++;
                        break;

                    case 'delete':
                        $treatment = $installment->dentalTreatment;
                        $installment->delete();
                        $this->updateTreatmentPaidAmount($treatment);
                        $count++;
                        break;
                }
            }

            DB::commit();

            $actionNames = [
                'mark_paid' => 'تحديد كمدفوع',
                'mark_overdue' => 'تحديد كمتأخر',
                'send_reminder' => 'إرسال تذكير',
                'delete' => 'حذف'
            ];

            return back()->with('success', "تم {$actionNames[$request->action]} {$count} قسط بنجاح");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage());
        }
    }

    /**
     * Get overdue installments
     */
    public function overdue()
    {
        $installments = DentalInstallment::with(['dentalTreatment.patient', 'dentalTreatment.doctor'])
            ->where('due_date', '<', now())
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->paginate(15);

        return view('dental.installments.overdue', compact('installments'));
    }

    /**
     * Payment calendar
     */
    public function calendar(Request $request)
    {
        $query = DentalInstallment::with(['dentalTreatment.patient']);

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('due_date', [$request->start, $request->end]);
        }

        $installments = $query->get();

        $events = $installments->map(function ($installment) {
            $statusColors = [
                'pending' => '#ffc107',
                'paid' => '#28a745',
                'overdue' => '#dc3545',
                'cancelled' => '#6c757d'
            ];

            return [
                'id' => $installment->id,
                'title' => $installment->dentalTreatment->patient->name . ' - قسط ' . $installment->installment_number,
                'start' => $installment->due_date->format('Y-m-d'),
                'backgroundColor' => $statusColors[$installment->status] ?? '#6c757d',
                'borderColor' => $statusColors[$installment->status] ?? '#6c757d',
                'url' => route('dental.installments.show', $installment),
                'extendedProps' => [
                    'amount' => $installment->amount,
                    'status' => $installment->status
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Update treatment paid amount based on paid installments
     */
    private function updateTreatmentPaidAmount(DentalTreatment $treatment)
    {
        $paidAmount = $treatment->installments()
            ->where('status', 'paid')
            ->sum('amount');

        $treatment->update(['paid_amount' => $paidAmount]);
    }
}