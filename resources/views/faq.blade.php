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

    /* ── HERO ─────────────────────────────────────── */
    .faq-hero {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        padding: 96px 0 80px;
        position: relative; overflow: hidden;
    }
    .faq-hero::before {
        content: ''; position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .faq-hero .container { position: relative; z-index: 1; text-align: center; }
    .faq-hero__eyebrow {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.16em;
        text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 16px;
    }
    .faq-hero__eyebrow::before {
        content: ''; display: block; width: 22px; height: 2px;
        background: rgba(255,255,255,0.4); flex-shrink: 0;
    }
    .faq-hero h1 {
        font-size: clamp(30px, 4vw, 50px); font-weight: 800; color: #fff;
        line-height: 1.12; letter-spacing: -0.025em; margin: 0 0 14px;
    }
    .faq-hero p {
        font-size: 17px; color: rgba(255,255,255,0.72);
        line-height: 1.65; max-width: 520px; margin: 0 auto 28px;
    }
    .faq-breadcrumb {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        font-size: 13px; color: rgba(255,255,255,0.5);
    }
    .faq-breadcrumb a { color: rgba(255,255,255,0.7); text-decoration: none; }
    .faq-breadcrumb a:hover { color: #fff; }
    .faq-breadcrumb span { color: rgba(255,255,255,0.35); }

    /* ── SEARCH BAR ──────────────────────────────── */
    .faq-search { max-width: 480px; margin: 0 auto 28px; position: relative; }
    .faq-search input {
        width: 100%; height: 48px; padding: 0 48px 0 18px;
        font-size: 14px; color: var(--text-dark);
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25);
        border-radius: var(--radius-sm); outline: none; color: #fff;
        font-family: inherit;
        transition: background 0.18s, border-color 0.18s;
    }
    .faq-search input::placeholder { color: rgba(255,255,255,0.55); }
    .faq-search input:focus { background: rgba(255,255,255,0.18); border-color: rgba(255,255,255,0.45); }
    .faq-search__icon {
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
        color: rgba(255,255,255,0.5); pointer-events: none;
    }
    .faq-search__icon svg { width: 17px; height: 17px; }

    /* ── TAB NAV ─────────────────────────────────── */
    .faq-tabs-wrap { background: var(--white); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10; }
    .faq-tabs {
        display: flex; overflow-x: auto; gap: 0;
        scrollbar-width: none;
    }
    .faq-tabs::-webkit-scrollbar { display: none; }
    .faq-tab {
        display: flex; align-items: center; gap: 8px;
        padding: 16px 22px; font-size: 13px; font-weight: 600;
        color: var(--text-muted); white-space: nowrap; cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: color 0.18s, border-color 0.18s;
        text-decoration: none;
    }
    .faq-tab:hover { color: var(--navy); }
    .faq-tab.active { color: var(--navy); border-bottom-color: var(--navy); }
    .faq-tab__icon { width: 16px; height: 16px; }

    /* ── MAIN SECTION ────────────────────────────── */
    .faq-main { padding: 72px 0 100px; background: var(--off-white); }

    /* ── CATEGORY BLOCK ──────────────────────────── */
    .faq-category { margin-bottom: 64px; scroll-margin-top: 80px; }
    .faq-category__head {
        display: flex; align-items: center; gap: 14px; margin-bottom: 28px;
    }
    .faq-category__icon {
        width: 44px; height: 44px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .faq-category__icon svg { width: 20px; height: 20px; }
    .faq-category__title {
        font-size: 20px; font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.015em; margin: 0;
    }
    .faq-category__count {
        font-size: 11px; font-weight: 700; color: var(--text-muted);
        background: rgba(22,46,102,0.07); padding: 3px 10px; border-radius: 100px;
        margin-left: 4px;
    }

    /* icon colour classes */
    .ic-blue   { background: #ddeeff; color: #1255aa; }
    .ic-teal   { background: #d5f2e8; color: #0d6e52; }
    .ic-purple { background: #edeafd; color: #5445b8; }
    .ic-amber  { background: #fef0d5; color: #8a5010; }
    .ic-red    { background: #fde8e8; color: #b83232; }
    .ic-green  { background: #e3f4d5; color: #3a6d10; }
    .ic-slate  { background: #e8ecf6; color: #3a4a7a; }
    .ic-rose   { background: #fce7f3; color: #9d174d; }

    /* ── ACCORDION ───────────────────────────────── */
    .faq-list { display: flex; flex-direction: column; gap: 10px; }
    .faq-item {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius-md); overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s;
    }
    .faq-item:hover { box-shadow: var(--shadow-md); }
    .faq-item.open { border-color: rgba(22,46,102,0.22); }

    .faq-question {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        padding: 18px 22px; cursor: pointer;
        font-size: 14.5px; font-weight: 600; color: var(--text-dark);
        line-height: 1.4; user-select: none;
    }
    .faq-question:hover { background: rgba(22,46,102,0.02); }
    .faq-question__text { flex: 1; }
    .faq-question__chevron {
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--off-white); border: 1px solid var(--border);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        transition: transform 0.22s, background 0.18s;
    }
    .faq-question__chevron svg { width: 13px; height: 13px; color: var(--text-muted); }
    .faq-item.open .faq-question__chevron { transform: rotate(180deg); background: var(--navy); border-color: var(--navy); }
    .faq-item.open .faq-question__chevron svg { color: #fff; }

    .faq-answer {
        display: none; padding: 0 22px 20px;
        font-size: 14px; color: var(--text-muted); line-height: 1.75;
        border-top: 1px solid var(--border);
    }
    .faq-answer p { margin: 14px 0 0; }
    .faq-answer ul { margin: 12px 0 0; padding-left: 18px; display: flex; flex-direction: column; gap: 6px; }
    .faq-answer ul li { font-size: 13.5px; }
    .faq-item.open .faq-answer { display: block; }

    /* ── CTA STRIP ───────────────────────────────── */
    .faq-cta { padding: 0 0 88px; background: var(--off-white); }
    .faq-cta__strip {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        border-radius: var(--radius-lg); padding: 52px 52px;
        display: flex; align-items: center; justify-content: space-between; gap: 24px;
        box-shadow: var(--shadow-lg);
    }
    @media (max-width: 767px) { .faq-cta__strip { flex-direction: column; text-align: center; padding: 36px 28px; } }
    .faq-cta__strip h3 { font-size: 22px; font-weight: 800; color: #fff; margin: 0 0 8px; letter-spacing: -0.01em; }
    .faq-cta__strip p  { font-size: 15px; color: rgba(255,255,255,0.68); margin: 0; line-height: 1.5; }
    .faq-cta-btn {
        display: inline-flex; align-items: center; gap: 9px;
        background: #fff; color: var(--navy); font-size: 14px; font-weight: 700;
        padding: 13px 26px; border-radius: var(--radius-sm); text-decoration: none;
        white-space: nowrap; flex-shrink: 0; letter-spacing: 0.01em;
        transition: background 0.18s, transform 0.18s;
    }
    .faq-cta-btn:hover { background: #e8eefb; color: var(--navy); text-decoration: none; transform: translateX(2px); }
    .faq-cta-btn svg { width: 15px; height: 15px; }
    .faq-cta-btn--outline {
        background: rgba(255,255,255,0.12); color: #fff;
        border: 1px solid rgba(255,255,255,0.28);
    }
    .faq-cta-btn--outline:hover { background: rgba(255,255,255,0.2); color: #fff; }
    .faq-cta__btns { display: flex; gap: 12px; flex-wrap: wrap; flex-shrink: 0; }
    @media (max-width: 767px) { .faq-cta__btns { justify-content: center; } }
</style>

<div class="main">

    <!-- ════════════════ HERO ════════════════ -->
    <section class="faq-hero">
        <div class="container">
            <p class="faq-hero__eyebrow">Support</p>
            <h1>Frequently Asked Questions</h1>
            <p>Everything you need to know about Oysterchecks — our platform, services, and how we work.</p>

            <div class="faq-search">
                <input type="text" id="faqSearch" placeholder="Search questions…" autocomplete="off">
                <span class="faq-search__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </span>
            </div>

            <nav class="faq-breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span>›</span>
                <span style="color:rgba(255,255,255,0.85);">FAQ</span>
            </nav>
        </div>
    </section>


    <!-- ════════════════ TAB NAV ════════════════ -->
    <div class="faq-tabs-wrap">
        <div class="container">
            <div class="faq-tabs">
                <a href="#cat-company" class="faq-tab active" data-cat="cat-company">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                    The Company
                </a>
                <a href="#cat-employment" class="faq-tab" data-cat="cat-employment">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    Employment Checks
                </a>
                <a href="#cat-kyc" class="faq-tab" data-cat="cat-kyc">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2.5"/><path d="M7 10h5M7 14h8"/></svg>
                    KYC / KYB
                </a>
                <a href="#cat-aml" class="faq-tab" data-cat="cat-aml">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/></svg>
                    AML &amp; Monitoring
                </a>
                <a href="#cat-bpss" class="faq-tab" data-cat="cat-bpss">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    BPSS &amp; BS7858
                </a>
                <a href="#cat-grc" class="faq-tab" data-cat="cat-grc">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M3 9h18M9 21V9"/></svg>
                    GRC &amp; Assurance
                </a>
                <a href="#cat-data" class="faq-tab" data-cat="cat-data">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/><path d="M9 12l2 2 4-4"/></svg>
                    Data &amp; Security
                </a>
                <a href="#cat-platform" class="faq-tab" data-cat="cat-platform">
                    <svg class="faq-tab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    Platform &amp; Integration
                </a>
            </div>
        </div>
    </div>


    <!-- ════════════════ FAQ CONTENT ════════════════ -->
    <section class="faq-main">
        <div class="container">

            <!-- 1. THE COMPANY -->
            <div class="faq-category" id="cat-company">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                    </div>
                    <h2 class="faq-category__title">The Company <span class="faq-category__count">4 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Who is Oysterchecks?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Oysterchecks trades as The Morgans Consortium Consulting Limited (TMC) — a private registered company (registration number 09045481). We are a unified risk and assurance intelligence platform specialising in automated background screening, KYC, KYB, AML transaction monitoring, GRC assurance, and employment vetting solutions across the globe.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Where is Oysterchecks located?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>We operate from two offices:</p>
                            <ul>
                                <li><strong>UK:</strong> International House, 24 Holborn Viaduct, London EC1A 2BN, United Kingdom</li>
                                <li><strong>Nigeria:</strong> 2nd Floor, 1 Adeola Adeoye Street, Off Toyin Street, Ikeja, Lagos</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Which industries does Oysterchecks serve?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Oysterchecks serves a wide range of industries with tailored screening, compliance, and assurance solutions. Our fully automated and tested platform supports:</p>
                            <ul>
                                <li>Banking and financial services</li>
                                <li>Fintech and payments</li>
                                <li>HR, recruitment and staffing</li>
                                <li>Government and public sector</li>
                                <li>Healthcare and social care</li>
                                <li>Security and defence</li>
                                <li>Real estate and tenancy services</li>
                                <li>Asset and wealth management</li>
                                <li>Construction, hospitality and retail</li>
                                <li>Aviation and multinational corporations</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How do I get started with Oysterchecks?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Getting started is straightforward. You can register for an account directly on our platform, book a live demo with one of our specialists, or contact our team to discuss your organisation's specific requirements. We will guide you through onboarding, platform configuration, and integration with your existing systems.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 2. EMPLOYMENT CHECKS -->
            <div class="faq-category" id="cat-employment">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </div>
                    <h2 class="faq-category__title">Employment Checks <span class="faq-category__count">5 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What employment checks does Oysterchecks offer?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Oysterchecks offers comprehensive employment screening packages to match every role and risk level, including:</p>
                            <ul>
                                <li>DBS checks (basic, standard, enhanced, and volunteer)</li>
                                <li>Credit check including PEP and sanctions screening</li>
                                <li>Right to Work (RTW) verification</li>
                                <li>ID verification</li>
                                <li>Employment referencing and gap analysis (up to 5 years)</li>
                                <li>Adverse media, social media and undercover journalistic checks</li>
                                <li>Qualification and directorship verification</li>
                                <li>UK driving licence check</li>
                                <li>Character references</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What screening packages are available?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>We offer four tiered employment check packages:</p>
                            <ul>
                                <li><strong>GDPR Screening (£100k/month):</strong> DBS check and credit check including PEP and sanctions</li>
                                <li><strong>Entry Screening (£150k/month):</strong> DBS, credit check, ID verification and RTW check</li>
                                <li><strong>Mid-Tier (£250k/month):</strong> All of the above plus 3-year employment referencing and adverse media checks</li>
                                <li><strong>Director & Senior Management (£500k/month):</strong> Full suite including qualification and directorship verification</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How long does an employment check take?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Most checks, including ID verification, DBS and credit checks, are completed in real time. Employment referencing and gap analysis typically takes 24–72 hours depending on the responsiveness of referees. Address verification by our agent network has a turnaround of 24–72 hours depending on location.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Why should organisations perform background checks?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Background checks reduce the risk of negligent hiring, improve workplace safety, and protect your organisation's reputation. Hiring verified, ethical employees leads to increased productivity, a safer working environment, and allows HR and leadership to focus on strategic priorities rather than managing preventable risk.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Can Oysterchecks perform international employment checks?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. Oysterchecks has access to global data sources covering 180+ countries, enabling us to perform international background checks for overseas hires. Our platform returns fully verified, validated global reports quickly — making international screening as seamless as domestic checks.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 3. KYC / KYB -->
            <div class="faq-category" id="cat-kyc">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2.5"/><path d="M7 10h5M7 14h8"/><circle cx="16.5" cy="7.5" r="2" fill="currentColor" stroke="none"/></svg>
                    </div>
                    <h2 class="faq-category__title">KYC / KYB &amp; Customer Due Diligence <span class="faq-category__count">6 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What KYC and KYB solutions does Oysterchecks provide?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p><strong>KYC (Know Your Customer):</strong> Our KYC solution performs full identity verification globally including ID verification, address verification, biometric verification, phone number verification, facial matching, liveness detection, and AML screening including PEP and sanctions checks.</p>
                            <p><strong>KYB (Know Your Business):</strong> Our KYB solution verifies the legal existence and registry of a business, including its address, incorporation documents, the identity of directors, and ultimate beneficial owners (UBOs).</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How does the KYC verification process work?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our KYC process includes document scanning, selfie-based liveness detection, facial comparison, biometric verification, and automated sanctions and watchlist screening. The process is fully configurable — you can tailor it to include or exclude specific checks depending on your risk appetite and regulatory obligations.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Which documents do you accept for KYC?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>We accept government and state-backed identity documents from countries globally. Our AI automatically scans and extracts information from documents including:</p>
                            <ul>
                                <li>International passports</li>
                                <li>National ID cards</li>
                                <li>Driving licences</li>
                                <li>Residence permits and other jurisdiction-specific documents</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What is Periodic KYC and why is it important?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Periodic KYC is the process of re-verifying existing customers at defined intervals to ensure their information remains current and their risk profile has not changed. Regulators increasingly require organisations to refresh KYC data rather than relying on a one-time onboarding check. Oysterchecks automates periodic re-verification, reducing manual effort and ensuring ongoing compliance.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How are KYC results reported?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>The identity verification report returns a structured dataset of personally identifiable information and verification outcomes via API response. Results are also viewable in the Oysterchecks dashboard with full audit trail, allowing your compliance team to review, action, and archive results.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Does Oysterchecks support Enhanced Due Diligence (EDD)?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. For higher-risk customers — such as Politically Exposed Persons (PEPs) or those in high-risk jurisdictions — Oysterchecks supports Enhanced Due Diligence workflows including deeper adverse media screening, source of funds verification, and more intensive ongoing monitoring. EDD workflows can be triggered automatically based on configurable risk scoring thresholds.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 4. AML & TRANSACTION MONITORING -->
            <div class="faq-category" id="cat-aml">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <h2 class="faq-category__title">AML &amp; Transaction Monitoring <span class="faq-category__count">5 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What AML solutions does Oysterchecks offer?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our AML solution covers the full spectrum of financial crime prevention:</p>
                            <ul>
                                <li>AML risk assessment and customer risk profiling</li>
                                <li>PEP (Politically Exposed Person) screening</li>
                                <li>Sanctions and global watchlist screening</li>
                                <li>Adverse media monitoring</li>
                                <li>Real-time transaction monitoring</li>
                                <li>AI-driven fraud pattern and behavioural analytics</li>
                                <li>Suspicious activity identification and escalation</li>
                                <li>Case management and full audit trail</li>
                                <li>Regulatory reporting support</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How does transaction monitoring work?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Oysterchecks monitors transactions in real time using a combination of rule-based thresholds and AI-driven behavioural analytics. When a transaction or pattern of activity breaches a configured threshold or matches a known fraud indicator, the system automatically generates an alert, initiates a case, and notifies the relevant compliance officer — with a full audit trail maintained throughout.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What sanctions and watchlists does Oysterchecks screen against?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our screening covers major global sanctions lists and watchlists including OFAC, UN, EU, HM Treasury, and other jurisdiction-specific regulatory lists. Screening is performed in real time during onboarding and can be configured to run continuously throughout the customer lifecycle.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Can rules and thresholds be customised?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. All transaction monitoring rules, alert thresholds, and risk scoring parameters are fully configurable. You can tailor them to your organisation's specific risk appetite, customer base, and regulatory obligations. Our team will help you design and implement a rule set appropriate for your sector.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Does Oysterchecks support Suspicious Activity Report (SAR) filing?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. Our case management module captures all relevant evidence, generates structured reports, and maintains the documentation required to support SAR filing with the relevant regulator (e.g. the National Crime Agency in the UK). The platform ensures your compliance team has audit-ready evidence at all times.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 5. BPSS & BS7858 -->
            <div class="faq-category" id="cat-bpss">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-slate">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </div>
                    <h2 class="faq-category__title">BPSS Clearance &amp; BS7858 Vetting <span class="faq-category__count">4 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What is BPSS clearance and who needs it?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>BPSS (Baseline Personnel Security Standard) is the minimum level of security clearance required for individuals working in the UK government and public sector, or those handling government assets. It is also commonly required for contractors supplying services to government departments. BPSS clearance covers identity verification, right to work, 3-year employment history, and criminal record (unspent convictions).</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What does BS7858 vetting involve?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>BS7858 is the British Standard for security screening of individuals employed in an environment where the security and safety of people or property is a primary concern — such as security guards, door supervisors, and those in the private security industry. Our BS7858 package includes ID verification, right to work, 5-year employment referencing (including gap analysis), basic DBS check, and credit check including PEP and sanctions screening.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How long does BPSS or BS7858 clearance take?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Identity, RTW, and credit components are returned in real time. Employment referencing — particularly the 5-year history required for BS7858 — typically takes 3–10 business days depending on the number of employers and their responsiveness. Oysterchecks actively chases references and keeps you updated throughout the process.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Are BPSS and BS7858 results audit-ready?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. All vetting reports generated by Oysterchecks include full documentation, evidence records, and timestamps that satisfy both BPSS and BS7858 audit requirements. Reports are stored securely and accessible from your dashboard at any time.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 6. GRC & ASSURANCE -->
            <div class="faq-category" id="cat-grc">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M3 9h18M9 21V9"/></svg>
                    </div>
                    <h2 class="faq-category__title">GRC &amp; Assurance Solutions <span class="faq-category__count">4 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What GRC and assurance capabilities does Oysterchecks provide?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our GRC and Assurance solution enables organisations to move beyond periodic compliance reporting to continuous, data-driven assurance. Capabilities include:</p>
                            <ul>
                                <li>Combined assurance framework integrating 1st, 2nd, and 3rd lines of defence</li>
                                <li>Independent, data-driven assurance extracted directly from operational systems</li>
                                <li>Continuous control monitoring (CCM) with automated testing</li>
                                <li>Risk and control mapping engine</li>
                                <li>Enterprise risk intelligence dashboard with real-time exposure views</li>
                                <li>Regulatory compliance reporting and audit-ready evidence</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How is Oysterchecks's GRC approach different from traditional solutions?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Most GRC solutions rely on periodic reviews and data submitted by the business itself — creating blind spots and delays. Oysterchecks extracts data independently and directly from operational systems, providing continuous, real-time assurance based on what the data actually shows — not what is reported. This eliminates subjectivity and ensures control failures are detected immediately rather than weeks later.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Which sectors is the GRC solution designed for?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our GRC and Assurance solution is designed for organisations operating in complex, regulated environments including banking and financial services, fintech and payments, government and public sector, healthcare and NHS organisations, energy, aviation, and large multinational corporations.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Can Oysterchecks support multi-jurisdiction compliance?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. Our platform supports multi-jurisdiction regulatory frameworks and can be configured to align with the specific compliance obligations applicable in each country or region where your organisation operates. This includes GDPR (EU/UK), FCA regulations, CBN requirements, and other region-specific frameworks.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 7. DATA & SECURITY -->
            <div class="faq-category" id="cat-data">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <h2 class="faq-category__title">Data Privacy &amp; Security <span class="faq-category__count">4 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How does Oysterchecks secure data?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>We use enterprise-grade encryption at rest and in transit. Our primary data centres are located within the European Union where data privacy is governed by GDPR. Where we process and store highly sensitive government data, we only do so on the premises of the government agency or in an approved local data centre. Our platform is designed in accordance with ISO 27001 and role-based access controls ensure data is only accessible to authorised personnel.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How long does Oysterchecks store user data?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>We perform verification services on behalf of our clients and rely on clients to define their data retention requirements. Once instructed — either through our client agreement or via a specific request — we delete the information we have collected when performing the requested services. Data retention is always conducted in compliance with applicable GDPR and regional data protection regulations.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Is Oysterchecks GDPR compliant?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. Oysterchecks is fully GDPR compliant. Our platform is built with privacy-by-design principles, and our data centres in the EU ensure data is processed and stored in line with GDPR requirements. We also support region-specific compliance frameworks including ISO 27001, the UK Data Protection Act, and local regulations in the jurisdictions we operate in.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Can Oysterchecks be deployed on-premise?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. We offer flexible deployment options including cloud, hybrid, and on-premise deployments to meet the specific data residency, security, and operational requirements of your organisation. Our team will advise on the most appropriate deployment model based on your regulatory environment and infrastructure.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 8. PLATFORM & INTEGRATION -->
            <div class="faq-category" id="cat-platform">
                <div class="faq-category__head">
                    <div class="faq-category__icon ic-rose">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </div>
                    <h2 class="faq-category__title">Platform &amp; Integration <span class="faq-category__count">4 questions</span></h2>
                </div>
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">How does Oysterchecks integrate with our existing systems?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Oysterchecks is built API-first, making integration straightforward. We provide a fully documented REST API that connects to CRMs (Salesforce, HubSpot, Zoho), HR systems, onboarding tools, ERP platforms, and financial systems. Our team provides implementation support to ensure seamless integration with your existing tech stack.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Is the platform accessible on mobile devices?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. The Oysterchecks platform is fully accessible from desktop, tablet, and smartphone via a responsive web interface. We also provide a mobile app for iOS and Android that allows you to initiate screening processes and receive real-time results with a single tap — making the platform fully accessible for teams on the move.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">Can the platform be customised for our specific workflows?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. Oysterchecks is fully customisable. You can configure workflows, dashboards, alert thresholds, rules, and reporting to match your organisation's specific operational requirements and compliance obligations. Custom workflows are available for regulated sectors including banking, fintech, healthcare, security, and government.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span class="faq-question__text">What support is available after implementation?</span>
                            <span class="faq-question__chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>
                        </div>
                        <div class="faq-answer">
                            <p>We provide ongoing technical support, platform updates, and advisory services post-implementation. Our team remains available to support regulatory changes, workflow adjustments, and system enhancements as your organisation grows. You can reach us via our support portal, email at enquiries@oysterchecks.com, or by phone on +234 170 017 70.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>


    <!-- ════════════════ CTA ════════════════ -->
    <section class="faq-cta">
        <div class="container">
            <div class="faq-cta__strip">
                <div>
                    <h3>Still have questions?</h3>
                    <p>Our team is happy to help. Speak to a specialist or book a live demo to see the platform in action.</p>
                </div>
                <div class="faq-cta__btns">
                    <a href="{{ route('contact-us') }}" class="faq-cta-btn">
                        Contact Us
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                    <a href="{{ route('book-a-demo') }}" class="faq-cta-btn faq-cta-btn--outline">Book a Demo</a>
                </div>
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Accordion ──────────────────────────────── */
    document.querySelectorAll('.faq-question').forEach(function (q) {
        q.addEventListener('click', function () {
            var item = this.closest('.faq-item');
            var isOpen = item.classList.contains('open');
            // close all
            document.querySelectorAll('.faq-item.open').forEach(function (i) { i.classList.remove('open'); });
            // toggle clicked
            if (!isOpen) item.classList.add('open');
        });
    });

    /* ── Tab nav ────────────────────────────────── */
    document.querySelectorAll('.faq-tab').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.faq-tab').forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            var target = document.querySelector(this.getAttribute('href'));
            if (target) { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    });

    /* ── Live search ────────────────────────────── */
    var searchInput = document.getElementById('faqSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('.faq-item').forEach(function (item) {
                var text = item.textContent.toLowerCase();
                item.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
            // show/hide category blocks that have all items hidden
            document.querySelectorAll('.faq-category').forEach(function (cat) {
                var visible = Array.from(cat.querySelectorAll('.faq-item')).some(function (i) { return i.style.display !== 'none'; });
                cat.style.display = visible ? '' : 'none';
            });
        });
    }

});
</script>

@endsection