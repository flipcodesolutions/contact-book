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
     * A department right includes that department in every country.
     * A country right includes every department in that country.
     * All departments, or all countries, shows every lead.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->seesAllLeads()) {
            return $query;
        }

        $departmentIds = $user->departments()->pluck('departments.id')->all();
        $countryIds = $user->countries()->pluck('countries.id')->all();

        if (count($departmentIds) === 0 && count($countryIds) === 0) {
            return $query->where('created_by', $user->id);
        }

        return $query->where(function ($innerQuery) use ($departmentIds, $countryIds, $user) {
            $innerQuery->where('created_by', $user->id);

            if (count($departmentIds) > 0) {
                $innerQuery->orWhereHas('departments', function ($departmentQuery) use ($departmentIds) {
                    $departmentQuery->whereIn('departments.id', $departmentIds);
                });
            }

            if (count($countryIds) > 0) {
                $innerQuery->orWhereIn('country_id', $countryIds);
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

        return $query;
    }
}
