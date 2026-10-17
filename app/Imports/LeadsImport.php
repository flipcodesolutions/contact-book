<?php

namespace App\Imports;

use App\Http\Requests\LeadRequest;
use App\Models\Country;
use App\Models\Department;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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
        $departments = Department::withTrashed()->get();
        $statuses = LeadStatus::query()->get();

        foreach ($pendingRows as $pendingRow) {
            $this->importRow($pendingRow['row_number'], $pendingRow['row'], $countries, $departments, $statuses);
        }
    }

    private function importRow($rowNumber, $row, $countries, $departments, $statuses)
    {
        // 1. Phone country code and phone number
        $rawPhoneCode = $this->firstCell($row, [
            'phone_country_code', 'dial_code', 'phone_code', 'country_code', 'calling_code', 'code',
        ]);
        $rawPhone = $this->firstCell($row, [
            'phone_number', 'phone', 'mobile', 'contact_number', 'mobile_number', 'contact',
        ]);

        // If phone number starts with + and no separate dial code, extract it
        if ($rawPhoneCode === '' && str_starts_with($rawPhone, '+')) {
            $matchedCountry = $countries->sortByDesc(fn ($c) => strlen($c->dial_code))->first(function ($c) use ($rawPhone) {
                return str_starts_with($rawPhone, $c->dial_code);
            });

            if ($matchedCountry) {
                $rawPhoneCode = $matchedCountry->dial_code;
                $rawPhone = substr($rawPhone, strlen($matchedCountry->dial_code));
            }
        }

        // 2. Resolve Country (by ISO2, name, alias, dial code, or optional)
        $rawCountry = $this->firstCell($row, [
            'country_iso2', 'country', 'country_name', 'iso2', 'country_code', 'iso',
        ]);

        $countryResult = $this->resolveCountry($rawCountry, $rawPhoneCode, $countries);

        if ($countryResult['error'] !== null) {
            $this->skip($rowNumber, $countryResult['error']);

            return;
        }

        $country = $countryResult['country'];

        // If phone country code is still missing but country is known, default to country's dial code
        if ($rawPhoneCode === '' && $country !== null && $country->dial_code) {
            $rawPhoneCode = $country->dial_code;
        }

        $phoneCountryCode = $this->dialCode($rawPhoneCode);

        // 3. Departments
        $rawDepartments = $this->firstCell($row, ['departments', 'department', 'dept', 'depts']);
        $departmentNames = $this->departmentNames($rawDepartments);
        $departmentIds = [];

        foreach ($departmentNames as $departmentName) {
            $department = null;

            foreach ($departments as $possibleDepartment) {
                if (strcasecmp($possibleDepartment->name, $departmentName) === 0) {
                    $department = $possibleDepartment;
                    break;
                }
            }

            if ($department && $department->trashed()) {
                $department->restore();
            }

            if ($department === null && $this->user->canManageDepartments()) {
                $department = Department::create([
                    'name' => $departmentName,
                    'is_active' => true,
                ]);
                $departments->push($department);
            }

            if ($department === null) {
                $this->skip($rowNumber, 'Department "'.$departmentName.'" was not found.');

                return;
            }

            $departmentIds[] = $department->id;
        }

        // 4. Status
        $rawStatus = $this->firstCell($row, ['status', 'lead_status']);
        $statusId = null;

        if ($rawStatus !== '') {
            $status = null;

            foreach ($statuses as $possibleStatus) {
                if (strtolower($possibleStatus->name) === strtolower($rawStatus)) {
                    $status = $possibleStatus;
                    break;
                }
            }

            if ($status === null) {
                $this->skip($rowNumber, 'Status "'.$rawStatus.'" was not found.');

                return;
            }

            $statusId = $status->id;
        } else {
            $defaultStatus = $statuses->firstWhere('name', 'Lead Received') ?? $statuses->first();
            $statusId = $defaultStatus ? $defaultStatus->id : null;
        }

        // 5. Source
        $rawSource = $this->firstCell($row, ['source', 'lead_source']);

        $countryId = $country ? $country->id : null;

        $input = LeadRequest::normalizeRow([
            'full_name' => $this->firstCell($row, ['full_name', 'name', 'contact_name', 'client_name']),
            'phone_country_code' => $phoneCountryCode,
            'phone_number' => $rawPhone,
            'email' => $this->firstCell($row, ['email', 'email_address', 'mail']),
            'website' => $this->firstCell($row, ['website', 'web', 'url']),
            'company_name' => $this->firstCell($row, ['company_name', 'company', 'business_name', 'organization']),
            'job_title' => $this->firstCell($row, ['job_title', 'title', 'designation']),
            'work_department' => $this->firstCell($row, ['work_department', 'client_department']),
            'address' => $this->firstCell($row, ['address', 'street', 'street_address']),
            'notes' => $this->firstCell($row, ['notes', 'note', 'comments', 'remarks']),
            'city' => $this->firstCell($row, ['city', 'town']),
            'birthday' => $this->dateCell($row, ['birthday', 'birth_date', 'dob']),
            'anniversary' => $this->dateCell($row, ['anniversary', 'anniversary_date']),
            'other_date_label' => $this->firstCell($row, ['other_date_label']),
            'other_date' => $this->dateCell($row, ['other_date']),
            'country_id' => $countryId,
            'department_ids' => $departmentIds,
            'lead_status_id' => $statusId,
            'lead_source_id' => $this->sourceId($rawSource),
            'source_name' => $this->sourceName($rawSource),
        ]);

        // Check if an existing lead with this email exists for this country (or no country)
        $existingLead = null;
        if (! empty($input['email'])) {
            $existingLead = Lead::query()
                ->where('email', strtolower($input['email']))
                ->where(function ($query) use ($countryId) {
                    if ($countryId) {
                        $query->where('country_id', $countryId);
                    } else {
                        $query->whereNull('country_id');
                    }
                })
                ->first();
        }

        $validator = Validator::make(
            $input,
            LeadRequest::rulesFor($this->user, $existingLead, $countryId, $departmentIds),
            LeadRequest::messageList()
        );

        if ($validator->fails()) {
            $this->skip($rowNumber, implode(' ', $validator->errors()->all()));

            return;
        }

        $validated = LeadRequest::applySource($validator->validated());
        $chosenDepartmentIds = $validated['department_ids'] ?? [];
        unset($validated['department_ids']);

        try {
            if ($existingLead) {
                // Update existing lead with non-empty fields
                $updateData = array_filter($validated, fn ($val) => $val !== null && $val !== '');
                $existingLead->update($updateData);
                if (! empty($chosenDepartmentIds)) {
                    $existingLead->departments()->sync($chosenDepartmentIds);
                }
                $this->importedCount++;
            } else {
                $validated['created_by'] = $this->user->id;
                $lead = Lead::create($validated);
                if (! empty($chosenDepartmentIds)) {
                    $lead->departments()->sync($chosenDepartmentIds);
                }
                $this->importedCount++;
            }
        } catch (\Exception $exception) {
            report($exception);
            $this->skip($rowNumber, 'This row could not be saved.');
        }
    }

    private function resolveCountry($rawCountry, $phoneCountryCode, $countries): array
    {
        $rawCountry = trim((string) $rawCountry);

        if ($rawCountry !== '') {
            $upper = strtoupper($rawCountry);

            // Exact ISO2 code match
            foreach ($countries as $c) {
                if (strtoupper($c->iso2) === $upper) {
                    return ['country' => $c, 'error' => null];
                }
            }

            // Exact Country Name match (case-insensitive)
            foreach ($countries as $c) {
                if (strcasecmp($c->name, $rawCountry) === 0) {
                    return ['country' => $c, 'error' => null];
                }
            }

            // Common aliases
            $aliasMap = [
                'UK' => 'GB',
                'USA' => 'US',
                'AMERICA' => 'US',
                'UAE' => 'AE',
                'DUBAI' => 'AE',
                'SAUDI' => 'SA',
                'KSA' => 'SA',
                'NZ' => 'NZ',
                'ENGLAND' => 'GB',
                'GREAT BRITAIN' => 'GB',
            ];

            if (isset($aliasMap[$upper])) {
                $mappedIso = $aliasMap[$upper];
                foreach ($countries as $c) {
                    if (strtoupper($c->iso2) === $mappedIso) {
                        return ['country' => $c, 'error' => null];
                    }
                }
            }

            // Partial match
            foreach ($countries as $c) {
                if (stripos($c->name, $rawCountry) !== false || stripos($rawCountry, $c->name) !== false) {
                    return ['country' => $c, 'error' => null];
                }
            }

            return [
                'country' => null,
                'error' => 'Country "'.$rawCountry.'" was not found or is inactive. Please use a valid country name or 2-letter code (e.g. India or IN).',
            ];
        }

        // If country cell was empty, check dial code
        if ($phoneCountryCode !== '') {
            $dialCode = $this->dialCode($phoneCountryCode);
            foreach ($countries as $c) {
                if ($c->dial_code === $dialCode) {
                    return ['country' => $c, 'error' => null];
                }
            }
        }

        // If user has access to only one country, default to it
        if (! $this->user->all_countries && ! $this->user->seesAllLeads()) {
            $userCountries = $this->user->countries()->where('countries.is_active', true)->get();
            if ($userCountries->count() === 1) {
                return ['country' => $userCountries->first(), 'error' => null];
            }
        }

        // Country is optional/nullable
        return ['country' => null, 'error' => null];
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
        $keys = [
            'full_name', 'name', 'contact_name',
            'email', 'email_address', 'mail',
            'phone_number', 'phone', 'mobile', 'contact_number',
            'company_name', 'company',
            'city',
            'country_iso2', 'country', 'country_name', 'country_code', 'iso2',
        ];

        foreach ($keys as $key) {
            if ($this->cell($row, $key) !== '') {
                return false;
            }
        }

        return true;
    }

    private function firstCell($row, array $keys): string
    {
        foreach ($keys as $key) {
            $val = $this->cell($row, $key);
            if ($val !== '') {
                return $val;
            }
        }

        return '';
    }

    private function dateCell($row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! isset($row[$key]) || $row[$key] === null || $row[$key] === '') {
                continue;
            }

            $value = $row[$key];

            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            if (is_numeric($value) && (int) $value > 1000 && (int) $value < 60000) {
                try {
                    return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // fall through
                }
            }

            $str = trim((string) $value);
            if ($str === '') {
                continue;
            }

            try {
                return Carbon::parse($str)->format('Y-m-d');
            } catch (\Throwable $e) {
                return $str;
            }
        }

        return null;
    }

    private function departmentNames($value): array
    {
        if ($value === '') {
            return [];
        }

        $names = [];

        foreach (explode(',', $value) as $name) {
            $name = trim(trim($name), '"\'');

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
        $value = str_replace(' ', '', (string) $value);

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
