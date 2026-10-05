<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeadImportTest extends ArgilTestCase
{
    public function test_excel_import_skips_a_bad_row_and_saves_a_good_row(): void
    {
        $export = $this->makeDepartment('Export');
        $uk = $this->makeCountry('United Kingdom', 'GB', '+44');
        $this->makeStatus('Lead Received');
        $manager = $this->makeUser('manager', [$export], [$uk]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['full_name', 'phone_country_code', 'phone_number', 'email', 'company_name', 'address', 'city', 'country_iso2', 'departments', 'status'],
            ['Good Person', '+44', '7700900123', 'good@example.com', 'Good Co', '1 High Street', 'London', 'GB', 'Export', 'Lead Received'],
            ['Bad Person', '+44', '12', 'not-an-email', 'Bad Co', '', 'London', 'ZZ', 'Nope', 'Missing Status'],
        ], null, 'A1');

        $path = storage_path('framework/leads-import-test.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $file = new UploadedFile($path, 'leads.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($manager)->post(route('leads.import.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('leads.import'));
        $response->assertSessionHas('import_summary');

        $summary = session('import_summary');

        $this->assertSame(1, $summary['imported']);
        $this->assertSame(1, $summary['skipped']);
        $this->assertDatabaseHas('leads', ['email' => 'good@example.com']);
        $this->assertDatabaseMissing('leads', ['email' => 'not-an-email']);
    }
}
