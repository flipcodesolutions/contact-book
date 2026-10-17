<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * List users. Managers can open this page but cannot change anyone.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->canViewUsers(), 403);

        $users = User::query()
            ->with([
                'departments' => fn ($query) => $query->where('departments.is_active', true),
                'countries' => fn ($query) => $query->where('countries.is_active', true),
            ])
            ->orderBy('name')
            ->paginate(10);

        return view('users.index', [
            'users' => $users,
            'canManage' => $request->user()->canManageUsers(),
        ]);
    }

    /**
     * Show the form for a new user.
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->canManageUsers(), 403);

        return view('users.create', $this->formData($request->user(), new User));
    }

    /**
     * Save a new user. The role is set on its own line so it is never mass-assigned.
     */
    public function store(UserRequest $request)
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $user = new User;
        $this->fillUser($user, $request);
        $user->save();
        $this->syncAccess($user, $request);

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    /**
     * Show the form for an existing user.
     */
    public function edit(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUsers(), 403);
        abort_unless($request->user()->isSuperAdmin() || ! $user->isSuperAdmin(), 403);

        return view('users.edit', $this->formData($request->user(), $user));
    }

    /**
     * Save changes to a user, with the last-super-admin and self-lock rules applied.
     */
    public function update(UserRequest $request, User $user)
    {
        abort_unless($request->user()->canManageUsers(), 403);
        abort_unless($request->user()->isSuperAdmin() || ! $user->isSuperAdmin(), 403);

        $blocked = $this->blockedChange($request, $user);

        if ($blocked !== null) {
            return back()->withInput()->with('error', $blocked);
        }

        $this->fillUser($user, $request);
        $user->save();
        $this->syncAccess($user, $request);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    /**
     * Activate or deactivate a user from the list.
     */
    public function toggle(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUsers(), 403);
        abort_unless($request->user()->isSuperAdmin() || ! $user->isSuperAdmin(), 403);

        $newIsActive = ! $user->is_active;

        if ($user->id === $request->user()->id && ! $newIsActive) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($this->removesLastSuperAdmin($user, $user->role, $newIsActive)) {
            return back()->with('error', 'You cannot deactivate the last super admin.');
        }

        $user->is_active = $newIsActive;
        $user->save();

        return back()->with('success', 'User updated.');
    }

    /**
     * Stop a person locking themselves out, or removing the last super admin.
     */
    private function blockedChange(Request $request, User $user): ?string
    {
        $newRole = $request->input('role');
        $newIsActive = $request->boolean('is_active');

        if ($user->id === $request->user()->id && $newRole !== $user->role) {
            return 'You cannot change your own role.';
        }

        if ($user->id === $request->user()->id && ! $newIsActive) {
            return 'You cannot deactivate your own account.';
        }

        if ($this->removesLastSuperAdmin($user, $newRole, $newIsActive)) {
            return 'You cannot deactivate or demote the last super admin.';
        }

        return null;
    }

    private function removesLastSuperAdmin(User $user, $newRole, $newIsActive): bool
    {
        if (! $user->isSuperAdmin()) {
            return false;
        }

        $otherActiveSuperAdmins = User::query()
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->count();

        if ($otherActiveSuperAdmins > 0) {
            return false;
        }

        return $newRole !== 'super_admin' || ! $newIsActive;
    }

    private function fillUser(User $user, UserRequest $request): void
    {
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->is_active = $request->boolean('is_active');
        $user->role = $request->input('role');

        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }
    }

    /**
     * Super admins ignore department and country limits, so their pivots are cleared.
     */
    private function syncAccess(User $user, UserRequest $request): void
    {
        if ($user->isAdmin()) {
            $user->all_departments = true;
            $user->all_countries = true;
            $user->save();
            $user->departments()->sync([]);
            $user->countries()->sync([]);

            return;
        }

        $user->all_departments = $request->boolean('all_departments');
        $user->all_countries = $request->boolean('all_countries');
        $user->save();

        if ($user->all_departments) {
            $user->departments()->sync([]);
        } else {
            $user->departments()->sync($request->input('department_ids', []));
        }

        if ($user->all_countries) {
            $user->countries()->sync([]);
        } else {
            $user->countries()->sync($request->input('country_ids', []));
        }
    }

    private function formData(User $actor, User $user): array
    {
        $roleOptions = [];

        foreach (User::roleOptions() as $value => $label) {
            if ($actor->canAssignRole($value) || $user->role === $value) {
                $roleOptions[$value] = $label;
            }
        }

        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();
        $countries = Country::query()->where('is_active', true)->orderBy('name')->get();

        $selectedDepartmentIds = $this->selectedIds(
            'department_ids',
            $user->exists ? ($user->all_departments ? $departments->pluck('id')->all() : $user->departments()->where('departments.is_active', true)->pluck('departments.id')->all()) : []
        );
        $selectedCountryIds = $this->selectedIds(
            'country_ids',
            $user->exists ? ($user->all_countries ? $countries->pluck('id')->all() : $user->countries()->where('countries.is_active', true)->pluck('countries.id')->all()) : []
        );

        return compact(
            'user',
            'roleOptions',
            'departments',
            'countries',
            'selectedDepartmentIds',
            'selectedCountryIds'
        );
    }

    /**
     * Keep ticked checkboxes after a validation error, otherwise use the saved ones.
     */
    private function selectedIds(string $key, array $fallback): array
    {
        if (old($key) === null) {
            return $fallback;
        }

        $ids = [];

        foreach ((array) old($key) as $id) {
            $ids[] = (int) $id;
        }

        return $ids;
    }
}
