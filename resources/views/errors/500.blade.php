@extends('layouts.app')

@section('title', 'Something went wrong')

@section('content')
    <div class="card">
        <div class="card-body py-5 text-center">
            <h1 class="h3">Something went wrong</h1>
            <p class="text-secondary">Please try again in a moment. If it keeps happening, contact an administrator.</p>
            <a class="btn btn-primary" href="{{ route('leads.index') }}">Back to leads</a>
        </div>
    </div>
@endsection
