<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use App\Models\SecurityLog;
use App\Models\LoginAttempt;
use App\Models\User;

class SecurityService
{
    /**
     * تسجيل العمليات الأمنية
     */
    public function logSecurityEvent($event, $description, $level = 'info', $userId = null, $additionalData = [])
    {
        try {
            $userId = $userId ?? Auth::id();
            
            SecurityLog::create([
                'user_id' => $userId,
                'event_type' => $event,
                'description' => $description,
                'level' => $level,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'additional_data' => json_encode($additionalData),
                'created_at' => now()
            ]);
            
            // تسجيل في ملف اللوج أيضاً
            Log::channel('security')->{$level}("Security Event: {$event}", [
                'user_id' => $userId,
                'description' => $description,
                'ip' => Request::ip(),
                'additional_data' => $additionalData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to log security event: ' . $e->getMessage());
        }
    }
    
    /**
     * تسجيل محاولات تسجيل الدخول
     */
    public function logLoginAttempt($email, $success = false, $reason = null)
    {
        try {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'success' => $success,
                'failure_reason' => $reason,
                'attempted_at' => now()
            ]);
            
            if (!$success) {
                $this->checkSuspiciousActivity($email, Request::ip());
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to log login attempt: ' . $e->getMessage());
        }
    }
    
    /**
     * فحص النشاط المشبوه
     */
    public function checkSuspiciousActivity($email, $ip)
    {
        // فحص محاولات تسجيل الدخول الفاشلة في آخر 15 دقيقة
        $recentFailures = LoginAttempt::where('email', $email)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes(15))
            ->count();
            
        if ($recentFailures >= 5) {
            $this->logSecurityEvent(
                'suspicious_login_attempts',
                "Multiple failed login attempts for email: {$email}",
                'warning',
                null,
                ['email' => $email, 'ip' => $ip, 'attempts' => $recentFailures]
            );
            
            // إرسال تنبيه للإدارة
            $this->sendSecurityAlert('Multiple Failed Login Attempts', [
                'email' => $email,
                'ip' => $ip,
                'attempts' => $recentFailures,
                'time' => now()->format('Y-m-d H:i:s')
            ]);
        }
        
        // فحص محاولات من نفس IP
        $ipFailures = LoginAttempt::where('ip_address', $ip)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subHour())
            ->count();
            
        if ($ipFailures >= 10) {
            $this->logSecurityEvent(
                'suspicious_ip_activity',
                "Multiple failed login attempts from IP: {$ip}",
                'critical',
                null,
                ['ip' => $ip, 'attempts' => $ipFailures]
            );
        }
    }
    
    /**
     * تشفير البيانات الحساسة
     */
    public function encryptSensitiveData($data)
    {
        try {
            return encrypt($data);
        } catch (\Exception $e) {
            Log::error('Failed to encrypt sensitive data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * فك تشفير البيانات الحساسة
     */
    public function decryptSensitiveData($encryptedData)
    {
        try {
            return decrypt($encryptedData);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt sensitive data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * إرسال تنبيهات الأمان
     */
    public function sendSecurityAlert($title, $data)
    {
        try {
            // الحصول على المديرين
            $admins = User::role(['admin', 'super-admin'])->get();
            
            foreach ($admins as $admin) {
                // إرسال إشعار داخلي
                $admin->notify(new \App\Notifications\SecurityAlert($title, $data));
            }
            
            // تسجيل في اللوج
            Log::channel('security')->critical("Security Alert: {$title}", $data);
            
        } catch (\Exception $e) {
            Log::error('Failed to send security alert: ' . $e->getMessage());
        }
    }
    
    /**
     * فحص صحة النظام
     */
    public function performSecurityHealthCheck()
    {
        $issues = [];
        
        try {
            // فحص محاولات تسجيل الدخول المشبوهة
            $suspiciousLogins = LoginAttempt::where('success', false)
                ->where('attempted_at', '>=', now()->subDay())
                ->count();
                
            if ($suspiciousLogins > 50) {
                $issues[] = "High number of failed login attempts: {$suspiciousLogins}";
            }
            
            // فحص المستخدمين غير النشطين لفترة طويلة
            $inactiveUsers = User::where('last_login_at', '<', now()->subDays(90))
                ->whereNotNull('last_login_at')
                ->count();
                
            if ($inactiveUsers > 0) {
                $issues[] = "Users inactive for 90+ days: {$inactiveUsers}";
            }
            
            // فحص الصلاحيات المفرطة
            $superAdmins = User::role('super-admin')->count();
            if ($superAdmins > 3) {
                $issues[] = "Too many super admins: {$superAdmins}";
            }
            
            // تسجيل النتائج
            $this->logSecurityEvent(
                'security_health_check',
                'Security health check completed',
                empty($issues) ? 'info' : 'warning',
                null,
                ['issues_found' => count($issues), 'issues' => $issues]
            );
            
            return [
                'status' => empty($issues) ? 'healthy' : 'issues_found',
                'issues' => $issues,
                'checked_at' => now()
            ];
            
        } catch (\Exception $e) {
            Log::error('Security health check failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'checked_at' => now()
            ];
        }
    }
    
    /**
     * تنظيف السجلات القديمة
     */
    public function cleanupOldLogs($daysToKeep = 90)
    {
        try {
            $cutoffDate = now()->subDays($daysToKeep);
            
            // حذف سجلات الأمان القديمة
            $deletedSecurity = SecurityLog::where('created_at', '<', $cutoffDate)->delete();
            
            // حذف محاولات تسجيل الدخول القديمة
            $deletedLogins = LoginAttempt::where('attempted_at', '<', $cutoffDate)->delete();
            
            $this->logSecurityEvent(
                'logs_cleanup',
                'Old security logs cleaned up',
                'info',
                null,
                [
                    'security_logs_deleted' => $deletedSecurity,
                    'login_attempts_deleted' => $deletedLogins,
                    'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s')
                ]
            );
            
            return [
                'security_logs_deleted' => $deletedSecurity,
                'login_attempts_deleted' => $deletedLogins
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to cleanup old logs: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * إنشاء نسخة احتياطية من البيانات الحساسة
     */
    public function createSecurityBackup()
    {
        try {
            $backupData = [
                'users' => User::with('roles', 'permissions')->get()->toArray(),
                'security_logs' => SecurityLog::where('created_at', '>=', now()->subDays(30))->get()->toArray(),
                'login_attempts' => LoginAttempt::where('attempted_at', '>=', now()->subDays(30))->get()->toArray(),
                'created_at' => now()->toISOString()
            ];
            
            $filename = 'security_backup_' . now()->format('Y_m_d_H_i_s') . '.json';
            $path = storage_path('app/backups/security/' . $filename);
            
            // إنشاء المجلد إذا لم يكن موجوداً
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            // تشفير البيانات قبل الحفظ
            $encryptedData = $this->encryptSensitiveData(json_encode($backupData));
            file_put_contents($path, $encryptedData);
            
            $this->logSecurityEvent(
                'security_backup_created',
                'Security backup created successfully',
                'info',
                null,
                ['filename' => $filename, 'size' => filesize($path)]
            );
            
            return $filename;
            
        } catch (\Exception $e) {
            Log::error('Failed to create security backup: ' . $e->getMessage());
            return false;
        }
    }
}