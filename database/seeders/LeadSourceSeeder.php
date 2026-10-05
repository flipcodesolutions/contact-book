<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Starting sources. The lead form can add more through the Other option.
     */
    public function run(): void
    {
        $names = ['Website', 'Referral', 'Trade Show', 'Email', 'Phone', 'Social Media'];

        foreach ($names as $name) {
            LeadSource::updateOrCreate(['name' => $name]);
        }
    }
}
