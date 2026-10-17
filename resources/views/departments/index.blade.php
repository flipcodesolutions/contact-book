@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <h1 class="h3 mb-0">Departments</h1>
        <a class="btn btn-primary" href="{{ route('departments.create') }}">+ Create department</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Leads</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->leads_count }}</td>
                            <td>
                                @if ($department->is_active)
                                    <span class="badge badge-department">Active</span>
                                @else
                                    <span class="badge badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('departments.edit', $department) }}" title="Edit" aria-label="Edit {{ $department->name }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                <form method="POST" action="{{ route('departments.destroy', $department) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete department &quot;{{ $department->name }}&quot;?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete" aria-label="Delete {{ $department->name }}">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No departments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
