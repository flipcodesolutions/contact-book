<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExport implements FromArray, WithHeadings
{
    private $rows;

    public function __construct($leads)
    {
        $rows = [];

        foreach ($leads as $lead) {
            $departmentNames = [];

            foreach ($lead->departments as $department) {
                $departmentNames[] = $department->name;
            }

            $rows[] = [
                $lead->full_name,
                $lead->phone_country_code,
                $lead->phone_number,
                $lead->email,
                $lead->company_name,
                $lead->address,
                $lead->city,
                $lead->country ? $lead->country->iso2 : '',
                implode(', ', $departmentNames),
                $lead->status ? $lead->status->name : '',
                $lead->website,
                $lead->notes,
                $lead->birthday ? $lead->birthday->format('Y-m-d') : '',
                $lead->anniversary ? $lead->anniversary->format('Y-m-d') : '',
                $lead->other_date_label,
                $lead->other_date ? $lead->other_date->format('Y-m-d') : '',
                $lead->job_title,
                $lead->work_department,
                $lead->source ? $lead->source->name : '',
            ];
        }

        $this->rows = $rows;
    }

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
        return $this->rows;
    }
}
