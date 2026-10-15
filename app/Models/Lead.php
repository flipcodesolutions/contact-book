<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone_country_code',
        'phone_number',
        'email',
        'website',
        'company_name',
        'job_title',
        'work_department',
        'address',
        'notes',
        'city',
        'birthday',
        'anniversary',
        'other_date_label',
        'other_date',
        'country_id',
        'lead_status_id',
        'lead_source_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'anniversary' => 'date',
            'other_date' => 'date',
        ];
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    /**
     * tel: link using the dial code and the number, digits only.
     */
    public function callLink(): string
    {
        return 'tel:+'.$this->phoneDigits();
    }

    /**
     * WhatsApp needs the country code and number with no plus sign.
     */
    public function whatsappLink(): string
    {
        return 'https://wa.me/'.$this->phoneDigits();
    }

    /**
     * Open the address in Google Maps.
     */
    public function mapLink(): string
    {
        $parts = [];

        if ($this->address) {
            $parts[] = $this->address;
        }

        if ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->country) {
            $parts[] = $this->country->name;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.urlencode(implode(', ', $parts));
    }

    public function phoneDigits(): string
    {
        return preg_replace('/[^0-9]/', '', $this->phone_country_code.$this->phone_number);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    /**
     * Limit the query to leads this user is allowed to see.
     * Admin and super admin see all leads.
     * Other users only see leads matching their assigned country AND department.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->seesAllLeads()) {
            return $query;
        }

        $hasAllCountries = (bool) $user->all_countries;
        $hasAllDepartments = (bool) $user->all_departments;
        $countryIds = $user->countries()->pluck('countries.id')->all();
        $departmentIds = $user->departments()->pluck('departments.id')->all();

        if (! $hasAllCountries && count($countryIds) === 0 && ! $hasAllDepartments && count($departmentIds) === 0) {
            return $query->where('created_by', $user->id);
        }

        return $query->where(function ($leadQuery) use ($countryIds, $departmentIds, $hasAllCountries, $hasAllDepartments) {
            if (! $hasAllCountries) {
                if (count($countryIds) === 0) {
                    $leadQuery->whereRaw('0 = 1');
                } else {
                    $leadQuery->whereIn('country_id', $countryIds);
                }
            }

            if (! $hasAllDepartments) {
                if (count($departmentIds) === 0) {
                    $leadQuery->whereRaw('0 = 1');
                } else {
                    $leadQuery->whereHas('departments', function ($departmentQuery) use ($departmentIds) {
                        $departmentQuery->whereIn('departments.id', $departmentIds);
                    });
                }
            }
        });
    }

    /**
     * Apply the leads-list filters. Each filter is its own if block
     * so it is easy to add another one later.
     */
    public function scopeFiltered($query, $request)
    {
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('phone_number', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('company_name', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('department_id')) {
            $departmentId = $request->input('department_id');

            $query->whereHas('departments', function ($departmentQuery) use ($departmentId) {
                $departmentQuery->where('departments.id', $departmentId);
            });
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->input('city').'%');
        }

        if ($request->filled('lead_status_id')) {
            $query->where('lead_status_id', $request->input('lead_status_id'));
        }

        if ($request->filled('lead_source_id')) {
            if ($request->input('lead_source_id') === 'none') {
                $query->whereNull('lead_source_id');
            } else {
                $query->where('lead_source_id', $request->input('lead_source_id'));
            }
        }

        return $query;
    }
}
