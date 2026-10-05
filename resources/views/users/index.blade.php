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

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Departments</th>
                        <th scope="col">Countries</th>
                        <th scope="col">Status</th>
                        @if ($canManage)
                            <th scope="col">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ \App\Models\User::roleOptions()[$user->role] ?? $user->role }}</td>
                            <td>
                                @if ($user->isSuperAdmin() || $user->all_departments)
                                    <span class="text-secondary">All</span>
                                @else
                                    @forelse ($user->departments as $department)
                                        <span class="badge badge-department me-1">{{ $department->name }}</span>
                                    @empty
                                        <span class="text-secondary">None</span>
                                    @endforelse
                                @endif
                            </td>
                            <td>
                                @if ($user->isSuperAdmin() || $user->all_countries)
                                    <span class="text-secondary">All</span>
                                @else
                                    @forelse ($user->countries as $country)
                                        <span class="badge badge-neutral me-1">{{ $country->iso2 }}</span>
                                    @empty
                                        <span class="text-secondary">None</span>
                                    @endforelse
                                @endif
                            </td>
                            <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                            @if ($canManage)
                                <td>
                                    @if (auth()->user()->isSuperAdmin() || ! $user->isSuperAdmin())
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('users.edit', $user) }}">Edit</a>
                                        <form method="POST" action="{{ route('users.toggle', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No users yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.pagination', ['paginator' => $users])
@endsection
