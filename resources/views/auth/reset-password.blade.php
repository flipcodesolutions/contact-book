@extends('layouts.guest')

@section('title', 'Reset password')

@section('content')
    <h2 class="h4 mb-3">Reset password</h2>

    <form method="POST" action="{{ route('password.update') }}" class="js-single-submit">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">New password</label>
            <div class="input-group">
                <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                <button class="btn btn-outline-secondary js-toggle-password" type="button" aria-label="Show password" onclick="togglePasswordVisibility(this); event.stopPropagation();">
                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="eye-slash-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none; pointer-events: none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div class="form-text">At least 8 characters, with letters and numbers.</div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <div class="input-group">
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                <button class="btn btn-outline-secondary js-toggle-password" type="button" aria-label="Show password" onclick="togglePasswordVisibility(this); event.stopPropagation();">
                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="eye-slash-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none; pointer-events: none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Save new password</button>
    </form>
@endsection
