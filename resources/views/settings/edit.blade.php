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
                    <label class="form-label" for="password">New password</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" minlength="8" autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
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

    @if (auth()->user()->canManageStatuses())
        <div class="card">
            <div class="card-body">
                <h2 class="h5">Lead statuses</h2>

                <form method="POST" action="{{ $editingStatus ? route('statuses.update', $editingStatus) : route('statuses.store') }}" class="row g-2 align-items-end mb-4 js-single-submit">
                    @csrf
                    @if ($editingStatus)
                        @method('PUT')
                    @endif
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="status_name">Name</label>
                        <input class="form-control @error('name') is-invalid @enderror" id="status_name" name="name" value="{{ old('name', $editingStatus ? $editingStatus->name : '') }}" required maxlength="100">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="color">Colour</label>
                        <input class="form-control @error('color') is-invalid @enderror" id="color" name="color" type="color" value="{{ old('color', $editingStatus ? $editingStatus->color : '#6B5442') }}" required>
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-primary">{{ $editingStatus ? 'Update status' : 'Add status' }}</button>
                        @if ($editingStatus)
                            <a class="btn btn-outline-secondary" href="{{ route('settings.edit') }}">Cancel</a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Status</th>
                                <th scope="col">Colour</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($statuses as $status)
                                <tr>
                                    <td>
                                        <span class="status-dot me-2" style="background: {{ $status->color }}"></span>
                                        {{ $status->name }}
                                    </td>
                                    <td>{{ $status->color }}</td>
                                    <td>
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('settings.edit', ['edit_status' => $status->id]) }}">Edit</a>
                                        <form method="POST" action="{{ route('statuses.destroy', $status) }}" class="d-inline" onsubmit="return confirm('Delete this status? Leads that use it will have no status.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No statuses yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
