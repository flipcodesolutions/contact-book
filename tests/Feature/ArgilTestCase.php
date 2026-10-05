<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Department;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ArgilTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeDepartment(string $name): Department
    {
        return Department::create([
            'name' => $name,
            'is_active' => true,
        ]);
    }

    protected function makeCountry(string $name, string $iso2, string $dialCode): Country
    {
        return Country::create([
            'name' => $name,
            'iso2' => $iso2,
            'dial_code' => $dialCode,
            'is_active' => true,
        ]);
    }

    protected function makeStatus(string $name = 'Lead Received'): LeadStatus
    {
        return LeadStatus::create([
            'name' => $name,
            'color' => '#2F6F4E',
        ]);
    }

    protected function makeUser(string $role, array $departments, array $countries, bool $active = true): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'is_active' => $active,
            'password' => 'Password123',
        ]);

        $departmentIds = [];

        foreach ($departments as $department) {
            $departmentIds[] = $department->id;
        }

        $countryIds = [];

        foreach ($countries as $country) {
            $countryIds[] = $country->id;
        }

        $user->departments()->sync($departmentIds);
        $user->countries()->sync($countryIds);

        return $user;
    }

    protected function makeLead(User $owner, Country $country, array $departments, string $name, string $email): Lead
    {
        $lead = Lead::create([
            'full_name' => $name,
            'phone_country_code' => $country->dial_code,
            'phone_number' => '7700900123',
            'email' => $email,
            'company_name' => 'Example Co',
            'address' => '1 High Street',
            'city' => 'London',
            'country_id' => $country->id,
            'created_by' => $owner->id,
        ]);

        $departmentIds = [];

        foreach ($departments as $department) {
            $departmentIds[] = $department->id;
        }

        $lead->departments()->sync($departmentIds);

        return $lead;
    }

    protected function leadPayload(Country $country, Department $department, array $overrides = []): array
    {
        $payload = [
            'full_name' => 'New Person',
            'phone_country_code' => $country->dial_code,
            'phone_number' => '7700 900 456',
            'email' => 'new.person@example.com',
            'company_name' => 'New Co',
            'address' => '2 Road',
            'city' => 'London',
            'country_id' => $country->id,
            'department_ids' => [$department->id],
        ];

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        return $payload;
    }
}
