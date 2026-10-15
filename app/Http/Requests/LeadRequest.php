<?php

namespace App\Http\Requests;

use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
{
    /**
     * Viewers cannot create or edit. Everyone else who can create leads can.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->canCreateLeads();
    }

    /**
     * Trim text, store the email in lowercase, and keep only digits in the phone number.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(self::normalizeRow($this->all()));
    }

    public function rules(): array
    {
        $lead = null;
        $leadId = $this->route('lead');

        if ($leadId) {
            $lead = Lead::visibleTo($this->user())->with('departments')->findOrFail($leadId);
        }

        $rules = self::rulesFor($this->user(), $lead, $this->input('country_id'), $this->input('department_ids', []));
        $rules['card_front'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        $rules['card_back'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        return $rules;
    }

    /**
     * The same rules are used by the lead form and by each Excel row.
     * A department right allows any country. A country right allows any department.
     */
    public static function rulesFor($user, ?Lead $lead = null, $countryId = null, $departmentIds = null): array
    {
        if ($departmentIds === null) {
            $departmentIds = [];
        }

        $selectedDepartmentIds = [];

        foreach ((array) $departmentIds as $departmentId) {
            $selectedDepartmentIds[] = (int) $departmentId;
        }

        $emailRule = Rule::unique('leads', 'email')->where(function ($query) use ($countryId) {
            $query->where('country_id', $countryId)->whereNull('deleted_at');
        });

        if ($lead) {
            $emailRule->ignore($lead->id);
        }

        return [
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'phone_country_code' => ['required', 'string', Rule::exists('countries', 'dial_code')->where('is_active', true)],
            'phone_number' => ['required', 'regex:/^[0-9]{6,15}$/'],
            'email' => ['required', 'email', 'max:190', $emailRule],
            'website' => ['nullable', 'string', 'max:190'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'work_department' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'birthday' => ['nullable', 'date'],
            'anniversary' => ['nullable', 'date'],
            'other_date_label' => ['nullable', 'string', 'max:100'],
            'other_date' => ['nullable', 'date'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id', function ($attribute, $value, $fail) use ($user, $selectedDepartmentIds) {
                $countryMissing = $value === null || $value === '';

                if ($countryMissing && count($selectedDepartmentIds) === 0) {
                    return;
                }

                if ($user->canUseLeadAccess($value, $selectedDepartmentIds)) {
                    return;
                }

                $fail('This department and country combination is outside your access. Both country and department must match your access.');
            }],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'lead_status_id' => ['nullable', 'integer', 'exists:lead_statuses,id'],
            'lead_source_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value === null || $value === '' || $value === 'other') {
                    return;
                }

                if (! LeadSource::query()->where('id', $value)->exists()) {
                    $fail('Choose a valid source.');
                }
            }],
            'source_name' => ['nullable', 'string', 'max:100', 'required_if:lead_source_id,other'],
        ];
    }

    /**
     * Turn the Other source box into a saved source id after validation passes.
     */
    public static function applySource(array $validated): array
    {
        $sourceName = isset($validated['source_name']) ? $validated['source_name'] : null;
        unset($validated['source_name']);

        if (isset($validated['lead_source_id']) && $validated['lead_source_id'] === 'other') {
            $validated['lead_source_id'] = LeadSource::storeName($sourceName);
        }

        if (! isset($validated['lead_source_id']) || $validated['lead_source_id'] === '') {
            $validated['lead_source_id'] = null;
        }

        return $validated;
    }

    public function messages(): array
    {
        return self::messageList();
    }

    public static function messageList(): array
    {
        return [
            'full_name.required' => 'Enter the contact name.',
            'full_name.min' => 'The name must be at least 2 characters.',
            'phone_country_code.required' => 'Choose a dial code.',
            'phone_country_code.exists' => 'Choose a valid dial code.',
            'phone_number.required' => 'Enter the contact number.',
            'phone_number.regex' => 'Enter 6 to 15 digits. Spaces and dashes are removed automatically.',
            'email.required' => 'Enter an email address.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'A lead with this email already exists for the selected country.',
            'country_id.required' => 'Choose a country.',
            'country_id.exists' => 'Choose a valid country.',
            'department_ids.required' => 'Choose a valid department.',
            'department_ids.*.exists' => 'Choose a valid department.',
            'department_ids.*.integer' => 'Choose a valid department.',
            'lead_status_id.exists' => 'Choose a valid status.',
            'source_name.required_if' => 'Type the new source name.',
            'birthday.date' => 'Enter a valid birthday.',
            'anniversary.date' => 'Enter a valid anniversary.',
            'other_date.date' => 'Enter a valid date.',
            'card_front.image' => 'The front of the card must be a photo.',
            'card_front.mimes' => 'Use a JPG, PNG, or WebP photo for the front.',
            'card_front.max' => 'The front photo must be 5 MB or smaller.',
            'card_back.image' => 'The back of the card must be a photo.',
            'card_back.mimes' => 'Use a JPG, PNG, or WebP photo for the back.',
            'card_back.max' => 'The back photo must be 5 MB or smaller.',
        ];
    }

    /**
     * Clean one form submission or one spreadsheet row before validation.
     */
    public static function normalizeRow(array $input): array
    {
        $phone = isset($input['phone_number']) ? (string) $input['phone_number'] : '';
        $phone = str_replace([' ', '-'], '', $phone);

        $address = isset($input['address']) ? trim((string) $input['address']) : '';
        $notes = isset($input['notes']) ? trim((string) $input['notes']) : '';
        $statusId = isset($input['lead_status_id']) ? $input['lead_status_id'] : null;
        $sourceId = isset($input['lead_source_id']) ? $input['lead_source_id'] : null;

        if ($statusId === '') {
            $statusId = null;
        }

        if ($sourceId === '') {
            $sourceId = null;
        }

        $input['full_name'] = trim((string) (isset($input['full_name']) ? $input['full_name'] : ''));
        $input['email'] = strtolower(trim((string) (isset($input['email']) ? $input['email'] : '')));
        $input['phone_number'] = $phone;
        $input['phone_country_code'] = trim((string) (isset($input['phone_country_code']) ? $input['phone_country_code'] : ''));
        $input['company_name'] = self::blankToNull(isset($input['company_name']) ? $input['company_name'] : null);
        $input['city'] = self::blankToNull(isset($input['city']) ? $input['city'] : null);
        $input['country_id'] = isset($input['country_id']) && $input['country_id'] !== '' ? $input['country_id'] : null;
        $input['address'] = $address === '' ? null : $address;
        $input['notes'] = $notes === '' ? null : $notes;
        $input['website'] = self::blankToNull(isset($input['website']) ? $input['website'] : null);
        $input['job_title'] = self::blankToNull(isset($input['job_title']) ? $input['job_title'] : null);
        $input['work_department'] = self::blankToNull(isset($input['work_department']) ? $input['work_department'] : null);
        $input['other_date_label'] = self::blankToNull(isset($input['other_date_label']) ? $input['other_date_label'] : null);
        $input['source_name'] = self::blankToNull(isset($input['source_name']) ? $input['source_name'] : null);
        $input['birthday'] = self::blankToNull(isset($input['birthday']) ? $input['birthday'] : null);
        $input['anniversary'] = self::blankToNull(isset($input['anniversary']) ? $input['anniversary'] : null);
        $input['other_date'] = self::blankToNull(isset($input['other_date']) ? $input['other_date'] : null);
        $input['lead_status_id'] = $statusId;
        $input['lead_source_id'] = $sourceId;

        return $input;
    }

    private static function blankToNull($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $value;
    }
}
