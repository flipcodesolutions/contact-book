<form method="POST" action="{{ $department->exists ? route('departments.update', $department) : route('departments.store') }}" class="card js-single-submit">
    @csrf
    @if ($department->exists)
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $department->name) }}" required maxlength="100">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @if ((string) old('is_active', $department->exists ? (int) $department->is_active : 1) === '1') checked @endif>
            <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">{{ $department->exists ? 'Update department' : 'Create department' }}</button>
        <a class="btn btn-outline-secondary" href="{{ route('departments.index') }}">Cancel</a>
    </div>
</form>
