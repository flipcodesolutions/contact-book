@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <h2 class="h4 mb-3">Forgot password</h2>
    <p class="text-secondary">Enter your email and we will send a reset link.</p>

    <form method="POST" action="{{ route('password.email') }}" class="js-single-submit">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
    </form>

    <p class="mt-3 mb-0 text-center">
        <a href="{{ route('login') }}">Back to log in</a>
    </p>
@endsection
