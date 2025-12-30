<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware للتحقق من صحة علاقات المريض ومعالجة الأخطاء
 */
class ValidatePatientRelationships
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // التحقق من وجود أخطاء في الاستجابة
            if ($response->getStatusCode() >= 500) {
                $this->logRelationshipError($request, 'Server error occurred');
            }
            
            return $response;
            
        } catch (QueryException $e) {
            // معالجة أخطاء قاعدة البيانات المتعلقة بالعلاقات
            return $this->handleDatabaseError($request, $e);
            
        } catch (\Exception $e) {
            // معالجة الأخطاء العامة
            return $this->handleGeneralError($request, $e);
        }
    }

    /**
     * معالجة أخطاء قاعدة البيانات
     */
    private function handleDatabaseError(Request $request, QueryException $e): Response
    {
        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();

        // تحديد نوع الخطأ
        $errorType = $this->identifyErrorType($errorMessage);
        
        // تسجيل الخطأ
        $this->logRelationshipError($request, $errorMessage, [
            'error_type' => $errorType,
            'error_code' => $errorCode,
            'sql' => $e->getSql() ?? 'N/A',
            'bindings' => $e->getBindings() ?? []
        ]);

        // إرجاع استجابة مناسبة حسب نوع الخطأ
        return $this->createErrorResponse($errorType, $request);
    }

    /**
     * معالجة الأخطاء العامة
     */
    private function handleGeneralError(Request $request, \Exception $e): Response
    {
        $this->logRelationshipError($request, $e->getMessage(), [
            'exception_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.',
                'error_code' => 'GENERAL_ERROR'
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.')
            ->withInput();
    }

    /**
     * تحديد نوع الخطأ من رسالة الخطأ
     */
    private function identifyErrorType(string $errorMessage): string
    {
        $patterns = [
            'UNDEFINED_RELATIONSHIP' => [
                'Call to undefined relationship',
                'Relationship [.*] does not exist',
                'Method .* does not exist'
            ],
            'UNKNOWN_COLUMN' => [
                'Unknown column',
                'Column not found',
                "doesn't exist"
            ],
            'FOREIGN_KEY_CONSTRAINT' => [
                'foreign key constraint',
                'Cannot add or update a child row',
                'Cannot delete or update a parent row'
            ],
            'TABLE_NOT_FOUND' => [
                "Table .* doesn't exist",
                'Base table or view not found'
            ],
            'DUPLICATE_ENTRY' => [
                'Duplicate entry',
                'Integrity constraint violation'
            ]
        ];

        foreach ($patterns as $type => $typePatterns) {
            foreach ($typePatterns as $pattern) {
                if (preg_match("/$pattern/i", $errorMessage)) {
                    return $type;
                }
            }
        }

        return 'UNKNOWN_DATABASE_ERROR';
    }

    /**
     * إنشاء استجابة خطأ مناسبة
     */
    private function createErrorResponse(string $errorType, Request $request): Response
    {
        $messages = [
            'UNDEFINED_RELATIONSHIP' => 'خطأ في تحميل بيانات المريض. يرجى المحاولة مرة أخرى.',
            'UNKNOWN_COLUMN' => 'خطأ في هيكل قاعدة البيانات. يرجى الاتصال بالدعم الفني.',
            'FOREIGN_KEY_CONSTRAINT' => 'لا يمكن تنفيذ هذا الإجراء بسبب ارتباط البيانات.',
            'TABLE_NOT_FOUND' => 'خطأ في قاعدة البيانات. يرجى الاتصال بالدعم الفني.',
            'DUPLICATE_ENTRY' => 'البيانات موجودة مسبقاً. يرجى التحقق من المعلومات.',
            'UNKNOWN_DATABASE_ERROR' => 'حدث خطأ في قاعدة البيانات. يرجى المحاولة مرة أخرى.'
        ];

        $message = $messages[$errorType] ?? $messages['UNKNOWN_DATABASE_ERROR'];
        $statusCode = $this->getStatusCodeForErrorType($errorType);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => $errorType
            ], $statusCode);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * الحصول على رمز الحالة المناسب لنوع الخطأ
     */
    private function getStatusCodeForErrorType(string $errorType): int
    {
        return match($errorType) {
            'UNDEFINED_RELATIONSHIP', 'UNKNOWN_COLUMN', 'TABLE_NOT_FOUND' => 500,
            'FOREIGN_KEY_CONSTRAINT' => 409,
            'DUPLICATE_ENTRY' => 422,
            default => 500
        };
    }

    /**
     * تسجيل أخطاء العلاقات
     */
    private function logRelationshipError(Request $request, string $message, array $context = []): void
    {
        $logContext = array_merge([
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ], $context);

        Log::error("Patient Relationship Error: {$message}", $logContext);

        // إضافة تسجيل خاص لأخطاء العلاقات
        Log::channel('relationships')->error($message, $logContext);
    }

    /**
     * التحقق من صحة العلاقات المطلوبة
     */
    public static function validateRequiredRelationships(array $relations, $model): array
    {
        $errors = [];
        
        foreach ($relations as $relation) {
            try {
                if (!method_exists($model, $relation)) {
                    $errors[] = "العلاقة '{$relation}' غير موجودة في النموذج " . get_class($model);
                    continue;
                }

                $relationInstance = $model->$relation();
                if (!$relationInstance instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $errors[] = "'{$relation}' ليست علاقة صحيحة في النموذج " . get_class($model);
                }
                
            } catch (\Exception $e) {
                $errors[] = "خطأ في التحقق من العلاقة '{$relation}': " . $e->getMessage();
            }
        }
        
        return $errors;
    }

    /**
     * إنشاء تقرير صحة العلاقات
     */
    public static function generateRelationshipHealthReport(): array
    {
        $models = [
            \App\Models\Patient::class => ['insurancePolicy', 'insuranceCompany', 'appointments', 'medicalRecords'],
            \App\Models\Appointment::class => ['patient', 'doctor', 'parentAppointment', 'followUpAppointments', 'medicalRecord'],
            \App\Models\MedicalRecord::class => ['patient', 'doctor', 'appointment', 'followUpAppointments']
        ];

        $report = [];
        
        foreach ($models as $modelClass => $relations) {
            $model = new $modelClass;
            $modelName = class_basename($modelClass);
            
            $report[$modelName] = [
                'model_class' => $modelClass,
                'total_relations' => count($relations),
                'valid_relations' => [],
                'invalid_relations' => [],
                'errors' => []
            ];

            foreach ($relations as $relation) {
                try {
                    if (method_exists($model, $relation)) {
                        $relationInstance = $model->$relation();
                        if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                            $report[$modelName]['valid_relations'][] = $relation;
                        } else {
                            $report[$modelName]['invalid_relations'][] = $relation;
                            $report[$modelName]['errors'][] = "'{$relation}' is not a valid relation";
                        }
                    } else {
                        $report[$modelName]['invalid_relations'][] = $relation;
                        $report[$modelName]['errors'][] = "Method '{$relation}' does not exist";
                    }
                } catch (\Exception $e) {
                    $report[$modelName]['invalid_relations'][] = $relation;
                    $report[$modelName]['errors'][] = "Error checking '{$relation}': " . $e->getMessage();
                }
            }
        }
        
        return $report;
    }
}