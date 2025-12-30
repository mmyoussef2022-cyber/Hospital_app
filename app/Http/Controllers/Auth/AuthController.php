<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        
        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني غير مسجل في النظام.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['حسابك غير مفعل. يرجى التواصل مع الإدارة.'],
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['كلمة المرور غير صحيحة.'],
            ]);
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Update last login time
            $user->update(['last_login_at' => now()]);
            
            // Log successful login
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->intended(route('home'));
        }

        throw ValidationException::withMessages([
            'email' => ['فشل في تسجيل الدخول. يرجى المحاولة مرة أخرى.'],
        ]);
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(RegisterRequest $request)
    {
        $userData = $request->validated();
        $userData['password'] = Hash::make($userData['password']);
        
        // Encrypt national ID
        $userData['national_id'] = encrypt($userData['national_id']);
        
        $user = User::create($userData);
        
        // Assign default role based on job title or department
        $this->assignDefaultRole($user, $request);
        
        // Log user registration
        Log::info('New user registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ]);

        Auth::login($user);
        
        return redirect()->route('home')->with('success', 'تم إنشاء الحساب بنجاح!');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'تم تسجيل الخروج بنجاح');
    }

    /**
     * Show password reset form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle password reset request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.exists' => 'البريد الإلكتروني غير مسجل في النظام'
        ]);

        // Here you would typically send a password reset email
        // For now, we'll just return a success message
        
        return back()->with('success', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني');
    }

    /**
     * Assign default role to new user
     */
    private function assignDefaultRole(User $user, RegisterRequest $request)
    {
        $jobTitle = strtolower($request->job_title ?? '');
        
        // Assign role based on job title
        if (str_contains($jobTitle, 'doctor') || str_contains($jobTitle, 'طبيب')) {
            $user->assignRole('Doctor');
        } elseif (str_contains($jobTitle, 'nurse') || str_contains($jobTitle, 'ممرض')) {
            $user->assignRole('Nurse');
        } elseif (str_contains($jobTitle, 'reception') || str_contains($jobTitle, 'استقبال')) {
            $user->assignRole('Receptionist');
        } elseif (str_contains($jobTitle, 'lab') || str_contains($jobTitle, 'مختبر')) {
            $user->assignRole('Lab Technician');
        } elseif (str_contains($jobTitle, 'radiology') || str_contains($jobTitle, 'أشعة')) {
            $user->assignRole('Radiology Technician');
        } elseif (str_contains($jobTitle, 'account') || str_contains($jobTitle, 'محاسب')) {
            $user->assignRole('Accountant');
        } elseif (str_contains($jobTitle, 'pharmac') || str_contains($jobTitle, 'صيدل')) {
            $user->assignRole('Pharmacist');
        } else {
            // Default role for other staff
            $user->assignRole('Receptionist');
        }
    }

    /**
     * API Login for mobile app
     */
    public function apiLogin(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !$user->is_active || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials or inactive account'
            ], 401);
        }

        // Create API token
        $token = $user->createToken('mobile-app')->plainTextToken;
        
        // Update last login
        $user->update(['last_login_at' => now()]);
        
        return response()->json([
            'user' => $user->load('roles', 'department'),
            'token' => $token,
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }

    /**
     * API Logout for mobile app
     */
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
