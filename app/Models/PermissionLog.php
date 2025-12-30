<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'permission_type',
        'permission_name',
        'old_data',
        'new_data',
        'granted_by',
        'reason',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // العلاقات
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    // النطاقات
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByPermissionType($query, string $type)
    {
        return $query->where('permission_type', $type);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSecurityEvents($query)
    {
        return $query->whereIn('action', [
            'unauthorized_access',
            'security_alert_sent',
            'role_assigned',
            'role_revoked',
            'permissions_delegated'
        ]);
    }

    // الخصائص المحسوبة
    public function getActionDisplayAttribute(): string
    {
        $actions = [
            'user_created' => 'إنشاء مستخدم',
            'user_updated' => 'تحديث مستخدم',
            'user_login' => 'تسجيل دخول',
            'user_logout' => 'تسجيل خروج',
            'role_assigned' => 'تعيين دور',
            'role_revoked' => 'إلغاء دور',
            'role_expired' => 'انتهاء صلاحية دور',
            'permissions_delegated' => 'تفويض صلاحيات',
            'unauthorized_access' => 'وصول غير مصرح',
            'security_alert_sent' => 'تنبيه أمني'
        ];

        return $actions[$this->action] ?? $this->action;
    }

    public function getPermissionTypeDisplayAttribute(): string
    {
        $types = [
            'user' => 'مستخدم',
            'role' => 'دور',
            'permission' => 'صلاحية',
            'delegation' => 'تفويض',
            'security' => 'أمان',
            'authentication' => 'مصادقة'
        ];

        return $types[$this->permission_type] ?? $this->permission_type;
    }

    public function getRiskLevelAttribute(): string
    {
        $highRiskActions = [
            'unauthorized_access',
            'security_alert_sent',
            'role_assigned',
            'permissions_delegated'
        ];

        $mediumRiskActions = [
            'role_revoked',
            'user_updated'
        ];

        if (in_array($this->action, $highRiskActions)) {
            return 'high';
        } elseif (in_array($this->action, $mediumRiskActions)) {
            return 'medium';
        }

        return 'low';
    }

    public function getRiskLevelDisplayAttribute(): string
    {
        $levels = [
            'high' => 'عالي',
            'medium' => 'متوسط',
            'low' => 'منخفض'
        ];

        return $levels[$this->risk_level] ?? 'غير محدد';
    }

    public function getRiskColorAttribute(): string
    {
        $colors = [
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'success'
        ];

        return $colors[$this->risk_level] ?? 'secondary';
    }

    // دوال مساعدة
    public function hasChanges(): bool
    {
        return !empty($this->old_data) || !empty($this->new_data);
    }

    public function getChangedFields(): array
    {
        if (!$this->hasChanges()) {
            return [];
        }

        $changes = [];
        
        if (is_array($this->old_data) && is_array($this->new_data)) {
            foreach ($this->new_data as $key => $newValue) {
                if (isset($this->old_data[$key]) && $this->old_data[$key] !== $newValue) {
                    $changes[$key] = [
                        'old' => $this->old_data[$key],
                        'new' => $newValue
                    ];
                }
            }
        }

        return $changes;
    }

    public function isSecurityEvent(): bool
    {
        return in_array($this->action, [
            'unauthorized_access',
            'security_alert_sent',
            'role_assigned',
            'role_revoked',
            'permissions_delegated'
        ]);
    }

    public function getFormattedDataAttribute(): array
    {
        $formatted = [];

        if ($this->new_data) {
            foreach ($this->new_data as $key => $value) {
                $formatted[$key] = $this->formatValue($key, $value);
            }
        }

        return $formatted;
    }

    private function formatValue(string $key, $value): string
    {
        if (is_null($value)) {
            return 'غير محدد';
        }

        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (in_array($key, ['created_at', 'updated_at', 'assigned_at', 'expires_at', 'login_at', 'logout_at'])) {
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return $value;
            }
        }

        return (string) $value;
    }
}