<nav class="navbar navbar-expand bg-white app-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ auth()->check() ? route('leads.index') : route('login') }}">
            {{-- Replace public/images/argil-logo.svg with the real Argil logo. --}}
            <img src="{{ asset('images/argil-logo.svg') }}" alt="" width="36" height="36">
            Argil Leads
        </a>

        @auth
            <ul class="navbar-nav ms-3 d-none d-lg-flex">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}" @if (request()->routeIs('leads.*')) aria-current="page" @endif>Leads</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.create') }}" @if (request()->routeIs('campaigns.*')) aria-current="page" @endif>Campaign</a>
                </li>
                @if (auth()->user()->canManageDepartments())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}" @if (request()->routeIs('departments.*')) aria-current="page" @endif>Departments</a>
                    </li>
                @endif
                @if (auth()->user()->canViewUsers())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" @if (request()->routeIs('users.*')) aria-current="page" @endif>Users</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif>Settings</a>
                </li>
            </ul>

            <div class="dropdown ms-auto">
                <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('settings.edit') }}">Profile</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Log out</button>
                        </form>
                    </li>
                </ul>
            </div>
        @endauth
    </div>
</nav>
