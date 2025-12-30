<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\CashRegister;
use App\Models\LabOrder;
use App\Models\RadiologyOrder;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // توجيه المستخدم إلى لوحة التحكم المناسبة حسب دوره
        if ($user->isSuperAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isDepartmentManager()) {
            return $this->departmentManagerDashboard();
        } elseif ($user->isDoctor()) {
            return $this->doctorDashboard();
        } elseif ($user->isReceptionStaff()) {
            return $this->receptionDashboard();
        } elseif ($user->isCashier()) {
            return $this->cashierDashboard();
        }

        return $this->defaultDashboard();
    }

    private function adminDashboard()
    {
        $stats = [
            'total_patients' => Patient::count(),
            'today_appointments' => Appointment::today()->count(),
            'pending_appointments' => Appointment::pending()->count(),
            'total_revenue_today' => Invoice::whereDate('created_at', today())->sum('total_amount'),
            'active_cash_registers' => CashRegister::active()->count(),
            'pending_lab_orders' => LabOrder::pending()->count(),
            'pending_radiology_orders' => RadiologyOrder::pending()->count(),
        ];

        $recentAppointments = Appointment::with(['patient', 'doctor'])
                                        ->latest()
                                        ->limit(10)
                                        ->get();

        $monthlyRevenue = Invoice::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
                                ->whereMonth('created_at', now()->month)
                                ->groupBy('date')
                                ->orderBy('date')
                                ->get();

        return view('dashboards.admin', compact('stats', 'recentAppointments', 'monthlyRevenue'));
    }

    private function departmentManagerDashboard()
    {
        $user = Auth::user();
        $departmentId = $user->department_id;

        $stats = [
            'department_patients' => Patient::where('department_id', $departmentId)->count(),
            'today_appointments' => Appointment::today()->whereHas('doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })->count(),
            'department_staff' => \App\Models\User::where('department_id', $departmentId)->count(),
            'monthly_revenue' => Invoice::whereHas('appointment.doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })->whereMonth('created_at', now()->month)->sum('total_amount'),
        ];

        return view('dashboards.department-manager', compact('stats'));
    }

    private function doctorDashboard()
    {
        $user = Auth::user();

        $stats = [
            'today_appointments' => $user->todayAppointments()->count(),
            'pending_appointments' => $user->appointments()->pending()->count(),
            'completed_today' => $user->todayAppointments()->completed()->count(),
            'total_patients' => $user->appointments()->distinct('patient_id')->count(),
        ];

        $todayAppointments = $user->todayAppointments()->with('patient')->get();
        $upcomingAppointments = $user->upcomingAppointments()->with('patient')->limit(5)->get();

        return view('dashboards.doctor', compact('stats', 'todayAppointments', 'upcomingAppointments'));
    }

    private function receptionDashboard()
    {
        $stats = [
            'today_appointments' => Appointment::today()->count(),
            'pending_appointments' => Appointment::pending()->count(),
            'confirmed_appointments' => Appointment::confirmed()->count(),
            'new_patients_today' => Patient::whereDate('created_at', today())->count(),
        ];

        $todayAppointments = Appointment::today()
                                       ->with(['patient', 'doctor'])
                                       ->orderBy('appointment_time')
                                       ->get();

        $pendingAppointments = Appointment::pending()
                                         ->with(['patient', 'doctor'])
                                         ->orderBy('appointment_date')
                                         ->orderBy('appointment_time')
                                         ->limit(10)
                                         ->get();

        return view('dashboards.reception', compact('stats', 'todayAppointments', 'pendingAppointments'));
    }

    private function cashierDashboard()
    {
        $stats = [
            'today_revenue' => Invoice::whereDate('created_at', today())->sum('total_amount'),
            'pending_payments' => Invoice::unpaid()->count(),
            'cash_registers_active' => CashRegister::active()->count(),
            'today_transactions' => Invoice::whereDate('created_at', today())->count(),
        ];

        $recentInvoices = Invoice::with(['patient', 'appointment.doctor'])
                                ->latest()
                                ->limit(10)
                                ->get();

        $cashRegisters = CashRegister::active()->with('department')->get();

        return view('dashboards.cashier', compact('stats', 'recentInvoices', 'cashRegisters'));
    }

    private function defaultDashboard()
    {
        $stats = [
            'total_appointments' => Appointment::count(),
            'total_patients' => Patient::count(),
        ];

        return view('dashboards.default', compact('stats'));
    }
}