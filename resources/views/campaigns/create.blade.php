<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#4A3C32">
    <title>Create Campaign - Argil Group</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/campaign.css') }}?v={{ time() }}">
</head>
<body class="campaign-page-body">

<div class="campaign-container">

    <!-- Top Header -->
    <header class="campaign-header">
        <a href="{{ route('leads.index') }}" class="campaign-header-back" aria-label="Go back to leads">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </a>

        <!-- Argil Group Logo Centered -->
        <a href="{{ route('leads.index') }}" class="campaign-brand-center">
            <!-- Argil Interlocking Geometric A Monogram -->
            <svg width="34" height="28" viewBox="0 0 60 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M30 4 L54 44 H6 L30 4Z" stroke="#B08F76" stroke-width="3.5" stroke-linejoin="round"/>
                <path d="M30 14 L44 40 H16 L30 14Z" fill="#FDF6EF" stroke="#B08F76" stroke-width="2" stroke-linejoin="round"/>
                <path d="M22 28 H38" stroke="#B08F76" stroke-width="3" stroke-linecap="round"/>
                <path d="M30 14 V40" stroke="#C4A58D" stroke-width="1.8"/>
            </svg>
            <span class="campaign-brand-title">ARGIL</span>
            <span class="campaign-brand-sub">GROUP</span>
        </a>

        <!-- Profile Avatar Button -->
        <button type="button" class="campaign-profile-btn" aria-label="User Profile" onclick="window.location.href='{{ route('settings.edit') }}'">
            @if(auth()->check())
                <span style="font-weight: 700; font-size: 14px;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            @else
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            @endif
        </button>
    </header>

    <!-- Stepper / Wizard Progress (3 Steps) -->
    <nav class="campaign-stepper" aria-label="Campaign Creation Steps">
        <div class="stepper-progress-line"></div>

        <!-- Step 1: Active -->
        <div class="stepper-item active">
            <div class="stepper-circle">1</div>
            <div class="stepper-label">Details &amp; Contacts</div>
        </div>

        <!-- Step 2: Inactive (Links to Step 2) -->
        <a href="{{ route('campaigns.email-content') }}" class="stepper-item text-decoration-none">
            <div class="stepper-circle">2</div>
            <div class="stepper-label">Email Content</div>
        </a>

        <!-- Step 3: Inactive -->
        <div class="stepper-item">
            <div class="stepper-circle">3</div>
            <div class="stepper-label">Review &amp; Send</div>
        </div>
    </nav>

    <!-- Page Title & Subtitle Section -->
    <div class="campaign-title-section">
        <div class="campaign-title-header">
            <div class="campaign-title-icon-badge">
                <!-- Megaphone / Broadcast Icon with Sound Waves -->
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 11 18-5v12L3 13v-2z"></path>
                    <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
                    <path d="M21 9a4 4 0 0 1 0 6"></path>
                </svg>
            </div>
            <h1 class="campaign-page-title">Create camapign</h1>
        </div>
        <p class="campaign-page-subtitle">
            Set up basic campaign attributes and upload verified recipient contacts for export dispatch.
        </p>
    </div>

    <!-- Main Campaign Form: Submits directly to the new Email Content page! -->
    <form id="campaign-form" method="POST" action="{{ route('campaigns.email-content') }}">
        @csrf

        <!-- 1. Campaign Details Card -->
        <section class="campaign-card campaign-card-details">
            <div class="campaign-card-header">
                <h2 class="campaign-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #6b5442;">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        <path d="M9 12h6"></path>
                        <path d="M9 16h6"></path>
                    </svg>
                    1. Campaign Details
                </h2>
                <div class="campaign-status-badge">
                    <span class="status-dot-amber"></span> Status: Draft
                </div>
            </div>

            <!-- Campaign Name Field -->
            <div class="campaign-form-group">
                <label class="campaign-field-label" for="campaign-name">
                    CAMPAIGN NAME <span class="text-danger">*</span>
                </label>
                <input 
                    type="text" 
                    id="campaign-name" 
                    name="name" 
                    class="campaign-field-input" 
                    value="{{ old('name', $defaultCampaignName) }}" 
                    required 
                    placeholder="Enter campaign name"
                >
            </div>

            <!-- Country Field -->
            <div class="campaign-form-group">
                <label class="campaign-field-label" for="campaign-country">
                    COUNTRY <span class="text-danger">*</span>
                </label>
                <select id="campaign-country" name="country_id" class="campaign-field-select" required>
                    @foreach($countries as $country)
                        <option 
                            value="{{ $country->id }}" 
                            @if($country->id == $selectedCountryId || $country->iso2 == 'AU') selected @endif
                        >
                            @if($country->iso2 == 'AU') 🇦🇺 Australia [AU]
                            @elseif($country->iso2 == 'GB') 🇬🇧 United Kingdom [GB]
                            @elseif($country->iso2 == 'US') 🇺🇸 United States [US]
                            @elseif($country->iso2 == 'IN') 🇮🇳 India [IN]
                            @elseif($country->iso2 == 'AE') 🇦🇪 United Arab Emirates [AE]
                            @elseif($country->iso2 == 'CA') 🇨🇦 Canada [CA]
                            @elseif($country->iso2 == 'DE') 🇩🇪 Germany [DE]
                            @else {{ $country->name }} [{{ $country->iso2 }}]
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Department Field -->
            <div class="campaign-form-group">
                <label class="campaign-field-label" for="campaign-department">
                    DEPARTMENT <span class="text-danger">*</span>
                </label>
                <select id="campaign-department" name="department_id" class="campaign-field-select" required>
                    @foreach($departments as $department)
                        <option 
                            value="{{ $department->id }}" 
                            @if($department->id == $selectedDepartmentId || str_contains(strtolower($department->name), 'export')) selected @endif
                        >
                            {{ $department->name }} Department
                        </option>
                    @endforeach
                </select>
            </div>
        </section>

        <!-- 2. Upload Contact File Card -->
        <section class="campaign-card campaign-upload-card" id="drop-zone">
            <input 
                type="file" 
                id="contact-file-input" 
                name="contact_file" 
                accept=".xlsx,.xls,.csv" 
                style="display: none;"
            >

            <div class="upload-icon-container">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"></path>
                    <polyline points="12 12 12 16"></polyline>
                    <polyline points="9 13 12 10 15 13"></polyline>
                </svg>
            </div>

            <h3 class="upload-heading">Upload Contact File</h3>
            <p class="upload-subtext">
                Drag and drop your Excel or CSV file here, or<br>
                <span class="upload-browse-link" id="browse-files-trigger">Browse Files</span>
            </p>

            <div class="upload-support-badge">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                Supported: .xlsx, .xls, .csv (Max 10MB)
            </div>
        </section>

        <!-- 3. Uploaded File Info Card -->
        <section class="campaign-card campaign-file-info-card" id="file-info-card">
            <div class="file-info-left">
                <div class="file-status-icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="file-details-col">
                    <div class="file-name-row">
                        <span class="file-name-text" id="display-filename">{{ $fileName }}</span>
                        <span class="file-size-text" id="display-filesize">({{ $fileSize }})</span>
                    </div>
                    <div class="file-synced-status">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="#2E7D32" stroke="none">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span id="display-sync-text">{{ $fileSyncedStatus }}</span>
                    </div>
                </div>
            </div>

            <div class="file-actions-right">
                <button type="button" class="file-action-btn" id="btn-sync-file" title="Sync Matrix" aria-label="Sync Matrix">
                    <svg id="sync-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                        <path d="M3 3v5h5"></path>
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
                        <path d="M16 21h5v-5"></path>
                    </svg>
                </button>
                <button type="button" class="file-action-btn delete-btn" id="btn-delete-file" title="Remove File" aria-label="Remove File">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </button>
            </div>
        </section>

        <!-- 4. Imported Recipients Card -->
        <section class="campaign-card campaign-recipients-card">
            <div class="recipients-card-header">
                <div class="recipients-title-group">
                    <h2 class="recipients-header-title">Imported Recipients</h2>
                    <span class="recipients-count-pill" id="recipients-count-badge">Showing 5 of {{ $totalRecipients }}</span>
                </div>
                <button type="button" class="btn-clean-flagged" id="btn-clean-flagged" title="Clean all flagged records">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"></path>
                    </svg>
                    <span>Clean All Flagged</span>
                </button>
            </div>

            <!-- Table of Recipients -->
            <div class="recipients-table-wrapper">
                <table class="recipients-table">
                    <thead>
                        <tr>
                            <th>CONTACT</th>
                            <th>EMAIL &amp; CITY</th>
                            <th>TERR...</th>
                        </tr>
                    </thead>
                    <tbody id="recipients-tbody">
                        @foreach($recipients as $recipient)
                            <tr class="recipient-row @if($recipient['is_domestic']) row-flagged @endif">
                                <td>
                                    <div class="recipient-name">{{ $recipient['name'] }}</div>
                                    <div class="recipient-phone">{{ $recipient['phone'] }}</div>
                                </td>
                                <td>
                                    <div class="recipient-email">{{ $recipient['email'] }}</div>
                                    <div class="recipient-city">{{ $recipient['city'] }}</div>
                                </td>
                                <td>
                                    <span class="badge-territory @if($recipient['is_domestic']) badge-territory-domestic @else badge-territory-export @endif" title="{{ $recipient['territory'] }}">
                                        {{ $recipient['territory_code'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Card Footer: Registry Resolution & Pagination -->
            <div class="recipients-card-footer">
                <div class="registry-resolution-text">
                    Auto-resolved against Australian AS-4459 Stone Trade Registry
                </div>
                <div class="pagination-inline-controls">
                    <button type="button" class="pagination-arrow-btn" id="btn-page-prev" aria-label="Previous Page" disabled>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <span class="pagination-page-indicator" id="page-indicator">Page 1 of 50</span>
                    <button type="button" class="pagination-arrow-btn" id="btn-page-next" aria-label="Next Page">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- Hidden inputs for file info to pass forward -->
        <input type="hidden" name="contact_file_name" id="hidden-file-name" value="{{ $fileName }}">
        <input type="hidden" name="contact_file_size" id="hidden-file-size" value="{{ $fileSize }}">

        <!-- Bottom Action Buttons: Submits to open the new Email Content page! -->
        <div class="campaign-action-bar">
            <a href="{{ route('leads.index') }}" class="btn-campaign-cancel text-decoration-none">Cancel</a>
            <button type="submit" class="btn-campaign-continue" id="btn-submit-to-content">
                <span>Continue to Format</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </div>
    </form>

</div>

<!-- Interactive Toast Feedback -->
<div class="campaign-toast" id="campaign-toast" role="alert" aria-live="assertive">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"></polyline>
    </svg>
    <span id="toast-message">Action completed</span>
</div>

<!-- Vanilla JS Interactions -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('contact-file-input');
    const browseTrigger = document.getElementById('browse-files-trigger');
    const fileInfoCard = document.getElementById('file-info-card');
    const displayFilename = document.getElementById('display-filename');
    const displayFilesize = document.getElementById('display-filesize');
    const hiddenFileName = document.getElementById('hidden-file-name');
    const hiddenFileSize = document.getElementById('hidden-file-size');
    const btnSync = document.getElementById('btn-sync-file');
    const syncIcon = document.getElementById('sync-icon');
    const btnDelete = document.getElementById('btn-delete-file');
    const btnCleanFlagged = document.getElementById('btn-clean-flagged');
    const btnPagePrev = document.getElementById('btn-page-prev');
    const btnPageNext = document.getElementById('btn-page-next');
    const pageIndicator = document.getElementById('page-indicator');
    const toast = document.getElementById('campaign-toast');
    const toastMessage = document.getElementById('toast-message');

    function showToast(msg) {
        toastMessage.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    // Trigger file browse
    browseTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        fileInput.click();
    });

    dropZone.addEventListener('click', function () {
        fileInput.click();
    });

    // Drag and Drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('drag-over');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
            handleSelectedFile(files[0]);
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length) {
            handleSelectedFile(this.files[0]);
        }
    });

    function handleSelectedFile(file) {
        const rawName = file.name;
        const extIndex = rawName.lastIndexOf('.');
        const baseName = extIndex !== -1 ? rawName.substring(0, extIndex) : rawName;
        const sizeKb = Math.round(file.size / 1024) + ' KB';

        displayFilename.textContent = baseName;
        displayFilesize.textContent = '(' + sizeKb + ')';
        hiddenFileName.value = baseName;
        hiddenFileSize.value = sizeKb;
        fileInfoCard.style.display = 'flex';
        showToast('File loaded: ' + file.name);
    }

    // Sync Button
    btnSync.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        syncIcon.style.transition = 'transform 0.8s ease';
        syncIcon.style.transform = 'rotate(360deg)';
        setTimeout(() => {
            syncIcon.style.transition = 'none';
            syncIcon.style.transform = 'rotate(0deg)';
            showToast('Morbi Sizing Matrix successfully re-synced!');
        }, 800);
    });

    // Delete Uploaded File Button
    btnDelete.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (confirm('Remove uploaded file?')) {
            fileInput.value = '';
            fileInfoCard.style.display = 'none';
            hiddenFileName.value = '';
            hiddenFileSize.value = '';
            showToast('File removed');
        }
    });

    // Clean All Flagged Action
    btnCleanFlagged.addEventListener('click', function (e) {
        e.preventDefault();
        const flaggedRows = document.querySelectorAll('.row-flagged');
        if (flaggedRows.length === 0) {
            showToast('No flagged records found');
            return;
        }
        flaggedRows.forEach(row => {
            const badge = row.querySelector('.badge-territory');
            if (badge) {
                badge.className = 'badge-territory badge-territory-export';
                badge.textContent = 'Expo...';
                badge.title = 'Export';
            }
            row.classList.remove('row-flagged');
        });
        showToast('Cleaned all flagged territory records!');
    });

    // Pagination
    let currentPage = 1;
    const totalPages = 50;

    const samplePages = {
        1: [
            { name: 'Liam Henderson', phone: '+61 412 345 678', email: 'l.henderson@sydneybuild.com.au', city: 'Sydney, Australia', territory: 'Expo...', isDomestic: false },
            { name: 'Chloe Mitchell', phone: '+61 498 765 432', email: 'chloe@melbournestone.com.au', city: 'Melbourne, Australia', territory: 'Expo...', isDomestic: false },
            { name: 'Oliver Zhang', phone: '+61 433 112 233', email: 'oliver@perthsurfaces.com.au', city: 'Perth, Australia', territory: 'Expo...', isDomestic: false },
            { name: 'Marcus Vance', phone: '+61 400 998 877', email: 'marcus@brisbanetiles.com.au', city: 'Brisbane, Australia', territory: 'Dom...', isDomestic: true },
            { name: 'Sarah Jenkins', phone: '+61 422 556 677', email: 'sarah@apaceramics.com', city: 'Adelaide, Australia', territory: 'Expo...', isDomestic: false },
        ],
        2: [
            { name: 'Daniel Hughes', phone: '+61 411 223 344', email: 'dhughes@canberrabuild.com.au', city: 'Canberra, Australia', territory: 'Expo...', isDomestic: false },
            { name: 'Jessica Taylor', phone: '+61 423 334 455', email: 'jessica@goldcoasttiles.com.au', city: 'Gold Coast, Australia', territory: 'Expo...', isDomestic: false },
            { name: 'Thomas Wright', phone: '+61 434 445 566', email: 'twright@hobartstone.com.au', city: 'Hobart, Australia', territory: 'Expo...', isDomestic: false },
            { name: 'Emily Clark', phone: '+61 445 556 677', email: 'eclark@darwininteriors.com.au', city: 'Darwin, Australia', territory: 'Dom...', isDomestic: true },
            { name: 'Ryan Murphy', phone: '+61 456 667 788', email: 'rmurphy@newcastletiles.com.au', city: 'Newcastle, Australia', territory: 'Expo...', isDomestic: false },
        ]
    };

    function renderPage(page) {
        pageIndicator.textContent = 'Page ' + page + ' of ' + totalPages;
        btnPagePrev.disabled = (page === 1);
        btnPageNext.disabled = (page === totalPages);

        const data = samplePages[page] || samplePages[1];
        const tbody = document.getElementById('recipients-tbody');
        tbody.innerHTML = '';
        data.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'recipient-row' + (item.isDomestic ? ' row-flagged' : '');
            tr.innerHTML = `
                <td>
                    <div class="recipient-name">${item.name}</div>
                    <div class="recipient-phone">${item.phone}</div>
                </td>
                <td>
                    <div class="recipient-email">${item.email}</div>
                    <div class="recipient-city">${item.city}</div>
                </td>
                <td>
                    <span class="badge-territory ${item.isDomestic ? 'badge-territory-domestic' : 'badge-territory-export'}" title="${item.isDomestic ? 'Domestic' : 'Export'}">
                        ${item.territory}
                    </span>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    btnPageNext.addEventListener('click', function () {
        if (currentPage < totalPages) {
            currentPage++;
            renderPage(currentPage);
        }
    });

    btnPagePrev.addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage--;
            renderPage(currentPage);
        }
    });
});
</script>

</body>
</html>
