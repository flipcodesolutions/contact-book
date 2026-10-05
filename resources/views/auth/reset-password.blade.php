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
            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">At least 8 characters, with letters and numbers.</div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Save new password</button>
    </form>
@endsection
