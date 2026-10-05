<?php

namespace App\Http\Controllers;

use App\Exports\LeadsTemplateExport;
use App\Imports\LeadsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LeadImportController extends Controller
{
    /**
     * Show the upload page and, after an import, the summary of what happened.
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->canImportLeads(), 403);

        return view('leads.import');
    }

    /**
     * Import the good rows and skip the bad ones. The file is not stored.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->canImportLeads(), 403);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());

                    if (! in_array($extension, ['xlsx', 'xls', 'csv'])) {
                        $fail('Upload an .xlsx, .xls, or .csv file.');
                    }
                },
            ],
        ], [
            'file.required' => 'Choose a spreadsheet to import.',
            'file.max' => 'The file must be 2 MB or smaller.',
        ]);

        $import = new LeadsImport($request->user());
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $exception) {
            report($exception);

            return back()->withErrors([
                'file' => 'That file could not be read. Download the sample template and try again.',
            ]);
        }

        if ($import->tooManyRows) {
            return back()->withErrors([
                'file' => 'The file has more than 1000 data rows. Split it and try again.',
            ]);
        }

        return redirect()->route('leads.import')->with('import_summary', [
            'imported' => $import->importedCount,
            'skipped' => $import->skippedCount,
            'errors' => $import->errors,
        ]);
    }

    /**
     * Download a one-row example with the column names the importer expects.
     */
    public function template(Request $request)
    {
        abort_unless($request->user()->canImportLeads(), 403);

        return Excel::download(new LeadsTemplateExport, 'argil-leads-template.xlsx');
    }
}
