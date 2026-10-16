<form method="POST" action="{{ $lead->exists ? route('leads.update', $lead) : route('leads.store') }}" class="js-single-submit" enctype="multipart/form-data">
    @csrf
    @if ($lead->exists)
        @method('PUT')
    @endif

    @php
        $cardSectionOpen = $errors->has('card_front') || $errors->has('card_back');
    @endphp

    <div class="accordion lead-filter-accordion mb-3" id="visiting-card-section">
        <div class="accordion-item">
            <h2 class="accordion-header" id="visiting-card-heading">
                <button class="accordion-button {{ $cardSectionOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#visiting-card-panel" aria-expanded="{{ $cardSectionOpen ? 'true' : 'false' }}" aria-controls="visiting-card-panel">
                    Visiting card
                </button>
            </h2>
            <div id="visiting-card-panel" class="accordion-collapse collapse {{ $cardSectionOpen ? 'show' : '' }}" aria-labelledby="visiting-card-heading" data-bs-parent="#visiting-card-section">
                <div class="accordion-body">
            <p class="text-secondary mb-3">Add a clear photo of the front and the back. The reader fills the fields it can see. Check them, choose a department, then save the lead.</p>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="card_front">Front</label>
                    <input class="form-control @error('card_front') is-invalid @enderror" id="card_front" name="card_front" type="file" accept="image/jpeg,image/png,image/webp">
                    @error('card_front')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <img id="card-front-preview" class="card-photo-preview d-none mt-2" alt="Front of the visiting card">
                    @if ($lead->card_front)
                        <img class="card-photo-preview mt-2" src="{{ asset('storage/'.$lead->card_front) }}" alt="Saved front of the visiting card">
                    @endif
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="card_back">Back</label>
                    <input class="form-control @error('card_back') is-invalid @enderror" id="card_back" name="card_back" type="file" accept="image/jpeg,image/png,image/webp">
                    @error('card_back')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <img id="card-back-preview" class="card-photo-preview d-none mt-2" alt="Back of the visiting card">
                    @if ($lead->card_back)
                        <img class="card-photo-preview mt-2" src="{{ asset('storage/'.$lead->card_back) }}" alt="Saved back of the visiting card">
                    @endif
                </div>
            </div>

            <button type="button" class="btn btn-outline-secondary mt-3" id="read-card-button" data-url="{{ route('leads.visiting-card') }}">
                <span id="card-read-spinner" class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                Read card and fill the form
            </button>
            <div id="card-read-status" class="form-text"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h5 mb-0">Contact Particulars</h2>
                @if (! $lead->exists)
                    <button type="submit" class="btn btn-primary lead-save-top d-lg-none">Save Contact</button>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $lead->full_name) }}" required minlength="2" maxlength="120">
                @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="phone_number">Contact Number</label>
                <div class="row g-2">
                    <div class="col-12 col-sm-5">
                        <label class="visually-hidden" for="phone_country_code">Dial code</label>
                        <div class="position-relative" id="dial-code-picker">
                            <select class="form-select @error('phone_country_code') is-invalid @enderror" id="phone_country_code" name="phone_country_code" required style="position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; top: 0; left: 0; z-index: -1;">
                                <option value="">Dial code</option>
                                @php $dialCodeSelected = false; @endphp
                                @foreach ($dialCodes as $dialCode)
                                    <option value="{{ $dialCode->dial_code }}" @if (! $dialCodeSelected && old('phone_country_code', $lead->phone_country_code) === $dialCode->dial_code) selected @php $dialCodeSelected = true; @endphp @endif>
                                        {{ $dialCode->dial_code }} {{ $dialCode->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" class="form-select text-start @error('phone_country_code') is-invalid @enderror" id="dial-code-btn" aria-haspopup="true" aria-expanded="false" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                <span id="dial-code-label" class="{{ old('phone_country_code', $lead->phone_country_code) ? '' : 'text-secondary' }}">
                                    @php
                                        $currentCode = old('phone_country_code', $lead->phone_country_code);
                                        $matchingCountry = $dialCodes->firstWhere('dial_code', $currentCode);
                                    @endphp
                                    {{ $matchingCountry ? $matchingCountry->dial_code . ' ' . $matchingCountry->name : ($currentCode ?: 'Dial code') }}
                                </span>
                            </button>

                            <div class="dropdown-menu p-2 shadow border w-100" id="dial-code-dropdown" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1050; min-width: 270px; border-radius: 8px;">
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm" id="dial-code-search-input" placeholder="Search country or code..." autocomplete="off">
                                </div>
                                <div class="dial-code-options-list" style="max-height: 220px; overflow-y: auto;">
                                    <div class="dropdown-item py-1 px-2 rounded dial-code-opt text-secondary" data-value="" data-label="Dial code" data-search="dial code" style="cursor: pointer;">
                                        <em>Dial code</em>
                                    </div>
                                    @foreach ($dialCodes as $dialCode)
                                        <div class="dropdown-item py-1 px-2 rounded dial-code-opt" data-value="{{ $dialCode->dial_code }}" data-label="{{ $dialCode->dial_code }} {{ $dialCode->name }}" data-search="{{ strtolower($dialCode->dial_code . ' ' . $dialCode->name) }}" style="cursor: pointer; font-size: 0.875rem;">
                                            <strong style="color: var(--argil-brown, #6b5442);">{{ $dialCode->dial_code }}</strong> {{ $dialCode->name }}
                                        </div>
                                    @endforeach
                                </div>
                                <div id="dial-code-empty" class="text-secondary text-center py-2 small d-none">
                                    No matching dial code
                                </div>
                            </div>
                        </div>
                        @error('phone_country_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-sm-7">
                        <input class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $lead->phone_number) }}" required inputmode="tel" maxlength="20" aria-label="Contact number">
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $lead->email) }}" required maxlength="190">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="company_name">Company / Business Name</label>
                <input class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $lead->company_name) }}" maxlength="150">
                @error('company_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="job_title">Job title</label>
                <input class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title', $lead->job_title) }}" maxlength="120">
                @error('job_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-label" for="work_department">Work department</label>
                <input class="form-control @error('work_department') is-invalid @enderror" id="work_department" name="work_department" value="{{ old('work_department', $lead->work_department) }}" maxlength="120">
                <div class="form-text">The contact's department at their company. This is not the routing department below.</div>
                @error('work_department')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    @php
        $territorySectionOpen = $errors->has('address') || $errors->has('city') || $errors->has('country_id');
    @endphp
    <div class="accordion lead-filter-accordion mb-3" id="territory-section">
        <div class="accordion-item">
            <h2 class="accordion-header" id="territory-heading">
                <button class="accordion-button {{ $territorySectionOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#territory-panel" aria-expanded="{{ $territorySectionOpen ? 'true' : 'false' }}" aria-controls="territory-panel">
                    Dispatch &amp; Territory
                </button>
            </h2>
            <div id="territory-panel" class="accordion-collapse collapse {{ $territorySectionOpen ? 'show' : '' }}" aria-labelledby="territory-heading" data-bs-parent="#territory-section">
                <div class="accordion-body">

            <div class="mb-3">
                <label class="form-label" for="address">Physical Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" maxlength="500">{{ old('address', $lead->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="city">City</label>
                <input class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $lead->city) }}" maxlength="100">
                @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-label" for="country_id">Country</label>
                <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id">
                    <option value="">Choose a country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @if ((string) old('country_id', $lead->country_id) === (string) $country->id) selected @endif>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
                @error('country_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">{{ $territory }}</div>
            </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $datesSectionOpen = $errors->has('birthday') || $errors->has('anniversary') || $errors->has('other_date_label') || $errors->has('other_date');
    @endphp
    <div class="accordion lead-filter-accordion mb-3" id="dates-section">
        <div class="accordion-item">
            <h2 class="accordion-header" id="dates-heading">
                <button class="accordion-button {{ $datesSectionOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#dates-panel" aria-expanded="{{ $datesSectionOpen ? 'true' : 'false' }}" aria-controls="dates-panel">
                    Important dates
                </button>
            </h2>
            <div id="dates-panel" class="accordion-collapse collapse {{ $datesSectionOpen ? 'show' : '' }}" aria-labelledby="dates-heading" data-bs-parent="#dates-section">
                <div class="accordion-body">
            <p class="text-secondary">Optional.</p>

            <div class="mb-3">
                <label class="form-label" for="birthday">Birthday</label>
                <input class="form-control @error('birthday') is-invalid @enderror" id="birthday" name="birthday" type="date" value="{{ old('birthday', $lead->birthday ? $lead->birthday->format('Y-m-d') : '') }}">
                @error('birthday')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="anniversary">Anniversary</label>
                <input class="form-control @error('anniversary') is-invalid @enderror" id="anniversary" name="anniversary" type="date" value="{{ old('anniversary', $lead->anniversary ? $lead->anniversary->format('Y-m-d') : '') }}">
                @error('anniversary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="other_date_label">Other date label</label>
                <input class="form-control @error('other_date_label') is-invalid @enderror" id="other_date_label" name="other_date_label" value="{{ old('other_date_label', $lead->other_date_label) }}" maxlength="100" placeholder="For example, site visit">
                @error('other_date_label')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-label" for="other_date">Other date</label>
                <input class="form-control @error('other_date') is-invalid @enderror" id="other_date" name="other_date" type="date" value="{{ old('other_date', $lead->other_date ? $lead->other_date->format('Y-m-d') : '') }}">
                @error('other_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $notesSectionOpen = $errors->has('lead_source_id') || $errors->has('source_name') || $errors->has('website') || $errors->has('notes');
    @endphp
    <div class="accordion lead-filter-accordion mb-3" id="notes-section">
        <div class="accordion-item">
            <h2 class="accordion-header" id="notes-heading">
                <button class="accordion-button {{ $notesSectionOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#notes-panel" aria-expanded="{{ $notesSectionOpen ? 'true' : 'false' }}" aria-controls="notes-panel">
                    Source and notes
                </button>
            </h2>
            <div id="notes-panel" class="accordion-collapse collapse {{ $notesSectionOpen ? 'show' : '' }}" aria-labelledby="notes-heading" data-bs-parent="#notes-section">
                <div class="accordion-body">
            <p class="text-secondary">Optional. Other saves the new source for next time.</p>

            @php $selectedSource = (string) old('lead_source_id', $lead->lead_source_id); @endphp
            <div class="mb-3">
                <label class="form-label" for="lead_source_id">Source</label>
                <select class="form-select @error('lead_source_id') is-invalid @enderror" id="lead_source_id" name="lead_source_id">
                    <option value="">No source</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source->id }}" @if ($selectedSource === (string) $source->id) selected @endif>{{ $source->name }}</option>
                    @endforeach
                    <option value="other" @if ($selectedSource === 'other') selected @endif>Other</option>
                </select>
                @error('lead_source_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 {{ $selectedSource === 'other' ? '' : 'd-none' }}" id="other-source-wrap">
                <label class="form-label" for="source_name">New source</label>
                <input class="form-control @error('source_name') is-invalid @enderror" id="source_name" name="source_name" value="{{ old('source_name') }}" maxlength="100">
                @error('source_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="website">Website</label>
                <input class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', $lead->website) }}" maxlength="190" placeholder="https://">
                @error('website')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" maxlength="2000">{{ old('notes', $lead->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Department Routing</h2>
            <p class="text-secondary">Optional.</p>

            @if ($availableDepartments->isNotEmpty())
                <label class="dept-chip">
                    <input type="checkbox" id="dept-select-all">
                    <span>All</span>
                </label>
            @endif

            @foreach ($availableDepartments as $department)
                <label class="dept-chip">
                    <input type="checkbox" name="department_ids[]" value="{{ $department->id }}" @if (in_array($department->id, $selectedDepartmentIds)) checked @endif>
                    <span>{{ $department->name }}</span>
                </label>
            @endforeach

            @foreach ($lockedDepartments as $department)
                <label class="dept-chip is-locked">
                    <input type="checkbox" disabled>
                    <span>{{ $department->name }}</span>
                    <span class="badge badge-neutral">SCOPE LOCK</span>
                </label>
            @endforeach

            @error('department_ids')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
            @error('department_ids.*')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary flex-fill" href="{{ route('leads.index') }}">Cancel</a>
        <button type="submit" class="btn btn-primary flex-fill">Save</button>
    </div>
</form>

<script>
    var sourceSelect = document.getElementById('lead_source_id');
    var otherSourceWrap = document.getElementById('other-source-wrap');

    if (sourceSelect && otherSourceWrap) {
        sourceSelect.addEventListener('change', function () {
            if (sourceSelect.value === 'other') {
                otherSourceWrap.classList.remove('d-none');
            } else {
                otherSourceWrap.classList.add('d-none');
            }
        });
    }

    // Select/Unselect All Departments
    var selectAllDept = document.getElementById('dept-select-all');
    var deptCheckboxes = document.querySelectorAll('input[name="department_ids[]"]:not(:disabled)');

    if (selectAllDept && deptCheckboxes.length > 0) {
        function syncSelectAllState() {
            var allChecked = Array.from(deptCheckboxes).every(function (cb) {
                return cb.checked;
            });
            selectAllDept.checked = allChecked;
        }

        syncSelectAllState();

        selectAllDept.addEventListener('change', function () {
            var isChecked = selectAllDept.checked;
            deptCheckboxes.forEach(function (cb) {
                cb.checked = isChecked;
            });
        });

        deptCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', syncSelectAllState);
        });
    }

    // Searchable Dial Code Dropdown
    (function () {
        var picker = document.getElementById('dial-code-picker');
        if (!picker) return;

        var select = document.getElementById('phone_country_code');
        var btn = document.getElementById('dial-code-btn');
        var label = document.getElementById('dial-code-label');
        var dropdown = document.getElementById('dial-code-dropdown');
        var searchInput = document.getElementById('dial-code-search-input');
        var options = dropdown.querySelectorAll('.dial-code-opt');
        var emptyMsg = document.getElementById('dial-code-empty');

        function syncLabel() {
            if (!select || !label) return;
            var selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                label.textContent = selectedOpt.textContent.trim();
                label.classList.remove('text-secondary');
            } else {
                label.textContent = 'Dial code';
                label.classList.add('text-secondary');
            }
        }

        select.addEventListener('change', syncLabel);

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var isOpen = dropdown.style.display === 'block';
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        function openDropdown() {
            dropdown.style.display = 'block';
            btn.setAttribute('aria-expanded', 'true');
            searchInput.value = '';
            filterOptions('');
            setTimeout(function () {
                searchInput.focus();
            }, 50);
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            btn.setAttribute('aria-expanded', 'false');
        }

        function filterOptions(query) {
            query = query.toLowerCase().trim();
            var visibleCount = 0;
            options.forEach(function (opt) {
                var searchData = opt.getAttribute('data-search') || '';
                if (!query || searchData.indexOf(query) !== -1) {
                    opt.style.display = '';
                    visibleCount++;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (emptyMsg) {
                emptyMsg.classList.toggle('d-none', visibleCount > 0);
            }
        }

        searchInput.addEventListener('input', function () {
            filterOptions(this.value);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                for (var i = 0; i < options.length; i++) {
                    if (options[i].style.display !== 'none' && options[i].getAttribute('data-value')) {
                        selectOption(options[i]);
                        break;
                    }
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
                btn.focus();
            }
        });

        function selectOption(opt) {
            var val = opt.getAttribute('data-value');
            select.value = val;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncLabel();
            closeDropdown();
            btn.focus();
        }

        options.forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                selectOption(this);
            });
        });

        document.addEventListener('click', function (e) {
            if (!picker.contains(e.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && dropdown.style.display === 'block') {
                closeDropdown();
            }
        });
    })();
</script>
<script src="{{ asset('js/visiting-card.js') }}"></script>
