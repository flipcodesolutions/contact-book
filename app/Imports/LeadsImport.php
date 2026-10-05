<?php

namespace App\Imports;

use App\Http\Requests\LeadRequest;
use App\Models\Country;
use App\Models\Department;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeadsImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;

    public $skippedCount = 0;

    public $errors = [];

    public $tooManyRows = false;

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Read every data row. Good rows are saved. Bad rows are skipped
     * and listed with their spreadsheet row number.
     */
    public function collection(Collection $rows)
    {
        $pendingRows = [];

        foreach ($rows as $index => $row) {
            if ($this->isBlank($row)) {
                continue;
            }

            // Heading is row 1, so the first data row is row 2.
            $pendingRows[] = [
                'row_number' => $index + 2,
                'row' => $row,
            ];
        }

        if (count($pendingRows) > 1000) {
            $this->tooManyRows = true;

            return;
        }

        $countries = Country::query()->where('is_active', true)->get();
        $departments = Department::query()->where('is_active', true)->get();
        $statuses = LeadStatus::query()->get();

        foreach ($pendingRows as $pendingRow) {
            $this->importRow($pendingRow['row_number'], $pendingRow['row'], $countries, $departments, $statuses);
        }
    }

    private function importRow($rowNumber, $row, $countries, $departments, $statuses)
    {
        $iso2 = strtoupper($this->cell($row, 'country_iso2'));
        $country = null;

        foreach ($countries as $possibleCountry) {
            if (strtoupper($possibleCountry->iso2) === $iso2) {
                $country = $possibleCountry;
                break;
            }
        }

        if ($country === null) {
            $this->skip($rowNumber, 'Country code "'.$iso2.'" was not found or is inactive.');

            return;
        }

        $departmentNames = $this->departmentNames($this->cell($row, 'departments'));
        $departmentIds = [];

        foreach ($departmentNames as $departmentName) {
            $department = null;

            foreach ($departments as $possibleDepartment) {
                if (strtolower($possibleDepartment->name) === strtolower($departmentName)) {
                    $department = $possibleDepartment;
                    break;
                }
            }

            if ($department === null) {
                $this->skip($rowNumber, 'Department "'.$departmentName.'" was not found or is inactive.');

                return;
            }

            $departmentIds[] = $department->id;
        }

        $statusName = $this->cell($row, 'status');
        $statusId = null;

        if ($statusName !== '') {
            $status = null;

            foreach ($statuses as $possibleStatus) {
                if (strtolower($possibleStatus->name) === strtolower($statusName)) {
                    $status = $possibleStatus;
                    break;
                }
            }

            if ($status === null) {
                $this->skip($rowNumber, 'Status "'.$statusName.'" was not found.');

                return;
            }

            $statusId = $status->id;
        }

        $input = LeadRequest::normalizeRow([
            'full_name' => $this->cell($row, 'full_name'),
            'phone_country_code' => $this->dialCode($this->cell($row, 'phone_country_code')),
            'phone_number' => $this->cell($row, 'phone_number'),
            'email' => $this->cell($row, 'email'),
            'website' => $this->cell($row, 'website'),
            'company_name' => $this->cell($row, 'company_name'),
            'job_title' => $this->cell($row, 'job_title'),
            'work_department' => $this->cell($row, 'work_department'),
            'address' => $this->cell($row, 'address'),
            'notes' => $this->cell($row, 'notes'),
            'city' => $this->cell($row, 'city'),
            'birthday' => $this->cell($row, 'birthday'),
            'anniversary' => $this->cell($row, 'anniversary'),
            'other_date_label' => $this->cell($row, 'other_date_label'),
            'other_date' => $this->cell($row, 'other_date'),
            'country_id' => $country->id,
            'department_ids' => $departmentIds,
            'lead_status_id' => $statusId,
            'lead_source_id' => $this->sourceId($this->cell($row, 'source')),
            'source_name' => $this->sourceName($this->cell($row, 'source')),
        ]);

        $validator = Validator::make($input, LeadRequest::rulesFor($this->user, null, $country->id, $departmentIds), LeadRequest::messageList());

        if ($validator->fails()) {
            $this->skip($rowNumber, implode(' ', $validator->errors()->all()));

            return;
        }

        $validated = LeadRequest::applySource($validator->validated());
        $chosenDepartmentIds = $validated['department_ids'];
        unset($validated['department_ids']);
        $validated['created_by'] = $this->user->id;

        try {
            $lead = Lead::create($validated);
            $lead->departments()->sync($chosenDepartmentIds);
            $this->importedCount++;
        } catch (\Exception $exception) {
            $this->skip($rowNumber, 'This row could not be saved.');
        }
    }

    private function skip($rowNumber, $message)
    {
        $this->skippedCount++;

        if (count($this->errors) < 100) {
            $this->errors[] = [
                'row' => $rowNumber,
                'message' => $message,
            ];
        }
    }

    private function isBlank($row): bool
    {
        $keys = ['full_name', 'email', 'phone_number', 'company_name', 'city', 'country_iso2'];

        foreach ($keys as $key) {
            if ($this->cell($row, $key) !== '') {
                return false;
            }
        }

        return true;
    }

    private function departmentNames($value): array
    {
        if ($value === '') {
            return [];
        }

        $names = [];

        foreach (explode(',', $value) as $name) {
            $name = trim($name);

            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * Spreadsheet dial codes sometimes arrive as 44 instead of +44.
     */
    private function dialCode($value): string
    {
        $value = str_replace(' ', '', $value);

        if ($value !== '' && $value[0] !== '+') {
            $value = '+'.$value;
        }

        return $value;
    }

    /**
     * Excel often turns a phone number into a float. Keep it as a plain string.
     */
    private function cell($row, $key): string
    {
        if (! isset($row[$key]) || $row[$key] === null) {
            return '';
        }

        $value = $row[$key];

        if (is_int($value) || is_float($value)) {
            return number_format($value, 0, '.', '');
        }

        return trim((string) $value);
    }

    /**
     * Use an existing source id, or "other" so a new name is saved after the row is valid.
     */
    private function sourceId($name)
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        foreach (LeadSource::query()->get() as $source) {
            if (strtolower($source->name) === strtolower($name)) {
                return $source->id;
            }
        }

        return 'other';
    }

    private function sourceName($name)
    {
        if ($this->sourceId($name) === 'other') {
            return trim((string) $name);
        }

        return null;
    }
}
