<form method="POST" action="{{ $lead->exists ? route('leads.update', $lead) : route('leads.store') }}" class="js-single-submit" enctype="multipart/form-data">
    @csrf
    @if ($lead->exists)
        @method('PUT')
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Visiting card</h2>
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

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Contact Particulars</h2>

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
                        <select class="form-select @error('phone_country_code') is-invalid @enderror" id="phone_country_code" name="phone_country_code" required>
                            <option value="">Dial code</option>
                            @php $dialCodeSelected = false; @endphp
                            @foreach ($dialCodes as $dialCode)
                                <option value="{{ $dialCode->dial_code }}" @if (! $dialCodeSelected && old('phone_country_code', $lead->phone_country_code) === $dialCode->dial_code) selected @php $dialCodeSelected = true; @endphp @endif>
                                    {{ $dialCode->dial_code }} {{ $dialCode->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('phone_country_code')
                            <div class="invalid-feedback">{{ $message }}</div>
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

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Dispatch &amp; Territory</h2>

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

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Important dates</h2>
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

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Source and notes</h2>
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

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5">Department Routing</h2>
            <p class="text-secondary">Optional.</p>

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

    @if ($lead->exists)
        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label" for="lead_status_id">Status</label>
                <select class="form-select @error('lead_status_id') is-invalid @enderror" id="lead_status_id" name="lead_status_id">
                    <option value="">No status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" @if ((string) old('lead_status_id', $lead->lead_status_id) === (string) $status->id) selected @endif>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
                @error('lead_status_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    @endif

    <div class="d-flex flex-column flex-sm-row gap-2">
        <button type="submit" class="btn btn-primary">{{ $lead->exists ? 'Update Lead' : 'Create Lead' }}</button>
        <a class="btn btn-outline-secondary" href="{{ route('leads.index') }}">Cancel</a>
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
</script>
<script src="{{ asset('js/visiting-card.js') }}"></script>
