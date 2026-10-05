<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Common markets, with ISO2 codes and dial codes for the phone field.
     */
    public function run(): void
    {
        $countries = [
            ['United Kingdom', 'GB', '+44'],
            ['Australia', 'AU', '+61'],
            ['India', 'IN', '+91'],
            ['United Arab Emirates', 'AE', '+971'],
            ['United States', 'US', '+1'],
            ['Canada', 'CA', '+1'],
            ['Germany', 'DE', '+49'],
            ['France', 'FR', '+33'],
            ['Italy', 'IT', '+39'],
            ['Spain', 'ES', '+34'],
            ['Netherlands', 'NL', '+31'],
            ['Belgium', 'BE', '+32'],
            ['Switzerland', 'CH', '+41'],
            ['Austria', 'AT', '+43'],
            ['Sweden', 'SE', '+46'],
            ['Norway', 'NO', '+47'],
            ['Denmark', 'DK', '+45'],
            ['Finland', 'FI', '+358'],
            ['Ireland', 'IE', '+353'],
            ['Portugal', 'PT', '+351'],
            ['Poland', 'PL', '+48'],
            ['Greece', 'GR', '+30'],
            ['Turkey', 'TR', '+90'],
            ['Saudi Arabia', 'SA', '+966'],
            ['Qatar', 'QA', '+974'],
            ['Kuwait', 'KW', '+965'],
            ['Oman', 'OM', '+968'],
            ['Bahrain', 'BH', '+973'],
            ['Singapore', 'SG', '+65'],
            ['Malaysia', 'MY', '+60'],
            ['New Zealand', 'NZ', '+64'],
            ['South Africa', 'ZA', '+27'],
            ['Japan', 'JP', '+81'],
            ['China', 'CN', '+86'],
            ['Hong Kong', 'HK', '+852'],
            ['South Korea', 'KR', '+82'],
            ['Brazil', 'BR', '+55'],
            ['Mexico', 'MX', '+52'],
            ['Indonesia', 'ID', '+62'],
            ['Thailand', 'TH', '+66'],
            ['Vietnam', 'VN', '+84'],
            ['Philippines', 'PH', '+63'],
            ['Egypt', 'EG', '+20'],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso2' => $country[1]],
                [
                    'name' => $country[0],
                    'dial_code' => $country[2],
                    'is_active' => true,
                ]
            );
        }
    }
}
