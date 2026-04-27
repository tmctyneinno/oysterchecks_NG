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
    .grc-hero {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        padding: 100px 0 84px;
        position: relative;
        overflow: hidden;
    }
    .grc-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .grc-hero .container { position: relative; z-index: 1; }
    .grc-hero__eyebrow {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.16em;
        text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 16px;
    }
    .grc-hero__eyebrow::before {
        content: ''; display: block; width: 22px; height: 2px;
        background: rgba(255,255,255,0.4); flex-shrink: 0;
    }
    .grc-hero h1 {
        font-size: clamp(30px, 4vw, 50px); font-weight: 800; color: #fff;
        line-height: 1.12; letter-spacing: -0.025em; margin: 0 0 18px;
    }
    .grc-hero p {
        font-size: 17px; color: rgba(255,255,255,0.72);
        line-height: 1.7; max-width: 580px; margin: 0 0 32px;
    }
    .grc-hero__actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .grc-btn-primary {
        display: inline-flex; align-items: center; gap: 9px;
        background: #fff; color: var(--navy); font-size: 14px; font-weight: 700;
        padding: 13px 26px; border-radius: var(--radius-sm); text-decoration: none;
        letter-spacing: 0.01em; transition: background 0.18s, transform 0.18s;
        white-space: nowrap;
    }
    .grc-btn-primary:hover { background: #e8eefb; color: var(--navy); text-decoration: none; transform: translateY(-1px); }
    .grc-btn-secondary {
        display: inline-flex; align-items: center; gap: 9px;
        background: rgba(255,255,255,0.12); color: #fff; font-size: 14px; font-weight: 600;
        padding: 13px 26px; border-radius: var(--radius-sm); text-decoration: none;
        border: 1px solid rgba(255,255,255,0.25); letter-spacing: 0.01em;
        transition: background 0.18s; white-space: nowrap;
    }
    .grc-btn-secondary:hover { background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; }
    .grc-btn-primary svg, .grc-btn-secondary svg { width: 15px; height: 15px; }
    .grc-breadcrumb {
        display: flex; align-items: center; gap: 8px;
        margin-top: 32px; font-size: 13px; color: rgba(255,255,255,0.5);
    }
    .grc-breadcrumb a { color: rgba(255,255,255,0.7); text-decoration: none; }
    .grc-breadcrumb a:hover { color: #fff; }
    .grc-breadcrumb span { color: rgba(255,255,255,0.35); }

    /* ── INTRO ────────────────────────────────────── */
    .grc-intro { padding: 88px 0 72px; background: var(--white); }
    .grc-pill {
        display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.13em;
        text-transform: uppercase; color: var(--navy); background: rgba(22,46,102,0.08);
        padding: 5px 16px; border-radius: 100px; margin-bottom: 18px;
    }
    .grc-intro h2 {
        font-size: clamp(24px, 3vw, 38px); font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.025em; line-height: 1.18; margin: 0 0 18px;
    }
    .grc-intro__lead {
        font-size: 17px; color: var(--text-muted); line-height: 1.75; margin: 0 0 16px;
    }
    .grc-intro__body { font-size: 15px; color: var(--text-muted); line-height: 1.8; margin: 0; }
    .grc-differentiator {
        background: var(--navy-dark); border-radius: var(--radius-md);
        padding: 28px 32px; margin-top: 36px;
    }
    .grc-differentiator p {
        font-size: 15px; color: rgba(255,255,255,0.75); line-height: 1.7; margin: 0;
    }
    .grc-differentiator strong { color: #fff; }

    /* ── STATS ────────────────────────────────────── */
    .grc-stats { background: var(--off-white); padding: 0; }
    .grc-stats__grid {
        display: grid; grid-template-columns: repeat(4, 1fr);
    }
    @media (max-width: 767px) { .grc-stats__grid { grid-template-columns: repeat(2, 1fr); } }
    .grc-stats__item {
        padding: 40px 28px; border-right: 1px solid var(--border);
        border-bottom: 1px solid var(--border); text-align: center;
    }
    .grc-stats__number {
        font-size: 32px; font-weight: 800; color: var(--navy);
        letter-spacing: -0.03em; line-height: 1; margin: 0 0 6px;
    }
    .grc-stats__label { font-size: 12px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.06em; text-transform: uppercase; margin: 0; }

    /* ── SOLUTIONS GRID ───────────────────────────── */
    .grc-solutions { padding: 88px 0; background: var(--off-white); }
    .grc-section-heading { text-align: center; margin-bottom: 56px; }
    .grc-section-heading h2 {
        font-size: clamp(24px, 3vw, 36px); font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.025em; margin: 0 0 12px;
    }
    .grc-section-heading p { font-size: 15px; color: var(--text-muted); max-width: 500px; margin: 0 auto; line-height: 1.65; }

    .grc-sol-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px;
    }
    @media (max-width: 991px) { .grc-sol-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .grc-sol-grid { grid-template-columns: 1fr; } }

    .grc-sol-card {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius-lg); padding: 30px 26px 26px;
        display: flex; flex-direction: column;
        box-shadow: var(--shadow-sm);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .grc-sol-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: rgba(22,46,102,0.2); }
    .grc-sol-card__num {
        font-size: 11px; font-weight: 700; letter-spacing: 0.12em;
        text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px;
    }
    .grc-sol-card__icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center; margin-bottom: 18px;
    }
    .grc-sol-card__icon svg { width: 22px; height: 22px; }
    .grc-sol-card h3 { font-size: 15px; font-weight: 700; color: var(--text-dark); margin: 0 0 10px; line-height: 1.3; letter-spacing: -0.01em; }
    .grc-sol-card p { font-size: 13px; color: var(--text-muted); line-height: 1.6; margin: 0 0 16px; flex: 1; }
    .grc-sol-card ul { list-style: none; padding: 0; margin: 0 0 auto; display: flex; flex-direction: column; gap: 7px; }
    .grc-sol-card ul li { font-size: 12.5px; color: var(--text-muted); padding-left: 14px; position: relative; line-height: 1.45; }
    .grc-sol-card ul li::before { content: ''; position: absolute; left: 0; top: 6px; width: 4px; height: 4px; border-radius: 50%; background: var(--navy); opacity: 0.25; }
    .grc-sol-outcome {
        margin-top: 18px; padding: 9px 13px; border-radius: var(--radius-sm);
        font-size: 12px; font-weight: 600; line-height: 1.4;
        display: flex; align-items: flex-start; gap: 7px;
    }
    .grc-sol-outcome::before { content: '→'; flex-shrink: 0; font-weight: 700; }

    .ic-blue   { background: #ddeeff; color: #1255aa; }
    .ic-teal   { background: #d5f2e8; color: #0d6e52; }
    .ic-purple { background: #edeafd; color: #5445b8; }
    .ic-amber  { background: #fef0d5; color: #8a5010; }
    .ic-red    { background: #fde8e8; color: #b83232; }
    .ic-green  { background: #e3f4d5; color: #3a6d10; }
    .ic-slate  { background: #e8ecf6; color: #3a4a7a; }

    .out-blue   { background: #ddeeff; color: #1255aa; }
    .out-teal   { background: #d5f2e8; color: #0d6e52; }
    .out-purple { background: #edeafd; color: #5445b8; }
    .out-amber  { background: #fef0d5; color: #8a5010; }
    .out-red    { background: #fde8e8; color: #b83232; }
    .out-green  { background: #e3f4d5; color: #3a6d10; }
    .out-slate  { background: #e8ecf6; color: #3a4a7a; }

    /* ── USE CASE ─────────────────────────────────── */
    .grc-usecase { padding: 88px 0; background: var(--white); }
    .grc-usecase__wrap {
        background: var(--off-white); border: 1px solid var(--border);
        border-radius: var(--radius-lg); overflow: hidden;
        display: grid; grid-template-columns: 1fr 1fr;
        box-shadow: var(--shadow-md);
    }
    @media (max-width: 767px) { .grc-usecase__wrap { grid-template-columns: 1fr; } }
    .grc-usecase__left { padding: 44px 40px; background: var(--navy-dark); }
    .grc-usecase__left .grc-pill { background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.8); }
    .grc-usecase__left h2 { font-size: clamp(20px, 2.5vw, 28px); font-weight: 800; color: #fff; letter-spacing: -0.02em; line-height: 1.25; margin: 0 0 14px; }
    .grc-usecase__left p { font-size: 14px; color: rgba(255,255,255,0.68); line-height: 1.7; margin: 0; }
    .grc-usecase__right { padding: 44px 40px; }
    .grc-usecase__steps { display: flex; flex-direction: column; gap: 20px; margin-bottom: 28px; }
    .grc-usecase__step {
        display: flex; align-items: flex-start; gap: 14px;
    }
    .grc-usecase__step-num {
        width: 30px; height: 30px; border-radius: 50%; background: var(--navy);
        color: #fff; font-size: 12px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .grc-usecase__step-text h5 { font-size: 13.5px; font-weight: 700; color: var(--text-dark); margin: 0 0 3px; }
    .grc-usecase__step-text p { font-size: 13px; color: var(--text-muted); margin: 0; line-height: 1.5; }
    .grc-usecase__result {
        background: var(--navy-dark); border-radius: var(--radius-md);
        padding: 18px 20px;
    }
    .grc-usecase__result h5 { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.55); margin: 0 0 6px; }
    .grc-usecase__result p { font-size: 14px; color: rgba(255,255,255,0.85); margin: 0; line-height: 1.55; }

    /* ── WHO THIS IS FOR ──────────────────────────── */
    .grc-who { padding: 88px 0; background: var(--off-white); }
    .grc-who__grid {
        display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px;
        margin-top: 48px;
    }
    @media (max-width: 991px) { .grc-who__grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 575px) { .grc-who__grid { grid-template-columns: repeat(2, 1fr); } }
    .grc-who__item {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 24px 18px;
        text-align: center; box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .grc-who__item:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .grc-who__icon {
        width: 44px; height: 44px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 12px;
    }
    .grc-who__icon svg { width: 20px; height: 20px; }
    .grc-who__label { font-size: 13px; font-weight: 600; color: var(--text-dark); margin: 0; line-height: 1.3; }

    /* ── WHY OYSTERCHECKS ─────────────────────────── */
    .grc-why { padding: 88px 0; background: var(--white); }
    .grc-why__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 48px; }
    @media (max-width: 767px) { .grc-why__grid { grid-template-columns: 1fr; } }
    .grc-why__item {
        display: flex; align-items: flex-start; gap: 14px;
        background: var(--off-white); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 22px 20px;
    }
    .grc-why__check {
        width: 32px; height: 32px; border-radius: 50%;
        background: rgba(22,46,102,0.08); color: var(--navy);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .grc-why__check svg { width: 14px; height: 14px; }
    .grc-why__text { font-size: 14px; font-weight: 600; color: var(--text-dark); line-height: 1.5; margin: 0; padding-top: 5px; }

    /* ── CTA ──────────────────────────────────────── */
    .grc-cta { padding: 0 0 88px; background: var(--white); }
    .grc-cta__strip {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        border-radius: var(--radius-lg); padding: 52px 52px;
        display: flex; align-items: center; justify-content: space-between; gap: 24px;
        box-shadow: var(--shadow-lg);
    }
    @media (max-width: 767px) { .grc-cta__strip { flex-direction: column; text-align: center; padding: 36px 28px; } }
    .grc-cta__strip h3 { font-size: 22px; font-weight: 800; color: #fff; margin: 0 0 8px; letter-spacing: -0.01em; }
    .grc-cta__strip p  { font-size: 15px; color: rgba(255,255,255,0.68); margin: 0; line-height: 1.5; }
    .grc-cta__btns { display: flex; gap: 12px; flex-wrap: wrap; justify-content: flex-end; flex-shrink: 0; }
    @media (max-width: 767px) { .grc-cta__btns { justify-content: center; } }
</style>

<div class="main">

    <!-- ══════════════════ HERO ══════════════════ -->
    <section class="grc-hero">
        <div class="container">
            <p class="grc-hero__eyebrow">GRC &amp; Assurance</p>
            <h1>Governance, Risk, Compliance<br>&amp; Assurance Solutions</h1>
            <p>Move beyond periodic reporting. Oysterchecks enables continuous, data-driven assurance with real-time visibility into risk, control effectiveness, and compliance across your organisation.</p>
            <div class="grc-hero__actions">
                <a href="{{ route('book-a-demo') }}" class="grc-btn-primary">
                    Request a Demo
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                </a>
                <a href="{{ route('contact-us') }}" class="grc-btn-secondary">Speak to a GRC Specialist</a>
            </div>
            <nav class="grc-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span>›</span>
                <a href="{{ route('services') }}">Services</a>
                <span>›</span>
                <span style="color:rgba(255,255,255,0.85);">GRC &amp; Assurance</span>
            </nav>
        </div>
    </section>


    <!-- ══════════════════ INTRO ══════════════════ -->
    <section class="grc-intro">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <span class="grc-pill">From compliance to continuous assurance</span>
                    <h2>Traditional GRC is no longer sufficient.</h2>
                    <p class="grc-intro__lead">
                        Traditional GRC approaches rely heavily on manual reporting, periodic reviews, and data provided by the business. That model creates blind spots, delays, and regulatory exposure.
                    </p>
                    <p class="grc-intro__body">
                        Oysterchecks enables organisations to move towards continuous, data-driven assurance — providing real-time visibility into risk, control effectiveness, and compliance across the enterprise. Our GRC and Assurance solutions integrate seamlessly into your organisation, transforming how risk and compliance are managed, monitored, and evidenced.
                    </p>
                    <div class="grc-differentiator">
                        <p>Most GRC solutions rely on periodic reporting and manual inputs. Oysterchecks delivers <strong>continuous, independent assurance powered by real-time data</strong> — transforming GRC from a reporting exercise into an intelligent capability.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div style="background: var(--off-white); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 26px 22px;">
                            <div style="font-size: 13px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Real-time</div>
                            <div style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">Continuous control monitoring, not periodic snapshots</div>
                        </div>
                        <div style="background: var(--off-white); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 26px 22px;">
                            <div style="font-size: 13px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Independent</div>
                            <div style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">Data extracted directly — not submitted by the business</div>
                        </div>
                        <div style="background: var(--off-white); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 26px 22px;">
                            <div style="font-size: 13px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Unified</div>
                            <div style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">1st, 2nd, and 3rd line integrated into one assurance view</div>
                        </div>
                        <div style="background: var(--off-white); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 26px 22px;">
                            <div style="font-size: 13px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Audit-ready</div>
                            <div style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">Evidence and documentation generated automatically</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════ STATS ══════════════════ -->
    <div class="grc-stats">
        <div class="container-fluid p-0">
            <div class="grc-stats__grid">
                <div class="grc-stats__item">
                    <p class="grc-stats__number">3-in-1</p>
                    <p class="grc-stats__label">Lines of defence unified</p>
                </div>
                <div class="grc-stats__item">
                    <p class="grc-stats__number">Real-time</p>
                    <p class="grc-stats__label">Control monitoring</p>
                </div>
                <div class="grc-stats__item">
                    <p class="grc-stats__number">180+</p>
                    <p class="grc-stats__label">Jurisdictions supported</p>
                </div>
                <div class="grc-stats__item">
                    <p class="grc-stats__number">100%</p>
                    <p class="grc-stats__label">Audit-ready evidence</p>
                </div>
            </div>
        </div>
    </div>


    <!-- ══════════════════ SOLUTIONS ══════════════ -->
    <section class="grc-solutions">
        <div class="container">
            <div class="grc-section-heading">
                <span class="grc-pill" style="display:inline-block; margin-bottom:14px;">What we offer</span>
                <h2>Seven integrated GRC &amp; Assurance capabilities</h2>
                <p>Designed to integrate seamlessly into your organisation and transform how risk and compliance are managed.</p>
            </div>

            <div class="grc-sol-grid">

                <!-- 1 -->
                <div class="grc-sol-card">
                    <p class="grc-sol-card__num">01</p>
                    <div class="grc-sol-card__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2.5"/>
                            <path d="M3 9h18M9 21V9"/>
                        </svg>
                    </div>
                    <h3>Combined Assurance Framework</h3>
                    <p>Break down silos across the three lines of defence with a centralised assurance view.</p>
                    <ul>
                        <li>Integration of 1st, 2nd, and 3rd line functions</li>
                        <li>Centralised assurance view across business units</li>
                        <li>Elimination of duplicated effort and inconsistent reporting</li>
                        <li>Unified dashboards for leadership and regulators</li>
                    </ul>
                    <div class="grc-sol-outcome out-blue">A single, consistent view of assurance across the organisation</div>
                </div>

                <!-- 2 -->
                <div class="grc-sol-card">
                    <p class="grc-sol-card__num">02</p>
                    <div class="grc-sol-card__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </div>
                    <h3>Independent Data-Driven Assurance</h3>
                    <p>Move beyond reliance on business-submitted reports with direct data extraction.</p>
                    <ul>
                        <li>Direct extraction from operational systems</li>
                        <li>Independent validation of controls and processes</li>
                        <li>Reduction in manual reporting and subjectivity</li>
                        <li>Evidence-based assurance backed by real-time data</li>
                    </ul>
                    <div class="grc-sol-outcome out-teal">Assurance based on what the data shows, not what is reported</div>
                </div>

                <!-- 3 -->
                <div class="grc-sol-card">
                    <p class="grc-sol-card__num">03</p>
                    <div class="grc-sol-card__icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3>Continuous Control Monitoring (CCM)</h3>
                    <p>Monitor control effectiveness in real time with automated testing and early failure detection.</p>
                    <ul>
                        <li>Automated testing of key controls</li>
                        <li>Continuous monitoring across systems and processes</li>
                        <li>Early detection of control failures or weaknesses</li>
                        <li>Configurable rules and thresholds</li>
                    </ul>
                    <div class="grc-sol-outcome out-purple">Immediate visibility into whether controls are working as intended</div>
                </div>

                <!-- 4 -->
                <div class="grc-sol-card">
                    <p class="grc-sol-card__num">04</p>
                    <div class="grc-sol-card__icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="6" r="3"/>
                            <circle cx="18" cy="18" r="3"/>
                            <path d="M9 11L15 7.5M9 13L15 16.5"/>
                        </svg>
                    </div>
                    <h3>Risk &amp; Control Mapping Engine</h3>
                    <p>Connect operational data directly to risks, controls, and regulatory obligations.</p>
                    <ul>
                        <li>Mapping of data to risks, controls, and obligations</li>
                        <li>Alignment with internal frameworks and industry standards</li>
                        <li>Dynamic updates as risk profiles evolve</li>
                    </ul>
                    <div class="grc-sol-outcome out-amber">Clear traceability from data → control → risk → regulation</div>
                </div>

                <!-- 5 -->
                <div class="grc-sol-card">
                    <p class="grc-sol-card__num">05</p>
                    <div class="grc-sol-card__icon ic-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <h3>Enterprise Risk Intelligence Dashboard</h3>
                    <p>Enable better decision-making at all levels with real-time risk exposure and predictive insights.</p>
                    <ul>
                        <li>Real-time risk exposure across customers, employees, and operations</li>
                        <li>Control effectiveness indicators</li>
                        <li>Trend analysis and predictive insights</li>
                        <li>Executive-level reporting views</li>
                    </ul>
                    <div class="grc-sol-outcome out-green">A clear, actionable understanding of your organisation's risk posture</div>
                </div>

                <!-- 6 -->
                <div class="grc-sol-card">
                    <p class="grc-sol-card__num">06</p>
                    <div class="grc-sol-card__icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3>Regulatory Compliance &amp; Reporting</h3>
                    <p>Stay ahead of regulatory expectations with automated, audit-ready compliance reporting.</p>
                    <ul>
                        <li>Automated compliance reporting</li>
                        <li>Audit-ready evidence and documentation</li>
                        <li>Support for multi-jurisdiction regulatory frameworks</li>
                        <li>Transparent and traceable reporting</li>
                    </ul>
                    <div class="grc-sol-outcome out-red">Reduced regulatory risk and improved audit outcomes</div>
                </div>

                <!-- 7 — spans full bottom row on large screens -->
                <div class="grc-sol-card" style="grid-column: 1 / -1; max-width: 560px;">
                    <p class="grc-sol-card__num">07</p>
                    <div class="grc-sol-card__icon ic-slate">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                        </svg>
                    </div>
                    <h3>Integration with Enterprise Systems</h3>
                    <p>Seamlessly connect your ecosystem with API-driven architecture and real-time data ingestion.</p>
                    <ul>
                        <li>Integration with ERP, HR, CRM, and financial systems</li>
                        <li>API-driven architecture with real-time and batch data ingestion</li>
                        <li>Scalable across complex, multi-entity environments</li>
                    </ul>
                    <div class="grc-sol-outcome out-slate">A connected assurance ecosystem without disruption to operations</div>
                </div>

            </div>
        </div>
    </section>


    <!-- ══════════════════ USE CASE ══════════════ -->
    <section class="grc-usecase">
        <div class="container">
            <div class="grc-section-heading" style="margin-bottom: 44px;">
                <span class="grc-pill" style="display:inline-block; margin-bottom:14px;">Real-world impact</span>
                <h2>Use Case: Financial Institution</h2>
                <p>How a regulated financial institution uses Oysterchecks to achieve continuous assurance.</p>
            </div>
            <div class="grc-usecase__wrap">
                <div class="grc-usecase__left">
                    <span class="grc-pill">The challenge</span>
                    <h2>Gaps in compliance visibility were creating audit exposure and delayed issue detection</h2>
                    <p>Periodic reviews and manual reporting meant control failures were often identified weeks after they occurred — too late to prevent regulatory exposure.</p>
                </div>
                <div class="grc-usecase__right">
                    <div class="grc-usecase__steps">
                        <div class="grc-usecase__step">
                            <div class="grc-usecase__step-num">1</div>
                            <div class="grc-usecase__step-text">
                                <h5>Monitor onboarding controls</h5>
                                <p>Real-time monitoring of onboarding controls for high-risk customers, automatically flagging deviations.</p>
                            </div>
                        </div>
                        <div class="grc-usecase__step">
                            <div class="grc-usecase__step-num">2</div>
                            <div class="grc-usecase__step-text">
                                <h5>Track transaction anomalies</h5>
                                <p>AI-driven transaction surveillance detects unusual patterns and generates immediate alerts.</p>
                            </div>
                        </div>
                        <div class="grc-usecase__step">
                            <div class="grc-usecase__step-num">3</div>
                            <div class="grc-usecase__step-text">
                                <h5>Validate EDD processes</h5>
                                <p>Independent validation confirms enhanced due diligence workflows are being followed correctly.</p>
                            </div>
                        </div>
                        <div class="grc-usecase__step">
                            <div class="grc-usecase__step-num">4</div>
                            <div class="grc-usecase__step-text">
                                <h5>Auto-escalate control failures</h5>
                                <p>Control failures are automatically flagged and escalated to the compliance team with a full audit trail.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grc-usecase__result">
                        <h5>Result</h5>
                        <p>Reduced compliance gaps, faster issue detection, and audit-ready assurance across the entire organisation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════ WHO THIS IS FOR ═══════ -->
    <section class="grc-who">
        <div class="container">
            <div class="grc-section-heading">
                <span class="grc-pill" style="display:inline-block; margin-bottom:14px;">Who this is for</span>
                <h2>Built for complex, regulated environments</h2>
                <p>Our GRC &amp; Assurance solutions are designed for organisations where compliance is non-negotiable.</p>
            </div>
            <div class="grc-who__grid">
                <div class="grc-who__item">
                    <div class="grc-who__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                    </div>
                    <p class="grc-who__label">Banking &amp; Financial Services</p>
                </div>
                <div class="grc-who__item">
                    <div class="grc-who__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    </div>
                    <p class="grc-who__label">Fintech &amp; Payments</p>
                </div>
                <div class="grc-who__item">
                    <div class="grc-who__icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <p class="grc-who__label">Government &amp; Public Sector</p>
                </div>
                <div class="grc-who__item">
                    <div class="grc-who__icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <p class="grc-who__label">Healthcare &amp; NHS</p>
                </div>
                <div class="grc-who__item">
                    <div class="grc-who__icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
                    </div>
                    <p class="grc-who__label">Energy, Aviation &amp; Multinationals</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════ WHY OYSTERCHECKS ══════ -->
    <section class="grc-why">
        <div class="container">
            <div class="grc-section-heading">
                <span class="grc-pill" style="display:inline-block; margin-bottom:14px;">Why Oysterchecks</span>
                <h2>The smarter choice for GRC &amp; Assurance</h2>
            </div>
            <div class="grc-why__grid">
                <div class="grc-why__item">
                    <div class="grc-why__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <p class="grc-why__text">Independent, data-led assurance approach — not reliant on business submissions</p>
                </div>
                <div class="grc-why__item">
                    <div class="grc-why__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <p class="grc-why__text">Real-time visibility across the entire organisation, not periodic snapshots</p>
                </div>
                <div class="grc-why__item">
                    <div class="grc-why__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <p class="grc-why__text">Integration of identity, transaction, and workforce risk into one platform</p>
                </div>
                <div class="grc-why__item">
                    <div class="grc-why__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <p class="grc-why__text">Scalable, API-first platform designed for complex enterprise environments</p>
                </div>
                <div class="grc-why__item">
                    <div class="grc-why__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <p class="grc-why__text">Designed for modern regulatory and operational environments worldwide</p>
                </div>
                <div class="grc-why__item">
                    <div class="grc-why__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <p class="grc-why__text">Built within The Morgans Consortium ecosystem of governance and risk expertise</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════ CTA ════════════════════ -->
    <section class="grc-cta">
        <div class="container">
            <div class="grc-cta__strip">
                <div>
                    <h3>Ready to strengthen your GRC &amp; assurance framework?</h3>
                    <p>Request a demo or speak to a GRC specialist about your organisation's specific requirements.</p>
                </div>
                <div class="grc-cta__btns">
                    <a href="{{ route('book-a-demo') }}" class="grc-btn-primary">
                        Request a Demo
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                    <a href="{{ route('contact-us') }}" class="grc-btn-secondary">Speak to a Specialist</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection