<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $modules = ['patients', 'appointments', 'medical_records', 'prescriptions', 'laboratory', 'radiology'];
        $actions = ['view', 'create', 'edit', 'delete', 'manage'];
        
        $module = $this->faker->randomElement($modules);
        $action = $this->faker->randomElement($actions);
        $name = "{$module}.{$action}";
        
        return [
            'name' => $name,
            'name_ar' => $name . '_ar',
            'display_name' => ucfirst($module) . ' - ' . ucfirst($action),
            'display_name_ar' => ucfirst($module) . ' - ' . ucfirst($action) . ' عربي',
            'description' => "Permission to {$action} {$module}",
            'description_ar' => "صلاحية {$action} {$module}",
            'module' => $module,
            'module_ar' => $module . '_ar',
            'action' => $action,
            'action_ar' => $action . '_ar',
            'is_active' => true
        ];
    }
}