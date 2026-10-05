<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * One super admin from .env, plus one demo user for each other role.
     * Demo passwords are for local use. Change them before a real launch.
     */
    public function run(): void
    {
        $this->makeUser(
            env('SUPER_ADMIN_NAME', 'Super Admin'),
            env('SUPER_ADMIN_EMAIL', 'admin@argiltiles.com'),
            env('SUPER_ADMIN_PASSWORD', 'ChangeMe123'),
            'super_admin',
            [],
            []
        );

        $allDepartments = Department::query()->pluck('name')->all();
        $sampleCountries = ['GB', 'AU', 'IN', 'AE', 'US', 'CA'];

        $this->makeUser('Ava Admin', 'admin@argil.test', 'Password123', 'admin', $allDepartments, $sampleCountries, true, true);
        $this->makeUser('Noah Manager', 'manager@argil.test', 'Password123', 'manager', ['Export', 'Marketing', 'Sales'], ['GB', 'AU']);
        $this->makeUser('Mia Staff', 'staff@argil.test', 'Password123', 'staff', ['Export', 'Sales'], ['GB', 'AU']);
        $this->makeUser('Leo Viewer', 'viewer@argil.test', 'Password123', 'viewer', ['Export'], ['GB']);
    }

    /**
     * The User model hashes the plain password. role is set directly, not through fill().
     */
    private function makeUser($name, $email, $password, $role, array $departmentNames, array $countryIso2, $allDepartments = false, $allCountries = false): void
    {
        $allDepartments = $role === 'super_admin' || $allDepartments;
        $allCountries = $role === 'super_admin' || $allCountries;
        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            $existing->all_departments = $allDepartments;
            $existing->all_countries = $allCountries;
            $existing->save();

            return;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->is_active = true;
        $user->role = $role;
        $user->all_departments = $allDepartments;
        $user->all_countries = $allCountries;
        $user->save();

        if ($role === 'super_admin' || $allDepartments) {
            return;
        }

        $departmentIds = Department::query()->whereIn('name', $departmentNames)->pluck('id')->all();
        $countryIds = Country::query()->whereIn('iso2', $countryIso2)->pluck('id')->all();

        $user->departments()->sync($departmentIds);
        $user->countries()->sync($countryIds);
    }
}
