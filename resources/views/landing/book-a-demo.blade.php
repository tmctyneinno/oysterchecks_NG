@extends('layouts.landing')

@section('content')

<style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --navy:       #162E66;
        --navy-dark:  #0e1e44;
        --navy-mid:   #1e3d85;
        --red:        #DA251D;
        --white:      #ffffff;
        --off-white:  #f6f8fc;
        --text-dark:  #1a1f36;
        --text-muted: #5a6282;
        --border:     rgba(22, 46, 102, 0.12);
        --radius-sm:  8px;
        --radius-md:  12px;
        --radius-lg:  16px;
        --shadow-sm:  0 1px 4px rgba(22,46,102,0.08);
        --shadow-md:  0 4px 20px rgba(22,46,102,0.12);
        --shadow-lg:  0 12px 40px rgba(22,46,102,0.18);
    }

    /* ── PAGE WRAPPER ────────────────────────────── */
    .demo-page {
        background: var(--off-white);
        min-height: 100vh;
    }

    /* ── HERO / SPLIT LAYOUT ─────────────────────── */
    .demo-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 100vh;
    }
    @media (max-width: 991px) {
        .demo-split { grid-template-columns: 1fr; }
    }

    /* Left panel */
    .demo-split__left {
        background: linear-gradient(145deg, var(--navy-dark) 0%, var(--navy) 65%, var(--navy-mid) 100%);
        padding: 80px 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .demo-split__left::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    @media (max-width: 991px) {
        .demo-split__left { padding: 60px 32px; }
    }
    .demo-split__left-inner { position: relative; z-index: 1; max-width: 480px; }

    .demo-eyebrow {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.16em;
        text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 18px;
    }
    .demo-eyebrow::before {
        content: ''; display: block; width: 22px; height: 2px;
        background: rgba(255,255,255,0.4); flex-shrink: 0;
    }
    .demo-split__left h1 {
        font-size: clamp(26px, 3.5vw, 42px); font-weight: 800; color: #fff;
        line-height: 1.12; letter-spacing: -0.025em; margin: 0 0 10px;
    }
    .demo-split__left h1 span { color: #FA6E6E; }
    .demo-split__left .demo-sub {
        font-size: 16px; color: rgba(255,255,255,0.7); line-height: 1.7;
        margin: 0 0 40px;
    }

    /* Trust points */
    .demo-trust { display: flex; flex-direction: column; gap: 16px; margin-bottom: 44px; }
    .demo-trust__item {
        display: flex; align-items: flex-start; gap: 12px;
    }
    .demo-trust__check {
        width: 28px; height: 28px; border-radius: 50%;
        background: rgba(255,255,255,0.12); color: #fff;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .demo-trust__check svg { width: 13px; height: 13px; }
    .demo-trust__text { font-size: 14px; color: rgba(255,255,255,0.82); line-height: 1.5; padding-top: 4px; }

    /* Industry pills */
    .demo-industries { margin-bottom: 40px; }
    .demo-industries__label {
        font-size: 11px; font-weight: 700; letter-spacing: 0.12em;
        text-transform: uppercase; color: rgba(255,255,255,0.45); margin-bottom: 12px;
    }
    .demo-industries__pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .demo-industries__pill {
        font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.75);
        background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
        padding: 5px 12px; border-radius: 100px;
        transition: background 0.18s;
    }
    .demo-industries__pill:hover { background: rgba(255,255,255,0.18); }

    /* What to expect */
    .demo-expect {
        background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
        border-radius: var(--radius-md); padding: 20px 22px;
    }
    .demo-expect__title {
        font-size: 12px; font-weight: 700; letter-spacing: 0.1em;
        text-transform: uppercase; color: rgba(255,255,255,0.5); margin: 0 0 12px;
    }
    .demo-expect__steps { display: flex; flex-direction: column; gap: 10px; }
    .demo-expect__step {
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; color: rgba(255,255,255,0.75);
    }
    .demo-expect__num {
        width: 22px; height: 22px; border-radius: 50%;
        background: rgba(255,255,255,0.15); color: #fff;
        font-size: 11px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    /* Right panel — form */
    .demo-split__right {
        background: var(--white);
        padding: 72px 56px;
        display: flex; flex-direction: column; justify-content: center;
        overflow-y: auto;
    }
    @media (max-width: 991px) { .demo-split__right { padding: 48px 32px; } }
    @media (max-width: 575px) { .demo-split__right { padding: 36px 20px; } }

    .demo-form__head { margin-bottom: 32px; }
    .demo-form__pill {
        display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.12em;
        text-transform: uppercase; color: var(--navy); background: rgba(22,46,102,0.08);
        padding: 4px 14px; border-radius: 100px; margin-bottom: 12px;
    }
    .demo-form__head h2 {
        font-size: clamp(20px, 2.5vw, 28px); font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.02em; margin: 0 0 6px;
    }
    .demo-form__head p { font-size: 14px; color: var(--text-muted); margin: 0; line-height: 1.55; }
    .demo-form__mandatory { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

    /* Field rows */
    .demo-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 575px) { .demo-field-row { grid-template-columns: 1fr; } }
    .demo-field { margin-bottom: 14px; }
    .demo-field label {
        display: block; font-size: 12px; font-weight: 600; color: var(--text-dark);
        letter-spacing: 0.03em; margin-bottom: 5px;
    }
    .demo-field label span { color: var(--red); margin-left: 2px; }
    .demo-field input, .demo-field select {
        width: 100%; height: 44px; padding: 0 13px;
        font-size: 13.5px; color: var(--text-dark);
        background: var(--off-white); border: 1px solid var(--border);
        border-radius: var(--radius-sm); outline: none;
        transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        font-family: inherit;
    }
    .demo-field input:focus, .demo-field select:focus {
        border-color: var(--navy); background: var(--white);
        box-shadow: 0 0 0 3px rgba(22,46,102,0.08);
    }
    .demo-field input::placeholder { color: #b0b8cc; }

    /* Section labels within form */
    .demo-form-section-label {
        font-size: 12px; font-weight: 700; color: var(--text-dark);
        letter-spacing: 0.04em; margin: 18px 0 10px;
    }

    /* Checkbox groups */
    .demo-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    @media (max-width: 575px) { .demo-checks { grid-template-columns: 1fr; } }
    .demo-check-item {
        display: flex; align-items: center; gap: 9px;
        background: var(--off-white); border: 1px solid var(--border);
        border-radius: var(--radius-sm); padding: 10px 13px;
        cursor: pointer; transition: border-color 0.18s, background 0.18s;
        font-size: 13px; color: var(--text-dark); font-weight: 500;
    }
    .demo-check-item:hover { border-color: rgba(22,46,102,0.25); background: #eef2fb; }
    .demo-check-item input[type="checkbox"] {
        width: 15px; height: 15px; margin: 0; flex-shrink: 0; accent-color: var(--navy);
        cursor: pointer;
    }
    .demo-checks--3 { grid-template-columns: repeat(3, 1fr); }
    @media (max-width: 575px) { .demo-checks--3 { grid-template-columns: 1fr 1fr; } }

    /* Submit */
    .demo-submit-row {
        display: flex; align-items: flex-start; gap: 16px; margin-top: 22px;
        flex-wrap: wrap;
    }
    .demo-submit-btn {
        display: inline-flex; align-items: center; gap: 9px;
        background: var(--navy); color: #fff;
        font-size: 14px; font-weight: 700; padding: 13px 28px;
        border-radius: var(--radius-sm); border: none; cursor: pointer;
        letter-spacing: 0.01em; white-space: nowrap;
        transition: background 0.18s, transform 0.18s;
    }
    .demo-submit-btn:hover { background: var(--navy-dark); transform: translateY(-1px); }
    .demo-submit-btn svg { width: 15px; height: 15px; }
    .demo-submit-legal { font-size: 12px; color: var(--text-muted); line-height: 1.55; max-width: 280px; }
    .demo-submit-legal a { color: var(--navy); text-decoration: none; }
    .demo-submit-legal a:hover { text-decoration: underline; }

    /* ── INDUSTRIES SECTION ──────────────────────── */
    .demo-ind-section { padding: 80px 0; background: var(--white); }
    .demo-ind-heading { text-align: center; margin-bottom: 48px; }
    .demo-ind-pill {
        display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.13em;
        text-transform: uppercase; color: var(--navy); background: rgba(22,46,102,0.08);
        padding: 5px 16px; border-radius: 100px; margin-bottom: 14px;
    }
    .demo-ind-heading h2 {
        font-size: clamp(22px, 3vw, 34px); font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.025em; margin: 0 0 10px;
    }
    .demo-ind-heading p { font-size: 15px; color: var(--text-muted); max-width: 500px; margin: 0 auto; line-height: 1.65; }

    .demo-ind-grid {
        display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px;
    }
    @media (max-width: 991px) { .demo-ind-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 767px) { .demo-ind-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 575px) { .demo-ind-grid { grid-template-columns: repeat(2, 1fr); } }

    .demo-ind-item {
        background: var(--off-white); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 22px 16px;
        text-align: center; transition: transform 0.2s, box-shadow 0.2s;
    }
    .demo-ind-item:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .demo-ind-icon {
        width: 44px; height: 44px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center; margin: 0 auto 11px;
    }
    .demo-ind-icon svg { width: 20px; height: 20px; }
    .demo-ind-label { font-size: 12.5px; font-weight: 600; color: var(--text-dark); margin: 0; line-height: 1.3; }

    /* icon colours */
    .ic-blue   { background: #ddeeff; color: #1255aa; }
    .ic-teal   { background: #d5f2e8; color: #0d6e52; }
    .ic-purple { background: #edeafd; color: #5445b8; }
    .ic-amber  { background: #fef0d5; color: #8a5010; }
    .ic-red    { background: #fde8e8; color: #b83232; }
    .ic-green  { background: #e3f4d5; color: #3a6d10; }
    .ic-slate  { background: #e8ecf6; color: #3a4a7a; }
    .ic-rose   { background: #fce7f3; color: #9d174d; }
    .ic-sky    { background: #e0f2fe; color: #075985; }
    .ic-orange { background: #fff4e6; color: #92400e; }

    /* ── FEATURE CARDS ────────────────────────────── */
    .demo-feat-section { padding: 0 0 88px; background: var(--white); }
    .demo-feat-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px;
    }
    @media (max-width: 767px) { .demo-feat-grid { grid-template-columns: 1fr; } }

    .demo-feat-card {
        background: var(--off-white); border: 1px solid var(--border);
        border-radius: var(--radius-lg); padding: 28px 24px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .demo-feat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .demo-feat-card__icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
    }
    .demo-feat-card__icon svg { width: 22px; height: 22px; }
    .demo-feat-card h3 { font-size: 15px; font-weight: 700; color: var(--text-dark); margin: 0 0 8px; line-height: 1.3; }
    .demo-feat-card p  { font-size: 13.5px; color: var(--text-muted); margin: 0; line-height: 1.6; }
</style>

<div class="demo-page">

    <!-- ════════════════ HERO SPLIT ════════════════ -->
    <section class="demo-split">

        <!-- LEFT ── brand content -->
        <div class="demo-split__left">
            <div class="demo-split__left-inner">
                <p class="demo-eyebrow">Live Demo</p>
                <h1>
                    Stop Fraud, Build Trust &amp;<br>
                    Drive Growth with<br>
                    <span>Intelligent Verification</span>
                </h1>
                <p class="demo-sub">
                    See Oysterchecks in action — from real-time identity verification and AML monitoring to background screening and GRC assurance.
                </p>

                <div class="demo-trust">
                    <div class="demo-trust__item">
                        <div class="demo-trust__check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <p class="demo-trust__text">End-to-end platform covering KYC, AML, employment checks, and GRC assurance</p>
                    </div>
                    <div class="demo-trust__item">
                        <div class="demo-trust__check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <p class="demo-trust__text">AI-driven real-time results across 180+ countries and jurisdictions</p>
                    </div>
                    <div class="demo-trust__item">
                        <div class="demo-trust__check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <p class="demo-trust__text">API-first — integrates with your existing onboarding, HR, and CRM systems</p>
                    </div>
                    <div class="demo-trust__item">
                        <div class="demo-trust__check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <p class="demo-trust__text">GDPR, ISO 27001 and multi-jurisdiction compliance frameworks built in</p>
                    </div>
                </div>

                <div class="demo-industries">
                    <p class="demo-industries__label">Trusted across</p>
                    <div class="demo-industries__pills">
                        <span class="demo-industries__pill">Banking</span>
                        <span class="demo-industries__pill">Fintech</span>
                        <span class="demo-industries__pill">Healthcare</span>
                        <span class="demo-industries__pill">Government</span>
                        <span class="demo-industries__pill">Security</span>
                        <span class="demo-industries__pill">HR &amp; Recruitment</span>
                        <span class="demo-industries__pill">Real Estate</span>
                        <span class="demo-industries__pill">Aviation</span>
                    </div>
                </div>

                <div class="demo-expect">
                    <p class="demo-expect__title">What to expect in your demo</p>
                    <div class="demo-expect__steps">
                        <div class="demo-expect__step">
                            <span class="demo-expect__num">1</span>
                            A tailored walkthrough of the platform relevant to your industry
                        </div>
                        <div class="demo-expect__step">
                            <span class="demo-expect__num">2</span>
                            Live demonstration of screening, monitoring, and reporting features
                        </div>
                        <div class="demo-expect__step">
                            <span class="demo-expect__num">3</span>
                            Q&amp;A with a product specialist — no sales pressure
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT ── form -->
        <div class="demo-split__right">
            <div class="demo-form__head">
                <span class="demo-form__pill">Book your session</span>
                <h2>Book a Live Demo</h2>
                <p>Fill in the form and we'll be in touch to confirm your session within one business day.</p>
                <p class="demo-form__mandatory">Fields marked <span style="color:var(--red);">*</span> are required.</p>
            </div>

            <form action="{{ route('book-a-demo') }}" method="POST" id="demoForm" novalidate>
                @csrf

                <div class="demo-field-row">
                    <div class="demo-field">
                        <label>First Name <span>*</span></label>
                        <input type="text" name="firstname" placeholder="e.g. John" required>
                    </div>
                    <div class="demo-field">
                        <label>Last Name <span>*</span></label>
                        <input type="text" name="lastname" placeholder="e.g. Adeyemi" required>
                    </div>
                </div>

                <div class="demo-field">
                    <label>Company Email <span>*</span></label>
                    <input type="email" name="email" placeholder="you@company.com" required>
                </div>

                <div class="demo-field-row">
                    <div class="demo-field">
                        <label>Job Title <span>*</span></label>
                        <input type="text" name="jobtitle" placeholder="e.g. Head of Compliance" required>
                    </div>
                    <div class="demo-field">
                        <label>Company <span>*</span></label>
                        <input type="text" name="company" placeholder="Your organisation" required>
                    </div>
                </div>

                <div class="demo-field-row">
                    <div class="demo-field">
                        <label>Country <span>*</span></label>
                        <input type="text" name="country" placeholder="e.g. United Kingdom" required>
                    </div>
                    <div class="demo-field">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="+44 700 000 0000">
                    </div>
                </div>

                <div class="demo-field">
                    <label>Preferred Demo Date <span>*</span></label>
                    <input type="date" name="date" required>
                </div>

                <p class="demo-form-section-label">Which platform are you looking to protect? <span style="color:var(--red);">*</span></p>
                <div class="demo-checks" style="margin-bottom: 4px;">
                    <label class="demo-check-item">
                        <input type="checkbox" name="platform[]" value="mobile_app"> Mobile App
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="platform[]" value="website"> Website
                    </label>
                </div>

                <p class="demo-form-section-label">What are you looking to protect against? <span style="color:var(--red);">*</span></p>
                <div class="demo-checks demo-checks--3">
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="account_takeover"> Account takeover
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="fake_accounts"> Fake accounts
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="referral_abuse"> Referral &amp; promo abuse
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="payment_fraud"> Payment fraud
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="incentive_abuse"> Incentive abuse
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="spam_abuse"> Spam &amp; abuse
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="identity_fraud"> Identity fraud
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="aml"> AML / financial crime
                    </label>
                    <label class="demo-check-item">
                        <input type="checkbox" name="protect[]" value="compliance"> Compliance gaps
                    </label>
                </div>

                <div class="demo-submit-row">
                    <button type="submit" class="demo-submit-btn">
                        Book My Demo
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8h10M9 4l4 4-4 4"/>
                        </svg>
                    </button>
                    <p class="demo-submit-legal">
                        By submitting this form you agree to our
                        <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.
                    </p>
                </div>

            </form>
        </div>

    </section>


    <!-- ════════════════ INDUSTRIES ════════════════ -->
    <section class="demo-ind-section">
        <div class="container">
            <div class="demo-ind-heading">
                <span class="demo-ind-pill">Who we serve</span>
                <h2>The verification solution built for every industry</h2>
                <p>Leverage real-time intelligence to eliminate risk blind spots, deliver superior user experiences, and accelerate growth.</p>
            </div>
            <div class="demo-ind-grid">
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
                    <p class="demo-ind-label">Banking &amp; Finance</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
                    <p class="demo-ind-label">Fintech &amp; Payments</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                    <p class="demo-ind-label">HR &amp; Recruitment</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-slate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
                    <p class="demo-ind-label">Government &amp; Public Sector</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
                    <p class="demo-ind-label">Healthcare &amp; NHS</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/></svg></div>
                    <p class="demo-ind-label">Security &amp; Defence</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                    <p class="demo-ind-label">Real Estate &amp; Tenancy</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-sky"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 010 20"/></svg></div>
                    <p class="demo-ind-label">Aviation &amp; Multinationals</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-rose"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M3 9h18M9 21V9"/></svg></div>
                    <p class="demo-ind-label">Professional Services</p>
                </div>
                <div class="demo-ind-item">
                    <div class="demo-ind-icon ic-orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                    <p class="demo-ind-label">Digital &amp; Neobanking</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ════════════════ FEATURE CARDS ════════════════ -->
    <section class="demo-feat-section">
        <div class="container">
            <div class="demo-feat-grid">
                <div class="demo-feat-card">
                    <div class="demo-feat-card__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            <path d="M16 3l1.5 1.5L19 3"/>
                        </svg>
                    </div>
                    <h3>Global Device &amp; Identity Intelligence</h3>
                    <p>Harness the global standard for identity verification. Trusted results you can act on, no matter how device parameters or user behaviour changes.</p>
                </div>
                <div class="demo-feat-card">
                    <div class="demo-feat-card__icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3>Sophisticated Fraud Detection</h3>
                    <p>Expose tools and techniques used for fraud across your ecosystem. Stop sophisticated attacks — account takeover, synthetic identities, and payment fraud — before they cause harm.</p>
                </div>
                <div class="demo-feat-card">
                    <div class="demo-feat-card__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <h3>Real-Time Trust Signals</h3>
                    <p>Identify whether a session, device, or account can be trusted instantly. Accurate, actionable signals that empower your team to make faster, better decisions.</p>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection