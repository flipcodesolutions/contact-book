@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <h1 class="h3 mb-3">Settings</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">My profile</h2>
            <form method="POST" action="{{ route('profile.update') }}" class="js-single-submit">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="120">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="190">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="old_password">Old password</label>
                    <div class="input-group">
                        <input class="form-control @error('old_password') is-invalid @enderror" id="old_password" name="old_password" type="password" autocomplete="current-password">
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
                    @error('old_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">New password</label>
                    <div class="input-group">
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" minlength="8" autocomplete="new-password">
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
                    <div class="form-text">Leave blank to keep your current password. New passwords need letters and numbers.</div>
                </div>

                <button type="submit" class="btn btn-primary">Save profile</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Install App</h2>
            <p class="text-secondary">Install Argil Leads on your phone. This works over HTTPS.</p>
            <button type="button" class="btn btn-primary" id="install-app-button" hidden>Install App</button>
            <p id="ios-install-hint" class="mb-0" hidden>On iPhone or iPad, tap the Share button, then choose Add to Home Screen.</p>
            <p id="install-app-note" class="text-secondary mb-0">If the button stays hidden, open this site in Chrome or Edge over HTTPS, or use your browser's install menu.</p>
        </div>
    </div>

@endsection

@section('scripts')
<script>
window.togglePasswordVisibility = function (button) {
    if (!button) return;
    var group = button.closest ? button.closest('.input-group') : button.parentElement;
    var input = group ? group.querySelector('input') : null;
    if (!input) return;

    var eye = button.querySelector('.eye-icon');
    var eyeSlash = button.querySelector('.eye-slash-icon');

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
