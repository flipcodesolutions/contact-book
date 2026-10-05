<?php

namespace App\Http\Controllers;

use App\Exports\LeadsExport;
use App\Models\Lead;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LeadExportController extends Controller
{
    /**
     * Download the filtered list, or just the ticked leads when ids are posted.
     * The query always starts from visibleTo(), so another user's leads cannot be downloaded.
     */
    public function download(Request $request)
    {
        abort_unless($request->user()->canExportLeads(), 403);

        $query = Lead::query()
            ->visibleTo($request->user())
            ->with('country', 'departments', 'status', 'source')
            ->orderBy('id', 'desc');

        if ($request->isMethod('post')) {
            $leadIds = $request->input('lead_ids', []);

            if (! is_array($leadIds) || count($leadIds) === 0) {
                return back()->with('error', 'Select at least one lead first.');
            }

            $query->whereIn('id', $leadIds);
        } else {
            $query->filtered($request);
        }

        return Excel::download(new LeadsExport($query->get()), 'argil-leads.xlsx');
    }
}
