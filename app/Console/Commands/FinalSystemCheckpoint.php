<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\LabOrder;
use App\Models\RadiologyOrder;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\InsuranceCompany;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FinalSystemCheckpoint extends Command
{
    protected $signature = 'system:final-checkpoint';
    protected $description = 'نقطة التفتيش النهائية الشاملة للنظام - Comprehensive Final System Checkpoint';

    private $results = [];
    private $errors = [];
    private $warnings = [];
    private $totalTests = 0;
    private $passedTests = 0;

    public function handle()
    {
        $this->info('🏥 بدء نقطة التفتيش النهائية الشاملة للنظام');
        $this->info('🏥 Starting Comprehensive Final System Checkpoint');
        $this->line(str_repeat('=', 80));

        $this->testDatabaseConnectivity();
        $this->testCoreModels();
        $this->testUserPermissionSystem();
        $this->testReceptionMasterDashboard();
        $this->testCashierAdvancedDashboard();
        $this->testDoctorIntegratedDashboard();
        $this->testLabRadiologySpecializedDashboards();
        $this->testAdvancedPatientSystem();
        $this->testNotificationSystem();
        $this->testEventListenersSystem();
        $this->testReportsSystem();
        $this->testSecuritySystem();
        $this->testIntegrationTestingSystem();
        $this->testRouteAccessibility();
        $this->testSystemPerformance();

        $this->generateFinalReport();
    }

    private function testDatabaseConnectivity()
    {
        $this->logTest('🔌 اختبار الاتصال بقاعدة البيانات');

        try {
            DB::connection()->getPdo();
            $this->logSuccess('✅ الاتصال بقاعدة البيانات يعمل بنجاح');

            // Test main tables exist
            $requiredTables = [
                'users', 'patients', 'appointments', 'doctors', 'medical_records',
                'prescriptions', 'lab_orders', 'radiology_orders', 'invoices', 
                'payments', 'insurance_companies', 'roles', 'permissions'
            ];

            foreach ($requiredTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->logSuccess("✅ جدول {$table} موجود");
                } else {
                    $this->logError("❌ جدول {$table} مفقود");
                }
            }

        } catch (\Exception $e) {
            $this->logError('❌ فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
        }
    }

    private function testCoreModels()
    {
        $this->logTest('📊 اختبار النماذج الأساسية');

        $models = [
            'User' => User::class,
            'Patient' => Patient::class,
            'Appointment' => Appointment::class,
            'Doctor' => Doctor::class,
            'Invoice' => Invoice::class,
            'Payment' => Payment::class,
            'InsuranceCompany' => InsuranceCompany::class
        ];

        foreach ($models as $name => $class) {
            try {
                if (class_exists($class)) {
                    $count = $class::count();
                    $this->logSuccess("✅ نموذج {$name}: {$count} سجل");
                } else {
                    $this->logError("❌ نموذج {$name} غير موجود");
                }
            } catch (\Exception $e) {
                $this->logError("❌ خطأ في نموذج {$name}: " . $e->getMessage());
            }
        }
    }

    private function testUserPermissionSystem()
    {
        $this->logTest('👥 اختبار نظام المستخدمين والصلاحيات المتقدم');

        try {
            $rolesCount = Role::count();
            $permissionsCount = Permission::count();

            $this->logSuccess("✅ الأدوار: {$rolesCount}");
            $this->logSuccess("✅ الصلاحيات: {$permissionsCount}");

            // Test required roles exist
            $requiredRoles = [
                'Super Admin', 'Hospital Admin', 'super_admin', 'admin', 'doctor', 
                'reception', 'cashier', 'lab-technician', 'radiology-technician'
            ];

            foreach ($requiredRoles as $role) {
                if (Role::where('name', $role)->exists()) {
                    $this->logSuccess("✅ دور {$role} موجود");
                } else {
                    $this->logWarning("⚠️ دور {$role} مفقود");
                }
            }

            // Test users with roles
            $usersWithRoles = User::whereHas('roles')->count();
            $this->logSuccess("✅ المستخدمون مع الأدوار: {$usersWithRoles}");

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في نظام الصلاحيات: ' . $e->getMessage());
        }
    }

    private function testReceptionMasterDashboard()
    {
        $this->logTest('🏥 اختبار لوحة الاستقبال الشاملة');

        try {
            // Test controller exists
            if (class_exists('App\Http\Controllers\ReceptionMasterController')) {
                $this->logSuccess('✅ ReceptionMasterController موجود');
            } else {
                $this->logError('❌ ReceptionMasterController مفقود');
            }

            // Test routes exist
            $receptionRoutes = [
                'reception.dashboard'
            ];

            foreach ($receptionRoutes as $routeName) {
                if (Route::has($routeName)) {
                    $this->logSuccess("✅ مسار {$routeName} موجود");
                } else {
                    $this->logWarning("⚠️ مسار {$routeName} مفقود");
                }
            }

            // Test today's appointments
            $todayAppointments = Appointment::whereDate('appointment_date', today())->count();
            $this->logSuccess("✅ مواعيد اليوم: {$todayAppointments}");

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في لوحة الاستقبال: ' . $e->getMessage());
        }
    }

    private function testCashierAdvancedDashboard()
    {
        $this->logTest('💳 اختبار لوحة الخزينة المتقدمة');

        try {
            // Test controller exists
            if (class_exists('App\Http\Controllers\CashierAdvancedController')) {
                $this->logSuccess('✅ CashierAdvancedController موجود');
            } else {
                $this->logError('❌ CashierAdvancedController مفقود');
            }

            // Test payments today
            $paymentsToday = Payment::whereDate('created_at', today())->count();
            $this->logSuccess("✅ مدفوعات اليوم: {$paymentsToday}");

            // Test insurance integration
            $insuranceCompanies = InsuranceCompany::where('status', 'active')->count();
            $this->logSuccess("✅ شركات التأمين النشطة: {$insuranceCompanies}");

            // Test invoices
            $pendingInvoices = Invoice::where('status', 'pending')->count();
            $this->logSuccess("✅ الفواتير المعلقة: {$pendingInvoices}");

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في لوحة الخزينة: ' . $e->getMessage());
        }
    }

    private function testDoctorIntegratedDashboard()
    {
        $this->logTest('👨‍⚕️ اختبار لوحة تحكم الطبيب المتكاملة');

        try {
            // Test controller exists
            if (class_exists('App\Http\Controllers\DoctorIntegratedController')) {
                $this->logSuccess('✅ DoctorIntegratedController موجود');
            } else {
                $this->logError('❌ DoctorIntegratedController مفقود');
            }

            // Test medical procedures
            if (class_exists('App\Models\MedicalRecord')) {
                $medicalRecords = MedicalRecord::whereDate('created_at', today())->count();
                $this->logSuccess("✅ السجلات الطبية اليوم: {$medicalRecords}");
            }

            if (class_exists('App\Models\Prescription')) {
                $prescriptions = Prescription::whereDate('created_at', today())->count();
                $this->logSuccess("✅ الوصفات اليوم: {$prescriptions}");
            }

            // Test doctors
            $activeDoctors = Doctor::where('status', 'active')->count();
            $this->logSuccess("✅ الأطباء النشطون: {$activeDoctors}");

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في لوحة الطبيب: ' . $e->getMessage());
        }
    }

    private function testLabRadiologySpecializedDashboards()
    {
        $this->logTest('🔬 اختبار لوحات تحكم المختبر والأشعة المتخصصة');

        try {
            // Test controllers exist
            if (class_exists('App\Http\Controllers\LabSpecializedController')) {
                $this->logSuccess('✅ LabSpecializedController موجود');
            } else {
                $this->logError('❌ LabSpecializedController مفقود');
            }

            if (class_exists('App\Http\Controllers\RadiologySpecializedController')) {
                $this->logSuccess('✅ RadiologySpecializedController موجود');
            } else {
                $this->logError('❌ RadiologySpecializedController مفقود');
            }

            // Test lab orders
            if (class_exists('App\Models\LabOrder')) {
                $pendingLabOrders = LabOrder::where('status', 'pending')->count();
                $completedLabOrders = LabOrder::where('status', 'completed')->count();
                $this->logSuccess("✅ طلبات المختبر المعلقة: {$pendingLabOrders}");
                $this->logSuccess("✅ طلبات المختبر المكتملة: {$completedLabOrders}");
            }

            // Test radiology orders
            if (class_exists('App\Models\RadiologyOrder')) {
                $pendingRadiologyOrders = RadiologyOrder::where('status', 'pending')->count();
                $completedRadiologyOrders = RadiologyOrder::where('status', 'completed')->count();
                $this->logSuccess("✅ طلبات الأشعة المعلقة: {$pendingRadiologyOrders}");
                $this->logSuccess("✅ طلبات الأشعة المكتملة: {$completedRadiologyOrders}");
            }

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في لوحات المختبر والأشعة: ' . $e->getMessage());
        }
    }

    private function testAdvancedPatientSystem()
    {
        $this->logTest('👥 اختبار نظام المرضى المتقدم');

        try {
            // Test patient statistics
            $totalPatients = Patient::count();
            $activePatients = Patient::where('status', 'active')->count();
            
            $this->logSuccess("✅ إجمالي المرضى: {$totalPatients}");
            $this->logSuccess("✅ المرضى النشطون: {$activePatients}");

            // Test patient classification
            $cashPatients = Patient::where('patient_type', 'cash')->count();
            $insurancePatients = Patient::where('patient_type', 'insurance')->count();

            $this->logSuccess("✅ المرضى النقديون: {$cashPatients}");
            $this->logSuccess("✅ مرضى التأمين: {$insurancePatients}");

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في نظام المرضى: ' . $e->getMessage());
        }
    }

    private function testNotificationSystem()
    {
        $this->logTest('🔔 اختبار نظام الإشعارات المتقدم');

        try {
            // Test notification controller
            if (class_exists('App\Http\Controllers\NotificationController')) {
                $this->logSuccess('✅ NotificationController موجود');
            } else {
                $this->logError('❌ NotificationController مفقود');
            }

            // Test notification service
            if (class_exists('App\Services\NotificationService')) {
                $this->logSuccess('✅ NotificationService موجود');
            } else {
                $this->logError('❌ NotificationService مفقود');
            }

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في نظام الإشعارات: ' . $e->getMessage());
        }
    }

    private function testEventListenersSystem()
    {
        $this->logTest('⚡ اختبار نظام Event Listeners');

        try {
            // Test event listeners
            $listeners = [
                'App\Listeners\AppointmentEventListener',
                'App\Listeners\FinancialEventListener',
                'App\Listeners\MedicalEventListener',
                'App\Listeners\PatientEventListener',
                'App\Listeners\InsuranceEventListener'
            ];

            foreach ($listeners as $listener) {
                if (class_exists($listener)) {
                    $this->logSuccess("✅ مستمع الأحداث {$listener} موجود");
                } else {
                    $this->logWarning("⚠️ مستمع الأحداث {$listener} مفقود");
                }
            }

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في نظام Event Listeners: ' . $e->getMessage());
        }
    }

    private function testReportsSystem()
    {
        $this->logTest('📊 اختبار نظام التقارير المتقدم');

        try {
            // Test reports controller
            if (class_exists('App\Http\Controllers\ReportsAdvancedController')) {
                $this->logSuccess('✅ ReportsAdvancedController موجود');
            } else {
                $this->logError('❌ ReportsAdvancedController مفقود');
            }

            // Test report routes
            $reportRoutes = [
                'reports.executive-summary',
                'reports.financial',
                'reports.performance',
                'reports.patient-statistics',
                'reports.insurance'
            ];

            foreach ($reportRoutes as $route) {
                if (Route::has($route)) {
                    $this->logSuccess("✅ مسار التقرير {$route} موجود");
                } else {
                    $this->logWarning("⚠️ مسار التقرير {$route} مفقود");
                }
            }

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في نظام التقارير: ' . $e->getMessage());
        }
    }

    private function testSecuritySystem()
    {
        $this->logTest('🔒 اختبار نظام الأمان والمراقبة');

        try {
            // Test security controller
            if (class_exists('App\Http\Controllers\SecurityController')) {
                $this->logSuccess('✅ SecurityController موجود');
            } else {
                $this->logError('❌ SecurityController مفقود');
            }

            // Test security service
            if (class_exists('App\Services\SecurityService')) {
                $this->logSuccess('✅ SecurityService موجود');
            } else {
                $this->logError('❌ SecurityService مفقود');
            }

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في نظام الأمان: ' . $e->getMessage());
        }
    }

    private function testIntegrationTestingSystem()
    {
        $this->logTest('🧪 اختبار نظام اختبار التكامل');

        try {
            // Test integration controller
            if (class_exists('App\Http\Controllers\IntegrationTestController')) {
                $this->logSuccess('✅ IntegrationTestController موجود');
            } else {
                $this->logError('❌ IntegrationTestController مفقود');
            }

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في نظام اختبار التكامل: ' . $e->getMessage());
        }
    }

    private function testRouteAccessibility()
    {
        $this->logTest('🛣️ اختبار إمكانية الوصول للمسارات');

        try {
            $routeCollection = Route::getRoutes();
            $totalRoutes = count($routeCollection);

            $this->logSuccess("✅ إجمالي المسارات: {$totalRoutes}");

            // Test critical routes
            $criticalRoutes = [
                'login', 'dashboard', 'patients.index', 'appointments.index',
                'doctors.index', 'invoices.index', 'payments.index'
            ];

            foreach ($criticalRoutes as $route) {
                if (Route::has($route)) {
                    $this->logSuccess("✅ المسار الحرج {$route} موجود");
                } else {
                    $this->logError("❌ المسار الحرج {$route} مفقود");
                }
            }

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في اختبار المسارات: ' . $e->getMessage());
        }
    }

    private function testSystemPerformance()
    {
        $this->logTest('⚡ اختبار أداء النظام');

        try {
            // Test database query performance
            $start = microtime(true);
            Patient::with(['appointments'])->limit(10)->get();
            $queryTime = (microtime(true) - $start) * 1000;

            if ($queryTime < 1000) {
                $this->logSuccess("✅ أداء الاستعلامات جيد: " . number_format($queryTime, 2) . "ms");
            } else {
                $this->logWarning("⚠️ أداء الاستعلامات بطيء: " . number_format($queryTime, 2) . "ms");
            }

            // Test memory usage
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            $this->logSuccess("✅ استخدام الذاكرة: " . number_format($memoryUsage, 2) . "MB");

            // Test PHP version
            $phpVersion = PHP_VERSION;
            $this->logSuccess("✅ إصدار PHP: {$phpVersion}");

        } catch (\Exception $e) {
            $this->logError('❌ خطأ في اختبار الأداء: ' . $e->getMessage());
        }
    }

    private function logTest($message)
    {
        $this->totalTests++;
        $this->line('');
        $this->info($message);
        $this->line(str_repeat('-', 50));
    }

    private function logSuccess($message)
    {
        $this->passedTests++;
        $this->line($message);
        $this->results[] = ['type' => 'success', 'message' => $message];
    }

    private function logError($message)
    {
        $this->line($message);
        $this->errors[] = $message;
        $this->results[] = ['type' => 'error', 'message' => $message];
    }

    private function logWarning($message)
    {
        $this->line($message);
        $this->warnings[] = $message;
        $this->results[] = ['type' => 'warning', 'message' => $message];
    }

    private function generateFinalReport()
    {
        $this->line('');
        $this->line(str_repeat('=', 80));
        $this->info('📋 التقرير النهائي الشامل للنظام');
        $this->info('📋 Comprehensive Final System Report');
        $this->line(str_repeat('=', 80));

        // Overall statistics
        $successRate = ($this->passedTests / max($this->totalTests, 1)) * 100;

        $this->line('');
        $this->info('📊 الإحصائيات العامة:');
        $this->line("   • إجمالي الاختبارات: {$this->totalTests}");
        $this->line("   • الاختبارات الناجحة: {$this->passedTests}");
        $this->line("   • الأخطاء: " . count($this->errors));
        $this->line("   • التحذيرات: " . count($this->warnings));
        $this->line("   • معدل النجاح: " . number_format($successRate, 2) . "%");

        // System status
        $this->line('');
        if (count($this->errors) == 0) {
            $this->info('🎉 حالة النظام: ممتاز - جميع الأنظمة تعمل بنجاح!');
            $this->info('🎉 System Status: EXCELLENT - All systems operational!');
        } elseif (count($this->errors) <= 2) {
            $this->warn('✅ حالة النظام: جيد - أخطاء طفيفة تحتاج إصلاح');
            $this->warn('✅ System Status: GOOD - Minor issues need fixing');
        } else {
            $this->error('⚠️ حالة النظام: يحتاج تحسين - عدة أخطاء تحتاج إصلاح');
            $this->error('⚠️ System Status: NEEDS IMPROVEMENT - Several issues need fixing');
        }

        // Detailed errors
        if (!empty($this->errors)) {
            $this->line('');
            $this->error('❌ الأخطاء التي تحتاج إصلاح:');
            foreach ($this->errors as $error) {
                $this->line("   • {$error}");
            }
        }

        // Warnings
        if (!empty($this->warnings)) {
            $this->line('');
            $this->warn('⚠️ التحذيرات:');
            foreach ($this->warnings as $warning) {
                $this->line("   • {$warning}");
            }
        }

        // Recommendations
        $this->line('');
        $this->info('💡 التوصيات:');

        if (count($this->errors) == 0 && count($this->warnings) == 0) {
            $this->line('   • النظام جاهز للإنتاج!');
            $this->line('   • يمكن بدء التدريب للمستخدمين');
            $this->line('   • تفعيل النسخ الاحتياطي التلقائي');
        } else {
            $this->line('   • إصلاح الأخطاء المذكورة أعلاه');
            $this->line('   • مراجعة التحذيرات وتحسينها');
            $this->line('   • إعادة تشغيل الاختبار بعد الإصلاحات');
        }

        $this->line('   • مراقبة الأداء بانتظام');
        $this->line('   • تحديث النظام دورياً');
        $this->line('   • تدريب المستخدمين على الميزات الجديدة');

        $this->line('');
        $this->info('🏥 انتهت نقطة التفتيش النهائية الشاملة للنظام');
        $this->line('');
    }
}