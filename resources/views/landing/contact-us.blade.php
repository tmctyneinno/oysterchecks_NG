@extends('layouts.landing')

@section('content')

<style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --navy:       #162E66;
        --navy-dark:  #0e1e44;
        --navy-mid:   #1e3d85;
        --white:      #ffffff;
        --off-white:  #f6f8fc;
        --text-dark:  #1a1f36;
        --text-muted: #5a6282;
        --border:     rgba(22, 46, 102, 0.12);
        --radius-sm:  8px;
        --radius-md:  12px;
        --radius-lg:  16px;
        --shadow-sm:  0 1px 4px rgba(22,46,102,0.08);
        --shadow-md:  0 4px 20px rgba(22,46,102,0.10);
        --shadow-lg:  0 12px 40px rgba(22,46,102,0.14);
    }

    /* ── PAGE HERO ───────────────────────────────── */
    .ct-hero {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        padding: 96px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .ct-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .ct-hero .container { position: relative; z-index: 1; }
    .ct-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.6);
        margin-bottom: 16px;
    }
    .ct-hero__eyebrow::before {
        content: '';
        display: block;
        width: 22px; height: 2px;
        background: rgba(255,255,255,0.4);
        flex-shrink: 0;
    }
    .ct-hero h1 {
        font-size: clamp(32px, 4vw, 52px);
        font-weight: 800;
        color: #fff;
        line-height: 1.12;
        letter-spacing: -0.025em;
        margin: 0 0 16px;
    }
    .ct-hero p {
        font-size: 17px;
        color: rgba(255,255,255,0.72);
        line-height: 1.65;
        max-width: 500px;
        margin: 0;
    }
    .ct-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 28px;
        font-size: 13px;
        color: rgba(255,255,255,0.5);
    }
    .ct-breadcrumb a { color: rgba(255,255,255,0.7); text-decoration: none; }
    .ct-breadcrumb a:hover { color: #fff; }
    .ct-breadcrumb span { color: rgba(255,255,255,0.35); }

    /* ── INFO CARDS ──────────────────────────────── */
    .ct-info {
        background: var(--off-white);
        padding: 0;
    }
    .ct-info__grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
    }
    @media (max-width: 991px) { .ct-info__grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .ct-info__grid { grid-template-columns: 1fr; } }

    .ct-info__card {
        background: var(--white);
        border: 1px solid var(--border);
        padding: 36px 28px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
        transition: box-shadow 0.2s, transform 0.2s;
        margin: -1px 0 0 -1px;
    }
    .ct-info__card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); z-index: 1; position: relative; }
    .ct-info__icon {
        width: 48px; height: 48px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ct-info__icon svg { width: 22px; height: 22px; }
    .ic-blue   { background: #ddeeff; color: #1255aa; }
    .ic-teal   { background: #d5f2e8; color: #0d6e52; }
    .ic-purple { background: #edeafd; color: #5445b8; }
    .ic-amber  { background: #fef0d5; color: #8a5010; }

    .ct-info__label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin: 0;
    }
    .ct-info__value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
        line-height: 1.45;
    }
    .ct-info__value a { color: var(--navy); text-decoration: none; }
    .ct-info__value a:hover { text-decoration: underline; }

    /* ── MAIN CONTACT SECTION ────────────────────── */
    .ct-main {
        padding: 88px 0 100px;
        background: var(--white);
    }

    /* Form panel */
    .ct-form-panel {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 44px 40px;
        box-shadow: var(--shadow-md);
    }
    @media (max-width: 575px) { .ct-form-panel { padding: 28px 20px; } }

    .ct-form-panel__head { margin-bottom: 32px; }
    .ct-form-panel__pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--navy);
        background: rgba(22,46,102,0.08);
        padding: 4px 14px;
        border-radius: 100px;
        margin-bottom: 12px;
    }
    .ct-form-panel h2 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.02em;
        margin: 0 0 8px;
        line-height: 1.25;
    }
    .ct-form-panel p {
        font-size: 14px;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.6;
    }

    /* Form fields */
    .ct-field { margin-bottom: 16px; }
    .ct-field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-dark);
        letter-spacing: 0.03em;
        margin-bottom: 6px;
    }
    .ct-field input,
    .ct-field textarea,
    .ct-field select {
        width: 100%;
        height: 46px;
        padding: 0 14px;
        font-size: 14px;
        color: var(--text-dark);
        background: var(--off-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        outline: none;
        transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        font-family: inherit;
    }
    .ct-field textarea {
        height: 110px;
        padding: 12px 14px;
        resize: vertical;
    }
    .ct-field input:focus,
    .ct-field textarea:focus,
    .ct-field select:focus {
        border-color: var(--navy);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(22,46,102,0.08);
    }
    .ct-field input::placeholder,
    .ct-field textarea::placeholder { color: #a0a8bc; }

    .ct-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 575px) { .ct-field-row { grid-template-columns: 1fr; } }

    .ct-submit-btn {
        width: 100%;
        height: 50px;
        background: var(--navy);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.03em;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 8px;
        transition: background 0.18s, transform 0.18s;
    }
    .ct-submit-btn:hover { background: var(--navy-dark); transform: translateY(-1px); }
    .ct-submit-btn svg { width: 16px; height: 16px; }

    /* Side content */
    .ct-side { padding-left: 12px; }
    @media (max-width: 767px) { .ct-side { padding-left: 0; margin-top: 40px; } }

    .ct-side__heading {
        font-size: clamp(22px, 2.8vw, 30px);
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.02em;
        line-height: 1.25;
        margin: 0 0 16px;
    }
    .ct-side__lead {
        font-size: 16px;
        color: var(--text-muted);
        line-height: 1.7;
        margin: 0 0 32px;
    }
    .ct-side__divider {
        border: none;
        border-top: 1px solid var(--border);
        margin: 0 0 28px;
    }

    /* Trust items */
    .ct-trust-list {
        list-style: none;
        padding: 0; margin: 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .ct-trust-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
        color: var(--text-dark);
        line-height: 1.5;
    }
    .ct-trust-list__icon {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: rgba(22,46,102,0.08);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .ct-trust-list__icon svg { width: 13px; height: 13px; color: var(--navy); }

    /* Response note */
    .ct-response-note {
        margin-top: 32px;
        background: var(--off-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 20px 22px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .ct-response-note__icon {
        width: 36px; height: 36px;
        border-radius: var(--radius-sm);
        background: #ddeeff;
        color: #1255aa;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ct-response-note__icon svg { width: 18px; height: 18px; }
    .ct-response-note__text h6 {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 4px;
    }
    .ct-response-note__text p {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.5;
    }

    /* Alert messages */
    .ct-alert {
        padding: 12px 16px;
        border-radius: var(--radius-sm);
        font-size: 13.5px;
        font-weight: 500;
        margin-bottom: 20px;
        display: none;
    }
    .ct-alert--success { background: #d5f2e8; color: #0d6e52; display: block; }
    .ct-alert--error   { background: #fde8e8; color: #b83232; display: block; }
</style>

<div class="main">

    <!-- ═══════════════════════════════ HERO ═══ -->
    <section class="ct-hero">
        <div class="container">
            <p class="ct-hero__eyebrow">Get in touch</p>
            <h1>Contact Us</h1>
            <p>Have a question or ready to get started? Our team is here to help you every step of the way.</p>
            <nav class="ct-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span>›</span>
                <span style="color:rgba(255,255,255,0.85);">Contact Us</span>
            </nav>
        </div>
    </section>


    <!-- ═══════════════════════════ INFO CARDS ═══ -->
    <div class="ct-info">
        <div class="container-fluid p-0">
            <div class="ct-info__grid">

                <div class="ct-info__card">
                    <div class="ct-info__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.09 9.8a19.79 19.79 0 01-3.07-8.64A2 2 0 012 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14v2.92z"/>
                        </svg>
                    </div>
                    <p class="ct-info__label">Call Us</p>
                    <p class="ct-info__value"><a href="tel:+23417001770">+234 170 017 70</a></p>
                </div>

                <div class="ct-info__card">
                    <div class="ct-info__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <p class="ct-info__label">Visit Us</p>
                    <p class="ct-info__value">Adeola Adeoye Street,<br>Off Toyin Street, Ikeja</p>
                </div>

                <div class="ct-info__card">
                    <div class="ct-info__icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <p class="ct-info__label">Email Us</p>
                    <p class="ct-info__value"><a href="mailto:enquiries@oysterchecks.com">enquiries@oysterchecks.com</a></p>
                </div>

                <div class="ct-info__card">
                    <div class="ct-info__icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <p class="ct-info__label">Live Chat</p>
                    <p class="ct-info__value">Available 24 / 7</p>
                </div>

            </div>
        </div>
    </div>


    <!-- ═══════════════════════════ MAIN FORM ═══ -->
    <section class="ct-main">
        <div class="container">
            <div class="row">

                <!-- Form -->
                <div class="col-md-12 col-lg-7">
                    <div class="ct-form-panel">
                        <div class="ct-form-panel__head">
                            <span class="ct-form-panel__pill">Send us a message</span>
                            <h2>Ready to get started?</h2>
                            <p>Fill in the form below and our team will be in touch within one business day.</p>
                        </div>

                        @if(session('success'))
                            <div class="ct-alert ct-alert--success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="ct-alert ct-alert--error">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('ContactForm') }}" method="POST" id="contactForm" novalidate>
                            @csrf

                            <div class="ct-field-row">
                                <div class="ct-field">
                                    <label for="ct_name">Full Name <span style="color:#b83232;">*</span></label>
                                    <input type="text" id="ct_name" name="name" placeholder="e.g. John Adeyemi" required value="{{ old('name') }}">
                                    @error('name')<span style="font-size:12px;color:#b83232;">{{ $message }}</span>@enderror
                                </div>
                                <div class="ct-field">
                                    <label for="ct_email">Email Address <span style="color:#b83232;">*</span></label>
                                    <input type="email" id="ct_email" name="email" placeholder="you@company.com" required value="{{ old('email') }}">
                                    @error('email')<span style="font-size:12px;color:#b83232;">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="ct-field-row">
                                <div class="ct-field">
                                    <label for="ct_phone">Phone Number <span style="color:#b83232;">*</span></label>
                                    <input type="tel" id="ct_phone" name="phone" placeholder="+234 800 000 0000" required value="{{ old('phone') }}">
                                    @error('phone')<span style="font-size:12px;color:#b83232;">{{ $message }}</span>@enderror
                                </div>
                                <div class="ct-field">
                                    <label for="ct_company">Company Name <span style="color:#b83232;">*</span></label>
                                    <input type="text" id="ct_company" name="company" placeholder="Your organisation" required value="{{ old('company') }}">
                                    @error('company')<span style="font-size:12px;color:#b83232;">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="ct-field">
                                <label for="ct_address">Company Address <span style="color:#b83232;">*</span></label>
                                <input type="text" id="ct_address" name="address" placeholder="Street, City, Country" required value="{{ old('address') }}">
                                @error('address')<span style="font-size:12px;color:#b83232;">{{ $message }}</span>@enderror
                            </div>

                            <div class="ct-field">
                                <label for="ct_message">Message <span style="color:#b83232;">*</span></label>
                                <textarea id="ct_message" name="message" placeholder="Tell us about your requirements…" required>{{ old('message') }}</textarea>
                                @error('message')<span style="font-size:12px;color:#b83232;">{{ $message }}</span>@enderror
                            </div>

                            <button type="submit" class="ct-submit-btn" id="btnContactUs">
                                Send Request
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 8h10M9 4l4 4-4 4"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Side -->
                <div class="col-md-12 col-lg-5">
                    <div class="ct-side">
                        <h3 class="ct-side__heading">
                            Looking for comprehensive background checks, KYC &amp; AML compliance solutions?
                        </h3>
                        <p class="ct-side__lead">
                            You are in the right place. Oysterchecks provides a wide range of KYC, AML, and background verification solutions trusted by organisations across finance, healthcare, government, and beyond.
                        </p>

                        <hr class="ct-side__divider">

                        <ul class="ct-trust-list">
                            <li>
                                <div class="ct-trust-list__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                End-to-end identity verification and background screening
                            </li>
                            <li>
                                <div class="ct-trust-list__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                Real-time AML transaction monitoring and fraud detection
                            </li>
                            <li>
                                <div class="ct-trust-list__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                KYC / KYB onboarding with biometric authentication
                            </li>
                            <li>
                                <div class="ct-trust-list__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                API-first integration with your existing workflows
                            </li>
                            <li>
                                <div class="ct-trust-list__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                GDPR, ISO 27001 and regional compliance frameworks
                            </li>
                        </ul>

                        <div class="ct-response-note">
                            <div class="ct-response-note__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <div class="ct-response-note__text">
                                <h6>We'll get back to you quickly</h6>
                                <p>Our team responds to all enquiries within one business day. For urgent matters, please call us directly.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>

@endsection