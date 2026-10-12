<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#4A3C32">
    <title>Review &amp; Send Campaign - Argil Group</title>
    
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
        <a href="{{ route('campaigns.email-content') }}" class="campaign-header-back" aria-label="Go back to Email Content">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </a>

        <!-- Argil Group Logo Centered -->
        <a href="{{ route('leads.index') }}" class="campaign-brand-center">
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

    <!-- Stepper / Wizard Progress (3 Steps - Matches Mockup) -->
    <nav class="campaign-stepper" aria-label="Campaign Creation Steps">
        <div class="stepper-progress-line"></div>

        <!-- Step 1: Details & Contacts -->
        <a href="{{ route('campaigns.create') }}" class="stepper-item text-decoration-none">
            <div class="stepper-circle">1</div>
            <div class="stepper-label">Details &amp; Contacts</div>
        </a>

        <!-- Step 2: Email Content -->
        <a href="{{ route('campaigns.email-content') }}" class="stepper-item text-decoration-none">
            <div class="stepper-circle">2</div>
            <div class="stepper-label">Email Content</div>
        </a>

        <!-- Step 3: Review & Send (Active) -->
        <div class="stepper-item active">
            <div class="stepper-circle" style="background-color: #635144; color: #ffffff; font-weight: 700;">3</div>
            <div class="stepper-label" style="font-weight: 700; color: #221c18;">Review &amp; Send</div>
        </div>
    </nav>

    <!-- Page Title & Subtitle Section -->
    <div class="campaign-title-section" style="margin-bottom: 16px;">
        <h1 class="campaign-page-title" style="font-size: 24px; font-weight: 700; color: #1f1c19; margin-bottom: 4px;">Review &amp; Send</h1>
        <p class="campaign-page-subtitle" style="font-size: 13px; color: #71655b; margin: 0; padding-left: 1px;">
            Final review of campaign envelope, recipient audience, and dispatch scheduling.
        </p>
    </div>

    <!-- Hero Card / Showcase Banner (Matches Mockup media_1791287780430.png) -->
    <div class="campaign-hero-banner">
        <div class="campaign-hero-overlay">
            <!-- Floating Preview Badge in Top Right -->
            <div class="hero-preview-badge">
                <div class="hero-preview-tabs">
                    <span>Audience</span>
                    <span class="active">Design</span>
                    <span>DOMAINS</span>
                    <span>Review &amp; Send</span>
                </div>
                <div class="hero-preview-body">
                    <div class="hero-preview-thumb"></div>
                    <div class="hero-preview-info">
                        <div class="hero-preview-headline">Argil Spring Collection Launch</div>
                        <div class="hero-preview-sub">Audience: Interior Designers</div>
                        <div class="hero-preview-sub">Schedule: April 01, 2026</div>
                    </div>
                </div>
            </div>

            <!-- Overlaid Campaign Text on Bottom Left -->
            <div class="hero-tag-text">SPC FLOORING AUTUMN EXPORT</div>
            <h2 class="hero-title-text">{{ $campaignName ?? 'Australia Commercial Hub Campaign' }}</h2>
        </div>
    </div>

    <!-- Review & Submit Form -->
    <form id="campaign-review-form" method="POST" action="{{ route('campaigns.store') }}">
        @csrf

        <!-- Hidden parameters passed through from Steps 1 & 2 -->
        <input type="hidden" name="name" value="{{ $campaignName ?? 'Australia SPC October' }}">
        <input type="hidden" name="country_id" value="{{ $countryId ?? 2 }}">
        <input type="hidden" name="department_id" value="{{ $departmentId ?? 1 }}">
        <input type="hidden" name="format" value="{{ $format ?? 'SPC Flooring Showcase / Product Promotion' }}">
        <input type="hidden" name="subject" value="{{ $subject ?? "Discover Argil's Latest SPC Flooring Collection" }}">
        <input type="hidden" name="body_text" value="{{ $bodyText ?? 'Explore our latest engineered stone & rigid-core flooring solutions for Q4 projects.' }}">
        <input type="hidden" name="contact_file_name" value="{{ $contactFileName ?? 'Australia_SPC_Leads_Oct.xlsx' }}">
        <input type="hidden" name="contact_file_size" value="{{ $contactFileSize ?? '420 KB' }}">
        <input type="hidden" name="status" id="form-campaign-status" value="Sent">

        <!-- Card 1: Campaign Envelope -->
        <section class="campaign-card campaign-card-details">
            <div class="campaign-card-header mb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="envelope-header-icon">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                            <polyline points="3 7 12 13 21 7"></polyline>
                        </svg>
                    </div>
                    <div>
                        <h2 class="campaign-card-title" style="font-size: 15px; margin: 0;">Campaign Envelope</h2>
                        <div style="font-size: 11.5px; color: #7c7066; margin-top: 1px;">Target audience validation complete</div>
                    </div>
                </div>
                <a href="{{ route('campaigns.create') }}" class="btn-envelope-edit">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Edit</span>
                </a>
            </div>

            <!-- Envelope Details Inner Box -->
            <div class="envelope-details-box">
                <!-- Row 1: CAMPAIGN NAME -->
                <div class="envelope-row">
                    <span class="envelope-label">CAMPAIGN NAME</span>
                    <span class="envelope-val">{{ $campaignName ?? 'Australia SPC October' }}</span>
                </div>

                <!-- Row 2: RECIPIENTS -->
                <div class="envelope-row">
                    <span class="envelope-label">RECIPIENTS</span>
                    <div class="envelope-val">
                        <div>245 Valid Contacts</div>
                        <div class="attachment-file-sub">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                            </svg>
                            <span>{{ $contactFileName ?? 'Australia_SPC_Leads_Oct.xlsx' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Row 3: EMAIL FORMAT -->
                <div class="envelope-row">
                    <span class="envelope-label">EMAIL FORMAT</span>
                    <div class="envelope-val">
                        <span class="review-format-pill">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                            <span>{{ str_contains($format ?? '', 'Product Promotion') ? 'Product Promotion' : ($format ?? 'Product Promotion') }}</span>
                        </span>
                    </div>
                </div>

                <!-- Row 4: SUBJECT LINE -->
                <div class="envelope-row">
                    <span class="envelope-label">SUBJECT LINE</span>
                    <span class="envelope-val">{{ $subject ?? "Discover Argil's Latest SPC Flooring Collection" }}</span>
                </div>

                <!-- Row 5: FROM -->
                <div class="envelope-row">
                    <span class="envelope-label">FROM</span>
                    <div class="envelope-val">
                        <div>Argil Group</div>
                        <div class="from-email-sub">&lt;export@argiltiles.com&gt;</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Card 2: Dispatch Schedule -->
        <section class="campaign-card campaign-card-details">
            <div class="campaign-card-header mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="envelope-header-icon">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div>
                        <h2 class="campaign-card-title" style="font-size: 15px; margin: 0;">Dispatch Schedule</h2>
                        <div style="font-size: 11.5px; color: #7c7066; margin-top: 1px;">Choose immediate launch or custom timing</div>
                    </div>
                </div>
            </div>

            <!-- Option 1: Send Now (Selected by default) -->
            <div class="schedule-radio-row" id="option-send-now">
                <div class="custom-radio-circle" id="circle-send-now">
                    <div class="custom-radio-dot" id="dot-send-now"></div>
                </div>
                <div>
                    <div class="schedule-option-title">Send Now</div>
                    <div class="schedule-option-sub">
                        Immediately dispatch to 245 verified contacts via Sydney relay.
                    </div>
                </div>
            </div>

            <!-- Option 2: Schedule Send (Container box) -->
            <div class="schedule-send-box" id="option-schedule-send">
                <div class="d-flex align-items-start gap-3">
                    <div class="custom-radio-circle unselected" id="circle-schedule-send">
                        <div class="custom-radio-dot" id="dot-schedule-send" style="display: none;"></div>
                    </div>
                    <div>
                        <div class="schedule-option-title">Schedule Send</div>
                        <div class="schedule-option-sub">
                            Queue campaign for optimal opening window.
                        </div>
                    </div>
                </div>

                <!-- Timing Grid -->
                <div class="schedule-timing-grid">
                    <div class="sched-timing-col">
                        <span class="sched-timing-label">SCHEDULED DATE</span>
                        <span class="sched-timing-val">15 Oct 2026</span>
                    </div>
                    <div class="sched-timing-col">
                        <span class="sched-timing-label">SCHEDULED TIME</span>
                        <span class="sched-timing-val">09:00 AM</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bottom Action Bar (3-Pill Navigation Button Container) -->
        <div class="review-bottom-nav">
            <!-- Action 1: Save Draft -->
            <button type="button" class="btn-review-secondary" id="btn-save-draft">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Save Draft</span>
            </button>

            <!-- Action 2: Schedule -->
            <button type="button" class="btn-review-secondary" id="btn-schedule-action">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Schedule</span>
            </button>

            <!-- Action 3: Send Now (Primary Submit) -->
            <button type="submit" class="btn-review-primary" id="btn-send-now-submit">
                <span>Send Now</span>
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
    <span id="toast-message">Campaign status updated</span>
</div>

<!-- JavaScript Interactivity -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('campaign-review-form');
    const statusInput = document.getElementById('form-campaign-status');
    const optSendNow = document.getElementById('option-send-now');
    const optSchedule = document.getElementById('option-schedule-send');
    const circleSendNow = document.getElementById('circle-send-now');
    const dotSendNow = document.getElementById('dot-send-now');
    const circleSchedule = document.getElementById('circle-schedule-send');
    const dotSchedule = document.getElementById('dot-schedule-send');
    const btnSaveDraft = document.getElementById('btn-save-draft');
    const btnSchedule = document.getElementById('btn-schedule-action');
    const btnSendNow = document.getElementById('btn-send-now-submit');
    const toast = document.getElementById('campaign-toast');
    const toastMessage = document.getElementById('toast-message');

    let currentMode = 'now'; // 'now' or 'scheduled'

    function showToast(msg) {
        toastMessage.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    function selectSendNow() {
        currentMode = 'now';
        circleSendNow.classList.remove('unselected');
        dotSendNow.style.display = 'block';

        circleSchedule.classList.add('unselected');
        dotSchedule.style.display = 'none';
        statusInput.value = 'Sent';
    }

    function selectSchedule() {
        currentMode = 'scheduled';
        circleSendNow.classList.add('unselected');
        dotSendNow.style.display = 'none';

        circleSchedule.classList.remove('unselected');
        dotSchedule.style.display = 'block';
        statusInput.value = 'Scheduled';
    }

    optSendNow.addEventListener('click', selectSendNow);
    optSchedule.addEventListener('click', selectSchedule);

    btnSaveDraft.addEventListener('click', function () {
        statusInput.value = 'Draft';
        showToast('Saving draft...');
        setTimeout(() => {
            form.submit();
        }, 300);
    });

    btnSchedule.addEventListener('click', function () {
        selectSchedule();
        showToast('Scheduling campaign for 15 Oct 2026...');
        setTimeout(() => {
            form.submit();
        }, 400);
    });

    btnSendNow.addEventListener('click', function (e) {
        if (currentMode === 'scheduled') {
            statusInput.value = 'Scheduled';
        } else {
            statusInput.value = 'Sent';
        }
    });
});
</script>

</body>
</html>
