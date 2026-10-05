<?php

namespace Tests\Feature;

class LeadAccessTest extends ArgilTestCase
{
    public function test_a_user_cannot_see_or_create_leads_outside_their_departments_and_countries(): void
    {
        $export = $this->makeDepartment('Export');
        $sales = $this->makeDepartment('Sales');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $australia = $this->makeCountry('Australia', 'AU', '+61');
        $owner = $this->makeUser('admin', [$export, $sales], [$uk, $australia]);
        $staff = $this->makeUser('staff', [$export], [$uk]);

        $hidden = $this->makeLead($owner, $australia, [$sales], 'Secret Lead', 'secret@example.com');
        $visible = $this->makeLead($owner, $uk, [$export], 'Visible Lead', 'visible@example.com');

        $this->actingAs($staff)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Visible Lead')
            ->assertDontSee('Secret Lead');

        $this->actingAs($staff)
            ->get(route('leads.edit', $hidden))
            ->assertNotFound();

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($australia, $export, [
                'email' => 'department-any-country@example.com',
            ]))
            ->assertRedirect();

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($uk, $sales, [
                'email' => 'country-any-department@example.com',
            ]))
            ->assertRedirect();

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($australia, $sales, [
                'email' => 'neither-side@example.com',
            ]))
            ->assertSessionHasErrors('country_id');

        $this->assertDatabaseHas('leads', ['email' => 'department-any-country@example.com']);
        $this->assertDatabaseHas('leads', ['email' => 'country-any-department@example.com']);
        $this->assertDatabaseMissing('leads', ['email' => 'neither-side@example.com']);
    }

    public function test_department_rights_and_country_rights_are_independent(): void
    {
        $export = $this->makeDepartment('Export');
        $sales = $this->makeDepartment('Sales');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $australia = $this->makeCountry('Australia', 'AU', '+61');
        $owner = $this->makeUser('admin', [$export, $sales], [$uk, $australia]);

        $departmentUser = $this->makeUser('staff', [$export], []);
        $countryUser = $this->makeUser('manager', [], [$uk]);
        $allDepartments = $this->makeUser('admin', [], []);
        $allDepartments->all_departments = true;
        $allDepartments->save();

        $this->makeLead($owner, $australia, [$export], 'Export Australia', 'export-au@example.com');
        $this->makeLead($owner, $uk, [$sales], 'Sales UK', 'sales-uk@example.com');

        $this->actingAs($departmentUser)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Export Australia')
            ->assertDontSee('Sales UK');

        $this->actingAs($countryUser)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Sales UK')
            ->assertDontSee('Export Australia');

        $this->actingAs($allDepartments)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Export Australia')
            ->assertSee('Sales UK');
    }

    public function test_other_source_is_saved_into_the_source_table(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $staff = $this->makeUser('staff', [$export], [$uk]);

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($uk, $export, [
                'email' => 'sourced@example.com',
                'lead_source_id' => 'other',
                'source_name' => 'Trade magazine',
                'website' => 'https://example.com',
                'notes' => 'Called back',
                'birthday' => '1990-01-02',
                'job_title' => 'Buyer',
                'work_department' => 'Procurement',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('lead_sources', ['name' => 'Trade magazine']);
        $this->assertDatabaseHas('leads', [
            'email' => 'sourced@example.com',
            'website' => 'https://example.com',
            'job_title' => 'Buyer',
            'work_department' => 'Procurement',
        ]);
    }

    public function test_super_admin_can_see_every_lead(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $owner = $this->makeUser('staff', [$export], [$uk]);
        $superAdmin = $this->makeUser('super_admin', [], []);
        $this->makeLead($owner, $uk, [$export], 'Any Lead', 'any@example.com');

        $this->actingAs($superAdmin)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Any Lead');
    }

    public function test_viewer_cannot_create_delete_import_or_export(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $owner = $this->makeUser('admin', [$export], [$uk]);
        $viewer = $this->makeUser('viewer', [$export], [$uk]);
        $lead = $this->makeLead($owner, $uk, [$export], 'Viewer Lead', 'viewer-lead@example.com');

        $this->actingAs($viewer)->get(route('leads.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('leads.store'), $this->leadPayload($uk, $export))->assertForbidden();
        $this->actingAs($viewer)->delete(route('leads.destroy', $lead))->assertForbidden();
        $this->actingAs($viewer)->get(route('leads.import'))->assertForbidden();
        $this->actingAs($viewer)->get(route('leads.export'))->assertForbidden();
    }

    public function test_staff_can_create_and_export_but_not_delete_or_import(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $owner = $this->makeUser('admin', [$export], [$uk]);
        $staff = $this->makeUser('staff', [$export], [$uk]);
        $lead = $this->makeLead($owner, $uk, [$export], 'Staff Lead', 'staff-lead@example.com');

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($uk, $export))
            ->assertRedirect(route('leads.index'));

        $this->assertDatabaseHas('leads', [
            'email' => 'new.person@example.com',
            'phone_number' => '7700900456',
        ]);

        $this->actingAs($staff)->get(route('leads.export'))->assertOk();
        $this->actingAs($staff)->delete(route('leads.destroy', $lead))->assertForbidden();
        $this->actingAs($staff)->get(route('leads.import'))->assertForbidden();
        $this->actingAs($staff)->post(route('leads.import.store'))->assertForbidden();
    }

    public function test_manager_can_delete_and_open_import(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $owner = $this->makeUser('admin', [$export], [$uk]);
        $manager = $this->makeUser('manager', [$export], [$uk]);
        $lead = $this->makeLead($owner, $uk, [$export], 'Manager Lead', 'manager-lead@example.com');

        $this->actingAs($manager)->get(route('leads.import'))->assertOk();
        $this->actingAs($manager)->delete(route('leads.destroy', $lead))->assertRedirect(route('leads.index'));
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    public function test_manager_can_view_users_but_not_create_them(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $manager = $this->makeUser('manager', [$export], [$uk]);

        $this->actingAs($manager)->get(route('users.index'))->assertOk()->assertDontSee('Create user');
        $this->actingAs($manager)->get(route('users.create'))->assertForbidden();
    }

    public function test_admin_cannot_create_a_super_admin(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $admin = $this->makeUser('admin', [$export], [$uk]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Super',
            'email' => 'new.super@example.com',
            'role' => 'super_admin',
            'password' => 'Password123',
            'is_active' => '1',
        ])->assertSessionHasErrors('role');
    }

    public function test_a_lead_can_be_saved_with_only_name_email_and_phone(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $staff = $this->makeUser('staff', [$export], [$uk]);

        $this->actingAs($staff)
            ->post(route('leads.store'), [
                'full_name' => 'Only Contact',
                'phone_country_code' => '+44',
                'phone_number' => '7700900456',
                'email' => 'only.contact@example.com',
                'company_name' => '',
                'city' => '',
                'country_id' => '',
            ])
            ->assertRedirect(route('leads.index'));

        $this->assertDatabaseHas('leads', [
            'email' => 'only.contact@example.com',
            'company_name' => null,
            'city' => null,
            'country_id' => null,
        ]);

        $this->actingAs($staff)
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Only Contact');
    }

    public function test_lead_validation_errors(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $australia = $this->makeCountry('Australia', 'AU', '+61');
        $staff = $this->makeUser('staff', [$export], [$uk, $australia]);

        $this->actingAs($staff)
            ->post(route('leads.store'), [])
            ->assertSessionHasErrors(['full_name', 'phone_number', 'email']);

        $this->makeLead($staff, $uk, [$export], 'Existing Person', 'dup@example.com');

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($uk, $export, [
                'email' => 'dup@example.com',
                'full_name' => 'Second Person',
            ]))
            ->assertSessionHasErrors('email');

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($australia, $export, [
                'email' => 'dup@example.com',
                'full_name' => 'Australia Person',
            ]))
            ->assertRedirect(route('leads.index'));
    }
}
