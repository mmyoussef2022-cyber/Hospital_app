<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Models\FinancialPayment;
use App\Models\FinancialRefund;
use App\Models\DoctorFinancialAccount;
use App\Models\DepartmentFinancialAccount;
use Carbon\Carbon;

class FinancialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:financial.view')->only(['index', 'show']);
        $this->middleware('permission:financial.create')->only(['create', 'store']);
        $this->middleware('permission:financial.edit')->only(['edit', 'update']);
        $this->middleware('permission:financial.delete')->only(['destroy']);
    }

    public function index()
    {
        $stats = [
            'total_revenue_today' => FinancialPayment::today()->completed()->sum('amount'),
            'total_refunds_today' => FinancialRefund::whereDate('refund_date', today())->completed()->sum('refund_amount'),
            'pending_payments' => FinancialPayment::pending()->count(),
            'pending_refunds' => FinancialRefund::pending()->count(),
            'cash_balance' => FinancialAccount::where('account_code', '1110')->first()?->balance ?? 0,
            'accounts_receivable' => FinancialAccount::where('account_code', '1120')->first()?->balance ?? 0
        ];

        $recentPayments = FinancialPayment::with(['patient', 'receivedBy'])
                                        ->latest()
                                        ->limit(10)
                                        ->get();

        $recentRefunds = FinancialRefund::with(['originalPayment.patient', 'requestedBy'])
                                      ->latest()
                                      ->limit(5)
                                      ->get();

        return view('financial.index', compact('stats', 'recentPayments', 'recentRefunds'));
    }
