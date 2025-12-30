<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;

/**
 * Helper class للوصول الآمن للعلاقات في Laravel
 * يوفر طرق آمنة للوصول للعلاقات مع معالجة الأخطاء
 */
class RelationshipHelper
{
    /**
     * الوصول الآمن لعلاقة معينة
     * 
     * @param Model $model النموذج الأساسي
     * @param string $relationName اسم العلاقة
     * @param mixed $default القيمة الافتراضية في حالة الخطأ
     * @return mixed
     */
    public static function safeRelation(Model $model, string $relationName, $default = null)
    {
        try {
            // التحقق من وجود العلاقة في النموذج
            if (!method_exists($model, $relationName)) {
                Log::warning("Relation '{$relationName}' does not exist on model " . get_class($model));
                return $default;
            }

            // محاولة الوصول للعلاقة
            $relation = $model->$relationName();
            
            // التحقق من أن النتيجة هي علاقة صحيحة
            if (!$relation instanceof Relation) {
                Log::warning("Method '{$relationName}' on model " . get_class($model) . " is not a valid relation");
                return $default;
            }

            // إرجاع النتيجة
            return $model->$relationName;
            
        } catch (\Exception $e) {
            Log::error("Error accessing relation '{$relationName}' on model " . get_class($model) . ": " . $e->getMessage());
            return $default;
        }
    }

    /**
     * التحميل الآمن للعلاقات
     * 
     * @param Model $model النموذج الأساسي
     * @param array|string $relations العلاقات المراد تحميلها
     * @param bool $forceReload إجبار إعادة التحميل
     * @return Model
     */
    public static function safeLoad(Model $model, $relations, bool $forceReload = false)
    {
        try {
            $relations = is_string($relations) ? [$relations] : $relations;
            $validRelations = [];

            foreach ($relations as $relationKey => $relationValue) {
                // التعامل مع العلاقات المعقدة (مع closure)
                if (is_numeric($relationKey)) {
                    $relationName = is_string($relationValue) ? $relationValue : $relationKey;
                } else {
                    $relationName = $relationKey;
                }

                // استخراج اسم العلاقة الأساسي (قبل النقطة الأولى)
                $baseRelationName = explode('.', $relationName)[0];

                // التحقق من وجود العلاقة
                if (method_exists($model, $baseRelationName)) {
                    if (is_numeric($relationKey)) {
                        $validRelations[] = $relationValue;
                    } else {
                        $validRelations[$relationKey] = $relationValue;
                    }
                } else {
                    Log::warning("Skipping invalid relation '{$baseRelationName}' on model " . get_class($model));
                }
            }

            // تحميل العلاقات الصحيحة فقط
            if (!empty($validRelations)) {
                if ($forceReload) {
                    return $model->load($validRelations);
                } else {
                    return $model->loadMissing($validRelations);
                }
            }

            return $model;
            
        } catch (\Exception $e) {
            Log::error("Error loading relations on model " . get_class($model) . ": " . $e->getMessage());
            return $model;
        }
    }

    /**
     * التحقق من وجود علاقة معينة
     * 
     * @param Model $model النموذج
     * @param string $relationName اسم العلاقة
     * @return bool
     */
    public static function hasRelation(Model $model, string $relationName): bool
    {
        try {
            if (!method_exists($model, $relationName)) {
                return false;
            }

            $relation = $model->$relationName();
            return $relation instanceof Relation;
            
        } catch (\Exception $e) {
            Log::error("Error checking relation '{$relationName}' on model " . get_class($model) . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على قيمة آمنة من علاقة متداخلة
     * 
     * @param Model $model النموذج الأساسي
     * @param string $path المسار للقيمة (مثل: 'user.profile.name')
     * @param mixed $default القيمة الافتراضية
     * @return mixed
     */
    public static function safeNestedValue(Model $model, string $path, $default = null)
    {
        try {
            $segments = explode('.', $path);
            $current = $model;

            foreach ($segments as $segment) {
                if ($current === null) {
                    return $default;
                }

                if ($current instanceof Model) {
                    // إذا كان العنصر الحالي نموذج، نحاول الوصول للخاصية أو العلاقة
                    if (isset($current->$segment)) {
                        $current = $current->$segment;
                    } elseif (method_exists($current, $segment)) {
                        $current = static::safeRelation($current, $segment, null);
                    } else {
                        return $default;
                    }
                } elseif (is_object($current) && isset($current->$segment)) {
                    $current = $current->$segment;
                } elseif (is_array($current) && isset($current[$segment])) {
                    $current = $current[$segment];
                } else {
                    return $default;
                }
            }

            return $current;
            
        } catch (\Exception $e) {
            Log::error("Error accessing nested value '{$path}' on model " . get_class($model) . ": " . $e->getMessage());
            return $default;
        }
    }

    /**
     * تحميل علاقات متعددة بأمان مع معالجة الأخطاء الفردية
     * 
     * @param Model $model النموذج
     * @param array $relations مصفوفة العلاقات
     * @return array مصفوفة النتائج مع حالة كل علاقة
     */
    public static function safeBulkLoad(Model $model, array $relations): array
    {
        $results = [];
        
        foreach ($relations as $relationName) {
            try {
                if (static::hasRelation($model, $relationName)) {
                    $model->loadMissing($relationName);
                    $results[$relationName] = [
                        'success' => true,
                        'data' => $model->$relationName,
                        'error' => null
                    ];
                } else {
                    $results[$relationName] = [
                        'success' => false,
                        'data' => null,
                        'error' => "Relation '{$relationName}' does not exist"
                    ];
                }
            } catch (\Exception $e) {
                $results[$relationName] = [
                    'success' => false,
                    'data' => null,
                    'error' => $e->getMessage()
                ];
                Log::error("Error loading relation '{$relationName}': " . $e->getMessage());
            }
        }
        
        return $results;
    }

    /**
     * إنشاء استعلام آمن مع العلاقات
     * 
     * @param string $modelClass فئة النموذج
     * @param array $relations العلاقات المراد تحميلها
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function safeQueryWith(string $modelClass, array $relations)
    {
        try {
            $model = new $modelClass;
            $validRelations = [];

            foreach ($relations as $relationKey => $relationValue) {
                $relationName = is_numeric($relationKey) ? $relationValue : $relationKey;
                
                // استخراج اسم العلاقة الأساسي
                $baseRelationName = explode('.', $relationName)[0];

                if (static::hasRelation($model, $baseRelationName)) {
                    if (is_numeric($relationKey)) {
                        $validRelations[] = $relationValue;
                    } else {
                        $validRelations[$relationKey] = $relationValue;
                    }
                } else {
                    Log::warning("Skipping invalid relation '{$baseRelationName}' in query for model {$modelClass}");
                }
            }

            return $modelClass::with($validRelations);
            
        } catch (\Exception $e) {
            Log::error("Error creating safe query for model {$modelClass}: " . $e->getMessage());
            return $modelClass::query();
        }
    }
}