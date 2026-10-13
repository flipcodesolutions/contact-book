<div class="card lead-card h-100">
    <div class="card-body d-flex flex-column">
        <!-- Header: Avatar, Name, Role & Country Badge -->
        <div class="d-flex align-items-start gap-2 mb-2">
            <div class="user-avatar-circle">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <h2 class="h5 mb-0 text-truncate">{{ $user->name }}</h2>
                    @if ($user->isSuperAdmin() || $user->all_countries)
                        <span class="badge badge-neutral text-nowrap">All Countries</span>
                    @elseif ($user->countries->isNotEmpty())
                        <span class="badge badge-neutral text-nowrap">{{ $user->countries->first()->name }}@if($user->countries->count() > 1) <small>+{{ $user->countries->count() - 1 }}</small>@endif</span>
                    @else
                        <span class="badge badge-neutral text-nowrap">{{ \App\Models\User::roleOptions()[$user->role] ?? $user->role }}</span>
                    @endif
                </div>
                <div class="text-secondary small">
                    {{ \App\Models\User::roleOptions()[$user->role] ?? ucfirst($user->role) }}
                </div>
            </div>
        </div>

        <!-- Phone Number with Copy Button (if present) -->
        @if ($user->phone)
            <div class="small lead-copy-row mb-1">
                <span class="lead-copy-text">{{ $user->phone }}</span>
                <button type="button" class="copy-btn" data-copy="{{ $user->phone }}" aria-label="Copy phone for {{ $user->name }}">
                    <svg class="copy-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                    <svg class="copied-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>
                </button>
            </div>
        @endif

        <!-- Email with Copy Button -->
        <div class="small lead-copy-row lead-email mb-2">
            <span class="lead-copy-text text-truncate">{{ $user->email }}</span>
            <button type="button" class="copy-btn" data-copy="{{ $user->email }}" aria-label="Copy email for {{ $user->name }}">
                <svg class="copy-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                <svg class="copied-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>
            </button>
        </div>

        <!-- Contact Action Buttons -->
        <div class="lead-contact-actions mb-2">
            <a class="icon-btn" href="mailto:{{ $user->email }}" aria-label="Email {{ $user->name }}" title="Send email">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
            </a>
            @if ($user->phone)
                <a class="icon-btn" href="tel:{{ $user->phone }}" aria-label="Call {{ $user->name }}" title="Call">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v2a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 1h2a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L7.1 8.9a16 16 0 0 0 6 6l1.5-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
                </a>
            @endif
        </div>

        <!-- Scope / Country List -->
        <div class="small lead-address text-secondary mb-2">
            @if ($user->isSuperAdmin() || $user->all_countries)
                All Countries
            @else
                @if ($user->countries->isNotEmpty())
                    {{ $user->countries->pluck('name')->join(', ') }}
                @else
                    No country scope
                @endif
            @endif
        </div>

        <!-- Department Badges (Matches Table View & Lead Card) -->
        <div class="mb-2">
            @if ($user->isSuperAdmin() || $user->all_departments)
                <span class="text-secondary small">All</span>
            @else
                @forelse ($user->departments as $department)
                    <span class="badge badge-department me-1 mb-1">{{ $department->name }}</span>
                @empty
                    <span class="text-secondary small">None</span>
                @endforelse
            @endif
        </div>

        <!-- Bottom Row: Status Dot + Actions -->
        <div class="d-flex align-items-center mt-auto pt-2 border-top">
            <span class="status-dot me-2" style="background: {{ $user->is_active ? '#22c55e' : '#a8a29e' }};"></span>
            <span class="small fw-medium">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
            @if ($canManage)
                <div class="ms-auto d-flex align-items-center gap-1">
                    @if (auth()->user()->isSuperAdmin() || ! $user->isSuperAdmin())
                        <a class="icon-btn" href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->name }}" title="Edit">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form method="POST" action="{{ route('users.toggle', $user) }}" class="d-inline" onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} user {{ $user->name }}?')">
                            @csrf
                            <button type="submit" class="btn btn-link icon-btn p-0" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}" style="text-decoration: none;">
                                <i class="bi {{ $user->is_active ? 'bi-trash' : 'bi-arrow-counterclockwise' }}" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
