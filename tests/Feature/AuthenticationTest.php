<?php

namespace Tests\Feature;

class AuthenticationTest extends ArgilTestCase
{
    public function test_login_works(): void
    {
        $department = $this->makeDepartment('Export');
        $country = $this->makeCountry('United Kingdom', 'GB', '+44');
        $user = $this->makeUser('staff', [$department], [$country]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('leads.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $department = $this->makeDepartment('Export');
        $country = $this->makeCountry('United Kingdom', 'GB', '+44');
        $user = $this->makeUser('staff', [$department], [$country], false);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_with_an_old_session_is_logged_out(): void
    {
        $department = $this->makeDepartment('Export');
        $country = $this->makeCountry('United Kingdom', 'GB', '+44');
        $user = $this->makeUser('staff', [$department], [$country]);

        $this->actingAs($user);
        $user->is_active = false;
        $user->save();

        $this->get(route('leads.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('leads.index'))->assertRedirect(route('login'));
    }
}
