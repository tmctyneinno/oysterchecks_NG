@extends('layouts.landing')

@section('content')

<style>
    /* ═══════════════════════════════════════════════════
       GLOBAL VARIABLES & RESETS
    ═══════════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --navy:        #162E66;
        --navy-dark:   #0e1e44;
        --navy-mid:    #1e3d85;
        --white:       #ffffff;
        --off-white:   #f6f8fc;
        --text-dark:   #1a1f36;
        --text-muted:  #5a6282;
        --border:      rgba(22, 46, 102, 0.12);
        --radius-sm:   8px;
        --radius-md:   12px;
        --radius-lg:   16px;
        --shadow-sm:   0 1px 4px rgba(22,46,102,0.08);
        --shadow-md:   0 4px 20px rgba(22,46,102,0.10);
        --shadow-lg:   0 12px 40px rgba(22,46,102,0.14);
    }

    /* ═══════════════════════════════════════════════════
       HERO SECTION
    ═══════════════════════════════════════════════════ */
    .oc-hero {
        background: url('{{asset('/landing_assets/img/bg-shape.png')}}') no-repeat center center / cover;
        padding: 110px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .oc-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(14,30,68,0.72) 0%, rgba(22,46,102,0.55) 100%);
        pointer-events: none;
    }
    .oc-hero .container { position: relative; z-index: 2; }

    .oc-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.7);
        margin-bottom: 18px;
    }
    .oc-hero__eyebrow::before {
        content: '';
        width: 22px;
        height: 2px;
        background: rgba(255,255,255,0.45);
        display: block;
        flex-shrink: 0;
    }
    .oc-hero h1 {
        font-size: clamp(30px, 4vw, 50px);
        font-weight: 800;
        color: #fff;
        line-height: 1.12;
        letter-spacing: -0.025em;
        margin: 0 0 20px;
    }
    .oc-hero p {
        font-size: 17px;
        color: rgba(255,255,255,0.78);
        line-height: 1.7;
        max-width: 500px;
        margin: 0 0 32px;
    }
    .oc-hero__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        color: var(--navy);
        font-size: 14px;
        font-weight: 700;
        padding: 14px 28px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        letter-spacing: 0.01em;
        transition: background 0.18s, transform 0.18s;
    }
    .oc-hero__btn:hover { background: #e8eefb; color: var(--navy); text-decoration: none; transform: translateY(-1px); }
    .oc-hero__img { width: 100%; max-width: 520px; }

    /* ═══════════════════════════════════════════════════
       PROMO STRIP
    ═══════════════════════════════════════════════════ */
    .oc-promo {
        background: var(--navy-dark);
        padding: 0;
    }
    .oc-promo__grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
    }
    @media (max-width: 991px) { .oc-promo__grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 575px) { .oc-promo__grid { grid-template-columns: repeat(2, 1fr); } }

    .oc-promo__item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 28px 16px;
        border-right: 1px solid rgba(255,255,255,0.08);
        border-bottom: 1px solid rgba(255,255,255,0.08);
        text-align: center;
        transition: background 0.2s;
    }
    .oc-promo__item:hover { background: rgba(255,255,255,0.05); }
    .oc-promo__item img { width: 36px; height: 36px; object-fit: contain; opacity: 0.85; }
    .oc-promo__item span {
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,0.75);
        line-height: 1.3;
    }

    /* ═══════════════════════════════════════════════════
       SECTION UTILITY
    ═══════════════════════════════════════════════════ */
    .oc-section { padding: 80px 0; }
    .oc-section--alt { background: var(--off-white); }
    .oc-section--dark { background: var(--navy-dark); }

    .oc-section-heading { text-align: center; margin-bottom: 56px; }
    .oc-section-heading__pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--navy);
        background: rgba(22,46,102,0.08);
        padding: 5px 16px;
        border-radius: 100px;
        margin-bottom: 16px;
    }
    .oc-section-heading h2 {
        font-size: clamp(24px, 3vw, 38px);
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.025em;
        margin: 0 0 12px;
        line-height: 1.2;
    }
    .oc-section-heading p {
        font-size: 16px;
        color: var(--text-muted);
        max-width: 500px;
        margin: 0 auto;
        line-height: 1.65;
    }
    .oc-section-heading--light h2 { color: #fff; }
    .oc-section-heading--light p  { color: rgba(255,255,255,0.65); }
    .oc-section-heading--light .oc-section-heading__pill {
        background: rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.85);
    }

    /* ═══════════════════════════════════════════════════
       PLATFORM DELIVERS — FEATURE CARDS
    ═══════════════════════════════════════════════════ */
    .oc-feature-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (max-width: 767px) { .oc-feature-grid { grid-template-columns: 1fr; } }

    .oc-feature-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 28px 28px 24px;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .oc-feature-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .oc-feature-card__head {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }
    .oc-feature-card__icon {
        width: 44px; height: 44px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .oc-feature-card__icon svg { width: 20px; height: 20px; }
    .oc-feature-card__title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
        line-height: 1.3;
    }
    .oc-feature-card hr {
        border: none;
        border-top: 1px solid var(--border);
        margin: 0 0 16px;
    }
    .oc-feature-card ul {
        list-style: none;
        padding: 0; margin: 0;
        display: flex; flex-direction: column; gap: 8px;
    }
    .oc-feature-card ul li {
        font-size: 13.5px;
        color: var(--text-muted);
        padding-left: 16px;
        position: relative;
        line-height: 1.45;
    }
    .oc-feature-card ul li::before {
        content: '';
        position: absolute; left: 0; top: 7px;
        width: 5px; height: 5px;
        border-radius: 50%;
        background: var(--navy);
        opacity: 0.22;
    }

    /* icon colours */
    .ic-blue   { background: #ddeeff; color: #1255aa; }
    .ic-teal   { background: #d5f2e8; color: #0d6e52; }
    .ic-red    { background: #fde8e8; color: #b83232; }
    .ic-purple { background: #edeafd; color: #5445b8; }

    /* ═══════════════════════════════════════════════════
       SERVICES / PRODUCTS — SIX CARDS
    ═══════════════════════════════════════════════════ */
    .oc-services-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    @media (max-width: 991px) { .oc-services-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .oc-services-grid { grid-template-columns: 1fr; } }

    .oc-svc-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 30px 28px 26px;
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-sm);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .oc-svc-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-color: rgba(22,46,102,0.22);
    }
    .oc-svc-card__icon {
        width: 50px; height: 50px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 20px;
    }
    .oc-svc-card__icon svg { width: 24px; height: 24px; }

    .oc-svc-card__title {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 5px;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }
    .oc-svc-card__sub {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0 0 18px;
        line-height: 1.5;
    }
    .oc-svc-card hr {
        border: none;
        border-top: 1px solid var(--border);
        margin: 0 0 16px;
    }
    .oc-svc-card ul {
        list-style: none;
        padding: 0; margin: 0 0 auto;
        display: flex; flex-direction: column; gap: 8px;
    }
    .oc-svc-card ul li {
        font-size: 13px;
        color: var(--text-muted);
        padding-left: 16px;
        position: relative;
        line-height: 1.45;
    }
    .oc-svc-card ul li::before {
        content: '';
        position: absolute; left: 0; top: 6px;
        width: 5px; height: 5px;
        border-radius: 50%;
        background: var(--navy);
        opacity: 0.22;
    }
    .oc-svc-outcome {
        margin-top: 20px;
        padding: 9px 14px;
        border-radius: var(--radius-sm);
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.4;
        display: flex; align-items: flex-start; gap: 7px;
    }
    .oc-svc-outcome::before { content: '→'; flex-shrink: 0; font-weight: 700; }

    /* outcome colours */
    .oc-out--blue   { background: #ddeeff; color: #1255aa; }
    .oc-out--teal   { background: #d5f2e8; color: #0d6e52; }
    .oc-out--red    { background: #fde8e8; color: #b83232; }
    .oc-out--purple { background: #edeafd; color: #5445b8; }
    .oc-out--green  { background: #e3f4d5; color: #3a6d10; }
    .oc-out--amber  { background: #fef0d5; color: #8a5010; }

    /* icon extra colours */
    .ic-green  { background: #e3f4d5; color: #3a6d10; }
    .ic-amber  { background: #fef0d5; color: #8a5010; }

    /* ═══════════════════════════════════════════════════
       WHY CHOOSE / WHO IT'S FOR — TABS
    ═══════════════════════════════════════════════════ */
    .oc-tabs-wrap { background: var(--white); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); }
    .oc-tabs-nav {
        display: flex;
        border-bottom: 1px solid var(--border);
        background: var(--off-white);
    }
    .oc-tabs-nav a {
        flex: 1;
        text-align: center;
        padding: 18px 16px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        border-bottom: 3px solid transparent;
        transition: color 0.18s, border-color 0.18s, background 0.18s;
    }
    .oc-tabs-nav a:hover { color: var(--navy); background: rgba(22,46,102,0.04); }
    .oc-tabs-nav a.active { color: var(--navy); border-bottom-color: var(--navy); background: var(--white); }
    .oc-tab-pane { display: none; }
    .oc-tab-pane.active { display: block; }
    .oc-tab-inner { padding: 48px 40px; }
    @media (max-width: 767px) { .oc-tab-inner { padding: 32px 24px; } }
    .oc-tab-inner ul { list-style: none; padding: 0; margin: 0 0 28px; display: flex; flex-direction: column; gap: 12px; }
    .oc-tab-inner ul li {
        font-size: 15px;
        color: var(--text-dark);
        padding-left: 22px;
        position: relative;
        line-height: 1.5;
    }
    .oc-tab-inner ul li::before {
        content: '';
        position: absolute; left: 0; top: 8px;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--navy);
        opacity: 0.3;
    }

    /* ═══════════════════════════════════════════════════
       CTA STRIP
    ═══════════════════════════════════════════════════ */
    .oc-cta-strip {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        border-radius: var(--radius-lg);
        padding: 52px 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        box-shadow: var(--shadow-lg);
    }
    @media (max-width: 767px) { .oc-cta-strip { flex-direction: column; text-align: center; padding: 36px 28px; } }
    .oc-cta-strip h3 { font-size: 22px; font-weight: 800; color: #fff; margin: 0 0 8px; letter-spacing: -0.01em; }
    .oc-cta-strip p  { font-size: 15px; color: rgba(255,255,255,0.68); margin: 0; line-height: 1.5; }
    .oc-cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        color: var(--navy);
        font-size: 14px;
        font-weight: 700;
        padding: 14px 28px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        letter-spacing: 0.01em;
        transition: background 0.18s, transform 0.18s;
    }
    .oc-cta-btn:hover { background: #e8eefb; color: var(--navy); text-decoration: none; transform: translateX(2px); }
    .oc-cta-btn svg { width: 16px; height: 16px; }
</style>

<div class="main">

    <!-- ═══════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════ -->
    <section class="oc-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 col-lg-6">
                    <p class="oc-hero__eyebrow">AI-Driven Risk Intelligence</p>
                    <h1>Instant Verification.<br>Intelligent Decisions.</h1>
                    <p>
                        Oysterchecks is not just a verification or compliance tool.
                        It is a unified risk and assurance intelligence platform designed
                        to help organisations prove trust, in real time.
                    </p>
                     <a href="{{ route('login') }}" class="oc-cta-btn">
                        Get Started Now
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8h10M9 4l4 4-4 4"/>
                        </svg>
                    </a>
                </div>
                <div class="col-md-12 col-lg-6 text-center mt-5 mt-lg-0">
                    <img src="{{asset('/landing_assets/fontpage.png')}}" alt="Oysterchecks Platform" class="oc-hero__img img-fluid">
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════
         PROMO STRIP
    ═══════════════════════════════════════════════ -->
    <div class="oc-promo">
        <div class="oc-promo__grid">
            <div class="oc-promo__item">
                <img src="{{asset('/landing_assets/img/bpss.png')}}" alt="">
                <span>Verification &amp; Vetting</span>
            </div>
            <div class="oc-promo__item">
                <img src="{{asset('/landing_assets/img/es.png')}}" alt="">
                <span>Real-time &amp; AI-led</span>
            </div>
            <div class="oc-promo__item">
                <img src="{{asset('/landing_assets/img/bs7858.png')}}" alt="">
                <span>Transaction Monitoring</span>
            </div>
            <div class="oc-promo__item">
                <img src="{{asset('/landing_assets/img/es.png')}}" alt="">
                <span>Integration &amp; Customisation</span>
            </div>
            <div class="oc-promo__item">
                <img src="{{asset('/landing_assets/img/bs7858.png')}}" alt="">
                <span>Employment Checks</span>
            </div>
            <div class="oc-promo__item">
                <img src="{{asset('/landing_assets/img/bpss.png')}}" alt="">
                <span>Education Checks</span>
            </div>
        </div>
    </div>


    <!-- ═══════════════════════════════════════════════
         WHAT THE PLATFORM DELIVERS
    ═══════════════════════════════════════════════ -->
    <section class="oc-section oc-section--alt">
        <div class="container">
            <div class="oc-section-heading">
                <span class="oc-section-heading__pill">Platform overview</span>
                <h2>What the platform delivers</h2>
                <p>Four integrated capability layers working together as a single, unified intelligence platform.</p>
            </div>

            <div class="oc-feature-grid">

                <div class="oc-feature-card">
                    <div class="oc-feature-card__head">
                        <div class="oc-feature-card__icon ic-blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        </div>
                        <h3 class="oc-feature-card__title">Real-time &amp; AI-led Identity</h3>
                    </div>
                    <hr>
                    <ul>
                        <li>Real-time &amp; AI-led processing</li>
                        <li>Instant identity authentication</li>
                        <li>AI-enabled biometric checks (facial, fingerprint, liveness, behavioural)</li>
                    </ul>
                </div>

                <div class="oc-feature-card">
                    <div class="oc-feature-card__head">
                        <div class="oc-feature-card__icon ic-teal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/>
                                <path d="M9 12l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="oc-feature-card__title">Global Background Screening</h3>
                    </div>
                    <hr>
                    <ul>
                        <li>Global background screening</li>
                        <li>Criminal, adverse media, compliance and sanctions checks</li>
                        <li>Employment referencing and education verification</li>
                        <li>BPSS, BS7858, right-to-work, right-to-rent</li>
                        <li>Tenancy and landlord referencing</li>
                        <li>KYC, KYB, AML, PEP &amp; sanctions screening</li>
                    </ul>
                </div>

                <div class="oc-feature-card">
                    <div class="oc-feature-card__head">
                        <div class="oc-feature-card__icon ic-red">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3l18 18M9 9a3 3 0 004.5 4M6.5 6.5A7 7 0 0019 12"/>
                                <circle cx="12" cy="12" r="9"/>
                            </svg>
                        </div>
                        <h3 class="oc-feature-card__title">AML &amp; Fraud Surveillance</h3>
                    </div>
                    <hr>
                    <ul>
                        <li>Real-time AML surveillance</li>
                        <li>Alerts, case management and audit trail</li>
                        <li>AI-driven fraud pattern detection</li>
                        <li>Behavioural analytics and thresholds</li>
                    </ul>
                </div>

                <div class="oc-feature-card">
                    <div class="oc-feature-card__head">
                        <div class="oc-feature-card__icon ic-purple">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2.5"/>
                                <path d="M3 9h18M9 21V9"/>
                            </svg>
                        </div>
                        <h3 class="oc-feature-card__title">Integration &amp; Infrastructure</h3>
                    </div>
                    <hr>
                    <ul>
                        <li>API-first architecture</li>
                        <li>Connects to CRMs (Salesforce, HubSpot, Zoho etc.)</li>
                        <li>Custom workflows for regulated sectors</li>
                        <li>Enterprise-grade encryption &amp; role-based access</li>
                        <li>Cloud, hybrid or on-prem deployments</li>
                        <li>GDPR, ISO27001 and region-specific frameworks</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════
         SERVICES / PRODUCTS
    ═══════════════════════════════════════════════ -->
    <section class="oc-section">
        <div class="container">
            <div class="oc-section-heading">
                <span class="oc-section-heading__pill">Services &amp; Products</span>
                <h2>Our Platform</h2>
                <p>A modular, API-driven platform supporting the full lifecycle of risk, compliance, and assurance.</p>
            </div>

            <div class="oc-services-grid">

                <!-- 1 -->
                <div class="oc-svc-card">
                    <div class="oc-svc-card__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            <path d="M16 3l1.5 1.5L19 3"/>
                        </svg>
                    </div>
                    <h3 class="oc-svc-card__title">Identity &amp; Background Intelligence</h3>
                    <p class="oc-svc-card__sub">Verify individuals and organisations with confidence</p>
                    <hr>
                    <ul>
                        <li>Global identity verification</li>
                        <li>Employment and education checks</li>
                        <li>Criminal records and adverse media screening</li>
                        <li>Right-to-work and regulatory vetting</li>
                        <li>Business (KYB) verification</li>
                    </ul>
                    <div class="oc-svc-outcome oc-out--blue">Trusted onboarding and workforce integrity</div>
                </div>

                <!-- 2 -->
                <div class="oc-svc-card">
                    <div class="oc-svc-card__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="14" rx="2.5"/>
                            <path d="M7 10h5M7 14h8"/>
                            <circle cx="16.5" cy="7.5" r="2" fill="currentColor" stroke="none"/>
                        </svg>
                    </div>
                    <h3 class="oc-svc-card__title">KYC / KYB &amp; Customer Due Diligence</h3>
                    <p class="oc-svc-card__sub">Deliver compliant, seamless onboarding experiences</p>
                    <hr>
                    <ul>
                        <li>Document verification and biometric authentication</li>
                        <li>Address verification</li>
                        <li>PEP and sanctions screening</li>
                        <li>Risk profiling and scoring</li>
                        <li>Enhanced due diligence workflows</li>
                    </ul>
                    <div class="oc-svc-outcome oc-out--teal">Faster onboarding with stronger compliance</div>
                </div>

                <!-- 3 -->
                <div class="oc-svc-card">
                    <div class="oc-svc-card__icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="oc-svc-card__title">AML &amp; Financial Crime Monitoring</h3>
                    <p class="oc-svc-card__sub">Detect, prevent, and respond to financial crime in real time</p>
                    <hr>
                    <ul>
                        <li>Transaction monitoring</li>
                        <li>Sanctions and watchlist screening</li>
                        <li>Fraud detection and behavioural analytics</li>
                        <li>Alerting and case management</li>
                        <li>Suspicious activity identification</li>
                    </ul>
                    <div class="oc-svc-outcome oc-out--red">Reduced exposure to financial crime and regulatory risk</div>
                </div>

                <!-- 4 -->
                <div class="oc-svc-card">
                    <div class="oc-svc-card__icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="6" r="3"/>
                            <circle cx="18" cy="18" r="3"/>
                            <path d="M9 11L15 7.5M9 13L15 16.5"/>
                        </svg>
                    </div>
                    <h3 class="oc-svc-card__title">Integrated Risk Intelligence</h3>
                    <p class="oc-svc-card__sub">Bring together risk signals across your organisation</p>
                    <hr>
                    <ul>
                        <li>Unified risk view across customers, employees, and transactions</li>
                        <li>Cross-system data aggregation</li>
                        <li>Real-time intelligence feeds</li>
                        <li>API-first integration with enterprise systems</li>
                    </ul>
                    <div class="oc-svc-outcome oc-out--purple">A single source of truth for risk intelligence</div>
                </div>

                <!-- 5 -->
                <div class="oc-svc-card">
                    <div class="oc-svc-card__icon ic-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2.5"/>
                            <path d="M7 12h10M7 7.5h6M7 16.5h4"/>
                        </svg>
                    </div>
                    <h3 class="oc-svc-card__title">Assurance &amp; Control Monitoring</h3>
                    <p class="oc-svc-card__sub">Next-generation continuous assurance capability</p>
                    <hr>
                    <ul>
                        <li>Continuous control monitoring (CCM)</li>
                        <li>Independent data sourcing from operational systems</li>
                        <li>Control effectiveness testing</li>
                        <li>Combined assurance dashboards (1st, 2nd, 3rd line)</li>
                        <li>Audit-ready evidence and reporting</li>
                    </ul>
                    <div class="oc-svc-outcome oc-out--green">Demonstrable, real-time assurance across the organisation</div>
                </div>

                <!-- 6 -->
                <div class="oc-svc-card">
                    <div class="oc-svc-card__icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        </svg>
                    </div>
                    <h3 class="oc-svc-card__title">Advisory &amp; Implementation</h3>
                    <p class="oc-svc-card__sub">Technology combined with deep domain expertise</p>
                    <hr>
                    <ul>
                        <li>AML and financial crime advisory</li>
                        <li>Risk and compliance framework design</li>
                        <li>Regulatory readiness and audit support</li>
                        <li>Platform implementation and integration</li>
                    </ul>
                    <div class="oc-svc-outcome oc-out--amber">Practical, scalable compliance aligned to your operating model</div>
                </div>

            </div><!-- /.oc-services-grid -->
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════
         WHY CHOOSE / WHO IT'S BUILT FOR — TABS
    ═══════════════════════════════════════════════ -->
    <section class="oc-section oc-section--alt">
        <div class="container">
            <div class="oc-section-heading">
                <span class="oc-section-heading__pill">Our advantage</span>
                <h2>Built for organisations that can't afford to get it wrong</h2>
            </div>

            <div class="oc-tabs-wrap">
                <nav class="oc-tabs-nav" id="ocTabNav">
                    <a href="#" class="active" data-tab="tab-why">Why organisations choose OysterChecks</a>
                    <a href="#" data-tab="tab-who">Who it's built for</a>
                </nav>

                <div id="tab-why" class="oc-tab-pane active">
                    <div class="oc-tab-inner">
                        <div class="row align-items-center">
                            <div class="col-md-12 col-lg-6">
                                <ul>
                                    <li>Built within THE MORGANS CONSORTIUM ecosystem of governance and risk expertise</li>
                                    <li>End-to-end verification, monitoring and decision intelligence in one place</li>
                                    <li>Customisable for any industry, country or compliance framework</li>
                                    <li>Easy integration with existing workflows and tech</li>
                                    <li>Global data reach with local regulatory alignment</li>
                                    <li>AI that speeds up decisions without losing accuracy</li>
                                    <li>Suitable for high-stakes environments: finance, aviation, government, healthcare, security</li>
                                </ul>
                                <a href="{{ route('login') }}" class="oc-cta-btn" style="margin-top:8px;">
                                    Get Started Now
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 8h10M9 4l4 4-4 4"/>
                                    </svg>
                                </a>
                            </div>
                            <div class="col-md-12 col-lg-6 text-center mt-4 mt-lg-0">
                                <img src="{{asset('/landing_assets/img/laptop.png')}}" alt="Platform" class="img-fluid" style="max-width:460px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-who" class="oc-tab-pane">
                    <div class="oc-tab-inner">
                        <div class="row align-items-center">
                            <div class="col-md-12 col-lg-6 text-center mb-4 mb-lg-0">
                                <img src="{{asset('/landing_assets/img/laptop.png')}}" alt="Platform" class="img-fluid" style="max-width:460px;">
                            </div>
                            <div class="col-md-12 col-lg-6">
                                <ul>
                                    <li>Banks, fintech &amp; payment companies</li>
                                    <li>HR, recruitment &amp; staffing agencies</li>
                                    <li>Real estate, landlords &amp; tenancy services</li>
                                    <li>Government &amp; public sector agencies</li>
                                    <li>Healthcare &amp; social care providers</li>
                                    <li>Security &amp; defence contractors</li>
                                    <li>Professional services firms</li>
                                    <li>Any organisation needing trusted verification at scale</li>
                                </ul>
                                <a href="{{ route('login') }}" class="oc-cta-btn" style="margin-top:8px;">
                                    Get Started Now
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 8h10M9 4l4 4-4 4"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.oc-tabs-wrap -->
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════
         CTA STRIP
    ═══════════════════════════════════════════════ -->
    <section class="oc-section">
        <div class="container">
            <div class="oc-cta-strip">
                <div>
                    <h3>Ready to prove trust in real time?</h3>
                    <p>Get started with Oysterchecks or speak to our team about your specific requirements.</p>
                </div>
                <a href="{{ route('login') }}" class="oc-cta-btn">
                    Get Started Now
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 8h10M9 4l4 4-4 4"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

</div><!-- /.main -->

<script>
    /* Simple tab switcher — no jQuery dependency */
    document.addEventListener('DOMContentLoaded', function () {
        var nav   = document.getElementById('ocTabNav');
        var links = nav ? nav.querySelectorAll('a') : [];

        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-tab');

                /* update nav */
                links.forEach(function (l) { l.classList.remove('active'); });
                this.classList.add('active');

                /* update panes */
                document.querySelectorAll('.oc-tab-pane').forEach(function (p) {
                    p.classList.remove('active');
                });
                var pane = document.getElementById(targetId);
                if (pane) pane.classList.add('active');
            });
        });
    });
</script> 

@endsection