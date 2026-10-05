@extends('layouts.app')

@section('title', 'Page expired')

@section('content')
    <div class="card">
        <div class="card-body py-5 text-center">
            <h1 class="h3">Your page expired</h1>
            <p class="text-secondary">The form sat open too long. Go back and try again.</p>
            <a class="btn btn-primary" href="{{ route('login') }}">Log in</a>
        </div>
    </div>
@endsection
