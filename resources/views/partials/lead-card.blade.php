<div class="card lead-card h-100">
    <div class="card-body d-flex flex-column">
        <div class="d-flex align-items-start gap-2 mb-2">
            @if (auth()->user()->canDeleteLeads() || auth()->user()->canExportLeads())
                <input class="form-check-input lead-checkbox mt-1" type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" form="bulk-action-form" aria-label="Select {{ $lead->full_name }}">
            @endif
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <h2 class="h5 mb-1">{{ $lead->full_name }}</h2>
                    @if ($lead->country)
                        <span class="badge badge-neutral">{{ $lead->country->name }}</span>
                    @endif
                </div>
                @if ($lead->company_name)
                    <div class="text-secondary">{{ $lead->company_name }}</div>
                @endif
            </div>
        </div>

        <div class="small lead-copy-row mb-1">
            <span class="lead-copy-text">{{ $lead->phone_country_code }} {{ $lead->phone_number }}</span>
            <button type="button" class="copy-btn" data-copy="{{ $lead->phone_country_code }} {{ $lead->phone_number }}" aria-label="Copy phone number for {{ $lead->full_name }}">
                <svg class="copy-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                <svg class="copied-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>
            </button>
        </div>
        <div class="small lead-copy-row lead-email mb-2">
            <span class="lead-copy-text">{{ $lead->email }}</span>
            <button type="button" class="copy-btn" data-copy="{{ $lead->email }}" aria-label="Copy email for {{ $lead->full_name }}">
                <svg class="copy-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                <svg class="copied-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>
            </button>
        </div>

        <div class="lead-contact-actions mb-2">
            <a class="icon-btn" href="mailto:{{ $lead->email }}" aria-label="Email {{ $lead->full_name }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
            </a>
            <a class="icon-btn" href="{{ $lead->mapLink() }}" target="_blank" rel="noopener" aria-label="Map for {{ $lead->full_name }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            </a>
            <a class="icon-btn" href="{{ $lead->callLink() }}" aria-label="Call {{ $lead->full_name }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v2a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 1h2a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L7.1 8.9a16 16 0 0 0 6 6l1.5-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
            </a>
            <a class="icon-btn" href="{{ $lead->whatsappLink() }}" target="_blank" rel="noopener" aria-label="WhatsApp {{ $lead->full_name }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 11.5a8.4 8.4 0 0 1-12.4 7.3L4.2 20l1.1-3.6A8.4 8.4 0 1 1 20.5 11.5z"/></svg>
            </a>
        </div>
        @if ($lead->address || $lead->city)
            <div class="small lead-address text-secondary mb-2">
                {{ $lead->address }}@if ($lead->address && $lead->city), @endif{{ $lead->city }}
            </div>
        @endif

        <div class="mb-2">
            @foreach ($lead->departments as $department)
                <span class="badge badge-department me-1 mb-1">{{ $department->name }}</span>
            @endforeach
        </div>

        <div class="d-flex align-items-center mt-auto">
            <span class="status-dot me-2" style="background: {{ $lead->status ? $lead->status->color : '#EFE7DF' }}"></span>
            <span class="small">{{ $lead->status ? $lead->status->name : 'No status' }}</span>
            <span class="ms-auto">
                @if (auth()->user()->canCreateLeads())
                    <a class="icon-btn" href="{{ route('leads.edit', $lead) }}" aria-label="Edit {{ $lead->full_name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </a>
                @endif
                @if (auth()->user()->canDeleteLeads())
                    <button type="button" class="btn btn-link icon-btn" data-bs-toggle="modal" data-bs-target="#deleteLeadModal" data-delete-url="{{ route('leads.destroy', $lead) }}" data-lead-name="{{ $lead->full_name }}" aria-label="Delete {{ $lead->full_name }}">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                @endif
            </span>
        </div>
    </div>
</div>
