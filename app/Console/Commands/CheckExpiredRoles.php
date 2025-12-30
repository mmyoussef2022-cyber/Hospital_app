<?php

namespace App\Console\Commands;

use App\Services\AdvancedPermissionService;
use Illuminate\Console\Command;

class CheckExpiredRoles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'permissions:check-expired {--notify : Send notifications for expired roles}';

    /**
     * The console command description.
     */
    protected $description = 'Check and deactivate expired user roles';

    protected $permissionService;

    public function __construct(AdvancedPermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired roles...');

        $expiredCount = $this->permissionService->checkAndExpireRoles();

        if ($expiredCount > 0) {
            $this->info("Deactivated {$expiredCount} expired roles.");
            
            if ($this->option('notify')) {
                $this->info('Sending notifications for expired roles...');
                // يمكن إضافة منطق الإشعارات هنا
            }
        } else {
            $this->info('No expired roles found.');
        }

        return 0;
    }
}