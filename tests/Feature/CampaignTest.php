<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    /**
     * Test that the campaign creation page loads successfully.
     */
    public function test_campaign_create_page_loads(): void
    {
        $response = $this->get(route('campaigns.create'));

        $response->assertStatus(200);
        $response->assertSee('Create camapign');
        $response->assertSee('Campaign Details');
        $response->assertSee('Upload Contact File');
        $response->assertSee('Imported Recipients');
    }

    /**
     * Test that Step 2: Email Content page loads with dispatch parameters.
     */
    public function test_campaign_step_2_email_content_loads(): void
    {
        $response = $this->get(route('campaigns.create', ['step' => 2]));

        $response->assertStatus(200);
        $response->assertSee('Email Content');
        $response->assertSee('Dispatch Parameters');
        $response->assertSee('SELECT FORMAT');
        $response->assertSee('Curated Template');
        $response->assertSee('SUBJECT');
        $response->assertSee('Body &amp; Inbox Snippet', false);
    }

    /**
     * Test that saving a campaign stores it in the database.
     */
    public function test_campaign_can_be_saved(): void
    {
        $country = Country::first();
        $department = Department::first();

        $response = $this->post(route('campaigns.store'), [
            'name' => 'Australia SPC October Launch',
            'country_id' => $country->id,
            'department_id' => $department->id,
            'contact_file_name' => 'Australia_SPC_Leads_',
            'contact_file_size' => '420 KB',
        ]);

        $response->assertRedirect(route('campaigns.create'));
        $this->assertDatabaseHas('campaigns', [
            'name' => 'Australia SPC October Launch',
            'country_id' => $country->id,
            'department_id' => $department->id,
        ]);
    }

    /**
     * Test uploading a contact spreadsheet.
     */
    public function test_campaign_file_upload(): void
    {
        $file = UploadedFile::fake()->create('Australia_SPC_Leads.csv', 420);

        $response = $this->postJson(route('campaigns.upload'), [
            'contact_file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'fileName' => 'Australia_SPC_Leads',
        ]);
    }
}
