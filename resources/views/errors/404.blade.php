@extends('layouts.app')

@section('title', 'Not found')

@section('content')
    <div class="card">
        <div class="card-body py-5 text-center">
            <h1 class="h3">We could not find that page</h1>
            <p class="text-secondary">The link may be old, or the lead may be outside your territory.</p>
            <a class="btn btn-primary" href="{{ route('leads.index') }}">Back to leads</a>
        </div>
    </div>
@endsection
