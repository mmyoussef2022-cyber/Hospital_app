<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use App\Models\LabTest;
use App\Models\RadiologyStudy;
use App\Models\InsuranceCompany;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportsExport;

class ReportsAdvancedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:reports.view');
    }

    /**
     * عرض صفحة التقارير الرئيسية
     */
    public function index()
    {
        return view('reports.advanced.index');
    }

    /**
     * التقارير المالية الشاملة
     */
    public function financialReports(Request $request)
    {
        $period = $request->get('period', 'daily');
        $startDate = $request->get('start_date', Carbon::today());
        $endDate = $request->get('end_date', Carbon::today());

        $data = $this->getFinancialData($period, $startDate, $endDate);

        if ($request->get('export') === 'pdf') {
            return $this->exportFinancialPDF($data, $period);
        }

        if ($request->get('export') === 'excel') {
            return $this->exportFinancialExcel($data, $period);
        }

        return view('reports.advanced.financial', compact('data', 'period', 'startDate', 'endDate'));
    }

    /**
     * تقارير أداء الأطباء والأقسام
     */
    public function performanceReports(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $doctorId = $request->get('doctor_id');
        $departmentId = $request->get('department_id');

        $data = $this->getPerformanceData($period, $doctorId, $departmentId);

        if ($request->get('export') === 'pdf') {
            return $this->exportPerformancePDF($data, $period);
        }

        return view('reports.advanced.performance', compact('data', 'period'));
    }

    /**
     * تقارير إحصائيات المرضى والأمراض
     */
    public function patientStatistics(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $ageGroup = $request->get('age_group');
        $gender = $request->get('gender');

        $data = $this->getPatientStatisticsData($period, $ageGroup, $gender);

        if ($request->get('export') === 'pdf') {
            return $this->exportPatientStatisticsPDF($data, $period);
        }

        return view('reports.advanced.patient-statistics', compact('data', 'period'));
    }

    /**
     * تقارير شركات التأمين والمطالبات
     */
    public function insuranceReports(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $insuranceId = $request->get('insurance_id');
        $claimStatus = $request->get('claim_status');

        $data = $this->getInsuranceData($period, $insuranceId, $claimStatus);

        if ($request->get('export') === 'pdf') {
            return $this->exportInsurancePDF($data, $period);
        }

        return view('reports.advanced.insurance', compact('data', 'period'));
    }

    /**
     * تقارير الأدوية والمخزون
     */
    public function inventoryReports(Request $request)
    {
        $category = $request->get('category');
        $lowStock = $request->get('low_stock', false);
        $expired = $request->get('expired', false);

        $data = $this->getInventoryData($category, $lowStock, $expired);

        if ($request->get('export') === 'pdf') {
            return $this->exportInventoryPDF($data);
        }

        return view('reports.advanced.inventory', compact('data'));
    }

    /**
     * تقرير شامل للإدارة العليا
     */
    public function executiveSummary(Request $request)
    {
        $period = $request->get('period', 'monthly');
        
        $data = [
            'financial' => $this->getFinancialSummary($period),
            'operational' => $this->getOperationalSummary($period),
            'clinical' => $this->getClinicalSummary($period),
            'performance' => $this->getPerformanceSummary($period)
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportExecutivePDF($data, $period);
        }

        return view('reports.advanced.executive-summary', compact('data', 'period'));
    }

    /**
     * الحصول على البيانات المالية
     */
    private function getFinancialData($period, $startDate, $endDate)
    {
        $query = Payment::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_revenue' => $query->sum('amount'),
            'cash_payments' => $query->where('payment_method', 'cash')->sum('amount'),
            'card_payments' => $query->whereIn('payment_method', ['visa', 'mastercard'])->sum('amount'),
            'insurance_payments' => $query->where('payment_method', 'insurance')->sum('amount'),
            'pending_payments' => $query->where('status', 'pending')->sum('amount'),
            'daily_breakdown' => $this->getDailyBreakdown($startDate, $endDate),
            'payment_methods' => $this->getPaymentMethodsBreakdown($startDate, $endDate),
            'department_revenue' => $this->getDepartmentRevenue($startDate, $endDate)
        ];
    }

    /**
     * الحصول على بيانات الأداء
     */
    private function getPerformanceData($period, $doctorId = null, $departmentId = null)
    {
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();

        $query = Appointment::whereBetween('appointment_date', [$startDate, $endDate]);
        
        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return [
            'total_appointments' => $query->count(),
            'completed_appointments' => $query->where('status', 'completed')->count(),
            'cancelled_appointments' => $query->where('status', 'cancelled')->count(),
            'doctor_performance' => $this->getDoctorPerformance($startDate, $endDate, $doctorId),
            'department_performance' => $this->getDepartmentPerformance($startDate, $endDate, $departmentId),
            'patient_satisfaction' => $this->getPatientSatisfactionData($startDate, $endDate),
            'average_wait_time' => $this->getAverageWaitTime($startDate, $endDate)
        ];
    }

    /**
     * الحصول على إحصائيات المرضى
     */
    private function getPatientStatisticsData($period, $ageGroup = null, $gender = null)
    {
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();

        $query = Patient::whereBetween('created_at', [$startDate, $endDate]);

        if ($gender) {
            $query->where('gender', $gender);
        }

        return [
            'total_patients' => $query->count(),
            'new_patients' => $query->count(),
            'age_distribution' => $this->getAgeDistribution($query),
            'gender_distribution' => $this->getGenderDistribution($query),
            'common_diagnoses' => $this->getCommonDiagnoses($startDate, $endDate),
            'patient_flow' => $this->getPatientFlow($startDate, $endDate),
            'readmission_rate' => $this->getReadmissionRate($startDate, $endDate)
        ];
    }

    /**
     * الحصول على بيانات التأمين
     */
    private function getInsuranceData($period, $insuranceId = null, $claimStatus = null)
    {
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();

        return [
            'total_claims' => $this->getTotalClaims($startDate, $endDate, $insuranceId),
            'approved_claims' => $this->getApprovedClaims($startDate, $endDate, $insuranceId),
            'pending_claims' => $this->getPendingClaims($startDate, $endDate, $insuranceId),
            'rejected_claims' => $this->getRejectedClaims($startDate, $endDate, $insuranceId),
            'insurance_revenue' => $this->getInsuranceRevenue($startDate, $endDate, $insuranceId),
            'top_insurance_companies' => $this->getTopInsuranceCompanies($startDate, $endDate),
            'claim_processing_time' => $this->getClaimProcessingTime($startDate, $endDate)
        ];
    }

    /**
     * الحصول على بيانات المخزون
     */
    private function getInventoryData($category = null, $lowStock = false, $expired = false)
    {
        // هذا يحتاج إلى نموذج Medication/Inventory
        return [
            'total_items' => 0,
            'low_stock_items' => 0,
            'expired_items' => 0,
            'categories' => [],
            'usage_statistics' => [],
            'reorder_alerts' => []
        ];
    }

    /**
     * تصدير التقرير المالي كـ PDF
     */
    private function exportFinancialPDF($data, $period)
    {
        $pdf = PDF::loadView('reports.pdf.financial', compact('data', 'period'));
        return $pdf->download('financial-report-' . $period . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * تصدير التقرير المالي كـ Excel
     */
    private function exportFinancialExcel($data, $period)
    {
        return Excel::download(new ReportsExport($data, 'financial'), 
            'financial-report-' . $period . '-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * الحصول على تاريخ بداية الفترة
     */
    private function getPeriodStartDate($period)
    {
        switch ($period) {
            case 'daily':
                return Carbon::today();
            case 'weekly':
                return Carbon::now()->startOfWeek();
            case 'monthly':
                return Carbon::now()->startOfMonth();
            case 'quarterly':
                return Carbon::now()->startOfQuarter();
            case 'yearly':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->startOfMonth();
        }
    }

    /**
     * الحصول على التفصيل اليومي
     */
    private function getDailyBreakdown($startDate, $endDate)
    {
        return Payment::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * الحصول على تفصيل طرق الدفع
     */
    private function getPaymentMethodsBreakdown($startDate, $endDate)
    {
        return Payment::selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * الحصول على إيرادات الأقسام
     */
    private function getDepartmentRevenue($startDate, $endDate)
    {
        return DB::table('payments')
            ->join('appointments', 'payments.appointment_id', '=', 'appointments.id')
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->selectRaw('users.department, SUM(payments.amount) as total')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->groupBy('users.department')
            ->get();
    }

    /**
     * الحصول على أداء الأطباء
     */
    private function getDoctorPerformance($startDate, $endDate, $doctorId = null)
    {
        $query = DB::table('appointments')
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->selectRaw('users.name, users.id, COUNT(*) as total_appointments, 
                        SUM(CASE WHEN appointments.status = "completed" THEN 1 ELSE 0 END) as completed,
                        AVG(appointments.duration) as avg_duration')
            ->whereBetween('appointments.appointment_date', [$startDate, $endDate]);

        if ($doctorId) {
            $query->where('users.id', $doctorId);
        }

        return $query->groupBy('users.id', 'users.name')->get();
    }

    /**
     * الحصول على أداء الأقسام
     */
    private function getDepartmentPerformance($startDate, $endDate, $departmentId = null)
    {
        return DB::table('appointments')
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->selectRaw('users.department, COUNT(*) as total_appointments,
                        SUM(CASE WHEN appointments.status = "completed" THEN 1 ELSE 0 END) as completed')
            ->whereBetween('appointments.appointment_date', [$startDate, $endDate])
            ->groupBy('users.department')
            ->get();
    }

    /**
     * الحصول على بيانات رضا المرضى
     */
    private function getPatientSatisfactionData($startDate, $endDate)
    {
        // يحتاج إلى نموذج تقييم المرضى
        return [
            'average_rating' => 4.2,
            'total_reviews' => 150,
            'satisfaction_trend' => []
        ];
    }

    /**
     * الحصول على متوسط وقت الانتظار
     */
    private function getAverageWaitTime($startDate, $endDate)
    {
        return DB::table('appointments')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->avg('wait_time') ?? 0;
    }

    /**
     * الحصول على توزيع الأعمار
     */
    private function getAgeDistribution($query)
    {
        return $query->selectRaw('
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN "أقل من 18"
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 30 THEN "18-30"
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 31 AND 50 THEN "31-50"
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 51 AND 70 THEN "51-70"
                ELSE "أكثر من 70"
            END as age_group,
            COUNT(*) as count
        ')
        ->groupBy('age_group')
        ->get();
    }

    /**
     * الحصول على توزيع الجنس
     */
    private function getGenderDistribution($query)
    {
        return $query->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->get();
    }

    /**
     * الحصول على التشخيصات الشائعة
     */
    private function getCommonDiagnoses($startDate, $endDate)
    {
        return DB::table('medical_records')
            ->selectRaw('diagnosis, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('diagnosis')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * الحصول على تدفق المرضى
     */
    private function getPatientFlow($startDate, $endDate)
    {
        return DB::table('appointments')
            ->selectRaw('DATE(appointment_date) as date, COUNT(*) as patient_count')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * الحصول على معدل إعادة الدخول
     */
    private function getReadmissionRate($startDate, $endDate)
    {
        // حساب معدل إعادة الدخول خلال 30 يوم
        return 0.05; // 5% كمثال
    }

    // دوال التأمين
    private function getTotalClaims($startDate, $endDate, $insuranceId = null)
    {
        $query = DB::table('insurance_claims')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($insuranceId) {
            $query->where('insurance_id', $insuranceId);
        }
        
        return $query->count();
    }

    private function getApprovedClaims($startDate, $endDate, $insuranceId = null)
    {
        $query = DB::table('insurance_claims')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($insuranceId) {
            $query->where('insurance_id', $insuranceId);
        }
        
        return $query->count();
    }

    private function getPendingClaims($startDate, $endDate, $insuranceId = null)
    {
        $query = DB::table('insurance_claims')
            ->where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($insuranceId) {
            $query->where('insurance_id', $insuranceId);
        }
        
        return $query->count();
    }

    private function getRejectedClaims($startDate, $endDate, $insuranceId = null)
    {
        $query = DB::table('insurance_claims')
            ->where('status', 'rejected')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($insuranceId) {
            $query->where('insurance_id', $insuranceId);
        }
        
        return $query->count();
    }

    private function getInsuranceRevenue($startDate, $endDate, $insuranceId = null)
    {
        $query = DB::table('payments')
            ->where('payment_method', 'insurance')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($insuranceId) {
            $query->where('insurance_id', $insuranceId);
        }
        
        return $query->sum('amount');
    }

    private function getTopInsuranceCompanies($startDate, $endDate)
    {
        return DB::table('payments')
            ->join('insurances', 'payments.insurance_id', '=', 'insurances.id')
            ->selectRaw('insurances.name, SUM(payments.amount) as total_revenue, COUNT(*) as claim_count')
            ->where('payments.payment_method', 'insurance')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->groupBy('insurances.id', 'insurances.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();
    }

    private function getClaimProcessingTime($startDate, $endDate)
    {
        return DB::table('insurance_claims')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('processed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, processed_at)) as avg_processing_days')
            ->value('avg_processing_days') ?? 0;
    }

    /**
     * الحصول على الملخص المالي
     */
    private function getFinancialSummary($period)
    {
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();

        return [
            'total_revenue' => Payment::whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
            'outstanding_receivables' => Payment::where('status', 'pending')->sum('amount'),
            'insurance_pending' => Payment::where('payment_method', 'insurance')->where('status', 'pending')->sum('amount'),
            'revenue_growth' => $this->calculateRevenueGrowth($period)
        ];
    }

    /**
     * الحصول على الملخص التشغيلي
     */
    private function getOperationalSummary($period)
    {
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();

        return [
            'total_appointments' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->count(),
            'completed_appointments' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->where('status', 'completed')->count(),
            'cancelled_appointments' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->where('status', 'cancelled')->count(),
            'bed_occupancy_rate' => $this->getBedOccupancyRate()
        ];
    }

    /**
     * الحصول على الملخص السريري
     */
    private function getClinicalSummary($period)
    {
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();

        return [
            'lab_tests_ordered' => LabTest::whereBetween('created_at', [$startDate, $endDate])->count(),
            'radiology_studies' => RadiologyStudy::whereBetween('created_at', [$startDate, $endDate])->count(),
            'prescriptions_written' => Prescription::whereBetween('created_at', [$startDate, $endDate])->count(),
            'critical_results' => $this->getCriticalResultsCount($startDate, $endDate)
        ];
    }

    /**
     * الحصول على ملخص الأداء
     */
    private function getPerformanceSummary($period)
    {
        return [
            'patient_satisfaction' => 4.2,
            'staff_utilization' => 85,
            'average_wait_time' => 25,
            'readmission_rate' => 5.2
        ];
    }

    /**
     * حساب نمو الإيرادات
     */
    private function calculateRevenueGrowth($period)
    {
        $currentPeriodStart = $this->getPeriodStartDate($period);
        $currentPeriodEnd = Carbon::now();
        
        $previousPeriodStart = $this->getPreviousPeriodStart($period);
        $previousPeriodEnd = $currentPeriodStart->copy()->subDay();

        $currentRevenue = Payment::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->sum('amount');
        $previousRevenue = Payment::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->sum('amount');

        if ($previousRevenue > 0) {
            return (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
        }

        return 0;
    }

    /**
     * الحصول على بداية الفترة السابقة
     */
    private function getPreviousPeriodStart($period)
    {
        switch ($period) {
            case 'daily':
                return Carbon::yesterday();
            case 'weekly':
                return Carbon::now()->subWeek()->startOfWeek();
            case 'monthly':
                return Carbon::now()->subMonth()->startOfMonth();
            case 'quarterly':
                return Carbon::now()->subQuarter()->startOfQuarter();
            case 'yearly':
                return Carbon::now()->subYear()->startOfYear();
            default:
                return Carbon::now()->subMonth()->startOfMonth();
        }
    }

    /**
     * الحصول على معدل إشغال الأسرة
     */
    private function getBedOccupancyRate()
    {
        // يحتاج إلى نموذج الأسرة والغرف
        return 75; // 75% كمثال
    }

    /**
     * الحصول على عدد النتائج الحرجة
     */
    private function getCriticalResultsCount($startDate, $endDate)
    {
        return LabTest::whereBetween('created_at', [$startDate, $endDate])
            ->where('is_critical', true)
            ->count();
    }

    /**
     * تصدير التقرير التنفيذي كـ PDF
     */
    private function exportExecutivePDF($data, $period)
    {
        $pdf = PDF::loadView('reports.pdf.executive-summary', compact('data', 'period'));
        return $pdf->download('executive-summary-' . $period . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * تصدير تقرير الأداء كـ PDF
     */
    private function exportPerformancePDF($data, $period)
    {
        $pdf = PDF::loadView('reports.pdf.performance', compact('data', 'period'));
        return $pdf->download('performance-report-' . $period . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * تصدير تقرير إحصائيات المرضى كـ PDF
     */
    private function exportPatientStatisticsPDF($data, $period)
    {
        $pdf = PDF::loadView('reports.pdf.patient-statistics', compact('data', 'period'));
        return $pdf->download('patient-statistics-' . $period . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * تصدير تقرير التأمين كـ PDF
     */
    private function exportInsurancePDF($data, $period)
    {
        $pdf = PDF::loadView('reports.pdf.insurance', compact('data', 'period'));
        return $pdf->download('insurance-report-' . $period . '-' . date('Y-m-d') . '.pdf');
    }

    /**
     * تصدير تقرير المخزون كـ PDF
     */
    private function exportInventoryPDF($data)
    {
        $pdf = PDF::loadView('reports.pdf.inventory', compact('data'));
        return $pdf->download('inventory-report-' . date('Y-m-d') . '.pdf');
    }
}