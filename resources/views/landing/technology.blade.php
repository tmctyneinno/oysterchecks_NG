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

    /* ── HERO ───────────────────────────────────── */
    .tech-hero {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        padding: 96px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .tech-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .tech-hero .container { position: relative; z-index: 1; }
    .tech-hero__eyebrow {
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
    .tech-hero__eyebrow::before {
        content: '';
        display: block;
        width: 22px; height: 2px;
        background: rgba(255,255,255,0.4);
        flex-shrink: 0;
    }
    .tech-hero h1 {
        font-size: clamp(32px, 4vw, 52px);
        font-weight: 800;
        color: #fff;
        line-height: 1.12;
        letter-spacing: -0.025em;
        margin: 0 0 16px;
    }
    .tech-hero p {
        font-size: 17px;
        color: rgba(255,255,255,0.72);
        line-height: 1.65;
        max-width: 560px;
        margin: 0;
    }
    .tech-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 28px;
        font-size: 13px;
        color: rgba(255,255,255,0.5);
    }
    .tech-breadcrumb a { color: rgba(255,255,255,0.7); text-decoration: none; }
    .tech-breadcrumb a:hover { color: #fff; }
    .tech-breadcrumb span { color: rgba(255,255,255,0.35); }

    /* ── INTRO SECTION ──────────────────────────── */
    .tech-intro {
        padding: 88px 0 72px;
        background: var(--white);
    }
    .tech-intro__pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.13em;
        text-transform: uppercase;
        color: var(--navy);
        background: rgba(22,46,102,0.08);
        padding: 5px 16px;
        border-radius: 100px;
        margin-bottom: 18px;
    }
    .tech-intro h2 {
        font-size: clamp(26px, 3vw, 40px);
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.025em;
        line-height: 1.18;
        margin: 0 0 20px;
    }
    .tech-intro__lead {
        font-size: 17px;
        color: var(--text-muted);
        line-height: 1.75;
        margin: 0 0 20px;
        max-width: 640px;
    }
    .tech-intro__body {
        font-size: 15px;
        color: var(--text-muted);
        line-height: 1.8;
        max-width: 640px;
        margin: 0;
    }

    /* ── STATS ROW ──────────────────────────────── */
    .tech-stats {
        background: var(--navy-dark);
        padding: 0;
    }
    .tech-stats__grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
    }
    @media (max-width: 767px) { .tech-stats__grid { grid-template-columns: repeat(2, 1fr); } }

    .tech-stats__item {
        padding: 40px 32px;
        border-right: 1px solid rgba(255,255,255,0.08);
        border-bottom: 1px solid rgba(255,255,255,0.08);
        text-align: center;
    }
    .tech-stats__item:last-child { border-right: none; }
    .tech-stats__number {
        font-size: 36px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.03em;
        line-height: 1;
        margin: 0 0 6px;
    }
    .tech-stats__label {
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,0.55);
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin: 0;
    }

    /* ── CAPABILITY CARDS ───────────────────────── */
    .tech-caps {
        padding: 88px 0;
        background: var(--off-white);
    }
    .tech-caps__heading {
        text-align: center;
        margin-bottom: 56px;
    }
    .tech-caps__heading h2 {
        font-size: clamp(24px, 3vw, 36px);
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.025em;
        margin: 0 0 12px;
    }
    .tech-caps__heading p {
        font-size: 15px;
        color: var(--text-muted);
        max-width: 480px;
        margin: 0 auto;
        line-height: 1.65;
    }

    .tech-caps__grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }
    @media (max-width: 991px) { .tech-caps__grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .tech-caps__grid { grid-template-columns: 1fr; } }

    .tech-cap-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 30px 26px 26px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .tech-cap-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: rgba(22,46,102,0.2);
    }
    .tech-cap-card__icon {
        width: 50px; height: 50px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 20px;
    }
    .tech-cap-card__icon svg { width: 24px; height: 24px; }
    .tech-cap-card h3 {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 10px;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }
    .tech-cap-card p {
        font-size: 13.5px;
        color: var(--text-muted);
        line-height: 1.6;
        margin: 0;
    }

    .ic-blue   { background: #ddeeff; color: #1255aa; }
    .ic-teal   { background: #d5f2e8; color: #0d6e52; }
    .ic-purple { background: #edeafd; color: #5445b8; }
    .ic-amber  { background: #fef0d5; color: #8a5010; }
    .ic-red    { background: #fde8e8; color: #b83232; }
    .ic-green  { background: #e3f4d5; color: #3a6d10; }

    /* ── ACCESS / DEVICES SECTION ───────────────── */
    .tech-access {
        padding: 88px 0;
        background: var(--white);
    }
    .tech-access__inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: center;
    }
    @media (max-width: 767px) {
        .tech-access__inner { grid-template-columns: 1fr; gap: 40px; }
    }
    .tech-access__pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.13em;
        text-transform: uppercase;
        color: var(--navy);
        background: rgba(22,46,102,0.08);
        padding: 5px 16px;
        border-radius: 100px;
        margin-bottom: 18px;
    }
    .tech-access h2 {
        font-size: clamp(22px, 2.8vw, 34px);
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0 0 16px;
    }
    .tech-access p {
        font-size: 15px;
        color: var(--text-muted);
        line-height: 1.75;
        margin: 0 0 28px;
    }

    .tech-device-list {
        list-style: none;
        padding: 0; margin: 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .tech-device-list li {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
    }
    .tech-device-list__icon {
        width: 36px; height: 36px;
        border-radius: var(--radius-sm);
        background: rgba(22,46,102,0.07);
        color: var(--navy);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .tech-device-list__icon svg { width: 18px; height: 18px; }

    /* visual device mockup panel */
    .tech-access__visual {
        background: var(--off-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 36px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        box-shadow: var(--shadow-md);
    }
    .tech-access__visual-row {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
    }
    .tech-access__visual-icon {
        width: 40px; height: 40px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .tech-access__visual-icon svg { width: 20px; height: 20px; }
    .tech-access__visual-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 3px;
    }
    .tech-access__visual-sub {
        font-size: 12px;
        color: var(--text-muted);
        margin: 0;
    }
    .tech-access__visual-badge {
        margin-left: auto;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 100px;
        background: #d5f2e8;
        color: #0d6e52;
        white-space: nowrap;
    }

    /* ── INTEGRATION SECTION ────────────────────── */
    .tech-integration {
        padding: 88px 0 100px;
        background: var(--off-white);
    }
    .tech-integration__inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: center;
    }
    @media (max-width: 767px) {
        .tech-integration__inner { grid-template-columns: 1fr; gap: 40px; }
        .tech-integration__visual { order: -1; }
    }
    .tech-integration h2 {
        font-size: clamp(22px, 2.8vw, 34px);
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0 0 16px;
    }
    .tech-integration p {
        font-size: 15px;
        color: var(--text-muted);
        line-height: 1.75;
        margin: 0 0 24px;
    }
    .tech-integration__list {
        list-style: none;
        padding: 0; margin: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .tech-integration__list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        color: var(--text-dark);
        line-height: 1.5;
    }
    .tech-integration__list li::before {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--navy);
        opacity: 0.3;
        flex-shrink: 0;
        margin-top: 7px;
    }

    /* Integration visual grid */
    .tech-int-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    .tech-int-item {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 18px 14px;
        text-align: center;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .tech-int-item:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .tech-int-item__icon {
        width: 40px; height: 40px;
        border-radius: var(--radius-sm);
        margin: 0 auto 10px;
        display: flex; align-items: center; justify-content: center;
    }
    .tech-int-item__icon svg { width: 20px; height: 20px; }
    .tech-int-item__name {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }
    .tech-int-item__type {
        font-size: 10px;
        color: var(--text-muted);
        margin: 2px 0 0;
    }

    /* ── CTA ────────────────────────────────────── */
    .tech-cta {
        padding: 0 0 88px;
        background: var(--off-white);
    }
    .tech-cta__strip {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        border-radius: var(--radius-lg);
        padding: 52px 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        box-shadow: var(--shadow-lg);
    }
    @media (max-width: 767px) { .tech-cta__strip { flex-direction: column; text-align: center; padding: 36px 28px; } }
    .tech-cta__strip h3 { font-size: 22px; font-weight: 800; color: #fff; margin: 0 0 8px; letter-spacing: -0.01em; }
    .tech-cta__strip p  { font-size: 15px; color: rgba(255,255,255,0.68); margin: 0; line-height: 1.5; }
    .tech-cta-btn {
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
    .tech-cta-btn:hover { background: #e8eefb; color: var(--navy); text-decoration: none; transform: translateX(2px); }
    .tech-cta-btn svg { width: 16px; height: 16px; }
</style>

<div class="main">

    <!-- ══════════════════ HERO ══════════════════ -->
    <section class="tech-hero">
        <div class="container">
            <p class="tech-hero__eyebrow">Platform</p>
            <h1>Our Technology</h1>
            <p>A single, intelligent platform for background checks, KYC, AML compliance, and transaction monitoring — powered by AI and built for scale.</p>
            <nav class="tech-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span>›</span>
                <span style="color:rgba(255,255,255,0.85);">Technology</span>
            </nav>
        </div>
    </section>


    <!-- ══════════════════ INTRO ══════════════════ -->
    <section class="tech-intro">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <span class="tech-intro__pill">What we've built</span>
                    <h2>One platform.<br>Every check you need.</h2>
                    <p class="tech-intro__lead">
                        Imagine obtaining a background check, KYC and AML report, and automated transaction monitoring — all from a single platform. With Oysterchecks, that is exactly what you get.
                    </p>
                    <p class="tech-intro__body">
                        Our platform uses advanced data analytics to perform a wide variety of checks quickly, delivering fully verified and validated reports in real time. Every result is trusted information your organisation can rely on for confident, compliant decision-making.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div style="background:var(--off-white); border:1px solid var(--border); border-radius:var(--radius-md); padding:24px 20px;">
                            <div style="font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-0.03em; margin-bottom:4px;">Real-time</div>
                            <div style="font-size:13px; color:var(--text-muted);">Results delivered instantly</div>
                        </div>
                        <div style="background:var(--off-white); border:1px solid var(--border); border-radius:var(--radius-md); padding:24px 20px;">
                            <div style="font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-0.03em; margin-bottom:4px;">AI-led</div>
                            <div style="font-size:13px; color:var(--text-muted);">Intelligent data analytics</div>
                        </div>
                        <div style="background:var(--off-white); border:1px solid var(--border); border-radius:var(--radius-md); padding:24px 20px;">
                            <div style="font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-0.03em; margin-bottom:4px;">API-first</div>
                            <div style="font-size:13px; color:var(--text-muted);">Seamless system integration</div>
                        </div>
                        <div style="background:var(--off-white); border:1px solid var(--border); border-radius:var(--radius-md); padding:24px 20px;">
                            <div style="font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-0.03em; margin-bottom:4px;">Always on</div>
                            <div style="font-size:13px; color:var(--text-muted);">24 / 7 platform availability</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════ STATS ══════════════════ -->
    <div class="tech-stats">
        <div class="container-fluid p-0">
            <div class="tech-stats__grid">
                <div class="tech-stats__item">
                    <p class="tech-stats__number">100+</p>
                    <p class="tech-stats__label">Data sources</p>
                </div>
                <div class="tech-stats__item">
                    <p class="tech-stats__number">&lt;2s</p>
                    <p class="tech-stats__label">Avg. response time</p>
                </div>
                <div class="tech-stats__item">
                    <p class="tech-stats__number">99.9%</p>
                    <p class="tech-stats__label">Platform uptime</p>
                </div>
                <div class="tech-stats__item">
                    <p class="tech-stats__number">180+</p>
                    <p class="tech-stats__label">Countries covered</p>
                </div>
            </div>
        </div>
    </div>


    <!-- ══════════════════ CAPABILITIES ══════════ -->
    <section class="tech-caps">
        <div class="container">
            <div class="tech-caps__heading">
                <span class="tech-intro__pill" style="margin-bottom:14px; display:inline-block;">Core capabilities</span>
                <h2>Built for accuracy. Designed for speed.</h2>
                <p>Six technology layers that work together to deliver comprehensive, trustworthy results every time.</p>
            </div>
            <div class="tech-caps__grid">

                <div class="tech-cap-card">
                    <div class="tech-cap-card__icon ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            <path d="M16 3l1.5 1.5L19 3"/>
                        </svg>
                    </div>
                    <h3>AI-Powered Identity Verification</h3>
                    <p>Biometric facial recognition, liveness detection, fingerprint matching, and behavioural analysis — all processed in real time with AI precision.</p>
                </div>

                <div class="tech-cap-card">
                    <div class="tech-cap-card__icon ic-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3L4 7v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V7L12 3z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3>Global Background Screening Engine</h3>
                    <p>Access criminal records, adverse media, sanctions lists, employment history, and regulatory databases across 180+ countries from a single query.</p>
                </div>

                <div class="tech-cap-card">
                    <div class="tech-cap-card__icon ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <h3>Automated AML Transaction Monitoring</h3>
                    <p>Rule-based and AI-driven transaction surveillance that detects anomalous patterns, generates alerts, and maintains a complete audit trail automatically.</p>
                </div>

                <div class="tech-cap-card">
                    <div class="tech-cap-card__icon ic-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2.5"/>
                            <path d="M9 3v18M3 9h6M3 15h6"/>
                        </svg>
                    </div>
                    <h3>Advanced Data Analytics</h3>
                    <p>Complex multi-source data aggregation and cross-referencing technology that surfaces risk signals humans would miss — fast and at scale.</p>
                </div>

                <div class="tech-cap-card">
                    <div class="tech-cap-card__icon ic-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <path d="M8 21h8M12 17v4"/>
                        </svg>
                    </div>
                    <h3>User-Friendly Interface</h3>
                    <p>Designed for ease of use without sacrificing depth. Configurable dashboards, intuitive workflows, and role-based access controls for every team size.</p>
                </div>

                <div class="tech-cap-card">
                    <div class="tech-cap-card__icon ic-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        </svg>
                    </div>
                    <h3>Always Up-to-Date</h3>
                    <p>Our engineering team continuously monitors regulatory changes and emerging data standards, ensuring the platform reflects current requirements across all industries.</p>
                </div>

            </div>
        </div>
    </section>


    <!-- ══════════════════ ACCESS / DEVICES ══════ -->
    <section class="tech-access">
        <div class="container">
            <div class="tech-access__inner">
                <div>
                    <span class="tech-access__pill">Access anywhere</span>
                    <h2>Built for how your team actually works</h2>
                    <p>Whether your team is in the office, on the move, or working remotely — Oysterchecks is fully accessible across all devices. Initiate a screening process and receive real-time results with a single tap from your smartphone.</p>
                    <ul class="tech-device-list">
                        <li>
                            <div class="tech-device-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                                    <path d="M8 21h8M12 17v4"/>
                                </svg>
                            </div>
                            Desktop &amp; laptop — full platform experience
                        </li>
                        <li>
                            <div class="tech-device-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                            </div>
                            Mobile app — iOS &amp; Android, full feature access
                        </li>
                        <li>
                            <div class="tech-device-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <line x1="12" y1="20" x2="12.01" y2="20"/>
                                </svg>
                            </div>
                            Tablet — optimised responsive layout
                        </li>
                        <li>
                            <div class="tech-device-list__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                </svg>
                            </div>
                            API — embed directly into your own systems
                        </li>
                    </ul>
                </div>
                <div>
                    <div class="tech-access__visual">
                        <div class="tech-access__visual-row">
                            <div class="tech-access__visual-icon ic-blue">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                                    <path d="M8 21h8M12 17v4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="tech-access__visual-label">Web Platform</p>
                                <p class="tech-access__visual-sub">Full dashboard, reporting &amp; case management</p>
                            </div>
                            <span class="tech-access__visual-badge">Live</span>
                        </div>
                        <div class="tech-access__visual-row">
                            <div class="tech-access__visual-icon ic-teal">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                            </div>
                            <div>
                                <p class="tech-access__visual-label">Mobile App</p>
                                <p class="tech-access__visual-sub">iOS &amp; Android — initiate checks on the go</p>
                            </div>
                            <span class="tech-access__visual-badge">Live</span>
                        </div>
                        <div class="tech-access__visual-row">
                            <div class="tech-access__visual-icon ic-purple">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="tech-access__visual-label">REST API</p>
                                <p class="tech-access__visual-sub">Developer-friendly, fully documented</p>
                            </div>
                            <span class="tech-access__visual-badge">Live</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════ INTEGRATION ══════════ -->
    <section class="tech-integration">
        <div class="container">
            <div class="tech-integration__inner">
                <div class="tech-integration__visual">
                    <div class="tech-int-grid">
                        <div class="tech-int-item">
                            <div class="tech-int-item__icon ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
                            <p class="tech-int-item__name">Salesforce</p>
                            <p class="tech-int-item__type">CRM</p>
                        </div>
                        <div class="tech-int-item">
                            <div class="tech-int-item__icon ic-teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
                            <p class="tech-int-item__name">HubSpot</p>
                            <p class="tech-int-item__type">CRM</p>
                        </div>
                        <div class="tech-int-item">
                            <div class="tech-int-item__icon ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div>
                            <p class="tech-int-item__name">Zoho</p>
                            <p class="tech-int-item__type">CRM</p>
                        </div>
                        <div class="tech-int-item">
                            <div class="tech-int-item__icon ic-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/></svg></div>
                            <p class="tech-int-item__name">HR Systems</p>
                            <p class="tech-int-item__type">People</p>
                        </div>
                        <div class="tech-int-item">
                            <div class="tech-int-item__icon ic-red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></div>
                            <p class="tech-int-item__name">Onboarding</p>
                            <p class="tech-int-item__type">Workflow</p>
                        </div>
                        <div class="tech-int-item">
                            <div class="tech-int-item__icon ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
                            <p class="tech-int-item__name">Fintech</p>
                            <p class="tech-int-item__type">Finance</p>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="tech-access__pill">Integrations</span>
                    <h2>Fits into the tools you already use</h2>
                    <p>Our API-first architecture means Oysterchecks integrates seamlessly with your existing stack — from CRMs and HR platforms to onboarding tools and financial systems.</p>
                    <ul class="tech-integration__list">
                        <li>API-first with full REST documentation</li>
                        <li>Pre-built connectors for Salesforce, HubSpot, Zoho and more</li>
                        <li>Custom workflows for regulated sectors</li>
                        <li>Configurable triggers, rules and automated reporting</li>
                        <li>Enterprise-grade encryption and role-based access</li>
                        <li>Cloud, hybrid or on-premise deployments</li>
                        <li>GDPR, ISO 27001 and region-specific compliance frameworks</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════ CTA ════════════════════ -->
    <section class="tech-cta">
        <div class="container">
            <div class="tech-cta__strip">
                <div>
                    <h3>See the platform in action</h3>
                    <p>Get started today or contact our team to discuss your specific technical requirements.</p>
                </div>
                <a href="{{ route('login') }}" class="tech-cta-btn">
                    Get Started Now
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 8h10M9 4l4 4-4 4"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection