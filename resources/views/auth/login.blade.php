@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-hospital text-facebook" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-1">نظام إدارة المستشفى</h2>
                        <p class="text-muted">تسجيل الدخول إلى حسابك</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input id="email" type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" 
                                       required autocomplete="email" autofocus
                                       placeholder="أدخل بريدك الإلكتروني">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input id="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password"
                                       placeholder="أدخل كلمة المرور">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                تذكرني
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-facebook btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i>
                                تسجيل الدخول
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a class="text-decoration-none" href="{{ route('password.request') }}">
                                نسيت كلمة المرور؟
                            </a>
                        </div>

                        @can('users.create')
                        <hr class="my-4">
                        <div class="text-center">
                            <p class="text-muted mb-2">ليس لديك حساب؟</p>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus"></i>
                                إنشاء حساب جديد
                            </a>
                        </div>
                        @endcan
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <small class="text-muted">
                    © {{ date('Y') }} نظام إدارة المستشفى. جميع الحقوق محفوظة.
                </small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    }
});
</script>
@endpush
@endsection