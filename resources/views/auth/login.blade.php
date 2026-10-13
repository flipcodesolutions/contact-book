@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <h2 class="h4 mb-3">Log in</h2>

    <form method="POST" action="{{ route('login') }}" class="js-single-submit">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required autocomplete="current-password">
                <button class="btn btn-outline-secondary js-toggle-password" type="button" id="toggle-password" aria-label="Show password" onclick="togglePasswordVisibility(this); event.stopPropagation();">
                    <svg class="eye-icon" id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="eye-slash-icon" id="eye-slash-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none; pointer-events: none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Log in</button>
    </form>

    <p class="mt-3 mb-0 text-center">
        <a href="{{ route('password.request') }}">Forgot your password?</a>
    </p>

    <script>
        window.togglePasswordVisibility = function (button) {
            if (!button) return;
            var group = button.closest ? button.closest('.input-group') : button.parentElement;
            var input = group ? group.querySelector('input') : null;
            if (!input) return;

            var eye = button.querySelector('.eye-icon, #eye-icon');
            var eyeSlash = button.querySelector('.eye-slash-icon, #eye-slash-icon');

            var isPassword = input.type === 'password' || input.getAttribute('type') === 'password';
            var nextType = isPassword ? 'text' : 'password';

            input.type = nextType;
            input.setAttribute('type', nextType);

            if (eye) eye.style.display = isPassword ? 'none' : 'block';
            if (eyeSlash) eye.style.display = isPassword ? 'block' : 'none';
            button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        };
    </script>
@endsection
