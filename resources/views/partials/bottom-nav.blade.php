@auth
    <nav class="bottom-nav d-lg-none" aria-label="Mobile">
        <div class="d-flex">
            <a class="flex-fill d-flex flex-column align-items-center justify-content-center {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}" @if (request()->routeIs('leads.*')) aria-current="page" @endif>
                <i class="bi bi-person-lines-fill" aria-hidden="true"></i>
                Leads
            </a>
            <a class="flex-fill d-flex flex-column align-items-center justify-content-center {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.create') }}" @if (request()->routeIs('campaigns.*')) aria-current="page" @endif>
                <i class="bi bi-megaphone" aria-hidden="true"></i>
                Campaign
            </a>
            @if (auth()->user()->canManageDepartments())
                <a class="flex-fill d-flex flex-column align-items-center justify-content-center {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}" @if (request()->routeIs('departments.*')) aria-current="page" @endif>
                    <i class="bi bi-diagram-3" aria-hidden="true"></i>
                    Depts
                </a>
            @endif
            @if (auth()->user()->canViewUsers())
                <a class="flex-fill d-flex flex-column align-items-center justify-content-center {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" @if (request()->routeIs('users.*')) aria-current="page" @endif>
                    <i class="bi bi-people" aria-hidden="true"></i>
                    Users
                </a>
            @endif
            <a class="flex-fill d-flex flex-column align-items-center justify-content-center {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif>
                <i class="bi bi-gear" aria-hidden="true"></i>
                Settings
            </a>
        </div>
    </nav>
@endauth
