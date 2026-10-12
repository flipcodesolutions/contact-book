<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#4A3C32">
    <title>Email Content - Argil Group</title>
    
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
        <a href="{{ route('campaigns.create') }}" class="campaign-header-back" aria-label="Go back to Details">
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

        <!-- Step 1: Details & Contacts (Completed) -->
        <a href="{{ route('campaigns.create') }}" class="stepper-item completed text-decoration-none">
            <div class="stepper-circle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="stepper-label">Details &amp; Contacts</div>
        </a>

        <!-- Step 2: Email Content (Active) -->
        <div class="stepper-item active">
            <div class="stepper-circle">2</div>
            <div class="stepper-label">Email Content</div>
        </div>

        <!-- Step 3: Review & Send (Inactive) -->
        <div class="stepper-item">
            <div class="stepper-circle">3</div>
            <div class="stepper-label">Review &amp; Send</div>
        </div>
    </nav>

    <!-- Page Title & Subtitle Section -->
    <div class="campaign-title-section">
        <div class="campaign-title-header">
            <div class="campaign-title-icon-badge">
                <!-- Document / File with Folded Corner Icon -->
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            <h1 class="campaign-page-title">Email Content</h1>
        </div>
        <p class="campaign-page-subtitle">
            Configure format, subject line, and message body for this campaign.
        </p>
    </div>

    <!-- Main Email Content Form -->
    <form id="email-content-form" method="POST" action="{{ route('campaigns.review') }}">
        @csrf

        <!-- Hidden parameters passed from Step 1 -->
        <input type="hidden" name="name" value="{{ $campaignName ?? 'Australia SPC October Launch' }}">
        <input type="hidden" name="country_id" value="{{ $countryId ?? 2 }}">
        <input type="hidden" name="department_id" value="{{ $departmentId ?? 1 }}">
        <input type="hidden" name="contact_file_name" value="{{ $contactFileName ?? 'Australia_SPC_Leads_' }}">
        <input type="hidden" name="contact_file_size" value="{{ $contactFileSize ?? '420 KB' }}">

        <!-- Dispatch Parameters Card -->
        <section class="campaign-card campaign-card-details">
            <div class="campaign-card-header">
                <h2 class="campaign-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #6b5442;">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        <path d="M9 12h6"></path>
                        <path d="M9 16h6"></path>
                    </svg>
                    Dispatch Parameters
                </h2>
                <div class="recipients-count-pill" style="font-size: 11.5px; border-radius: 6px; padding: 3px 8px;">
                    Step 2 of 3
                </div>
            </div>

            <!-- Field 1: SELECT FORMAT * -->
            <div class="campaign-form-group">
                <div class="field-header-row">
                    <label class="campaign-field-label mb-0" for="email-format">
                        SELECT FORMAT <span class="text-danger">*</span>
                    </label>
                    <span class="format-badge-curated">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l2.4 7.4h7.6l-6.2 4.5 2.4 7.4-6.2-4.5-6.2 4.5 2.4-7.4-6.2-4.5h7.6z"/>
                        </svg>
                        Curated Template
                    </span>
                </div>
                <select id="email-format" name="format" class="campaign-field-select" required>
                    @foreach($formats as $fmt)
                        <option value="{{ $fmt }}" @if($fmt == $defaultFormat) selected @endif>
                            {{ $fmt }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Field 2: SUBJECT * -->
            <div class="campaign-form-group">
                <div class="field-header-row">
                    <label class="campaign-field-label mb-0" for="email-subject">
                        SUBJECT <span class="text-danger">*</span>
                    </label>
                    <span class="subject-counter-text" id="subject-counter">48 / 100</span>
                </div>
                <input 
                    type="text" 
                    id="email-subject" 
                    name="subject" 
                    class="campaign-field-input" 
                    maxlength="100" 
                    value="{{ old('subject', $defaultSubject) }}" 
                    required 
                    placeholder="Enter email subject line"
                >
            </div>

            <!-- Field 3: TEXT * -->
            <div class="campaign-form-group mb-0">
                <div class="field-header-row">
                    <label class="campaign-field-label mb-0" for="email-body">
                        TEXT <span class="text-danger">*</span>
                    </label>
                    <span class="text-sublabel-note">Body &amp; Inbox Snippet</span>
                </div>

                <!-- Standalone Toolbar Strip (Matches Mockup media_1791287231826.png) -->
                <div class="campaign-toolbar-strip" style="width: 100%; box-sizing: border-box;">
                    <button type="button" class="campaign-toolbar-btn fw-bold" id="btn-bold" title="Bold">B</button>
                    <button type="button" class="campaign-toolbar-btn btn-slash" id="btn-italic" title="Italic">/</button>
                    <button type="button" class="campaign-toolbar-btn btn-link-item" id="btn-link" title="Insert Link">
                        <svg width="15" height="11" viewBox="0 0 22 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11H6a4 4 0 0 1 0-8h3"></path>
                            <path d="M13 3h3a4 4 0 0 1 0 8h-3"></path>
                            <line x1="8" y1="7" x2="14" y2="7"></line>
                        </svg>
                        <span>Link</span>
                    </button>
                    <button type="button" class="campaign-toolbar-btn btn-tag-item" id="btn-tag" title="Insert Merge Tag">Tag</button>
                </div>

                <!-- Standalone Textarea Box -->
                <textarea 
                    id="email-body" 
                    name="body_text" 
                    class="campaign-textarea-box" 
                    rows="3"
                    style="width: 100%; min-width: 100%; max-width: 100%; display: block; box-sizing: border-box;"
                    required 
                    placeholder="Type message text..."
                >{{ old('body_text', $defaultText) }}</textarea>

                <p class="email-editor-helper">
                    Main message body preview displayed in email summary view and headers.
                </p>
            </div>
        </section>

        <!-- Bottom Action Buttons for Step 2 -->
        <div class="campaign-action-bar">
            <a href="{{ route('campaigns.create') }}" class="btn-campaign-cancel text-decoration-none">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back</span>
            </a>
            <button type="submit" class="btn-campaign-continue btn-continue-review" id="btn-continue-review">
                <span>Continue to Review</span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailSubject = document.getElementById('email-subject');
    const subjectCounter = document.getElementById('subject-counter');
    const emailBody = document.getElementById('email-body');
    const btnBold = document.getElementById('btn-bold');
    const btnItalic = document.getElementById('btn-italic');
    const btnLink = document.getElementById('btn-link');
    const btnTag = document.getElementById('btn-tag');

    const toast = document.getElementById('campaign-toast');
    const toastMessage = document.getElementById('toast-message');

    function showToast(msg) {
        toastMessage.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    // Character Counter
    function updateCounter() {
        const len = emailSubject.value.length;
        subjectCounter.textContent = len + ' / 100';
        if (len >= 90) {
            subjectCounter.style.color = '#dc2626';
        } else {
            subjectCounter.style.color = '#8c7e73';
        }
    }

    emailSubject.addEventListener('input', updateCounter);
    updateCounter();

    // Editor Helper Functions
    function wrapText(before, after) {
        const start = emailBody.selectionStart;
        const end = emailBody.selectionEnd;
        const text = emailBody.value;
        const selected = text.substring(start, end);
        emailBody.value = text.substring(0, start) + before + selected + after + text.substring(end);
        emailBody.focus();
        emailBody.setSelectionRange(start + before.length, end + before.length);
    }

    btnBold.addEventListener('click', function () {
        wrapText('**', '**');
    });

    btnItalic.addEventListener('click', function () {
        wrapText('*', '*');
    });

    btnLink.addEventListener('click', function () {
        const url = prompt('Enter URL link:', 'https://argiltiles.com');
        if (url) {
            wrapText('[', `](${url})`);
        }
    });

    btnTag.addEventListener('click', function () {
        const tag = prompt('Insert merge tag:', '[contact_name]');
        if (tag) {
            wrapText(tag, '');
        }
    });
});
</script>

</body>
</html>
