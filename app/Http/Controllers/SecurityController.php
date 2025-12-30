<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\SecurityService;
use App\Models\SecurityLog;
use App\Models\LoginAttempt;
use App\Models\User;
use Carbon\Carbon;

class SecurityController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->middleware('auth');
        $this->middleware('can:security.view');
        $this->securityService = $securityService;
    }

    /**
     * عرض لوحة تحكم الأمان
     */
    public function dashboard()
    {
        // إحصائيات الأمان
        $stats = [
            'total_security_events' => SecurityLog::count(),
            'critical_events_today' => SecurityLog::critical()
                ->whereDate('created_at', today())
                ->count(),
            'failed_logins_today' => LoginAttempt::failed()
                ->whereDate('attempted_at', today())
                ->count(),
            'unique_ips_today' => LoginAttempt::whereDate('attempted_at', today())
                ->distinct('ip_address')
                ->count(),
        ];

        // الأحداث الحرجة الأخيرة
        $criticalEvents = SecurityLog::critical()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        // محاولات تسجيل الدخول الفاشلة الأخيرة
        $failedLogins = LoginAttempt::failed()
            ->latest('attempted_at')
            ->take(10)
            ->get();

        // إحصائيات الأسبوع الماضي
        $weeklyStats = $this->getWeeklySecurityStats();

        // أكثر IPs نشاطاً
        $topIps = LoginAttempt::select('ip_address', DB::raw('count(*) as attempts'))
            ->where('attempted_at', '>=', now()->subDays(7))
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->take(10)
            ->get();

        return view('security.dashboard', compact(
            'stats',
            'criticalEvents',
            'failedLogins',
            'weeklyStats',
            'topIps'
        ));
    }

    /**
     * عرض سجلات الأمان
     */
    public function logs(Request $request)
    {
        $query = SecurityLog::with('user')->latest();

        // فلترة حسب المستوى
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // فلترة حسب نوع الحدث
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // فلترة حسب المستخدم
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // فلترة حسب IP
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        $logs = $query->paginate(50);

        // قائمة المستخدمين للفلترة
        $users = User::select('id', 'name', 'email')->get();

        // أنواع الأحداث المتاحة
        $eventTypes = SecurityLog::distinct('event_type')->pluck('event_type');

        return view('security.logs', compact('logs', 'users', 'eventTypes'));
    }

    /**
     * عرض محاولات تسجيل الدخول
     */
    public function loginAttempts(Request $request)
    {
        $query = LoginAttempt::latest('attempted_at');

        // فلترة حسب النجاح/الفشل
        if ($request->filled('success')) {
            $query->where('success', $request->success === '1');
        }

        // فلترة حسب البريد الإلكتروني
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // فلترة حسب IP
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('attempted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('attempted_at', '<=', $request->date_to);
        }

        $attempts = $query->paginate(50);

        return view('security.login-attempts', compact('attempts'));
    }

    /**
     * فحص صحة النظام
     */
    public function healthCheck()
    {
        $result = $this->securityService->performSecurityHealthCheck();
        
        return response()->json($result);
    }

    /**
     * إنشاء نسخة احتياطية أمنية
     */
    public function createBackup()
    {
        $filename = $this->securityService->createSecurityBackup();
        
        if ($filename) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء النسخة الاحتياطية بنجاح',
                'filename' => $filename
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في إنشاء النسخة الاحتياطية'
        ], 500);
    }

    /**
     * تنظيف السجلات القديمة
     */
    public function cleanupLogs(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365'
        ]);

        $result = $this->securityService->cleanupOldLogs($request->days);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'تم تنظيف السجلات القديمة بنجاح',
                'deleted' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في تنظيف السجلات'
        ], 500);
    }

    /**
     * عرض تفاصيل حدث أمني
     */
    public function showEvent(SecurityLog $securityLog)
    {
        $securityLog->load('user');
        
        return view('security.event-details', compact('securityLog'));
    }

    /**
     * حظر IP معين
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255'
        ]);

        // تسجيل حظر IP
        $this->securityService->logSecurityEvent(
            'ip_blocked',
            'IP address blocked: ' . $request->ip_address,
            'warning',
            Auth::id(),
            [
                'blocked_ip' => $request->ip_address,
                'reason' => $request->reason,
                'blocked_by' => Auth::user()->name
            ]
        );

        // يمكن إضافة منطق حظر IP هنا (مثل إضافة إلى firewall)

        return response()->json([
            'success' => true,
            'message' => 'تم حظر عنوان IP بنجاح'
        ]);
    }

    /**
     * الحصول على إحصائيات الأسبوع
     */
    protected function getWeeklySecurityStats()
    {
        $days = [];
        $securityEvents = [];
        $loginAttempts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('M d');

            $securityEvents[] = SecurityLog::whereDate('created_at', $date)->count();
            $loginAttempts[] = LoginAttempt::whereDate('attempted_at', $date)->count();
        }

        return [
            'days' => $days,
            'security_events' => $securityEvents,
            'login_attempts' => $loginAttempts
        ];
    }

    /**
     * تصدير سجلات الأمان
     */
    public function exportLogs(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,json,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        $query = SecurityLog::with('user');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        switch ($request->format) {
            case 'csv':
                return $this->exportToCsv($logs);
            case 'json':
                return $this->exportToJson($logs);
            case 'pdf':
                return $this->exportToPdf($logs);
        }
    }

    /**
     * تصدير إلى CSV
     */
    protected function exportToCsv($logs)
    {
        $filename = 'security_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'User', 'Event Type', 'Description', 'Level', 
                'IP Address', 'URL', 'Method', 'Created At'
            ]);

            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'N/A',
                    $log->event_type,
                    $log->description,
                    $log->level,
                    $log->ip_address,
                    $log->url,
                    $log->method,
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * تصدير إلى JSON
     */
    protected function exportToJson($logs)
    {
        $filename = 'security_logs_' . now()->format('Y_m_d_H_i_s') . '.json';
        
        return response()->json($logs->toArray())
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * تصدير إلى PDF
     */
    protected function exportToPdf($logs)
    {
        // يمكن استخدام مكتبة PDF مثل DomPDF
        // هذا مثال بسيط
        $filename = 'security_logs_' . now()->format('Y_m_d_H_i_s') . '.pdf';
        
        // تنفيذ تصدير PDF هنا
        
        return response()->json([
            'message' => 'PDF export will be implemented with PDF library'
        ]);
    }
}