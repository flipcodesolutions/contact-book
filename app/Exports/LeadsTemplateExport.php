<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'full_name',
            'phone_country_code',
            'phone_number',
            'email',
            'company_name',
            'address',
            'city',
            'country_iso2',
            'departments',
            'status',
            'website',
            'notes',
            'birthday',
            'anniversary',
            'other_date_label',
            'other_date',
            'job_title',
            'work_department',
            'source',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Oliver Bennett',
                '+44',
                '7700900123',
                'oliver@example.com',
                'Bennett Surfaces',
                '1 High Street',
                'London',
                'GB',
                'Export, Sales',
                'Lead Received',
                'https://example.com',
                'Met at the show',
                '1985-04-12',
                '2010-06-01',
                'Site visit',
                '2026-11-02',
                'Director',
                'Procurement',
                'Website',
            ],
        ];
    }
}
