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
            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
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
@endsection
