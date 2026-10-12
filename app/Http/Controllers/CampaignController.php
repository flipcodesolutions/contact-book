<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Country;
use App\Models\Department;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    /**
     * Show the campaign creation page (Step 1: Details & Contacts).
     */
    public function create(Request $request)
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Default or selected values
        $defaultCountry = $countries->firstWhere('iso2', 'AU') ?? $countries->first();
        $defaultDepartment = $departments->firstWhere('name', 'Export') ?? $departments->first();

        // Sample imported recipients matching the screenshot
        $sampleRecipients = [
            [
                'name' => 'Liam Henderson',
                'phone' => '+61 412 345 678',
                'email' => 'l.henderson@sydneybuild.com.au',
                'city' => 'Sydney, Australia',
                'territory' => 'Export',
                'territory_code' => 'Expo...',
                'is_domestic' => false,
            ],
            [
                'name' => 'Chloe Mitchell',
                'phone' => '+61 498 765 432',
                'email' => 'chloe@melbournestone.com.au',
                'city' => 'Melbourne, Australia',
                'territory' => 'Export',
                'territory_code' => 'Expo...',
                'is_domestic' => false,
            ],
            [
                'name' => 'Oliver Zhang',
                'phone' => '+61 433 112 233',
                'email' => 'oliver@perthsurfaces.com.au',
                'city' => 'Perth, Australia',
                'territory' => 'Export',
                'territory_code' => 'Expo...',
                'is_domestic' => false,
            ],
            [
                'name' => 'Marcus Vance',
                'phone' => '+61 400 998 877',
                'email' => 'marcus@brisbanetiles.com.au',
                'city' => 'Brisbane, Australia',
                'territory' => 'Domestic',
                'territory_code' => 'Dom...',
                'is_domestic' => true,
            ],
            [
                'name' => 'Sarah Jenkins',
                'phone' => '+61 422 556 677',
                'email' => 'sarah@apaceramics.com',
                'city' => 'Adelaide, Australia',
                'territory' => 'Export',
                'territory_code' => 'Expo...',
                'is_domestic' => false,
            ],
        ];

        $step = (int) $request->query('step', 1);
        $currentStep = in_array($step, [1, 2, 3]) ? $step : 1;

        $formats = [
            'SPC Flooring Showcase / Product Promotion',
            'New Collection Launch & Brochure',
            'Architect & Specifier Catalogue',
            'Crate & Container Export Dispatch Notification',
        ];

        return view('campaigns.create', [
            'currentStep' => $currentStep,
            'countries' => $countries,
            'departments' => $departments,
            'selectedCountryId' => $defaultCountry?->id,
            'selectedDepartmentId' => $defaultDepartment?->id,
            'defaultCampaignName' => 'Australia SPC October Launch',
            'fileName' => 'Australia_SPC_Leads_',
            'fileSize' => '420 KB',
            'fileSyncedStatus' => 'Uploaded successfully • Morbi Sizing Matrix Synced',
            'recipients' => $sampleRecipients,
            'totalRecipients' => 250,
            'currentPage' => 1,
            'totalPages' => 50,
            'formats' => $formats,
            'defaultFormat' => 'SPC Flooring Showcase / Product Promotion',
            'defaultSubject' => "Discover Argil's Latest SPC Flooring Collecti",
            'defaultText' => 'Explore our latest engineered stone & rigid-core flooring solutions for Q4 projects.',
        ]);
    }

    /**
     * Show Step 2: Email Content page.
     */
    public function emailContent(Request $request)
    {
        $campaignName = $request->input('name', $request->query('name', 'Australia SPC October Launch'));
        $countryId = $request->input('country_id', $request->query('country_id', 2));
        $departmentId = $request->input('department_id', $request->query('department_id', 1));
        $contactFileName = $request->input('contact_file_name', $request->query('contact_file_name', 'Australia_SPC_Leads_'));
        $contactFileSize = $request->input('contact_file_size', $request->query('contact_file_size', '420 KB'));

        $formats = [
            'SPC Flooring Showcase / Product Promotion',
            'New Collection Launch & Brochure',
            'Architect & Specifier Catalogue',
            'Crate & Container Export Dispatch Notification',
        ];

        return view('campaigns.email-content', [
            'campaignName' => $campaignName,
            'countryId' => $countryId,
            'departmentId' => $departmentId,
            'contactFileName' => $contactFileName,
            'contactFileSize' => $contactFileSize,
            'formats' => $formats,
            'defaultFormat' => 'SPC Flooring Showcase / Product Promotion',
            'defaultSubject' => "Discover Argil's Latest SPC Flooring Collecti",
            'defaultText' => 'Explore our latest engineered stone & rigid-core flooring solutions for Q4 projects.',
        ]);
    }

    /**
     * Show Step 3: Review & Send page.
     */
    public function review(Request $request)
    {
        $campaignName = $request->input('name', $request->query('name', 'Australia SPC October Launch'));
        $countryId = $request->input('country_id', $request->query('country_id', 2));
        $departmentId = $request->input('department_id', $request->query('department_id', 1));
        $format = $request->input('format', $request->query('format', 'SPC Flooring Showcase / Product Promotion'));
        $subject = $request->input('subject', $request->query('subject', "Discover Argil's Latest SPC Flooring Collecti"));
        $bodyText = $request->input('body_text', $request->query('body_text', 'Explore our latest engineered stone & rigid-core flooring solutions for Q4 projects.'));
        $contactFileName = $request->input('contact_file_name', $request->query('contact_file_name', 'Australia_SPC_Leads_'));
        $contactFileSize = $request->input('contact_file_size', $request->query('contact_file_size', '420 KB'));

        return view('campaigns.review', [
            'campaignName' => $campaignName,
            'countryId' => $countryId,
            'departmentId' => $departmentId,
            'format' => $format,
            'subject' => $subject,
            'bodyText' => $bodyText,
            'contactFileName' => $contactFileName,
            'contactFileSize' => $contactFileSize,
        ]);
    }

    /**
     * Store a campaign or advance to next step.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'format' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:100'],
            'body_text' => ['nullable', 'string'],
            'contact_file_name' => ['nullable', 'string', 'max:255'],
            'contact_file_size' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $status = $request->input('status', 'Sent');

        $campaign = Campaign::create([
            'name' => $validated['name'],
            'status' => $status,
            'format' => $validated['format'] ?? 'SPC Flooring Showcase / Product Promotion',
            'subject' => $validated['subject'] ?? "Discover Argil's Latest SPC Flooring Collection",
            'body_text' => $validated['body_text'] ?? 'Explore our latest engineered stone & rigid-core flooring solutions for Q4 projects.',
            'country_id' => $validated['country_id'],
            'department_id' => $validated['department_id'],
            'contact_file_name' => $validated['contact_file_name'] ?? 'Australia_SPC_Leads_Oct.xlsx',
            'contact_file_size' => $validated['contact_file_size'] ?? '420 KB',
            'recipients_count' => 245,
            'created_by' => Auth::id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Campaign saved successfully.',
                'campaign' => $campaign,
            ]);
        }

        $msg = $status === 'Scheduled' 
            ? 'Campaign successfully scheduled for 15 Oct 2026, 09:00 AM!'
            : ($status === 'Draft' ? 'Campaign draft saved successfully.' : 'Campaign "' . $campaign->name . '" dispatched successfully to 245 verified contacts!');

        return redirect()->route('leads.index')->with('status', $msg);
    }

    /**
     * Handle asynchronous or form contact file upload.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'contact_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('contact_file');
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileSizeKb = round($file->getSize() / 1024) . ' KB';

        return response()->json([
            'success' => true,
            'fileName' => $fileName,
            'fileSize' => $fileSizeKb,
            'statusText' => 'Uploaded successfully • Morbi Sizing Matrix Synced',
        ]);
    }
}
