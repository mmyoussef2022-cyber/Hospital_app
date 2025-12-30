<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SecurityService;

class SecurityMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'security:maintenance 
                            {--cleanup : Clean up old security logs}
                            {--backup : Create security backup}
                            {--health-check : Perform security health check}
                            {--days=90 : Number of days to keep logs (for cleanup)}';

    /**
     * The console command description.
     */
    protected $description = 'Perform security maintenance tasks';

    protected $securityService;

    /**
     * Create a new command instance.
     */
    public function __construct(SecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 بدء مهام الصيانة الأمنية...');

        if ($this->option('cleanup')) {
            $this->performCleanup();
        }

        if ($this->option('backup')) {
            $this->performBackup();
        }

        if ($this->option('health-check')) {
            $this->performHealthCheck();
        }

        // إذا لم يتم تحديد أي خيار، تنفيذ جميع المهام
        if (!$this->option('cleanup') && !$this->option('backup') && !$this->option('health-check')) {
            $this->performCleanup();
            $this->performBackup();
            $this->performHealthCheck();
        }

        $this->info('✅ تم إكمال مهام الصيانة الأمنية بنجاح!');
    }

    /**
     * تنظيف السجلات القديمة
     */
    protected function performCleanup()
    {
        $this->info('🧹 تنظيف السجلات القديمة...');
        
        $days = (int) $this->option('days');
        $result = $this->securityService->cleanupOldLogs($days);

        if ($result) {
            $this->info("✅ تم حذف {$result['security_logs_deleted']} سجل أمني");
            $this->info("✅ تم حذف {$result['login_attempts_deleted']} محاولة تسجيل دخول");
        } else {
            $this->error('❌ فشل في تنظيف السجلات');
        }
    }

    /**
     * إنشاء نسخة احتياطية
     */
    protected function performBackup()
    {
        $this->info('💾 إنشاء نسخة احتياطية أمنية...');
        
        $filename = $this->securityService->createSecurityBackup();

        if ($filename) {
            $this->info("✅ تم إنشاء النسخة الاحتياطية: {$filename}");
        } else {
            $this->error('❌ فشل في إنشاء النسخة الاحتياطية');
        }
    }

    /**
     * فحص صحة النظام
     */
    protected function performHealthCheck()
    {
        $this->info('🔍 فحص صحة النظام الأمني...');
        
        $result = $this->securityService->performSecurityHealthCheck();

        if ($result['status'] === 'healthy') {
            $this->info('✅ النظام الأمني في حالة جيدة');
        } elseif ($result['status'] === 'issues_found') {
            $this->warn('⚠️  تم العثور على مشاكل أمنية:');
            foreach ($result['issues'] as $issue) {
                $this->warn("   - {$issue}");
            }
        } else {
            $this->error('❌ فشل في فحص صحة النظام: ' . ($result['error'] ?? 'خطأ غير معروف'));
        }
    }
}