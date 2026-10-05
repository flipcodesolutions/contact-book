@extends('layouts.app')

@section('title', 'Import leads')

@section('content')
    <h1 class="h3 mb-1">Import leads</h1>
    <p class="page-subtitle">Upload .xlsx, .xls, or .csv. Maximum 2 MB and 1000 rows. Bad rows are skipped.</p>

    <p><a href="{{ route('leads.template') }}">Download sample template</a></p>

    @if (session('import_summary'))
        <div class="alert alert-info" role="status">
            <div>Imported {{ session('import_summary.imported') }}. Skipped {{ session('import_summary.skipped') }}.</div>
            @if (count(session('import_summary.errors')) > 0)
                <ul class="mb-0 mt-2">
                    @foreach (session('import_summary.errors') as $error)
                        <li>Row {{ $error['row'] }}: {{ $error['message'] }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('leads.import.store') }}" enctype="multipart/form-data" class="card js-single-submit">
        @csrf
        <div class="card-body">
            <label class="form-label" for="file">Spreadsheet</label>
            <input class="form-control @error('file') is-invalid @enderror" id="file" name="file" type="file" accept=".xlsx,.xls,.csv" required>
            @error('file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                Columns: full_name, phone_country_code, phone_number, email, company_name, address, city, country_iso2, departments, status.
                Separate departments with commas. country_iso2 is the two-letter code, such as GB.
            </div>
            <button type="submit" class="btn btn-primary mt-3">Import</button>
            <a class="btn btn-outline-secondary mt-3" href="{{ route('leads.index') }}">Cancel</a>
        </div>
    </form>
@endsection
