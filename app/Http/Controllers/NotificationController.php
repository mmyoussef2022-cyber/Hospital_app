<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\DeviceToken;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * عرض قائمة الإشعارات
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = Notification::where('recipient_type', get_class($user))
            ->where('recipient_id', $user->id)
            ->when($request->type, function($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->status, function($query, $status) {
                if ($status === 'unread') {
                    return $query->whereNull('read_at');
                } elseif ($status === 'read') {
                    return $query->whereNotNull('read_at');
                }
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Notification::forUser($user->id)->count(),
            'unread' => Notification::forUser($user->id)->unread()->count(),
            'critical' => Notification::forUser($user->id)->critical()->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * عرض إشعار واحد
     */
    public function show(Notification $notification)
    {
        $this->authorize('view', $notification);
        
        // تحديد الإشعار كمقروء
        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return view('notifications.show', compact('notification'));
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(Notification $notification)
    {
        $this->authorize('update', $notification);
        
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * تحديد جميع الإشعارات كمقروءة
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('recipient_type', get_class($user))
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * حذف إشعار
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);
        
        $notification->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * إرسال إشعار جديد (للمدراء)
     */
    public function store(Request $request)
    {
        $this->authorize('create', Notification::class);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string',
            'priority' => 'required|in:low,normal,high,critical',
            'recipients' => 'required|array',
            'recipients.*' => 'required|integer',
            'channels' => 'array',
            'scheduled_at' => 'nullable|date|after:now'
        ]);

        // تحديد المستلمين
        $recipients = collect($request->recipients)->map(function($id) {
            return \App\Models\User::find($id);
        })->filter();

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'priority' => $request->priority,
            'recipients' => $recipients->toArray(),
            'channels' => $request->channels,
            'sender_id' => Auth::id()
        ];

        if ($request->scheduled_at) {
            $notification = $this->notificationService->schedule($data, $request->scheduled_at);
        } else {
            $notification = $this->notificationService->send($data);
        }

        return response()->json([
            'success' => true,
            'notification_id' => $notification->id
        ]);
    }

    /**
     * عرض تفضيلات الإشعارات
     */
    public function preferences()
    {
        $user = Auth::user();
        
        $preferences = NotificationPreference::forUser(get_class($user), $user->id)->get();
        
        // إنشاء تفضيلات افتراضية إذا لم تكن موجودة
        if ($preferences->isEmpty()) {
            NotificationPreference::createDefaultPreferences(get_class($user), $user->id);
            $preferences = NotificationPreference::forUser(get_class($user), $user->id)->get();
        }

        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * تحديث تفضيلات الإشعارات
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*.notification_type' => 'required|string',
            'preferences.*.enabled' => 'boolean',
            'preferences.*.channels' => 'array',
            'preferences.*.quiet_hours_start' => 'nullable|date_format:H:i',
            'preferences.*.quiet_hours_end' => 'nullable|date_format:H:i',
            'preferences.*.escalation_enabled' => 'boolean',
            'preferences.*.escalation_delay_minutes' => 'integer|min:5|max:1440'
        ]);

        foreach ($request->preferences as $prefData) {
            NotificationPreference::updateOrCreate(
                [
                    'user_type' => get_class($user),
                    'user_id' => $user->id,
                    'notification_type' => $prefData['notification_type']
                ],
                [
                    'enabled' => $prefData['enabled'] ?? true,
                    'channels' => $prefData['channels'] ?? [],
                    'quiet_hours_start' => $prefData['quiet_hours_start'] ?? null,
                    'quiet_hours_end' => $prefData['quiet_hours_end'] ?? null,
                    'escalation_enabled' => $prefData['escalation_enabled'] ?? false,
                    'escalation_delay_minutes' => $prefData['escalation_delay_minutes'] ?? 60
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * تسجيل رمز جهاز للإشعارات الفورية
     */
    public function registerDevice(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:android,ios,web'
        ]);

        $user = Auth::user();
        
        DeviceToken::updateOrCreate(
            [
                'user_type' => get_class($user),
                'user_id' => $user->id,
                'token' => $request->token
            ],
            [
                'platform' => $request->platform,
                'is_active' => true,
                'last_used_at' => now()
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * إلغاء تسجيل رمز جهاز
     */
    public function unregisterDevice(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        DeviceToken::where('token', $request->token)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * الحصول على إحصائيات الإشعارات
     */
    public function statistics()
    {
        $this->authorize('viewAny', Notification::class);
        
        $stats = $this->notificationService->getStatistics('today');
        $weeklyStats = $this->notificationService->getStatistics('week');
        $monthlyStats = $this->notificationService->getStatistics('month');

        return view('notifications.statistics', compact('stats', 'weeklyStats', 'monthlyStats'));
    }

    /**
     * إرسال إشعار اختبار
     */
    public function sendTest(Request $request)
    {
        $this->authorize('create', Notification::class);
        
        $request->validate([
            'channel' => 'required|in:whatsapp,sms,push,email,in_app',
            'recipient_id' => 'required|integer'
        ]);

        $recipient = \App\Models\User::findOrFail($request->recipient_id);
        
        $notification = $this->notificationService->sendQuick(
            'إشعار اختبار',
            'هذا إشعار اختبار للتأكد من عمل النظام بشكل صحيح.',
            [$recipient],
            'system_alert',
            'normal'
        );

        return response()->json([
            'success' => true,
            'notification_id' => $notification->id
        ]);
    }

    /**
     * API للحصول على الإشعارات (للتطبيق المحمول)
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        
        $notifications = Notification::where('recipient_type', get_class($user))
            ->where('recipient_id', $user->id)
            ->when($request->unread_only, function($query) {
                return $query->whereNull('read_at');
            })
            ->orderBy('created_at', 'desc')
            ->limit($request->limit ?? 50)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::forUser($user->id)->unread()->count()
        ]);
    }
}