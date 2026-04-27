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
        --shadow-md:  0 4px 20px rgba(22,46,102,0.10);
        --shadow-lg:  0 12px 40px rgba(22,46,102,0.16);
    }

    /* ── HERO ────────────────────────────────────── */
    .ec-hero {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        padding: 88px 0 72px;
        position: relative; overflow: hidden;
    }
    .ec-hero::before {
        content: ''; position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .ec-hero .container { position: relative; z-index: 1; text-align: center; }
    .ec-hero__eyebrow {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.16em;
        text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 16px;
    }
    .ec-hero__eyebrow::before {
        content: ''; display: block; width: 22px; height: 2px;
        background: rgba(255,255,255,0.4); flex-shrink: 0;
    }
    .ec-hero h1 {
        font-size: clamp(28px, 4vw, 48px); font-weight: 800; color: #fff;
        line-height: 1.12; letter-spacing: -0.025em; margin: 0 0 14px;
    }
    .ec-hero p {
        font-size: 17px; color: rgba(255,255,255,0.72);
        line-height: 1.65; max-width: 520px; margin: 0 auto;
    }

    /* ── SHARED ──────────────────────────────────── */
    .ec-section { padding: 80px 0; }
    .ec-section--alt { background: var(--off-white); }
    .ec-section-heading { text-align: center; margin-bottom: 52px; }
    .ec-pill {
        display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.13em;
        text-transform: uppercase; color: var(--navy); background: rgba(22,46,102,0.08);
        padding: 5px 16px; border-radius: 100px; margin-bottom: 14px;
    }
    .ec-section-heading h2 {
        font-size: clamp(22px, 3vw, 34px); font-weight: 800; color: var(--text-dark);
        letter-spacing: -0.025em; margin: 0 0 10px;
    }
    .ec-section-heading p { font-size: 15px; color: var(--text-muted); max-width: 480px; margin: 0 auto; line-height: 1.65; }

    /* ── PRICING CARDS — EMPLOYMENT ──────────────── */
    .ec-pricing-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 1199px) { .ec-pricing-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px)  { .ec-pricing-grid { grid-template-columns: 1fr; } }

    .ec-plan {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        display: flex; flex-direction: column;
        transition: transform 0.22s, box-shadow 0.22s, border-color 0.22s;
        position: relative;
    }
    .ec-plan:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); border-color: rgba(22,46,102,0.22); }

    .ec-plan--popular {
        border-color: var(--navy);
        border-width: 2px;
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }
    .ec-plan--popular:hover { transform: translateY(-8px); }

    .ec-plan__badge {
        position: absolute; top: 16px; right: 16px;
        background: var(--red); color: #fff;
        font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; padding: 4px 10px; border-radius: 100px;
    }

    .ec-plan__head {
        background: var(--navy); padding: 24px 24px 20px;
    }
    .ec-plan--popular .ec-plan__head { background: var(--navy-dark); }

    .ec-plan__name {
        font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.75);
        text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 10px;
    }
    .ec-plan__price {
        display: flex; align-items: baseline; gap: 4px;
    }
    .ec-plan__price-amount {
        font-size: 34px; font-weight: 800; color: #fff; letter-spacing: -0.03em;
    }
    .ec-plan__price-period { font-size: 13px; color: rgba(255,255,255,0.55); }

    .ec-plan__body { padding: 24px; flex: 1; display: flex; flex-direction: column; }
    .ec-plan__label {
        font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
        text-transform: uppercase; color: var(--text-muted); margin: 0 0 14px;
    }
    .ec-plan__features { list-style: none; padding: 0; margin: 0 0 auto; display: flex; flex-direction: column; gap: 10px; }
    .ec-plan__features li {
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 13.5px; color: var(--text-dark); line-height: 1.45;
    }
    .ec-plan__check {
        width: 18px; height: 18px; flex-shrink: 0;
        background: #d5f2e8; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; margin-top: 1px;
    }
    .ec-plan__check svg { width: 10px; height: 10px; color: #0d6e52; }

    .ec-plan__cta {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        background: var(--navy); color: #fff;
        font-size: 14px; font-weight: 700; padding: 13px 20px;
        border-radius: var(--radius-sm); text-decoration: none;
        margin-top: 22px; letter-spacing: 0.01em;
        transition: background 0.18s, transform 0.18s;
    }
    .ec-plan__cta:hover { background: var(--navy-dark); color: #fff; text-decoration: none; transform: translateY(-1px); }
    .ec-plan__cta svg { width: 14px; height: 14px; }
    .ec-plan--popular .ec-plan__cta { background: var(--navy-dark); }

    /* ── COMPARISON TABLE ─────────────────────────── */
    .ec-table-wrap { overflow-x: auto; }
    .ec-table {
        width: 100%; border-collapse: collapse;
        font-size: 14px;
    }
    .ec-table thead tr {
        background: var(--navy);
    }
    .ec-table thead th {
        padding: 14px 20px; font-size: 12px; font-weight: 700;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: rgba(255,255,255,0.75); text-align: center;
        border: none;
    }
    .ec-table thead th:first-child { text-align: left; color: #fff; }

    .ec-table tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
    .ec-table tbody tr:hover { background: rgba(22,46,102,0.03); }
    .ec-table tbody tr:last-child { border-bottom: none; }

    .ec-table tbody td, .ec-table tbody th {
        padding: 13px 20px; vertical-align: middle;
    }
    .ec-table tbody th {
        font-size: 13.5px; font-weight: 600; color: var(--text-dark);
        text-align: left; max-width: 260px;
    }
    .ec-table tbody td { text-align: center; }

    .ec-table-check {
        width: 22px; height: 22px; border-radius: 50%;
        background: #d5f2e8; display: inline-flex; align-items: center; justify-content: center; margin: auto;
    }
    .ec-table-check svg { width: 11px; height: 11px; color: #0d6e52; }
    .ec-table-dash { color: #c4c9d9; font-size: 18px; font-weight: 300; }
    .ec-table-unlimited {
        font-size: 12px; font-weight: 700; color: #059669;
        background: #d5f2e8; padding: 3px 10px; border-radius: 100px;
        display: inline-block;
    }
    .ec-table-num {
        font-size: 13px; font-weight: 600; color: var(--text-dark);
    }

    /* column headers in table */
    .ec-table-col-header { display: flex; flex-direction: column; align-items: center; gap: 3px; }
    .ec-table-col-header span:first-child { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.55); }
    .ec-table-col-header span:last-child  { font-size: 13px; font-weight: 800; color: #fff; }

    /* ── BS7858 / ENTRY SCREENING ─────────────────── */
    .ec-bs-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
    }
    @media (max-width: 767px) { .ec-bs-grid { grid-template-columns: 1fr; } }

    .ec-bs-card {
        border-radius: var(--radius-lg); overflow: hidden;
        box-shadow: var(--shadow-md);
        display: flex; flex-direction: column;
    }
    .ec-bs-card__head { padding: 28px 28px 22px; }
    .ec-bs-card__head--navy { background: var(--navy); }
    .ec-bs-card__head--red  { background: var(--red); }

    .ec-bs-card__name { font-size: 12px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.65); margin: 0 0 10px; }
    .ec-bs-card__price { display: flex; align-items: baseline; gap: 5px; }
    .ec-bs-card__price-amount { font-size: 36px; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
    .ec-bs-card__price-period { font-size: 13px; color: rgba(255,255,255,0.55); }

    .ec-bs-card__cta {
        display: inline-flex; align-items: center; gap: 8px;
        color: #fff; font-size: 14px; font-weight: 700;
        padding: 12px 22px; border-radius: var(--radius-sm);
        text-decoration: none; margin-top: 18px; letter-spacing: 0.01em;
        border: 2px solid rgba(255,255,255,0.35);
        transition: background 0.18s;
    }
    .ec-bs-card__cta:hover { background: rgba(255,255,255,0.15); color: #fff; text-decoration: none; }
    .ec-bs-card__cta svg { width: 14px; height: 14px; }

    .ec-bs-card__body { background: var(--white); border: 1px solid var(--border); border-top: none; padding: 28px; flex: 1; }
    .ec-bs-card__label { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); margin: 0 0 14px; }
    .ec-bs-card__features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 11px; }
    .ec-bs-card__features li {
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 14px; color: var(--text-dark); line-height: 1.45;
    }
    .ec-bs-check {
        width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; margin-top: 1px;
    }
    .ec-bs-check--navy { background: rgba(22,46,102,0.1); color: var(--navy); }
    .ec-bs-check--red  { background: rgba(218,37,29,0.1); color: var(--red); }
    .ec-bs-check svg { width: 10px; height: 10px; }

    /* ── CTA STRIP ────────────────────────────────── */
    .ec-cta { padding: 0 0 88px; background: var(--off-white); }
    .ec-cta__strip {
        background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-mid) 100%);
        border-radius: var(--radius-lg); padding: 52px 52px;
        display: flex; align-items: center; justify-content: space-between; gap: 24px;
        box-shadow: var(--shadow-lg);
    }
    @media (max-width: 767px) { .ec-cta__strip { flex-direction: column; text-align: center; padding: 36px 28px; } }
    .ec-cta__strip h3 { font-size: 22px; font-weight: 800; color: #fff; margin: 0 0 8px; letter-spacing: -0.01em; }
    .ec-cta__strip p  { font-size: 15px; color: rgba(255,255,255,0.68); margin: 0; line-height: 1.5; }
    .ec-cta-btn {
        display: inline-flex; align-items: center; gap: 9px;
        background: #fff; color: var(--navy); font-size: 14px; font-weight: 700;
        padding: 13px 26px; border-radius: var(--radius-sm); text-decoration: none;
        white-space: nowrap; flex-shrink: 0; letter-spacing: 0.01em;
        transition: background 0.18s, transform 0.18s;
    }
    .ec-cta-btn:hover { background: #e8eefb; color: var(--navy); text-decoration: none; transform: translateX(2px); }
    .ec-cta-btn svg { width: 15px; height: 15px; }
</style>

<div class="main">

    <!-- ════════════════ HERO ════════════════ -->
    <section class="ec-hero">
        <div class="container">
            <p class="ec-hero__eyebrow">Services</p>
            <h1>Employment Checks</h1>
            <p>Comprehensive background screening packages designed to match every role, sector, and risk level.</p>
        </div>
    </section>


    <!-- ════════════════ EMPLOYMENT PRICING ════════════════ -->
    <section class="ec-section ec-section--alt">
        <div class="container">
            <div class="ec-section-heading">
                <span class="ec-pill">Employment check packages</span>
                <h2>Choose the right plan for your organisation</h2>
                <p>From basic GDPR-compliant screening to comprehensive director-level checks — all in one platform.</p>
            </div>

            <div class="ec-pricing-grid">

                <!-- Plan 1 -->
                <div class="ec-plan">
                    <div class="ec-plan__head">
                        <p class="ec-plan__name">GDPR Screening</p>
                        <div class="ec-plan__price">
                            <span class="ec-plan__price-amount">£100k</span>
                            <span class="ec-plan__price-period">/ month</span>
                        </div>
                    </div>
                    <div class="ec-plan__body">
                        <p class="ec-plan__label">What you get</p>
                        <ul class="ec-plan__features">
                            <li>
                                <span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                DBS check
                            </li>
                            <li>
                                <span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                Credit check (including PEP &amp; sanctions)
                            </li>
                        </ul>
                        <a href="{{ route('login') }}" class="ec-plan__cta">
                            Get Started
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Plan 2 -->
                <div class="ec-plan">
                    <div class="ec-plan__head">
                        <p class="ec-plan__name">Entry Screening</p>
                        <div class="ec-plan__price">
                            <span class="ec-plan__price-amount">£150k</span>
                            <span class="ec-plan__price-period">/ month</span>
                        </div>
                    </div>
                    <div class="ec-plan__body">
                        <p class="ec-plan__label">What you get</p>
                        <ul class="ec-plan__features">
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>DBS check</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Credit check (including PEP &amp; sanctions)</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>ID verification</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Right to Work (RTW) check</li>
                        </ul>
                        <a href="{{ route('login') }}" class="ec-plan__cta">
                            Get Started
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Plan 3 — Most Popular -->
                <div class="ec-plan ec-plan--popular">
                    <span class="ec-plan__badge">Most Popular</span>
                    <div class="ec-plan__head">
                        <p class="ec-plan__name">Mid-Tier</p>
                        <div class="ec-plan__price">
                            <span class="ec-plan__price-amount">£250k</span>
                            <span class="ec-plan__price-period">/ month</span>
                        </div>
                    </div>
                    <div class="ec-plan__body">
                        <p class="ec-plan__label">What you get</p>
                        <ul class="ec-plan__features">
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>DBS check</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Credit check (including PEP &amp; sanctions)</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>ID verification</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Right to Work (RTW) check</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>3-year employment referencing (incl. gap analysis)</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Adverse media, social media &amp; undercover journalistic checks</li>
                        </ul>
                        <a href="{{ route('login') }}" class="ec-plan__cta">
                            Get Started
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Plan 4 -->
                <div class="ec-plan">
                    <div class="ec-plan__head">
                        <p class="ec-plan__name">Director &amp; Senior Management</p>
                        <div class="ec-plan__price">
                            <span class="ec-plan__price-amount">£500k</span>
                            <span class="ec-plan__price-period">/ month</span>
                        </div>
                    </div>
                    <div class="ec-plan__body">
                        <p class="ec-plan__label">What you get</p>
                        <ul class="ec-plan__features">
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>DBS check</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Credit check (including PEP &amp; sanctions)</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>ID verification</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Right to Work (RTW) check</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>3-year employment referencing (incl. gap analysis)</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Adverse media, social media &amp; undercover journalistic checks</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Qualification verification</li>
                            <li><span class="ec-plan__check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>Directorship check</li>
                        </ul>
                        <a href="{{ route('login') }}" class="ec-plan__cta">
                            Get Started
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                </div>

            </div><!-- /.ec-pricing-grid -->
        </div>
    </section>


    <!-- ════════════════ OPTIONAL EXTRAS TABLE ════════════════ -->
    <section class="ec-section">
        <div class="container">
            <div class="ec-section-heading">
                <span class="ec-pill">Feature comparison</span>
                <h2>Optional extra checks</h2>
                <p>See exactly which additional checks are available with each package.</p>
            </div>

            <div class="ec-table-wrap">
                <table class="ec-table">
                    <thead>
                        <tr>
                            <th style="text-align:left; width:34%;">Check type</th>
                            <th>
                                <div class="ec-table-col-header">
                                    <span>GDPR</span>
                                    <span>£100k</span>
                                </div>
                            </th>
                            <th>
                                <div class="ec-table-col-header">
                                    <span>Entry</span>
                                    <span>£150k</span>
                                </div>
                            </th>
                            <th>
                                <div class="ec-table-col-header">
                                    <span>Mid-Tier</span>
                                    <span>£250k</span>
                                </div>
                            </th>
                            <th>
                                <div class="ec-table-col-header">
                                    <span>Director</span>
                                    <span>£500k</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Adverse media &amp; undercover journalism check</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-num">100</span></td>
                            <td><span class="ec-table-num">100</span></td>
                            <td><span class="ec-table-unlimited">Unlimited</span></td>
                        </tr>
                        <tr>
                            <th>Character reference</th>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>DBS basic</th>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>DBS standard</th>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>DBS enhanced</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>DBS volunteer</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>UK directorship check</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>UK driving licence check</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>Previous reference</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>Two previous references</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>Employment history (incl. gap analysis)</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                        <tr>
                            <th>Mobile app integration</th>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-dash">—</span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                            <td><span class="ec-table-check"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <!-- ════════════════ BS7858 / ENTRY SCREENING ════════════════ -->
    <section class="ec-section ec-section--alt">
        <div class="container">
            <div class="ec-section-heading">
                <span class="ec-pill">Specialist vetting</span>
                <h2>BS7858 &amp; security screening packages</h2>
                <p>Structured vetting packages designed for security, defence, and high-risk sector requirements.</p>
            </div>

            <div class="ec-bs-grid">

                <!-- BS7858 -->
                <div class="ec-bs-card">
                    <div class="ec-bs-card__head ec-bs-card__head--navy">
                        <p class="ec-bs-card__name">BS7858 Screening</p>
                        <div class="ec-bs-card__price">
                            <span class="ec-bs-card__price-amount">£150k</span>
                            <span class="ec-bs-card__price-period">/ month</span>
                        </div>
                        <a href="{{ route('login') }}" class="ec-bs-card__cta">
                            Get Started
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                    <div class="ec-bs-card__body">
                        <p class="ec-bs-card__label">You will get</p>
                        <ul class="ec-bs-card__features">
                            <li>
                                <span class="ec-bs-check ec-bs-check--navy"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                ID verification
                            </li>
                            <li>
                                <span class="ec-bs-check ec-bs-check--navy"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                Right to Work (RTW) check
                            </li>
                            <li>
                                <span class="ec-bs-check ec-bs-check--navy"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                5-year employment referencing (incl. gap analysis)
                            </li>
                            <li>
                                <span class="ec-bs-check ec-bs-check--navy"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                Basic DBS
                            </li>
                            <li>
                                <span class="ec-bs-check ec-bs-check--navy"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                Credit check (including PEP &amp; sanctions)
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Entry Screening -->
                <div class="ec-bs-card">
                    <div class="ec-bs-card__head ec-bs-card__head--red">
                        <p class="ec-bs-card__name">Entry Screening</p>
                        <div class="ec-bs-card__price">
                            <span class="ec-bs-card__price-amount">£250k</span>
                            <span class="ec-bs-card__price-period">/ month</span>
                        </div>
                        <a href="{{ route('login') }}" class="ec-bs-card__cta">
                            Get Started
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                    <div class="ec-bs-card__body">
                        <p class="ec-bs-card__label">You will get</p>
                        <ul class="ec-bs-card__features">
                            <li>
                                <span class="ec-bs-check ec-bs-check--red"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                ID verification
                            </li>
                            <li>
                                <span class="ec-bs-check ec-bs-check--red"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                Right to Work (RTW) check
                            </li>
                            <li>
                                <span class="ec-bs-check ec-bs-check--red"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                3-year employment referencing (incl. gap analysis)
                            </li>
                            <li>
                                <span class="ec-bs-check ec-bs-check--red"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg></span>
                                Basic DBS
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ════════════════ CTA ════════════════ -->
    <section class="ec-cta">
        <div class="container">
            <div class="ec-cta__strip">
                <div>
                    <h3>Not sure which package is right for you?</h3>
                    <p>Speak to our team and we'll recommend the best screening solution for your organisation and sector.</p>
                </div>
                <a href="{{ route('contact-us') }}" class="ec-cta-btn">
                    Speak to Our Team
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                </a>
            </div>
        </div>
    </section>

</div>

@endsection