@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Users</h1>
            @if (! $canManage)
                <p class="page-subtitle mb-0">You can view users. Only an admin can change them.</p>
            @endif
        </div>
        @if ($canManage)
            <a class="btn btn-primary" href="{{ route('users.create') }}">+ Create user</a>
        @endif
    </div>

    @if ($users->count() === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <h2 class="h5">No users found</h2>
                <p class="text-secondary mb-0">Create a user to get started.</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($users as $user)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('partials.user-card', ['user' => $user, 'canManage' => $canManage])
                </div>
            @endforeach
        </div>
    @endif

    @include('partials.pagination', ['paginator' => $users])
@endsection
