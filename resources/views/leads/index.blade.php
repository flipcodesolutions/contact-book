@extends('layouts.app')

@section('title', 'Contact Book')

@section('content')
    @php
        $extraFilterCount = 0;

        foreach (['department_id', 'country_id', 'city', 'lead_source_id'] as $filterName) {
            if (request()->filled($filterName)) {
                $extraFilterCount++;
            }
        }
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Contacts / Leads</h1>
            <p class="page-subtitle mb-0 d-none d-lg-block">Manage and route all Argil international surface leads.</p>
        </div>
        <div class="lead-action-row">
            @if (auth()->user()->canCreateLeads())
                <a class="btn btn-primary" href="{{ route('leads.create') }}">
                    <span class="d-lg-none">+ Lead</span>
                    <span class="d-none d-lg-inline">+ Create Lead</span>
                </a>
            @endif
            @if (auth()->user()->canImportLeads())
                <a class="btn btn-outline-secondary" href="{{ route('leads.import') }}">Import</a>
            @endif
            @if (auth()->user()->canExportLeads())
                <a class="btn btn-outline-secondary" href="{{ route('leads.export', request()->query()) }}">
                    <span class="d-lg-none">Excel</span>
                    <span class="d-none d-lg-inline">Download Excel</span>
                </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('leads.index') }}" class="card lead-filter-form mb-3">
        <div class="card-body">
            <label class="form-label d-none d-lg-block" for="search">Search</label>
            <div class="input-group lead-search-row">
                <input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Name, phone, email, company, city" aria-label="Search">
                <button type="submit" class="btn btn-primary d-lg-none">Search</button>
            </div>

            <div class="accordion lead-filter-accordion mt-2" id="lead-filters">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="lead-filter-heading">
                        <button class="accordion-button {{ $extraFilterCount === 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#lead-filter-panel" aria-expanded="{{ $extraFilterCount === 0 ? 'false' : 'true' }}" aria-controls="lead-filter-panel">
                            Filters
                            @if ($extraFilterCount > 0)
                                <span class="badge badge-department ms-2">{{ $extraFilterCount }}</span>
                            @endif
                        </button>
                    </h2>
                    <div id="lead-filter-panel" class="accordion-collapse collapse {{ $extraFilterCount > 0 ? 'show' : '' }}" data-has-filters="{{ $extraFilterCount > 0 ? '1' : '0' }}" aria-labelledby="lead-filter-heading" data-bs-parent="#lead-filters">
                        <div class="accordion-body">
                            <div class="row g-2">
                                <div class="col-12 col-lg-3">
                                    <label class="form-label" for="department_id">Department</label>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option value="">All</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" @if ((string) request('department_id') === (string) $department->id) selected @endif>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label" for="country_id">Country</label>
                                    <select class="form-select" id="country_id" name="country_id">
                                        <option value="">All</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}" @if ((string) request('country_id') === (string) $country->id) selected @endif>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label" for="city">City</label>
                                    <input class="form-control" id="city" name="city" value="{{ request('city') }}">
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label" for="lead_source_id">Source</label>
                                    <select class="form-select" id="lead_source_id" name="lead_source_id">
                                        <option value="">All</option>
                                        @foreach ($sources as $source)
                                            <option value="{{ $source->id }}" @if ((string) request('lead_source_id') === (string) $source->id) selected @endif>{{ $source->name }}</option>
                                        @endforeach
                                        <option value="none" @if (request('lead_source_id') === 'none') selected @endif>No source</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">Apply</button>
                                <a class="btn btn-outline-secondary" href="{{ route('leads.index') }}">Clear</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script>
        (function () {
            var panel = document.getElementById('lead-filter-panel');
            var button = document.querySelector('#lead-filter-heading .accordion-button');

            if (!panel || !button || panel.getAttribute('data-has-filters') === '1') {
                return;
            }

            if (window.matchMedia('(min-width: 992px)').matches) {
                panel.classList.add('show');
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
            }
        })();
    </script>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="text-secondary">Showing {{ $leads->count() }} of {{ $leads->total() }} leads</div>
        @if (auth()->user()->canDeleteLeads() || auth()->user()->canExportLeads())
            <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" id="select-page">
                <label class="form-check-label" for="select-page">Select page</label>
            </div>
        @endif
    </div>

    @if ($leads->count() === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <h2 class="h5">No leads found</h2>
                <p class="text-secondary mb-0">Try another search, or create a lead.</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($leads as $lead)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('partials.lead-card', ['lead' => $lead])
                </div>
            @endforeach
        </div>
    @endif

    @include('partials.pagination', ['paginator' => $leads])

    @if (auth()->user()->canDeleteLeads() || auth()->user()->canExportLeads())
        <form method="POST" id="bulk-action-form" action="{{ route('leads.bulk-destroy') }}">
            @csrf
        </form>

        <div id="selection-bar" class="selection-bar d-none p-3 mt-3">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div><strong id="selection-count">0</strong> Selected</div>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <button type="button" class="btn btn-outline-light" id="clear-selection">Clear</button>
                @if (auth()->user()->canExportLeads())
                    <button type="submit" class="btn btn-light" form="bulk-action-form" formaction="{{ route('leads.export') }}">Download selected</button>
                @endif
                @if (auth()->user()->canDeleteLeads())
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">Delete selected</button>
                @endif
            </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="deleteLeadModal" tabindex="-1" aria-labelledby="deleteLeadTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="deleteLeadTitle">Delete lead</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Delete <strong id="delete-lead-name"></strong>? This removes the lead from the list.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="delete-lead-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->canDeleteLeads())
        <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteTitle" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="bulkDeleteTitle">Delete selected leads</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">Delete every lead you ticked on this page?</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" form="bulk-action-form">Delete selected</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        // Count the ticked lead cards and show the bulk-action bar.
        var selectPage = document.getElementById('select-page');
        var clearSelection = document.getElementById('clear-selection');
        var deleteModal = document.getElementById('deleteLeadModal');

        function leadBoxes() {
            return document.querySelectorAll('.lead-checkbox');
        }

        function refreshSelection() {
            var boxes = leadBoxes();
            var selected = 0;
            var index;

            for (index = 0; index < boxes.length; index++) {
                if (boxes[index].checked) {
                    selected++;
                }
            }

            var bar = document.getElementById('selection-bar');
            var count = document.getElementById('selection-count');

            if (!bar || !count) {
                return;
            }

            count.textContent = String(selected);

            if (selected === 0) {
                bar.classList.add('d-none');
            } else {
                bar.classList.remove('d-none');
            }

            if (selectPage) {
                selectPage.checked = boxes.length > 0 && selected === boxes.length;
            }
        }

        if (selectPage) {
            selectPage.addEventListener('change', function () {
                var boxes = leadBoxes();
                var index;

                for (index = 0; index < boxes.length; index++) {
                    boxes[index].checked = selectPage.checked;
                }

                refreshSelection();
            });
        }

        var boxes = leadBoxes();
        var index;

        for (index = 0; index < boxes.length; index++) {
            boxes[index].addEventListener('change', refreshSelection);
        }

        if (clearSelection) {
            clearSelection.addEventListener('click', function () {
                var currentBoxes = leadBoxes();
                var boxIndex;

                for (boxIndex = 0; boxIndex < currentBoxes.length; boxIndex++) {
                    currentBoxes[boxIndex].checked = false;
                }

                if (selectPage) {
                    selectPage.checked = false;
                }

                refreshSelection();
            });
        }

        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var form = document.getElementById('delete-lead-form');
                var name = document.getElementById('delete-lead-name');

                if (!button || !form || !name) {
                    return;
                }

                form.action = button.getAttribute('data-delete-url');
                name.textContent = button.getAttribute('data-lead-name');
            });
        }
    </script>
@endsection
