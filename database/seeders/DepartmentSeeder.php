<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * The departments Argil routes leads into.
     */
    public function run(): void
    {
        $names = ['Export', 'Marketing', 'Sales', 'Accounts', 'Management', 'Domestic'];

        foreach ($names as $name) {
            Department::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
