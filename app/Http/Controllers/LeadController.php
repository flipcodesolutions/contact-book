<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadRequest;
use App\Models\Country;
use App\Models\Department;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use App\VisitingCardReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    /**
     * Show the contact list, with the filters from the URL applied.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $leads = Lead::query()
            ->visibleTo($user)
            ->filtered($request)
            ->with('country', 'departments', 'status', 'source')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $countries = $this->countriesFor($user);
        $departments = $this->departmentsFor($user);
        $statuses = LeadStatus::query()->orderBy('name')->get();
        $sources = LeadSource::query()->orderBy('name')->get();

        return view('leads.index', compact('leads', 'countries', 'departments', 'statuses', 'sources'));
    }

    /**
     * Show the empty lead form.
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->canCreateLeads(), 403);

        $lead = new Lead;

        return view('leads.create', $this->leadFormData($request->user(), $lead));
    }

    /**
     * Save a new lead, then attach the chosen departments.
     */
    public function store(LeadRequest $request)
    {
        abort_unless($request->user()->canCreateLeads(), 403);

        $lead = new Lead;
        $lead->created_by = $request->user()->id;
        $this->saveLead($lead, $request);

        return redirect()->route('leads.index')->with('success', 'Lead created.');
    }

    /**
     * Read the text from a visiting card and return the lead fields it contains.
     */
    public function readCard(Request $request)
    {
        abort_unless($request->user()->canCreateLeads(), 403);

        $request->validate([
            'text' => ['required', 'string', 'max:20000'],
        ]);

        $countries = Country::query()->where('is_active', true)->orderBy('name')->get();
        $fields = (new VisitingCardReader)->read($request->input('text'), $countries);

        return response()->json([
            'fields' => $fields,
            'labels' => $this->filledCardLabels($fields),
        ]);
    }

    /**
     * Show the form for a lead this user is allowed to see.
     */
    public function edit(Request $request, $lead)
    {
        abort_unless($request->user()->canCreateLeads(), 403);

        $lead = Lead::visibleTo($request->user())->with('country', 'departments')->findOrFail($lead);

        return view('leads.edit', $this->leadFormData($request->user(), $lead));
    }

    /**
     * Update a lead the user is allowed to see. created_by stays as it was.
     */
    public function update(LeadRequest $request, $lead)
    {
        abort_unless($request->user()->canCreateLeads(), 403);

        $lead = Lead::visibleTo($request->user())->findOrFail($lead);
        $this->saveLead($lead, $request);

        return redirect()->route('leads.index')->with('success', 'Lead updated.');
    }

    /**
     * Soft-delete one lead. Viewers and staff are turned away.
     */
    public function destroy(Request $request, $lead)
    {
        abort_unless($request->user()->canDeleteLeads(), 403);

        $lead = Lead::visibleTo($request->user())->findOrFail($lead);
        $this->deleteCardFiles($lead);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    /**
     * Soft-delete every selected lead the user is allowed to see.
     */
    public function bulkDestroy(Request $request)
    {
        abort_unless($request->user()->canDeleteLeads(), 403);

        $leadIds = $request->input('lead_ids', []);

        if (! is_array($leadIds) || count($leadIds) === 0) {
            return back()->with('error', 'Select at least one lead first.');
        }

        $leads = Lead::visibleTo($request->user())->whereIn('id', $leadIds)->get();

        foreach ($leads as $lead) {
            $this->deleteCardFiles($lead);
        }

        $deletedCount = Lead::visibleTo($request->user())->whereIn('id', $leadIds)->delete();

        return back()->with('success', $deletedCount.' lead(s) deleted.');
    }

    /**
     * Copy the validated fields onto the lead and sync its departments.
     */
    private function saveLead(Lead $lead, LeadRequest $request): Lead
    {
        $validated = LeadRequest::applySource($request->validated());
        $departmentIds = isset($validated['department_ids']) ? $validated['department_ids'] : [];
        unset($validated['department_ids'], $validated['card_front'], $validated['card_back']);

        $lead->fill($validated);
        $lead->save();
        $this->storeCard($lead, $request, 'card_front');
        $this->storeCard($lead, $request, 'card_back');
        $lead->save();
        $lead->departments()->sync($departmentIds);

        return $lead;
    }

    /**
     * Keep a new visiting-card photo and remove the previous file.
     */
    private function storeCard(Lead $lead, LeadRequest $request, string $field): void
    {
        if (! $request->hasFile($field)) {
            return;
        }

        if ($lead->{$field}) {
            Storage::disk('public')->delete($lead->{$field});
        }

        $lead->{$field} = $request->file($field)->store('visiting-cards', 'public');
    }

    /**
     * Remove saved visiting-card photos from storage.
     */
    private function deleteCardFiles(Lead $lead): void
    {
        if ($lead->card_front) {
            Storage::disk('public')->delete($lead->card_front);
        }

        if ($lead->card_back) {
            Storage::disk('public')->delete($lead->card_back);
        }
    }

    /**
     * Names of the fields the card reader actually found.
     */
    private function filledCardLabels(array $fields): array
    {
        $names = [
            'full_name' => 'name',
            'phone_number' => 'phone',
            'email' => 'email',
            'website' => 'website',
            'company_name' => 'company',
            'job_title' => 'job title',
            'work_department' => 'work department',
            'address' => 'address',
            'city' => 'city',
            'country_id' => 'country',
            'notes' => 'notes',
        ];
        $labels = [];

        foreach ($names as $key => $label) {
            if (isset($fields[$key]) && $fields[$key] !== '') {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * Lists for the create and edit forms.
     * Locked departments are shown disabled so the user can see them but not tick them.
     */
    private function leadFormData(User $user, Lead $lead): array
    {
        if ($user->seesAllLeads() || $user->all_countries) {
            $countries = Country::query()->where('is_active', true)->orderBy('name')->get();
        } else {
            $allowedCountryIds = $user->allowedCountryIds();
            $countries = Country::query()
                ->where('is_active', true)
                ->whereIn('id', $allowedCountryIds === [] ? [0] : $allowedCountryIds)
                ->orderBy('name')
                ->get();
        }

        if ($lead->exists && $lead->country && ! $countries->contains('id', $lead->country_id)) {
            $countries->push($lead->country);
        }

        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();

        if ($user->seesAllLeads() || $user->all_departments) {
            $availableDepartments = $departments->values();
            $lockedDepartments = collect();
        } else {
            $allowedDepartmentIds = $user->allowedDepartmentIds();
            $availableDepartments = $departments->whereIn('id', $allowedDepartmentIds)->values();
            $lockedDepartments = $departments->whereNotIn('id', $allowedDepartmentIds)->values();
        }

        if (old('department_ids') !== null) {
            $selectedDepartmentIds = [];

            foreach ((array) old('department_ids') as $departmentId) {
                $selectedDepartmentIds[] = (int) $departmentId;
            }
        } elseif ($lead->exists) {
            $selectedDepartmentIds = $lead->departments->pluck('id')->all();
        } else {
            $selectedDepartmentIds = [];
        }

        if ($user->seesAllLeads()) {
            $territory = 'All countries and all departments';
        } else {
            $territory = 'Only leads matching your assigned countries and departments.';
        }

        $dialCodes = Country::query()->where('is_active', true)->orderBy('name')->get();
        $statuses = LeadStatus::query()->orderBy('name')->get();
        $sources = LeadSource::query()->orderBy('name')->get();

        return compact(
            'lead',
            'countries',
            'availableDepartments',
            'lockedDepartments',
            'selectedDepartmentIds',
            'territory',
            'dialCodes',
            'statuses',
            'sources'
        );
    }

    private function countriesFor(User $user)
    {
        if ($user->seesAllLeads() || $user->all_countries) {
            return Country::query()->where('is_active', true)->orderBy('name')->get();
        }

        $countryIds = $user->countries()->pluck('countries.id')->all();

        return Country::query()
            ->where('is_active', true)
            ->whereIn('id', $countryIds === [] ? [0] : $countryIds)
            ->orderBy('name')
            ->get();
    }

    private function departmentsFor(User $user)
    {
        if ($user->seesAllLeads() || $user->all_departments) {
            return Department::query()->where('is_active', true)->orderBy('name')->get();
        }

        $departmentIds = $user->departments()->pluck('departments.id')->all();

        return Department::query()
            ->where('is_active', true)
            ->whereIn('id', $departmentIds === [] ? [0] : $departmentIds)
            ->orderBy('name')
            ->get();
    }
}
