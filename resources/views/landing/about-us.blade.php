@extends('layouts.landing')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Outfit:wght@300;400;500;600&display=swap');

    :root {
        --navy:   #0B1F3A;
        --navy2:  #042C53;
        --teal:   #0E7C6A;
        --teal-l: #E0F4F0;
        --gold:   #C49A3C;
        --gold-l: #FBF4E6;
        --slate:  #4A5568;
        --light:  #F7F8FA;
        --border: #E2E8F0;
        --white:  #ffffff;
    }

    .oc-page { font-family: 'Outfit', sans-serif; color: var(--navy); }

    /* ── HERO ── */
    .oc-hero {
        background: var(--navy);
        position: relative;
        overflow: hidden;
        padding: 110px 0 90px;
    }
    .oc-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 80% at 80% 50%, rgba(14,124,106,0.18) 0%, transparent 70%),
            radial-gradient(ellipse 40% 60% at 10% 80%, rgba(196,154,60,0.10) 0%, transparent 60%);
    }
    .oc-hero-inner { position: relative; z-index: 1; }
    .oc-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #5DCAA5;
        margin-bottom: 20px;
    }
    .oc-eyebrow::before {
        content: '';
        display: inline-block;
        width: 28px; height: 2px;
        background: #5DCAA5;
        border-radius: 2px;
    }
    .oc-hero h1 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 64px;
        font-weight: 600;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 24px;
    }
    .oc-hero h1 em {
        font-style: italic;
        color: #5DCAA5;
    }
    .oc-hero-desc {
        font-size: 16px;
        font-weight: 300;
        color: rgba(255,255,255,0.6);
        line-height: 1.8;
        max-width: 540px;
        margin: 0;
    }
    .oc-hero-deco {
        position: absolute;
        right: -40px; top: 50%;
        transform: translateY(-50%);
        width: 340px; height: 340px;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.06);
        z-index: 0;
    }
    .oc-hero-deco::after {
        content: '';
        position: absolute;
        inset: 30px;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.04);
    }
    .oc-breadcrumb-wrap { margin-top: 36px; }
    .oc-breadcrumb-wrap .breadcrumb { background: transparent; padding: 0; margin: 0; }
    .oc-breadcrumb-wrap .breadcrumb-item a { color: rgba(255,255,255,0.45); text-decoration: none; font-size: 13px; }
    .oc-breadcrumb-wrap .breadcrumb-item.active { color: rgba(255,255,255,0.7); font-size: 13px; }
    .oc-breadcrumb-wrap .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.25); }

    /* ── SHARED SECTION STYLES ── */
    .oc-section { padding: 96px 0; }
    .oc-section-alt { background: var(--light); }

    .oc-section-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--teal);
        margin-bottom: 16px;
    }
    .oc-section-label::before {
        content: '';
        display: inline-block;
        width: 20px; height: 2px;
        background: var(--teal);
        border-radius: 2px;
    }

    .oc-heading {
        font-family: 'Cormorant Garamond', serif;
        font-size: 42px;
        font-weight: 600;
        color: var(--navy);
        line-height: 1.15;
        margin: 0 0 24px;
    }
    .oc-lead {
        font-size: 17px;
        font-weight: 400;
        color: #2d3748;
        line-height: 1.85;
        margin-bottom: 18px;
    }
    .oc-body {
        font-size: 15px;
        color: var(--slate);
        line-height: 1.85;
        margin-bottom: 16px;
    }

    /* ── SECTION 1: WHO WE ARE ── */
    .oc-who-quote {
        margin: 32px 0 0;
        padding: 28px 32px;
        border-left: 3px solid var(--teal);
        background: var(--teal-l);
        border-radius: 0 12px 12px 0;
    }
    .oc-who-quote p {
        font-family: 'Cormorant Garamond', serif;
        font-size: 20px;
        font-style: italic;
        color: var(--teal);
        margin: 0;
        line-height: 1.6;
    }
    .oc-who-aside {
        background: var(--navy);
        border-radius: 16px;
        padding: 40px 36px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .oc-who-aside-stat {
        padding: 22px 0;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .oc-who-aside-stat:last-child { border-bottom: none; padding-bottom: 0; }
    .oc-who-aside-stat:first-child { padding-top: 0; }
    .oc-who-aside-stat .label {
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.4);
        margin-bottom: 6px;
    }
    .oc-who-aside-stat .value {
        font-family: 'Cormorant Garamond', serif;
        font-size: 22px;
        font-weight: 600;
        color: #5DCAA5;
        line-height: 1.2;
    }

    /* ── SECTION 2: OUR PURPOSE ── */
    .oc-purpose-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 48px 52px;
        text-align: center;
        max-width: 720px;
        margin: 0 auto;
    }
    .oc-purpose-icon {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: var(--teal-l);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 28px;
        font-size: 28px;
    }
    .oc-purpose-card .oc-heading { font-size: 36px; margin-bottom: 20px; }
    .oc-purpose-statement {
        font-family: 'Cormorant Garamond', serif;
        font-size: 24px;
        font-style: italic;
        color: var(--teal);
        line-height: 1.55;
        margin: 0;
    }
    .oc-purpose-divider {
        width: 48px; height: 2px;
        background: var(--gold);
        border-radius: 2px;
        margin: 28px auto;
    }

    /* ── SECTION 3: WHAT WE DO ── */
    .oc-whatwedo-intro {
        font-size: 16px;
        color: var(--slate);
        line-height: 1.8;
        margin-bottom: 40px;
    }
    .oc-pillars {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }
    .oc-pillar {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 28px 28px 24px;
        position: relative;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .oc-pillar:hover {
        border-color: #0E7C6A;
        box-shadow: 0 4px 24px rgba(14,124,106,0.08);
    }
    .oc-pillar::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 3px; height: 100%;
        background: var(--teal);
        border-radius: 3px 0 0 3px;
    }
    .oc-pillar-num {
        font-family: 'Cormorant Garamond', serif;
        font-size: 36px;
        font-weight: 600;
        color: var(--teal-l);
        line-height: 1;
        margin-bottom: 10px;
    }
    .oc-pillar-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--navy);
        margin-bottom: 6px;
    }
    .oc-pillar-desc {
        font-size: 13px;
        color: var(--slate);
        line-height: 1.65;
        margin: 0;
    }
    .oc-outcome {
        background: var(--navy);
        border-radius: 14px;
        padding: 32px 36px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }
    .oc-outcome-icon {
        flex-shrink: 0;
        width: 44px; height: 44px;
        background: rgba(93,202,165,0.12);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-top: 2px;
    }
    .oc-outcome p {
        font-size: 15px;
        color: rgba(255,255,255,0.75);
        line-height: 1.75;
        margin: 0;
    }
    .oc-outcome p strong {
        color: #fff;
        font-weight: 500;
    }

    /* ── SECTION 4: OUR VISION ── */
    .oc-vision-wrap {
        background: var(--navy);
        border-radius: 20px;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
    .oc-vision-left {
        padding: 64px 52px;
        position: relative;
    }
    .oc-vision-left::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 80% 80% at 0% 100%, rgba(14,124,106,0.2) 0%, transparent 60%);
        pointer-events: none;
    }
    .oc-vision-left .oc-section-label { color: #5DCAA5; }
    .oc-vision-left .oc-section-label::before { background: #5DCAA5; }
    .oc-vision-left .oc-heading { color: #fff; font-size: 38px; margin-bottom: 28px; }
    .oc-vision-statement {
        font-family: 'Cormorant Garamond', serif;
        font-size: 21px;
        font-style: italic;
        color: rgba(255,255,255,0.75);
        line-height: 1.6;
        margin: 0;
    }
    .oc-vision-right {
        background: rgba(255,255,255,0.04);
        padding: 64px 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 28px;
        border-left: 1px solid rgba(255,255,255,0.06);
    }
    .oc-vision-pillar {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .oc-vision-pillar-icon {
        flex-shrink: 0;
        width: 40px; height: 40px;
        border-radius: 10px;
        background: rgba(93,202,165,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .oc-vision-pillar-title {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 4px;
    }
    .oc-vision-pillar-desc {
        font-size: 13px;
        color: rgba(255,255,255,0.5);
        margin: 0;
        line-height: 1.6;
    }

    /* ── SECTION 5: OUR MISSION ── */
    .oc-mission-wrap {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 2fr;
    }
    .oc-mission-left {
        background: var(--gold);
        padding: 56px 44px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .oc-mission-left::before {
        content: '';
        position: absolute;
        bottom: -40px; right: -40px;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .oc-mission-left::after {
        content: '';
        position: absolute;
        top: -20px; left: -20px;
        width: 100px; height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .oc-mission-left .oc-section-label {
        color: rgba(255,255,255,0.7);
        margin-bottom: 16px;
        position: relative; z-index: 1;
    }
    .oc-mission-left .oc-section-label::before { background: rgba(255,255,255,0.7); }
    .oc-mission-left-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 38px;
        font-weight: 600;
        color: #fff;
        line-height: 1.15;
        margin: 0;
        position: relative; z-index: 1;
    }
    .oc-mission-right {
        padding: 56px 52px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .oc-mission-statement {
        font-family: 'Cormorant Garamond', serif;
        font-size: 26px;
        font-weight: 400;
        color: var(--navy);
        line-height: 1.55;
        margin: 0 0 28px;
    }
    .oc-mission-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .oc-mission-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        color: var(--teal);
        background: var(--teal-l);
        border-radius: 20px;
        padding: 6px 14px;
    }
    .oc-mission-tag::before {
        content: '';
        display: inline-block;
        width: 5px; height: 5px;
        background: var(--teal);
        border-radius: 50%;
    }

    /* ── SECTION 5: OUR MISSION ── */
    .oc-mission-wrap {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 0;
        background: var(--gold-l);
        border: 1px solid #E8D9B0;
        border-radius: 20px;
        overflow: hidden;
    }
    .oc-mission-left {
        background: var(--gold);
        padding: 52px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .oc-mission-left .oc-section-label { color: rgba(255,255,255,0.7); }
    .oc-mission-left .oc-section-label::before { background: rgba(255,255,255,0.7); }
    .oc-mission-left-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 30px;
        font-weight: 600;
        color: #fff;
        line-height: 1.2;
        margin: 0;
    }
    .oc-mission-right {
        padding: 52px 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 28px;
    }
    .oc-mission-statement {
        font-family: 'Cormorant Garamond', serif;
        font-size: 22px;
        font-style: italic;
        color: var(--navy);
        line-height: 1.6;
        margin: 0;
    }
    .oc-mission-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .oc-mission-tag {
        font-size: 12px;
        font-weight: 500;
        color: #7A5C10;
        background: #F5E6C0;
        border: 1px solid #E8D9B0;
        border-radius: 20px;
        padding: 5px 14px;
    }

    /* ── SECTION 6: CORE VALUES ── */
    .oc-values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 48px;
    }
    .oc-value-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px 28px;
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .oc-value-card:hover {
        border-color: var(--teal);
        box-shadow: 0 6px 32px rgba(14,124,106,0.09);
    }
    .oc-value-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--teal), #5DCAA5);
        border-radius: 0 0 16px 16px;
    }
    .oc-value-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        background: var(--teal-l);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 18px;
    }
    .oc-value-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--navy);
        margin-bottom: 10px;
    }
    .oc-value-desc {
        font-size: 14px;
        color: var(--slate);
        line-height: 1.75;
        margin: 0;
    }
    .oc-values-statement {
        margin-top: 40px;
        padding: 28px 36px;
        background: var(--navy);
        border-radius: 14px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }
    .oc-values-statement-icon {
        flex-shrink: 0;
        font-size: 24px;
        margin-top: 2px;
    }
    .oc-values-statement p {
        font-size: 15px;
        color: rgba(255,255,255,0.75);
        line-height: 1.8;
        margin: 0;
    }
    .oc-values-statement p strong { color: #fff; font-weight: 500; }

    @media (max-width: 991px) {
        .oc-hero h1 { font-size: 42px; }
        .oc-pillars { grid-template-columns: 1fr; }
        .oc-vision-wrap { grid-template-columns: 1fr; }
        .oc-vision-right { border-left: none; border-top: 1px solid rgba(255,255,255,0.06); }
        .oc-who-aside { margin-top: 40px; }
        .oc-mission-wrap { grid-template-columns: 1fr; }
        .oc-mission-left { padding: 44px 36px; }
        .oc-mission-right { padding: 40px 36px; }
    }
</style>

<div class="main oc-page">

    <!-- ══════════ HERO ══════════ -->
    <section class="oc-hero" >
        <div class="oc-hero-deco"></div>
        <div class="container oc-hero-inner">
            <div class="row">
                <div class="col-lg-8">
                    <span class="oc-eyebrow">About Us</span>
                    <h1>Built to <em>change</em><br>how risk works.</h1>
                    <p class="oc-hero-desc">A global risk intelligence and assurance technology company — helping organisations verify, monitor, and manage risk with confidence.</p>
                    <div class="oc-breadcrumb-wrap">
                        <ol class="breadcrumb list-inline">
                            <li class="list-inline-item breadcrumb-item"><a href="#">Home</a></li>
                            <li class="list-inline-item breadcrumb-item"><a href="#">About us</a></li>
                            <!-- <li class="list-inline-item breadcrumb-item active">Who we are</li> -->
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ══════════ SECTION 1: WHO WE ARE ══════════ -->
    <section class="oc-section" id="who-we-are">
        <div class="container">
            <div class="row align-items-start justify-content-between">
                <div class="col-lg-7 mb-5 mb-lg-0">
                    <span class="oc-section-label">Who We Are</span>
                    <h2 class="oc-heading">Global Risk Intelligence,<br>Built for What's Next</h2>
                    <p class="oc-lead">Oysterchecks is a global risk intelligence and assurance technology company helping organisations build trust, strengthen compliance, and make better decisions with confidence.</p>
                    <p class="oc-body">We sit at the intersection of identity, risk, compliance, and financial crime prevention, providing real-time intelligence that enables organisations to verify, monitor, and manage risk across customers, employees, and transactions.</p>
                    <div class="oc-who-quote">
                        <p>"In a world where risk is increasingly complex, fast-moving, and cross-border, traditional approaches to compliance are no longer sufficient. Oysterchecks was built to change that."</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="oc-who-aside">
                        <div class="oc-who-aside-stat">
                            <div class="label">Focus</div>
                            <div class="value">Identity &amp; Risk Intelligence</div>
                        </div>
                        <div class="oc-who-aside-stat">
                            <div class="label">Coverage</div>
                            <div class="value">Global, Cross-Border</div>
                        </div>
                        <div class="oc-who-aside-stat">
                            <div class="label">Approach</div>
                            <div class="value">Real-Time, Data-Driven</div>
                        </div>
                        <div class="oc-who-aside-stat">
                            <div class="label">Mission</div>
                            <div class="value">Market Leadership in Screening</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════ SECTION 2: OUR PURPOSE ══════════ -->
    <section class="oc-section oc-section-alt" id="our-purpose">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="oc-purpose-card">
                        <div class="oc-purpose-icon">&#127919;</div>
                        <span class="oc-section-label" style="justify-content: center;">Our Purpose</span>
                        <h2 class="oc-heading">Why We Exist</h2>
                        <div class="oc-purpose-divider"></div>
                        <p class="oc-purpose-statement">"To enable organisations to move from reactive compliance to proactive, data-driven assurance."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════ SECTION 3: WHAT WE DO ══════════ -->
    <section class="oc-section" id="what-we-do">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <span class="oc-section-label" style="justify-content: center;">What We Do</span>
                    <h2 class="oc-heading">A Unified Platform for Risk &amp; Compliance</h2>
                    <p class="oc-whatwedo-intro">We provide a unified platform that replaces fragmented systems and manual reporting — giving organisations a single, trusted view of risk and compliance across their operations.</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="oc-pillars">
                        <div class="oc-pillar">
                            <div class="oc-pillar-num">01</div>
                            <div class="oc-pillar-title">Verifies Identities &amp; Organisations</div>
                            <p class="oc-pillar-desc">Instant, accurate verification of individuals and entities across thousands of global databases.</p>
                        </div>
                        <div class="oc-pillar">
                            <div class="oc-pillar-num">02</div>
                            <div class="oc-pillar-title">Detects &amp; Monitors Financial Crime Risks</div>
                            <p class="oc-pillar-desc">Continuous AML and transaction monitoring to detect suspicious activity before it becomes exposure.</p>
                        </div>
                        <div class="oc-pillar">
                            <div class="oc-pillar-num">03</div>
                            <div class="oc-pillar-title">Aggregates Intelligence from Multiple Sources</div>
                            <p class="oc-pillar-desc">A consolidated intelligence layer that brings together data from across your risk landscape.</p>
                        </div>
                        <div class="oc-pillar">
                            <div class="oc-pillar-num">04</div>
                            <div class="oc-pillar-title">Enables Continuous, Independent Assurance</div>
                            <p class="oc-pillar-desc">Ongoing assurance that operates independently of manual processes — always on, always accurate.</p>
                        </div>
                    </div>
                    <div class="oc-outcome">
                        <div class="oc-outcome-icon">&#9989;</div>
                        <p><strong>The result:</strong> Instead of relying on fragmented systems or manual reporting, organisations gain a single, trusted view of risk and compliance across their entire operations — in real time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════ SECTION 5: OUR MISSION ══════════ -->
    <section class="oc-section" id="our-mission">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div class="oc-mission-wrap">
                        <div class="oc-mission-left">
                            <span class="oc-section-label">Our Mission</span>
                            <h2 class="oc-mission-left-title">What Drives Us Every Day</h2>
                        </div>
                        <div class="oc-mission-right">
                            <p class="oc-mission-statement">"To achieve market dominance in providing exceptional and extensive Background, KYC and AML screening services across the world."</p>
                            <div class="oc-mission-tags">
                                <span class="oc-mission-tag">Background Screening</span>
                                <span class="oc-mission-tag">KYC</span>
                                <span class="oc-mission-tag">AML</span>
                                <span class="oc-mission-tag">Global Coverage</span>
                                <span class="oc-mission-tag">Market Leadership</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════ SECTION 4: OUR VISION ══════════ -->
    <section class="oc-section oc-section-alt" id="our-vision">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div class="oc-vision-wrap">
                        <div class="oc-vision-left">
                            <span class="oc-section-label">Our Vision</span>
                            <h2 class="oc-heading">Where We're Going</h2>
                            <p class="oc-vision-statement">"To become the leading Assurance Intelligence Platform, empowering organisations globally to demonstrate trust, transparency, and control in real time."</p>
                        </div>
                        <div class="oc-vision-right">
                            <div class="oc-vision-pillar">
                                <div class="oc-vision-pillar-icon">&#128269;</div>
                                <div>
                                    <div class="oc-vision-pillar-title">Trust</div>
                                    <p class="oc-vision-pillar-desc">Organisations that demonstrate verifiable trust earn stronger partnerships and greater market confidence.</p>
                                </div>
                            </div>
                            <div class="oc-vision-pillar">
                                <div class="oc-vision-pillar-icon">&#128203;</div>
                                <div>
                                    <div class="oc-vision-pillar-title">Transparency</div>
                                    <p class="oc-vision-pillar-desc">Full visibility into risk and compliance status — no blind spots, no surprises.</p>
                                </div>
                            </div>
                            <div class="oc-vision-pillar">
                                <div class="oc-vision-pillar-icon">&#9881;</div>
                                <div>
                                    <div class="oc-vision-pillar-title">Control</div>
                                    <p class="oc-vision-pillar-desc">Real-time assurance that puts organisations firmly in command of their risk posture.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

   

    <!-- ══════════ SECTION 6: CORE VALUES ══════════ -->
    <section class="oc-section oc-section-alt" id="core-values">
        <div class="container">
            <div class="row justify-content-center mb-2">
                <div class="col-lg-8 text-center">
                    <span class="oc-section-label" style="justify-content: center;">Core Values</span>
                    <h2 class="oc-heading">The Principles We Live By</h2>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div class="oc-values-grid">
                        <div class="oc-value-card">
                            <div class="oc-value-icon">&#9878;</div>
                            <div class="oc-value-title">Integrity</div>
                            <p class="oc-value-desc">We uphold integrity as a non-negotiable standard — in every engagement, every decision, and every relationship we build with clients.</p>
                        </div>
                        <div class="oc-value-card">
                            <div class="oc-value-icon">&#128270;</div>
                            <div class="oc-value-title">Honesty</div>
                            <p class="oc-value-desc">Honesty is the foundation of trust. We require truthful information from our clients and hold ourselves to the same standard in everything we deliver.</p>
                        </div>
                        <div class="oc-value-card">
                            <div class="oc-value-icon">&#128274;</div>
                            <div class="oc-value-title">Confidentiality</div>
                            <p class="oc-value-desc">All information shared with us is treated with the highest level of discretion — safeguarded as confidential throughout our business process.</p>
                        </div>
                    </div>
                    <div class="oc-values-statement">
                        <div class="oc-values-statement-icon">&#128172;</div>
                        <p>Oysterchecks believes that <strong>integrity and honesty</strong> are fundamental values in building lasting trust. We seek these qualities in every client relationship — because practicing them consistently is what enables us to deliver <strong>successful, optimal results</strong> for the organisations we serve.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection