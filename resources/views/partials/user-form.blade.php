<form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="js-single-submit">
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    <div class="card mb-3">
        <div class="card-body">
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
                <label class="form-label" for="phone">Phone</label>
                <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="role">Role</label>
                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                    @foreach ($roleOptions as $value => $label)
                        <option value="{{ $value }}" @if (old('role', $user->role) === $value) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" @if (! $user->exists) required @endif minlength="8" autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    At least 8 characters, with letters and numbers.
                    @if ($user->exists)
                        Leave blank to keep the current password.
                    @endif
                </div>
            </div>

            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @if ((string) old('is_active', $user->exists ? (int) $user->is_active : 1) === '1') checked @endif>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Departments</h2>
            <p class="text-secondary">A department right includes that department in every country. Tick All departments to include every department, including ones added later.</p>
            <div class="form-check mb-2">
                <input type="hidden" name="all_departments" value="0">
                <input class="form-check-input" type="checkbox" id="all_departments" name="all_departments" value="1" @if ((string) old('all_departments', $user->all_departments ? 1 : 0) === '1') checked @endif>
                <label class="form-check-label" for="all_departments">All departments</label>
            </div>
            @foreach ($departments as $department)
                <label class="dept-chip">
                    <input type="checkbox" name="department_ids[]" value="{{ $department->id }}" @if (in_array($department->id, $selectedDepartmentIds)) checked @endif>
                    <span>{{ $department->name }}</span>
                </label>
            @endforeach
            @error('department_ids')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Countries</h2>
            <p class="text-secondary">A country right includes every department in that country. Tick All countries to include every country. Choose at least one side unless the role is Super Admin.</p>
            <div class="form-check mb-2">
                <input type="hidden" name="all_countries" value="0">
                <input class="form-check-input" type="checkbox" id="all_countries" name="all_countries" value="1" @if ((string) old('all_countries', $user->all_countries ? 1 : 0) === '1') checked @endif>
                <label class="form-check-label" for="all_countries">All countries</label>
            </div>
            @foreach ($countries as $country)
                <label class="dept-chip">
                    <input type="checkbox" name="country_ids[]" value="{{ $country->id }}" @if (in_array($country->id, $selectedCountryIds)) checked @endif>
                    <span>{{ $country->name }}</span>
                </label>
            @endforeach
            @error('country_ids')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <button type="submit" class="btn btn-primary">{{ $user->exists ? 'Update user' : 'Create user' }}</button>
    <a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Cancel</a>
</form>
