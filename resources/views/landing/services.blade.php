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

    /* ── HERO ────────────────────────────────────── */
    .sv-hero {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        padding: 100px 0 84px;
        position: relative; overflow: hidden;
    }
    .sv-hero::before {
        content: ''; position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .sv-hero .container { position: relative; z-index: 1; }
    .sv-hero__eyebrow {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.16em;
        text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 16px;
    }
    .sv-hero__eyebrow::before {
        content: ''; display: block; width: 22px; height: 2px;
        background: rgba(255,255,255,0.4); flex-shrink: 0;
    }
    .sv-hero h1 {
        font-size: clamp(30px, 4vw, 52px); font-weight: 800; color: #fff;
        line-height: 1.1; letter-spacing: -0.025em; margin: 0 0 18px;
    }
    .sv-hero p {
        font-size: 17px; color: rgba(255,255,255,0.72);
        line-height: 1.7; max-width: 560px; margin: 0 0 32px;
    }
    .sv-hero__actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .sv-btn-primary {
        display: inline-flex; align-items: center; gap: 9px;
        background: #fff; color: var(--navy); font-size: 14px; font-weight: 700;
        padding: 13px 26px; border-radius: var(--radius-sm); text-decoration: none;
        letter-spacing: 0.01em; transition: background 0.18s, transform 0.18s; white-space: nowrap;
    }
    .sv-btn-primary:hover { background: #e8eefb; color: var(--navy); text-decoration: none; transform: translateY(-1px); }
    .sv-btn-primary svg { width: 15px; height: 15px; }
    .sv-btn-outline {
        display: inline-flex; align-items: center; gap: 9px;
        background: rgba(255,255,255,0.1); color: #fff; font-size: 14px; font-weight: 600;
        padding: 13px 26px; border-radius: var(--radius-sm); text-decoration: none;
        border: 1px solid rgba(255,255,255,0.25); transition: background 0.18s; white-space: nowrap;
    }
    .sv-btn-outline:hover { background: rgba(255,255,255,0.18); color: #fff; text-decoration: none; }
    .sv-breadcrumb {
        display: flex; align-items: center; gap: 8px;
        margin-top: 28px; font-size: 13px; color: rgba(255,255,255,0.5);
    }
    .sv-breadcrumb a { color: rgba(255,255,255,0.7); text-decoration: none; }
    .sv-breadcrumb a:hover { color: #fff; }
    .sv-breadcrumb span { color: rgba(255,255,255,0.35); }

    /* ── SHARED ──────────────────────────────────── */
    .sv-section { padding: 80px 0; }
    .sv-section--alt { background: var(--off-white); }
    .sv-pill {
        display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.13em;
        text-transform: uppercase; color: var(--navy); background: rgba(22,46,102,0.08);
        padding: 5px 16px; border-radius: 100px; margin-bottom: 14px;
    }
    .sv-heading { text-align: center; margin-bottom: 52px; }
    .sv-heading h2 {
        font-size: clamp(24px, 3vw, 36px); font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.025em; margin: 0 0 12px;
    }
    .sv-heading p { font-size: 15px; color: var(--text-muted); max-width: 500px; margin: 0 auto; line-height: 1.65; }

    /* ── WHY SCREENING (INTRO) ───────────────────── */
    .sv-intro__grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;
    }
    @media (max-width: 767px) { .sv-intro__grid { grid-template-columns: 1fr; gap: 32px; } }
    .sv-intro__text h2 {
        font-size: clamp(22px, 2.8vw, 34px); font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.02em; line-height: 1.2; margin: 0 0 16px;
    }
    .sv-intro__text p { font-size: 15px; color: var(--text-muted); line-height: 1.8; margin: 0; }
    .sv-intro__stats {
        display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    }
    .sv-intro__stat {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 24px 20px;
        box-shadow: var(--shadow-sm);
    }
    .sv-intro__stat-num {
        font-size: 28px; font-weight: 800; color: var(--navy);
        letter-spacing: -0.03em; margin: 0 0 4px;
    }
    .sv-intro__stat-label { font-size: 12px; color: var(--text-muted); margin: 0; line-height: 1.4; }

    /* ── WHY USE US ──────────────────────────────── */
    .sv-why-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    }
    @media (max-width: 767px) { .sv-why-grid { grid-template-columns: 1fr; } }
    .sv-why-item {
        display: flex; align-items: flex-start; gap: 14px;
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 20px;
        box-shadow: var(--shadow-sm);
    }
    .sv-why-icon {
        width: 36px; height: 36px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .sv-why-icon svg { width: 17px; height: 17px; }
    .sv-why-text h5 { font-size: 14px; font-weight: 700; color: var(--text-dark); margin: 0 0 5px; }
    .sv-why-text p  { font-size: 13px; color: var(--text-muted); margin: 0; line-height: 1.55; }

    /* ── MAIN SERVICES GRID ──────────────────────── */
    .sv-services-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px;
    }
    @media (max-width: 991px) { .sv-services-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .sv-services-grid { grid-template-columns: 1fr; } }

    .sv-card {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius-lg); padding: 28px 26px 24px;
        display: flex; flex-direction: column;
        box-shadow: var(--shadow-sm);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .sv-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); border-color: rgba(22,46,102,0.22); }
    .sv-card__icon {
        width: 50px; height: 50px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center; margin-bottom: 18px;
    }
    .sv-card__icon svg { width: 24px; height: 24px; }
    .sv-card__title { font-size: 15.5px; font-weight: 700; color: var(--text-dark); margin: 0 0 6px; line-height: 1.3; letter-spacing: -0.01em; }
    .sv-card__sub   { font-size: 13px; color: var(--text-muted); margin: 0 0 16px; line-height: 1.5; }
    .sv-card hr { border: none; border-top: 1px solid var(--border); margin: 0 0 14px; }
    .sv-card ul { list-style: none; padding: 0; margin: 0 0 auto; display: flex; flex-direction: column; gap: 8px; }
    .sv-card ul li { font-size: 13px; color: var(--text-muted); padding-left: 16px; position: relative; line-height: 1.45; }
    .sv-card ul li::before { content: ''; position: absolute; left: 0; top: 6px; width: 5px; height: 5px; border-radius: 50%; background: var(--navy); opacity: 0.22; }
    .sv-card__outcome {
        margin-top: 18px; padding: 9px 13px; border-radius: var(--radius-sm);
        font-size: 12.5px; font-weight: 600; line-height: 1.4;
        display: flex; align-items: flex-start; gap: 7px;
    }
    .sv-card__outcome::before { content: '→'; flex-shrink: 0; font-weight: 700; }
    .sv-card__link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 600; color: var(--navy);
        text-decoration: none; margin-top: 16px;
    }
    .sv-card__link:hover { text-decoration: underline; }
    .sv-card__link svg { width: 13px; height: 13px; }

    /* icon colour classes */
    .ic-blue   { background: #ddeeff; color: #1255aa; }
    .ic-teal   { background: #d5f2e8; color: #0d6e52; }
    .ic-red    { background: #fde8e8; color: #b83232; }
    .ic-purple { background: #edeafd; color: #5445b8; }
    .ic-green  { background: #e3f4d5; color: #3a6d10; }
    .ic-amber  { background: #fef0d5; color: #8a5010; }
    .ic-slate  { background: #e8ecf6; color: #3a4a7a; }
    .ic-rose   { background: #fce7f3; color: #9d174d; }
    .ic-sky    { background: #e0f2fe; color: #075985; }

    /* outcome colour classes */
    .out-blue   { background: #ddeeff; color: #1255aa; }
    .out-teal   { background: #d5f2e8; color: #0d6e52; }
    .out-red    { background: #fde8e8; color: #b83232; }
    .out-purple { background: #edeafd; color: #5445b8; }
    .out-green  { background: #e3f4d5; color: #3a6d10; }
    .out-amber  { background: #fef0d5; color: #8a5010; }
    .out-slate  { background: #e8ecf6; color: #3a4a7a; }
    .out-rose   { background: #fce7f3; color: #9d174d; }
    .out-sky    { background: #e0f2fe; color: #075985; }

    /* ── INDUSTRIES ──────────────────────────────── */
    .sv-ind-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
    }
    @media (max-width: 991px) { .sv-ind-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 575px) { .sv-ind-grid { grid-template-columns: repeat(2, 1fr); } }
    .sv-ind-item {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 22px 16px;
        text-align: center; box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .sv-ind-item:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .sv-ind-icon {
        width: 44px; height: 44px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 12px;
    }
    .sv-ind-icon svg { width: 20px; height: 20px; }
    .sv-ind-label { font-size: 13px; font-weight: 600; color: var(--text-dark); margin: 0; line-height: 1.3; }

    /* ── CTA ─────────────────────────────────────── */
    .sv-cta-strip {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        border-radius: var(--radius-lg); padding: 52px 52px;
        display: flex; align-items: center; justify-content: space-between; gap: 24px;
        box-shadow: var(--shadow-lg);
    }
    @media (max-width: 767px) { .sv-cta-strip { flex-direction: column; text-align: center; padding: 36px 28px; } }
    .sv-cta-strip h3 { font-size: 22px; font-weight: 800; color: #fff; margin: 0 0 8px; letter-spacing: -0.01em; }
    .sv-cta-strip p  { font-size: 15px; color: rgba(255,255,255,0.68); margin: 0; line-height: 1.5; }
    .sv-cta-btns { display: flex; gap: 12px; flex-wrap: wrap; flex-shrink: 0; }
    @media (max-width: 767px) { .sv-cta-btns { justify-content: center; } }
</style>

<div class="main">

    <!-- ════════════════════════ HERO ════════════════════════ -->
    <section class="sv-hero">
        <div class="container">
            <p class="sv-hero__eyebrow">Platform</p>
            <h1>Services &amp; Solutions</h1>
            <p>
                Everything your organisation needs to verify identity, screen employees,
                manage compliance, and monitor financial crime — in one intelligent platform.
            </p>
            <div class="sv-hero__actions">
                <a href="{{ route('login') }}" class="sv-btn-primary">
                    Get Started
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                </a>
                <a href="{{ route('book-a-demo') }}" class="sv-btn-outline">Book a Demo</a>
            </div>
            <nav class="sv-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span>›</span>
                <span style="color:rgba(255,255,255,0.85);">Services</span>
            </nav>
        </div>
    </section>


    <!-- ════════════════════ WHY SCREENING ════════════════════ -->
    <section class="sv-section">
        <div class="container">
            <div class="sv-intro__grid">
                <div class="sv-intro__text">
                    <span class="sv-pill">Why it matters</span>
                    <h2>Why employee screening &amp; background checks?</h2>
                    <p>
                        Employees are the real assets of any organisation and are expected to act professionally and ethically. Background checks reduce the risk of negligent hiring, improve workplace safety, and protect your organisation's reputation. Suitable employees drive productivity, reduce risk, and allow leadership to focus on what matters.
                    </p>
                    <p style="margin-top:16px; font-size:15px; color:var(--text-muted); line-height:1.8;">
                        Oysterchecks simplifies pre-employment checks by delivering extensive screening quickly while meeting all essential regulatory and professional standards — including global background checks for international hires.
                    </p>
                </div>
                <div class="sv-intro__stats">
                    <div class="sv-intro__stat">
                        <p class="sv-intro__stat-num">180+</p>
                        <p class="sv-intro__stat-label">Countries covered for global screening</p>
                    </div>
                    <div class="sv-intro__stat">
                        <p class="sv-intro__stat-num">Real-time</p>
                        <p class="sv-intro__stat-label">Verified results delivered instantly</p>
                    </div>
                    <div class="sv-intro__stat">
                        <p class="sv-intro__stat-num">AI-led</p>
                        <p class="sv-intro__stat-label">Intelligent data analytics and fraud detection</p>
                    </div>
                    <div class="sv-intro__stat">
                        <p class="sv-intro__stat-num">API-first</p>
                        <p class="sv-intro__stat-label">Seamless integration with your onboarding tools</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ════════════════════ WHY USE US ═══════════════════════ -->
    <section class="sv-section sv-section--alt">
        <div class="container">
            <div class="sv-heading">
                <span class="sv-pill">Why Oysterchecks</span>
                <h2>Built for organisations that can't afford to get it wrong</h2>
                <p>Six reasons thousands of organisations choose Oysterchecks for their screening and compliance needs.</p>
            </div>
            <div class="sv-why-grid">
                <div class="sv-why-item">
                    <div class="sv-why-icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-5"/></svg>
                    </div>
                    <div class="sv-why-text">
                        <h5>End-to-end in one platform</h5>
                        <p>Verification, monitoring, and decision intelligence — no need to juggle multiple vendors.</p>
                    </div>
                </div>
                <div class="sv-why-item">
                    <div class="sv-why-icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/></svg>
                    </div>
                    <div class="sv-why-text">
                        <h5>Global reach, local compliance</h5>
                        <p>Access global data with alignment to local regulatory frameworks in 180+ countries.</p>
                    </div>
                </div>
                <div class="sv-why-item">
                    <div class="sv-why-icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    </div>
                    <div class="sv-why-text">
                        <h5>AI that speeds up decisions</h5>
                        <p>AI-driven analytics that accelerate results without compromising accuracy.</p>
                    </div>
                </div>
                <div class="sv-why-item">
                    <div class="sv-why-icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                    </div>
                    <div class="sv-why-text">
                        <h5>Easy system integration</h5>
                        <p>API-first architecture connects seamlessly with your onboarding, HR, and CRM tools.</p>
                    </div>
                </div>
                <div class="sv-why-item">
                    <div class="sv-why-icon ic-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M7 12h10M7 7.5h6M7 16.5h4"/></svg>
                    </div>
                    <div class="sv-why-text">
                        <h5>Customisable for your sector</h5>
                        <p>Configurable workflows designed for regulated sectors: finance, healthcare, government, security.</p>
                    </div>
                </div>
                <div class="sv-why-item">
                    <div class="sv-why-icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    </div>
                    <div class="sv-why-text">
                        <h5>Built on governance expertise</h5>
                        <p>Developed within The Morgans Consortium ecosystem of risk, compliance, and assurance expertise.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ════════════════════ ALL SERVICES ═════════════════════ -->
    <section class="sv-section">
        <div class="container">
            <div class="sv-heading">
                <span class="sv-pill">Our services</span>
                <h2>Everything available on the Oysterchecks platform</h2>
                <p>A modular, API-driven platform supporting the full lifecycle of risk, compliance, and assurance.</p>
            </div>

            <div class="sv-services-grid">

                <!-- 1. Employment Checks -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            <path d="M16 3l1.5 1.5L19 3"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">Employment Checks</h3>
                    <p class="sv-card__sub">Comprehensive background screening for every role and risk level</p>
                    <hr>
                    <ul>
                        <li>DBS basic, standard &amp; enhanced</li>
                        <li>Credit check (including PEP &amp; sanctions)</li>
                        <li>Right to Work (RTW) check</li>
                        <li>Employment referencing &amp; gap analysis</li>
                        <li>Adverse media &amp; social media checks</li>
                        <li>Qualification &amp; directorship verification</li>
                    </ul>
                    <div class="sv-card__outcome out-blue">Trusted workforce with verified credentials</div>
                    <a href="{{ route('employment-checks') }}" class="sv-card__link">
                        View packages
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

                <!-- 2. BPSS Clearance -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-slate">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">BPSS Clearance</h3>
                    <p class="sv-card__sub">Baseline Personnel Security Standard checks for government and public sector</p>
                    <hr>
                    <ul>
                        <li>Identity and nationality verification</li>
                        <li>Right to Work confirmation</li>
                        <li>3-year employment history check</li>
                        <li>Criminal record (unspent convictions)</li>
                        <li>Full BPSS-compliant reporting</li>
                    </ul>
                    <div class="sv-card__outcome out-slate">Fully BPSS-compliant clearance for public sector roles</div>
                    <a href="{{ route('bpss-clearance') }}" class="sv-card__link">
                        Learn more
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

                <!-- 3. BS7858 Vetting -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">BS7858 Vetting</h3>
                    <p class="sv-card__sub">Security industry vetting to the BS7858 standard</p>
                    <hr>
                    <ul>
                        <li>ID verification &amp; RTW check</li>
                        <li>5-year employment referencing (incl. gap analysis)</li>
                        <li>Basic DBS check</li>
                        <li>Credit check (PEP &amp; sanctions)</li>
                        <li>BS7858-compliant documentation</li>
                    </ul>
                    <div class="sv-card__outcome out-red">Compliant vetting for security and defence roles</div>
                    <a href="{{ route('bs7858-vetting') }}" class="sv-card__link">
                        Learn more
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

                <!-- 4. KYC / KYB -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="14" rx="2.5"/>
                            <path d="M7 10h5M7 14h8"/>
                            <circle cx="16.5" cy="7.5" r="2" fill="currentColor" stroke="none"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">KYC / KYB &amp; Periodic KYC</h3>
                    <p class="sv-card__sub">Compliant, seamless onboarding and ongoing customer due diligence</p>
                    <hr>
                    <ul>
                        <li>Document verification &amp; biometric authentication</li>
                        <li>Address verification</li>
                        <li>PEP and sanctions screening</li>
                        <li>Risk profiling and scoring</li>
                        <li>Enhanced due diligence workflows</li>
                        <li>Periodic re-verification and refresh</li>
                    </ul>
                    <div class="sv-card__outcome out-teal">Faster onboarding with stronger compliance</div>
                    <a href="{{ route('periodic') }}" class="sv-card__link">
                        Learn more
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

                <!-- 5. AML Solution -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">AML Solution</h3>
                    <p class="sv-card__sub">Detect and respond to anti-money laundering risks across your organisation</p>
                    <hr>
                    <ul>
                        <li>AML risk assessment and profiling</li>
                        <li>PEP, sanctions &amp; watchlist screening</li>
                        <li>Adverse media monitoring</li>
                        <li>Case management and audit trail</li>
                        <li>AI-driven fraud pattern detection</li>
                        <li>Regulatory reporting support</li>
                    </ul>
                    <div class="sv-card__outcome out-purple">Reduced AML exposure and stronger regulatory standing</div>
                    <a href="{{ route('aml-solution') }}" class="sv-card__link">
                        Learn more
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

                <!-- 6. Transaction Monitoring -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-rose">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">Transaction Monitoring</h3>
                    <p class="sv-card__sub">Real-time surveillance to detect suspicious financial activity</p>
                    <hr>
                    <ul>
                        <li>Automated real-time transaction monitoring</li>
                        <li>Behavioural analytics and thresholds</li>
                        <li>Suspicious activity identification</li>
                        <li>Alerting and escalation workflows</li>
                        <li>Audit-ready evidence and reporting</li>
                    </ul>
                    <div class="sv-card__outcome out-rose">Proactive detection of financial crime in real time</div>
                    <a href="{{ route('transaction') }}" class="sv-card__link">
                        Learn more
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

                <!-- 7. Identity & Background Intelligence -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-sky">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">Identity &amp; Background Intelligence</h3>
                    <p class="sv-card__sub">Global identity verification and deep background screening</p>
                    <hr>
                    <ul>
                        <li>Global identity verification</li>
                        <li>Criminal records &amp; adverse media screening</li>
                        <li>Right-to-work and regulatory vetting</li>
                        <li>Business (KYB) verification</li>
                        <li>AI biometric checks (facial, fingerprint, liveness)</li>
                    </ul>
                    <div class="sv-card__outcome out-sky">Trusted onboarding and workforce integrity at scale</div>
                </div>

                <!-- 8. GRC & Assurance -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2.5"/>
                            <path d="M3 9h18M9 21V9"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">GRC &amp; Assurance Solutions</h3>
                    <p class="sv-card__sub">Continuous, data-driven governance, risk, and compliance assurance</p>
                    <hr>
                    <ul>
                        <li>Combined assurance framework (1st, 2nd, 3rd line)</li>
                        <li>Continuous control monitoring (CCM)</li>
                        <li>Risk &amp; control mapping engine</li>
                        <li>Enterprise risk intelligence dashboard</li>
                        <li>Regulatory compliance &amp; audit-ready reporting</li>
                    </ul>
                    <div class="sv-card__outcome out-green">Real-time assurance across the entire organisation</div>
                    <a href="{{ route('grc-assurance') }}" class="sv-card__link">
                        Learn more
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

                <!-- 9. Advisory & Implementation -->
                <div class="sv-card">
                    <div class="sv-card__icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        </svg>
                    </div>
                    <h3 class="sv-card__title">Advisory &amp; Implementation</h3>
                    <p class="sv-card__sub">Deep domain expertise combined with practical implementation support</p>
                    <hr>
                    <ul>
                        <li>AML and financial crime advisory</li>
                        <li>Risk and compliance framework design</li>
                        <li>Regulatory readiness and audit support</li>
                        <li>Platform implementation and integration</li>
                        <li>Custom workflow configuration</li>
                    </ul>
                    <div class="sv-card__outcome out-amber">Practical, scalable compliance aligned to your model</div>
                    <a href="{{ route('contact-us') }}" class="sv-card__link">
                        Speak to our team
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                </div>

            </div><!-- /.sv-services-grid -->
        </div>
    </section>


    <!-- ════════════════════ INDUSTRIES ══════════════════════ -->
    <section class="sv-section sv-section--alt">
        <div class="container">
            <div class="sv-heading">
                <span class="sv-pill">Industries we serve</span>
                <h2>Built for every regulated sector</h2>
                <p>Oysterchecks is trusted by organisations operating in high-risk and compliance-heavy environments worldwide.</p>
            </div>
            <div class="sv-ind-grid">
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
                    <p class="sv-ind-label">Banking &amp; Financial Services</p>
                </div>
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
                    <p class="sv-ind-label">Fintech &amp; Payments</p>
                </div>
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/></svg></div>
                    <p class="sv-ind-label">HR, Recruitment &amp; Staffing</p>
                </div>
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-slate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                    <p class="sv-ind-label">Government &amp; Public Sector</p>
                </div>
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
                    <p class="sv-ind-label">Healthcare &amp; Social Care</p>
                </div>
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/></svg></div>
                    <p class="sv-ind-label">Security &amp; Defence</p>
                </div>
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M3 9h18M9 21V9"/></svg></div>
                    <p class="sv-ind-label">Real Estate &amp; Tenancy</p>
                </div>
                <div class="sv-ind-item">
                    <div class="sv-ind-icon ic-sky"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg></div>
                    <p class="sv-ind-label">Aviation &amp; Multinationals</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ════════════════════ CTA ═════════════════════════════ -->
    <section class="sv-section">
        <div class="container">
            <div class="sv-cta-strip">
                <div>
                    <h3>Ready to get started with Oysterchecks?</h3>
                    <p>Speak to our team, request a demo, or create your account and begin screening today.</p>
                </div>
                <div class="sv-cta-btns">
                    <a href="{{ route('login') }}" class="sv-btn-primary">
                        Get Started
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                    <a href="{{ route('book-a-demo') }}" class="sv-btn-outline">Book a Demo</a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection