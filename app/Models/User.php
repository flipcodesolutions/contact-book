<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * role is intentionally missing. It must be set on the model
     * itself so a public form cannot change it by mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'all_departments' => 'boolean',
            'all_countries' => 'boolean',
        ];
    }

    /**
     * The five roles, in the order they appear on the user form.
     */
    public static function roleOptions(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'viewer' => 'Viewer',
        ];
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canViewUsers(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }

    public function canManageDepartments(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canManageStatuses(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canCreateLeads(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    public function canDeleteLeads(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }

    public function canImportLeads(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }

    public function canExportLeads(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager', 'staff']);
    }

    /**
     * Super admins can assign any role. Admins can assign every role except super admin.
     */
    public function canAssignRole(string $role): bool
    {
        if (! array_key_exists($role, self::roleOptions())) {
            return false;
        }

        if ($role === 'super_admin') {
            return $this->isSuperAdmin();
        }

        return $this->canManageUsers();
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * True when this person can see every lead.
     * Super admins and admins see everything. Other users see everything if both all_departments and all_countries are set.
     */
    public function seesAllLeads(): bool
    {
        return $this->isAdmin() || ($this->all_departments && $this->all_countries);
    }

    /**
     * True when this person owns every one of the given department ids.
     */
    public function ownsEveryDepartment(array $departmentIds): bool
    {
        if ($this->seesAllLeads() || $this->all_departments) {
            return true;
        }

        if (count($departmentIds) === 0) {
            return false;
        }

        $allowedIds = $this->departments()->pluck('departments.id')->all();

        foreach ($departmentIds as $departmentId) {
            $found = false;

            foreach ($allowedIds as $allowedId) {
                if ((int) $allowedId === (int) $departmentId) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                return false;
            }
        }

        return true;
    }

    /**
     * True when this person owns the given country id.
     */
    public function ownsCountry($countryId): bool
    {
        if ($this->seesAllLeads() || $this->all_countries) {
            return true;
        }

        $allowedIds = $this->countries()->pluck('countries.id')->all();

        foreach ($allowedIds as $allowedId) {
            if ((int) $allowedId === (int) $countryId) {
                return true;
            }
        }

        return false;
    }

    /**
     * The save is allowed when both country and departments match the user's access.
     */
    public function canUseLeadAccess($countryId, array $departmentIds): bool
    {
        if ($this->seesAllLeads()) {
            return true;
        }

        $countryAllowed = $this->all_countries || ($countryId && $this->ownsCountry($countryId));
        $departmentAllowed = $this->all_departments || (count($departmentIds) > 0 && $this->ownsEveryDepartment($departmentIds));

        return $countryAllowed && $departmentAllowed;
    }

    /**
     * Country ids this user may save on a lead.
     */
    public function allowedCountryIds(): array
    {
        if ($this->seesAllLeads() || $this->all_countries) {
            return Country::query()->where('is_active', true)->pluck('id')->all();
        }

        return $this->countries()->where('countries.is_active', true)->pluck('countries.id')->all();
    }

    /**
     * Department ids this user may save on a lead.
     */
    public function allowedDepartmentIds(): array
    {
        if ($this->seesAllLeads() || $this->all_departments) {
            return Department::query()->where('is_active', true)->pluck('id')->all();
        }

        return $this->departments()->where('departments.is_active', true)->pluck('departments.id')->all();
    }
}
