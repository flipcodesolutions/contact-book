<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoLeadSeeder extends Seeder
{
    /**
     * A few UK and Australia samples so the lead list is not empty after setup.
     */
    public function run(): void
    {
        $owner = User::query()->where('role', 'super_admin')->first();

        if ($owner === null) {
            return;
        }

        $samples = [
            ['Oliver Bennett', '+44', '7700900101', 'oliver.bennett@example.com', 'Bennett Surfaces', '14 King Street', 'London', 'GB', ['Export', 'Sales'], 'Lead Received'],
            ['Amelia Hughes', '+44', '7700900102', 'amelia.hughes@example.com', 'Hughes Stone', '8 Market Road', 'Manchester', 'GB', ['Domestic'], 'Sample Dispatched'],
            ['Noah Walker', '+61', '412345101', 'noah.walker@example.com', 'Walker Interiors', '22 George Street', 'Sydney', 'AU', ['Marketing'], 'Quartz Inquired'],
            ['Mia Thompson', '+61', '412345102', 'mia.thompson@example.com', 'Thompson Homes', '5 Collins Street', 'Melbourne', 'AU', ['Export'], 'Crate Dispatch Review'],
        ];

        foreach ($samples as $sample) {
            $alreadyExists = Lead::query()
                ->where('email', $sample[3])
                ->whereHas('country', function ($query) use ($sample) {
                    $query->where('iso2', $sample[7]);
                })
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $country = Country::query()->where('iso2', $sample[7])->first();
            $status = LeadStatus::query()->where('name', $sample[9])->first();

            if ($country === null) {
                continue;
            }

            $lead = new Lead;
            $lead->full_name = $sample[0];
            $lead->phone_country_code = $sample[1];
            $lead->phone_number = $sample[2];
            $lead->email = $sample[3];
            $lead->company_name = $sample[4];
            $lead->address = $sample[5];
            $lead->city = $sample[6];
            $lead->country_id = $country->id;
            $lead->lead_status_id = $status ? $status->id : null;
            $lead->created_by = $owner->id;
            $lead->save();

            $departmentIds = Department::query()->whereIn('name', $sample[8])->pluck('id')->all();
            $lead->departments()->sync($departmentIds);
        }
    }
}
