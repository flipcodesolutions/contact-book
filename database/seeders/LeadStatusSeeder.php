<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use Illuminate\Database\Seeder;

class LeadStatusSeeder extends Seeder
{
    /**
     * Starting statuses. Admins can rename them later in Settings.
     */
    public function run(): void
    {
        $statuses = [
            ['Lead Received', '#2F6F4E'],
            ['Quartz Inquired', '#C4A58D'],
            ['Sample Dispatched', '#1F4E79'],
            ['Crate Dispatch Review', '#9A4D12'],
        ];

        foreach ($statuses as $status) {
            LeadStatus::updateOrCreate(
                ['name' => $status[0]],
                ['color' => $status[1]]
            );
        }
    }
}
