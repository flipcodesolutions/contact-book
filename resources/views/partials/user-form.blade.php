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
                <div class="input-group">
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" @if (! $user->exists) required @endif minlength="8" autocomplete="new-password">
                    <button class="btn btn-outline-secondary js-toggle-password" type="button" aria-label="Show password" onclick="togglePasswordVisibility(this); event.stopPropagation();">
                        <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-slash-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none; pointer-events: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
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
            <p class="text-secondary">Choose the departments this user can access. Tick All departments to include every department, including ones added later.</p>
            <div class="form-check mb-2">
                <input type="hidden" name="all_departments" value="0">
                <input class="form-check-input" type="checkbox" id="all_departments" name="all_departments" value="1" @if ((string) old('all_departments', $user->all_departments ? 1 : 0) === '1') checked @endif>
                <label class="form-check-label" for="all_departments">All departments</label>
            </div>
            @foreach ($departments as $department)
                @php $isDeptSelected = in_array($department->id, $selectedDepartmentIds); @endphp
                <label class="dept-chip {{ $isDeptSelected ? 'is-checked' : '' }}">
                    <input type="checkbox" name="department_ids[]" value="{{ $department->id }}" @if ($isDeptSelected) checked @endif>
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
            <p class="text-secondary">Choose the countries this user can access. Tick All countries to include every country. Super Admin and Admin have full access to all data.</p>
            <div class="form-check mb-2">
                <input type="hidden" name="all_countries" value="0">
                <input class="form-check-input" type="checkbox" id="all_countries" name="all_countries" value="1" @if ((string) old('all_countries', $user->all_countries ? 1 : 0) === '1') checked @endif>
                <label class="form-check-label" for="all_countries">All countries</label>
            </div>
            @foreach ($countries as $country)
                @php $isCountrySelected = in_array($country->id, $selectedCountryIds); @endphp
                <label class="dept-chip {{ $isCountrySelected ? 'is-checked' : '' }}">
                    <input type="checkbox" name="country_ids[]" value="{{ $country->id }}" @if ($isCountrySelected) checked @endif>
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

<script>
    (function () {
        function updateChipClass(input) {
            var chip = input.closest ? input.closest('.dept-chip') : input.parentElement;
            if (chip && chip.classList) {
                if (input.checked) {
                    chip.classList.add('is-checked');
                } else {
                    chip.classList.remove('is-checked');
                }
            }
        }

        function initSelectAll(masterId, childSelector) {
            var master = document.getElementById(masterId);
            if (!master || master.dataset.syncBound === '1') return;
            master.dataset.syncBound = '1';

            var isSyncing = false;

            function getChildren() {
                return Array.from(document.querySelectorAll(childSelector));
            }

            function syncMaster() {
                var children = getChildren();
                if (children.length === 0) return;
                var allChecked = children.every(function (cb) { return cb.checked; });
                if (master.checked !== allChecked) {
                    isSyncing = true;
                    master.checked = allChecked;
                    isSyncing = false;
                }
            }

            function setAllChildren(checked) {
                var children = getChildren();
                isSyncing = true;
                children.forEach(function (cb) {
                    cb.checked = checked;
                    updateChipClass(cb);
                });
                isSyncing = false;
            }

            master.addEventListener('change', function () {
                if (isSyncing) return;
                setAllChildren(master.checked);
            });

            document.addEventListener('change', function (e) {
                if (isSyncing) return;
                if (!e.target || !e.target.matches(childSelector)) return;
                updateChipClass(e.target);
                syncMaster();
            });

            // Initial setup on page load
            var children = getChildren();
            if (children.length > 0) {
                if (master.checked) {
                    setAllChildren(true);
                } else {
                    var allChecked = children.every(function (cb) { return cb.checked; });
                    if (allChecked) {
                        master.checked = true;
                    }
                }
                children.forEach(updateChipClass);
            }
        }

        function runInit() {
            initSelectAll('all_departments', 'input[name="department_ids[]"]');
            initSelectAll('all_countries', 'input[name="country_ids[]"]');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runInit);
        } else {
            runInit();
        }
    })();
</script>
