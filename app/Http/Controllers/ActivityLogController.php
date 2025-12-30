<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.view');
    }

    /**
     * عرض سجل العمليات
     */
    public function index(Request $request)
    {
        $query = DB::table('activity_log')
                   ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                   ->select([
                       'activity_log.*',
                       'users.name as user_name',
                       'users.email as user_email'
                   ])
                   ->orderBy('activity_log.created_at', 'desc');

        // تطبيق الفلاتر
        if ($request->filled('user_id')) {
            $query->where('activity_log.causer_id', $request->user_id);
        }

        if ($request->filled('log_name')) {
            $query->where('activity_log.log_name', $request->log_name);
        }

        if ($request->filled('description')) {
            $query->where('activity_log.description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('activity_log.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('activity_log.created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20);

        // إحصائيات سريعة
        $stats = [
            'total_logs' => DB::table('activity_log')->count(),
            'today_logs' => DB::table('activity_log')->whereDate('created_at', today())->count(),
            'this_week_logs' => DB::table('activity_log')->where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'unique_users' => DB::table('activity_log')->distinct('causer_id')->count('causer_id')
        ];

        // قائمة المستخدمين للفلتر
        $users = DB::table('users')
                   ->select('id', 'name', 'email')
                   ->whereIn('id', function($query) {
                       $query->select('causer_id')
                             ->from('activity_log')
                             ->whereNotNull('causer_id');
                   })
                   ->orderBy('name')
                   ->get();

        // أنواع السجلات
        $logTypes = DB::table('activity_log')
                     ->select('log_name')
                     ->distinct()
                     ->whereNotNull('log_name')
                     ->orderBy('log_name')
                     ->pluck('log_name');

        return view('activity-logs.index', compact('logs', 'stats', 'users', 'logTypes'));
    }

    /**
     * عرض تفاصيل سجل معين
     */
    public function show($id)
    {
        $log = DB::table('activity_log')
                 ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                 ->select([
                     'activity_log.*',
                     'users.name as user_name',
                     'users.email as user_email'
                 ])
                 ->where('activity_log.id', $id)
                 ->first();

        if (!$log) {
            abort(404);
        }

        // تحويل JSON إلى مصفوفة
        $log->properties = json_decode($log->properties, true);

        return view('activity-logs.show', compact('log'));
    }

    /**
     * حذف السجلات القديمة
     */
    public function clearOld(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $cutoffDate = Carbon::now()->subDays($validated['days']);
        
        $deletedCount = DB::table('activity_log')
                          ->where('created_at', '<', $cutoffDate)
                          ->delete();

        return response()->json([
            'success' => true,
            'message' => "تم حذف {$deletedCount} سجل أقدم من {$validated['days']} يوم"
        ]);
    }
}