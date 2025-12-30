<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\SecurityService;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // فحص Rate Limiting
        $this->checkRateLimit($request);

        // تسجيل الطلب
        $this->logRequest($request);

        // فحص الأمان
        $this->performSecurityChecks($request);

        $response = $next($request);

        // تسجيل الاستجابة
        $this->logResponse($request, $response);

        return $response;
    }

    /**
     * فحص Rate Limiting
     */
    protected function checkRateLimit(Request $request)
    {
        $key = 'security_rate_limit:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 100)) {
            $this->securityService->logSecurityEvent(
                'rate_limit_exceeded',
                'Rate limit exceeded for IP: ' . $request->ip(),
                'warning',
                Auth::id(),
                ['ip' => $request->ip(), 'url' => $request->fullUrl()]
            );

            abort(429, 'Too Many Requests');
        }

        RateLimiter::hit($key, 3600); // 1 hour window
    }

    /**
     * تسجيل الطلب
     */
    protected function logRequest(Request $request)
    {
        // تسجيل الطلبات الحساسة فقط
        $sensitiveRoutes = [
            'login', 'logout', 'password', 'admin', 'users', 'roles', 'permissions'
        ];

        $isSensitive = collect($sensitiveRoutes)->some(function ($route) use ($request) {
            return str_contains($request->path(), $route);
        });

        if ($isSensitive) {
            $this->securityService->logSecurityEvent(
                'sensitive_request',
                'Sensitive route accessed: ' . $request->path(),
                'info',
                Auth::id(),
                [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'ip' => $request->ip()
                ]
            );
        }
    }

    /**
     * فحص الأمان
     */
    protected function performSecurityChecks(Request $request)
    {
        // فحص User Agent المشبوه
        $userAgent = $request->userAgent();
        $suspiciousAgents = ['bot', 'crawler', 'spider', 'scraper'];
        
        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                $this->securityService->logSecurityEvent(
                    'suspicious_user_agent',
                    'Suspicious user agent detected: ' . $userAgent,
                    'warning',
                    Auth::id(),
                    ['user_agent' => $userAgent, 'ip' => $request->ip()]
                );
                break;
            }
        }

        // فحص محاولات SQL Injection
        $this->checkSqlInjection($request);

        // فحص محاولات XSS
        $this->checkXssAttempts($request);
    }

    /**
     * فحص محاولات SQL Injection
     */
    protected function checkSqlInjection(Request $request)
    {
        $sqlPatterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\bor\b.*1.*=.*1)/i'
        ];

        $allInput = json_encode($request->all());

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $allInput)) {
                $this->securityService->logSecurityEvent(
                    'sql_injection_attempt',
                    'Potential SQL injection attempt detected',
                    'critical',
                    Auth::id(),
                    [
                        'ip' => $request->ip(),
                        'input' => $request->all(),
                        'pattern_matched' => $pattern
                    ]
                );

                // إرسال تنبيه فوري
                $this->securityService->sendSecurityAlert(
                    'SQL Injection Attempt Detected',
                    [
                        'ip' => $request->ip(),
                        'user_id' => Auth::id(),
                        'url' => $request->fullUrl(),
                        'time' => now()->format('Y-m-d H:i:s')
                    ]
                );

                abort(403, 'Forbidden');
            }
        }
    }

    /**
     * فحص محاولات XSS
     */
    protected function checkXssAttempts(Request $request)
    {
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i'
        ];

        $allInput = json_encode($request->all());

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $allInput)) {
                $this->securityService->logSecurityEvent(
                    'xss_attempt',
                    'Potential XSS attempt detected',
                    'critical',
                    Auth::id(),
                    [
                        'ip' => $request->ip(),
                        'input' => $request->all(),
                        'pattern_matched' => $pattern
                    ]
                );

                abort(403, 'Forbidden');
            }
        }
    }

    /**
     * تسجيل الاستجابة
     */
    protected function logResponse(Request $request, Response $response)
    {
        // تسجيل الاستجابات مع أخطاء الأمان
        if ($response->getStatusCode() >= 400) {
            $this->securityService->logSecurityEvent(
                'error_response',
                'Error response: ' . $response->getStatusCode(),
                'warning',
                Auth::id(),
                [
                    'status_code' => $response->getStatusCode(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip()
                ]
            );
        }
    }
}