@extends('layouts.app')

@section('title', 'Not allowed')

@section('content')
    <div class="card">
        <div class="card-body py-5 text-center">
            <h1 class="h3">You do not have access to that page</h1>
            <p class="text-secondary">Ask an administrator if you think this is a mistake.</p>
            <a class="btn btn-primary" href="{{ route('leads.index') }}">Back to leads</a>
        </div>
    </div>
@endsection
