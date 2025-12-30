<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorFinancialAccount;
use App\Models\DoctorTransaction;
use App\Models\DoctorCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DoctorFinancialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display financial dashboard
     */
    public function index()
    {
        $doctors = Doctor::with(['financialAccount', 'transactions', 'commissions'])
            ->active()
            ->paginate(15);

        $totalEarnings = DoctorTransaction::where('type', 'earning')
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawals = DoctorTransaction::where('type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');

        $pendingWithdrawals = DoctorTransaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        $monthlyEarnings = DoctorTransaction::where('type', 'earning')
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return view('financial.index', compact(
            'doctors',
            'totalEarnings',
            'totalWithdrawals',
            'pendingWithdrawals',
            'monthlyEarnings'
        ));
    }

    /**
     * Show doctor's financial account
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['financialAccount', 'transactions', 'commissions']);
        
        // Create financial account if doesn't exist
        if (!$doctor->financialAccount) {
            $doctor->createFinancialAccount();
            $doctor->load('financialAccount');
        }

        $recentTransactions = $doctor->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $monthlyEarnings = $doctor->transactions()
            ->where('type', 'earning')
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $yearlyEarnings = $doctor->transactions()
            ->where('type', 'earning')
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Monthly earnings chart data
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $earnings = $doctor->transactions()
                ->where('type', 'earning')
                ->where('status', 'completed')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'earnings' => $earnings
            ];
        }

        return view('financial.show', compact(
            'doctor',
            'recentTransactions',
            'monthlyEarnings',
            'yearlyEarnings',
            'monthlyData'
        ));
    }

    /**
     * Show transactions for a doctor
     */
    public function transactions(Doctor $doctor, Request $request)
    {
        $query = $doctor->transactions()->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20);

        return view('financial.transactions', compact('doctor', 'transactions'));
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(Request $request, Doctor $doctor)
    {
        $request->validate([
            'type' => 'required|in:earning,withdrawal,commission,bonus,refund',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'payment_method' => 'nullable|in:bank_transfer,cash,check,online_payment',
            'reference_number' => 'nullable|string|max:100'
        ]);

        // Create financial account if doesn't exist
        if (!$doctor->financialAccount) {
            $doctor->createFinancialAccount();
        }

        DB::beginTransaction();
        try {
            $transaction = $doctor->transactions()->create([
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'status' => $request->type === 'withdrawal' ? 'pending' : 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => $request->type !== 'withdrawal' ? now() : null
            ]);

            // Update financial account balance
            $this->updateFinancialAccount($doctor, $transaction);

            DB::commit();

            return redirect()->route('financial.show', $doctor)
                ->with('success', __('app.transaction') . ' ' . __('app.create') . ' ' . __('app.successfully'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', __('app.error_occurred'));
        }
    }

    /**
     * Process withdrawal request
     */
    public function processWithdrawal(Request $request, DoctorTransaction $transaction)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            return back()->with('error', __('app.invalid_transaction'));
        }

        DB::beginTransaction();
        try {
            if ($request->action === 'approve') {
                $transaction->update([
                    'status' => 'completed',
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'notes' => $request->notes
                ]);

                // Update financial account
                $this->updateFinancialAccount($transaction->doctor, $transaction);

                $message = __('app.withdrawal_approved');
            } else {
                $transaction->update([
                    'status' => 'rejected',
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'notes' => $request->notes
                ]);

                // Return amount to available balance
                $account = $transaction->doctor->financialAccount;
                $account->update([
                    'available_balance' => $account->available_balance + $transaction->amount,
                    'pending_balance' => $account->pending_balance - $transaction->amount
                ]);

                $message = __('app.withdrawal_rejected');
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', __('app.error_occurred'));
        }
    }

    /**
     * Show commission settings
     */
    public function commissions(Doctor $doctor)
    {
        $commissions = $doctor->commissions()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('financial.commissions', compact('doctor', 'commissions'));
    }

    /**
     * Update commission settings
     */
    public function updateCommission(Request $request, Doctor $doctor)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_amount' => 'nullable|numeric|min:0',
            'conditions' => 'nullable|string|max:1000',
            'effective_date' => 'required|date|after_or_equal:today'
        ]);

        // Create financial account if doesn't exist
        if (!$doctor->financialAccount) {
            $doctor->createFinancialAccount();
        }

        // Deactivate current commission
        $doctor->commissions()->where('status', 'active')->update(['status' => 'inactive']);

        // Create new commission
        $doctor->commissions()->create([
            'commission_rate' => $request->commission_rate,
            'minimum_amount' => $request->minimum_amount,
            'maximum_amount' => $request->maximum_amount,
            'conditions' => $request->conditions,
            'effective_date' => $request->effective_date,
            'status' => 'active',
            'created_by' => Auth::id()
        ]);

        // Update financial account commission rate
        $doctor->financialAccount->update([
            'commission_rate' => $request->commission_rate
        ]);

        return back()->with('success', __('app.commission_settings') . ' ' . __('app.update') . ' ' . __('app.successfully'));
    }

    /**
     * Generate financial report
     */
    public function report(Request $request, Doctor $doctor)
    {
        $request->validate([
            'report_type' => 'required|in:earnings,withdrawals,commissions,summary',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from'
        ]);

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);

        $data = [];

        switch ($request->report_type) {
            case 'earnings':
                $data = $this->getEarningsReport($doctor, $dateFrom, $dateTo);
                break;
            case 'withdrawals':
                $data = $this->getWithdrawalsReport($doctor, $dateFrom, $dateTo);
                break;
            case 'commissions':
                $data = $this->getCommissionsReport($doctor, $dateFrom, $dateTo);
                break;
            case 'summary':
                $data = $this->getSummaryReport($doctor, $dateFrom, $dateTo);
                break;
        }

        return view('financial.report', compact('doctor', 'data', 'dateFrom', 'dateTo', 'request'));
    }

    /**
     * Update financial account balance
     */
    private function updateFinancialAccount(Doctor $doctor, DoctorTransaction $transaction)
    {
        $account = $doctor->financialAccount;

        switch ($transaction->type) {
            case 'earning':
            case 'bonus':
                $account->balance += $transaction->amount;
                $account->available_balance += $transaction->amount;
                $account->total_earnings += $transaction->amount;
                break;

            case 'withdrawal':
                if ($transaction->status === 'pending') {
                    $account->available_balance -= $transaction->amount;
                    $account->pending_balance += $transaction->amount;
                } elseif ($transaction->status === 'completed') {
                    $account->balance -= $transaction->amount;
                    $account->pending_balance -= $transaction->amount;
                    $account->total_withdrawals += $transaction->amount;
                }
                break;

            case 'commission':
                $account->balance -= $transaction->amount;
                $account->available_balance -= $transaction->amount;
                break;

            case 'refund':
                $account->balance -= $transaction->amount;
                $account->available_balance -= $transaction->amount;
                break;
        }

        $account->save();
    }

    /**
     * Get earnings report data
     */
    private function getEarningsReport(Doctor $doctor, Carbon $dateFrom, Carbon $dateTo)
    {
        return $doctor->transactions()
            ->where('type', 'earning')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get withdrawals report data
     */
    private function getWithdrawalsReport(Doctor $doctor, Carbon $dateFrom, Carbon $dateTo)
    {
        return $doctor->transactions()
            ->where('type', 'withdrawal')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get commissions report data
     */
    private function getCommissionsReport(Doctor $doctor, Carbon $dateFrom, Carbon $dateTo)
    {
        return $doctor->transactions()
            ->where('type', 'commission')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get summary report data
     */
    private function getSummaryReport(Doctor $doctor, Carbon $dateFrom, Carbon $dateTo)
    {
        return [
            'total_earnings' => $doctor->transactions()
                ->where('type', 'earning')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            
            'total_withdrawals' => $doctor->transactions()
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            
            'total_commissions' => $doctor->transactions()
                ->where('type', 'commission')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            
            'pending_withdrawals' => $doctor->transactions()
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            
            'transactions_count' => $doctor->transactions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count()
        ];
    }
}