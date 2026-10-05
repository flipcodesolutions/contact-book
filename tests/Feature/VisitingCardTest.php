<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VisitingCardTest extends ArgilTestCase
{
    public function test_card_text_is_turned_into_lead_fields(): void
    {
        $export = $this->makeDepartment('Export');
        $australia = $this->makeCountry('Australia', 'AU', '+61');
        $staff = $this->makeUser('staff', [$export], [$australia]);

        $text = implode("\n", [
            'Mia Thompson',
            'Director',
            'Thompson Homes',
            '5 Collins Street',
            'Melbourne',
            'Australia',
            '+61 412 345 102',
            'mia.thompson@example.com',
            'www.thompsonhomes.com',
        ]);

        $this->actingAs($staff)
            ->postJson(route('leads.visiting-card'), ['text' => $text])
            ->assertOk()
            ->assertJsonPath('fields.full_name', 'Mia Thompson')
            ->assertJsonPath('fields.email', 'mia.thompson@example.com')
            ->assertJsonPath('fields.phone_country_code', '+61')
            ->assertJsonPath('fields.phone_number', '412345102')
            ->assertJsonPath('fields.country_id', (string) $australia->id);
    }

    public function test_a_viewer_cannot_read_a_visiting_card(): void
    {
        $export = $this->makeDepartment('Export');
        $australia = $this->makeCountry('Australia', 'AU', '+61');
        $viewer = $this->makeUser('viewer', [$export], [$australia]);

        $this->actingAs($viewer)
            ->postJson(route('leads.visiting-card'), ['text' => 'Mia Thompson'])
            ->assertForbidden();
    }

    public function test_creating_a_lead_stores_the_front_and_back_photos(): void
    {
        Storage::fake('public');

        $export = $this->makeDepartment('Export');
        $australia = $this->makeCountry('Australia', 'AU', '+61');
        $staff = $this->makeUser('staff', [$export], [$australia]);

        $this->actingAs($staff)
            ->post(route('leads.store'), $this->leadPayload($australia, $export, [
                'email' => 'card.lead@example.com',
                'card_front' => UploadedFile::fake()->image('front.jpg'),
                'card_back' => UploadedFile::fake()->image('back.jpg'),
            ]))
            ->assertRedirect(route('leads.index'));

        $lead = Lead::query()->where('email', 'card.lead@example.com')->first();

        $this->assertNotNull($lead);
        $this->assertNotNull($lead->card_front);
        $this->assertNotNull($lead->card_back);
        Storage::disk('public')->assertExists($lead->card_front);
        Storage::disk('public')->assertExists($lead->card_back);
    }
}
