<?php

namespace Tests\Unit;

use App\Models\Country;
use App\VisitingCardReader;
use Tests\TestCase;

class VisitingCardReaderTest extends TestCase
{
    public function test_it_reads_a_front_and_back_card_into_lead_fields(): void
    {
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
            'Export Department',
        ]);

        $fields = (new VisitingCardReader)->read($text, $this->countries());

        $this->assertSame('Mia Thompson', $fields['full_name']);
        $this->assertSame('Director', $fields['job_title']);
        $this->assertSame('Thompson Homes', $fields['company_name']);
        $this->assertSame('5 Collins Street', $fields['address']);
        $this->assertSame('Melbourne', $fields['city']);
        $this->assertSame('1', $fields['country_id']);
        $this->assertSame('+61', $fields['phone_country_code']);
        $this->assertSame('412345102', $fields['phone_number']);
        $this->assertSame('mia.thompson@example.com', $fields['email']);
        $this->assertSame('https://www.thompsonhomes.com', $fields['website']);
        $this->assertSame('Export Department', $fields['work_department']);
    }

    public function test_it_reads_a_local_number_when_the_country_is_on_the_card(): void
    {
        $text = implode("\n", [
            'Oliver Bennett',
            'Sales Manager',
            'Bennett Surfaces Ltd',
            '14 King Street',
            'London, United Kingdom',
            '07700 900 101',
            'oliver.bennett@example.com',
        ]);

        $fields = (new VisitingCardReader)->read($text, $this->countries());

        $this->assertSame('Oliver Bennett', $fields['full_name']);
        $this->assertSame('Sales Manager', $fields['job_title']);
        $this->assertSame('Bennett Surfaces Ltd', $fields['company_name']);
        $this->assertSame('14 King Street', $fields['address']);
        $this->assertSame('London', $fields['city']);
        $this->assertSame('2', $fields['country_id']);
        $this->assertSame('+44', $fields['phone_country_code']);
        $this->assertSame('7700900101', $fields['phone_number']);
        $this->assertSame('oliver.bennett@example.com', $fields['email']);
    }

    public function test_it_reads_an_indian_card_with_a_person_on_the_back(): void
    {
        $text = implode("\n", [
            'EE Ae rr',
            'KADAM',
            'N.K.Kadam',
            '9879348080',
            'www.kadamtravels.ccrm',
            'KADAM TOUR AND TRAVELS',
            '3, AKSHARDHAM APPARTMENT',
            'NEAR AKOTA GARDEN, BPC ROAD,',
            'AKOTA, VADODARA - 390020.',
            'Ph. : +91 7820082777',
            'Email : kadamtravels@hotmail.com',
            'web. : www.kadamtravels.com',
            'PROUDLY SERVING SINCE 2011',
            'United States',
        ]);

        $fields = (new VisitingCardReader)->read($text, $this->countries());

        $this->assertSame('N.K.Kadam', $fields['full_name']);
        $this->assertSame('KADAM TOUR AND TRAVELS', $fields['company_name']);
        $this->assertSame('+91', $fields['phone_country_code']);
        $this->assertSame('7820082777', $fields['phone_number']);
        $this->assertSame('kadamtravels@hotmail.com', $fields['email']);
        $this->assertSame('https://www.kadamtravels.com', $fields['website']);
        $this->assertSame('Vadodara', $fields['city']);
        $this->assertSame('4', $fields['country_id']);
        $this->assertStringContainsString('3, AKSHARDHAM APPARTMENT', $fields['address']);
        $this->assertStringContainsString('NEAR AKOTA GARDEN, BPC ROAD', $fields['address']);
        $this->assertStringContainsString('VADODARA - 390020', $fields['address']);
        $this->assertStringContainsString('Other number: +91 9879348080', $fields['notes']);
        $this->assertStringContainsString('PROUDLY SERVING SINCE 2011', $fields['notes']);
        $this->assertStringNotContainsString('EE Ae rr', $fields['notes']);
        $this->assertStringNotContainsString('United States', $fields['full_name']);
    }

    private function countries(): array
    {
        return [
            $this->country(1, 'Australia', 'AU', '+61'),
            $this->country(2, 'United Kingdom', 'GB', '+44'),
            $this->country(3, 'United Arab Emirates', 'AE', '+971'),
            $this->country(4, 'India', 'IN', '+91'),
            $this->country(5, 'United States', 'US', '+1'),
        ];
    }

    private function country($id, $name, $iso2, $dialCode): Country
    {
        $country = new Country;
        $country->id = $id;
        $country->name = $name;
        $country->iso2 = $iso2;
        $country->dial_code = $dialCode;

        return $country;
    }
}
