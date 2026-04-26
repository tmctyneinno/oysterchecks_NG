@extends('layouts.landing')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Outfit:wght@300;400;500;600&display=swap');

    :root {
        --navy:   #0B1F3A;
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
        position: relative;
        overflow: hidden;
        padding: 110px 0 90px;
        background: var(--navy);
    }
    .oc-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 80% at 85% 50%, rgba(14,124,106,0.2) 0%, transparent 65%),
            radial-gradient(ellipse 40% 60% at 5% 90%,  rgba(196,154,60,0.10) 0%, transparent 60%);
    }
    .oc-hero-inner { position: relative; z-index: 1; }
    .oc-hero-deco {
        position: absolute;
        right: -60px; top: 50%;
        transform: translateY(-50%);
        width: 360px; height: 360px;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.05);
        z-index: 0;
    }
    .oc-hero-deco::after {
        content: '';
        position: absolute;
        inset: 36px;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.04);
    }
    .oc-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #5DCAA5;
        margin-bottom: 18px;
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
        font-size: 60px;
        font-weight: 600;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 20px;
    }
    .oc-hero h1 em { font-style: italic; color: #5DCAA5; }
    .oc-hero-desc {
        font-size: 16px;
        font-weight: 300;
        color: rgba(255,255,255,0.6);
        line-height: 1.8;
        max-width: 520px;
        margin: 0;
    }
    .oc-breadcrumb-wrap { margin-top: 36px; }
    .oc-breadcrumb-wrap .breadcrumb { background: transparent; padding: 0; margin: 0; }
    .oc-breadcrumb-wrap .breadcrumb-item a { color: rgba(255,255,255,0.45); text-decoration: none; font-size: 13px; }
    .oc-breadcrumb-wrap .breadcrumb-item.active { color: rgba(255,255,255,0.75); font-size: 13px; }
    .oc-breadcrumb-wrap .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.25); }

    /* ── SHARED ── */
    .oc-section { padding: 88px 0; }
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
        margin-bottom: 14px;
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
        font-size: 40px;
        font-weight: 600;
        color: var(--navy);
        line-height: 1.15;
        margin: 0 0 16px;
    }
    .oc-subheading {
        font-size: 16px;
        color: var(--slate);
        line-height: 1.75;
        max-width: 600px;
        margin: 0 auto 56px;
    }

    /* ── INDUSTRIES GRID ── */
    .oc-industries-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }
    .oc-industry-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px 28px 28px;
        position: relative;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        display: flex;
        flex-direction: column;
    }
    .oc-industry-card:hover {
        border-color: var(--teal);
        box-shadow: 0 8px 36px rgba(14,124,106,0.10);
        transform: translateY(-3px);
    }
    .oc-industry-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--teal), #5DCAA5);
        border-radius: 16px 16px 0 0;
    }
    .oc-industry-icon {
        font-size: 28px;
        margin-bottom: 16px;
        line-height: 1;
    }
    .oc-industry-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 20px;
        font-weight: 600;
        color: var(--navy);
        margin-bottom: 14px;
        line-height: 1.2;
    }
    .oc-industry-clients {
        list-style: none;
        padding: 0; margin: 0 0 18px;
    }
    .oc-industry-clients li {
        font-size: 13px;
        color: var(--slate);
        padding: 5px 0;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .oc-industry-clients li:last-child { border-bottom: none; }
    .oc-industry-clients li::before {
        content: '';
        display: inline-block;
        width: 5px; height: 5px;
        background: var(--teal);
        border-radius: 50%;
        flex-shrink: 0;
    }
    .oc-use-cases {
        margin-top: auto;
        padding: 14px 16px;
        background: var(--teal-l);
        border-radius: 10px;
    }
    .oc-use-cases-label {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--teal);
        margin-bottom: 6px;
    }
    .oc-use-cases p {
        font-size: 12px;
        color: #085041;
        line-height: 1.6;
        margin: 0;
    }

    /* ── TECHNOLOGY SECTION ── */
    .oc-tech-hero {
        background: var(--navy);
        border-radius: 20px;
        overflow: hidden;
        padding: 60px 56px;
        position: relative;
        margin-bottom: 48px;
    }
    .oc-tech-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 70% 90% at 90% 50%, rgba(14,124,106,0.2) 0%, transparent 65%);
        pointer-events: none;
    }
    .oc-tech-hero .oc-section-label { color: #5DCAA5; }
    .oc-tech-hero .oc-section-label::before { background: #5DCAA5; }
    .oc-tech-hero .oc-heading { color: #fff; font-size: 36px; margin-bottom: 14px; }
    .oc-tech-hero p {
        font-size: 15px;
        color: rgba(255,255,255,0.6);
        line-height: 1.75;
        max-width: 560px;
        margin: 0;
    }

    .oc-tech-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    .oc-tech-grid-bottom {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .oc-tech-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px 26px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .oc-tech-card:hover {
        border-color: var(--teal);
        box-shadow: 0 4px 24px rgba(14,124,106,0.08);
    }
    .oc-tech-card-icon {
        width: 44px; height: 44px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 16px;
    }
    .oc-tech-card-icon.teal  { background: var(--teal-l); }
    .oc-tech-card-icon.navy  { background: #EEF2F8; }
    .oc-tech-card-icon.gold  { background: var(--gold-l); }
    .oc-tech-card-icon.dark  { background: #1a1a2e; }

    .oc-tech-card-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--navy);
        margin-bottom: 14px;
    }
    .oc-tech-features {
        list-style: none;
        padding: 0; margin: 0;
    }
    .oc-tech-features li {
        font-size: 13px;
        color: var(--slate);
        padding: 5px 0;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: flex-start;
        gap: 8px;
        line-height: 1.5;
    }
    .oc-tech-features li:last-child { border-bottom: none; }
    .oc-tech-features li::before {
        content: '';
        display: inline-block;
        width: 5px; height: 5px;
        background: var(--teal);
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 6px;
    }

    /* ── DATA ASSURANCE BANNER ── */
    .oc-assurance-banner {
        background: linear-gradient(135deg, var(--navy) 0%, #0D2B52 60%, #0E3D35 100%);
        border-radius: 18px;
        padding: 52px 56px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: center;
        margin-top: 48px;
    }
    .oc-assurance-banner .oc-section-label { color: #5DCAA5; }
    .oc-assurance-banner .oc-section-label::before { background: #5DCAA5; }
    .oc-assurance-banner .oc-heading { color: #fff; font-size: 32px; margin-bottom: 12px; }
    .oc-assurance-banner > div:first-child p {
        font-size: 15px;
        color: rgba(255,255,255,0.6);
        line-height: 1.75;
        margin: 0;
    }
    .oc-assurance-items {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .oc-assurance-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 18px 20px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
    }
    .oc-assurance-item-icon {
        flex-shrink: 0;
        width: 36px; height: 36px;
        background: rgba(93,202,165,0.12);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .oc-assurance-item-title {
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 3px;
    }
    .oc-assurance-item-desc {
        font-size: 12px;
        color: rgba(255,255,255,0.5);
        margin: 0;
        line-height: 1.5;
    }

    @media (max-width: 991px) {
        .oc-hero h1 { font-size: 40px; }
        .oc-industries-grid { grid-template-columns: 1fr 1fr; }
        .oc-tech-grid { grid-template-columns: 1fr 1fr; }
        .oc-tech-grid-bottom { grid-template-columns: 1fr; }
        .oc-assurance-banner { grid-template-columns: 1fr; padding: 40px 32px; }
        .oc-tech-hero { padding: 44px 36px; }
    }
    @media (max-width: 767px) {
        .oc-industries-grid { grid-template-columns: 1fr; }
        .oc-tech-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="main oc-page">

    <!-- ══════════ HERO ══════════ -->
    <section class="oc-hero">
        <div class="oc-hero-deco"></div>
        <div class="container oc-hero-inner">
            <div class="row">
                <div class="col-lg-8">
                    <span class="oc-eyebrow">Our Reach</span>
                    <h1>Industries<br>We <em>Serve</em></h1>
                    <p class="oc-hero-desc">Oysterchecks supports organisations operating in highly regulated and risk-sensitive environments — delivering intelligence, assurance, and compliance at scale.</p>
                    <div class="oc-breadcrumb-wrap">
                        <ol class="breadcrumb list-inline">
                            <li class="list-inline-item breadcrumb-item"><a href="#">Home</a></li>
                            <li class="list-inline-item breadcrumb-item active">Industries</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════ INDUSTRIES ══════════ -->
    <section class="oc-section" id="industries">
        <div class="container">
            <div class="row justify-content-center mb-2">
                <div class="col-lg-8 text-center">
                    <span class="oc-section-label" style="justify-content: center;">Industries We Serve</span>
                    <h2 class="oc-heading">Trusted Across Sectors</h2>
                    <p class="oc-subheading">From banking to aviation, we deliver tailored risk intelligence and compliance solutions built for the demands of each industry.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="oc-industries-grid">

                        <!-- Banking -->
                        <div class="oc-industry-card">
                            <div class="oc-industry-icon">&#127974;</div>
                            <div class="oc-industry-title">Banking &amp; Financial Services</div>
                            <ul class="oc-industry-clients">
                                <li>Retail and commercial banks</li>
                                <li>Investment and corporate banking</li>
                                <li>Digital and challenger banks</li>
                            </ul>
                            <div class="oc-use-cases">
                                <div class="oc-use-cases-label">Use Cases</div>
                                <p>KYC/AML, transaction monitoring, fraud prevention, regulatory compliance, combined assurance</p>
                            </div>
                        </div>

                        <!-- Fintech -->
                        <div class="oc-industry-card">
                            <div class="oc-industry-icon">&#128179;</div>
                            <div class="oc-industry-title">Fintech &amp; Payments</div>
                            <ul class="oc-industry-clients">
                                <li>Payment service providers</li>
                                <li>Digital wallets and neobanks</li>
                                <li>Crypto and digital asset platforms</li>
                            </ul>
                            <div class="oc-use-cases">
                                <div class="oc-use-cases-label">Use Cases</div>
                                <p>Real-time onboarding, fraud detection, sanctions screening, scalable compliance infrastructure</p>
                            </div>
                        </div>

                        <!-- Healthcare -->
                        <div class="oc-industry-card">
                            <div class="oc-industry-icon">&#127973;</div>
                            <div class="oc-industry-title">Healthcare &amp; Public Sector</div>
                            <ul class="oc-industry-clients">
                                <li>NHS and healthcare providers</li>
                                <li>Government agencies</li>
                                <li>Public sector organisations</li>
                            </ul>
                            <div class="oc-use-cases">
                                <div class="oc-use-cases-label">Use Cases</div>
                                <p>Workforce vetting, identity assurance, fraud prevention, governance and compliance monitoring</p>
                            </div>
                        </div>

                        <!-- Energy -->
                        <div class="oc-industry-card">
                            <div class="oc-industry-icon">&#128738;</div>
                            <div class="oc-industry-title">Energy, Oil &amp; Gas</div>
                            <ul class="oc-industry-clients">
                                <li>Multinational operators</li>
                                <li>Supply chain ecosystems</li>
                            </ul>
                            <div class="oc-use-cases">
                                <div class="oc-use-cases-label">Use Cases</div>
                                <p>Third-party due diligence, anti-bribery and corruption (ABC), supply chain risk monitoring</p>
                            </div>
                        </div>

                        <!-- Aviation -->
                        <div class="oc-industry-card">
                            <div class="oc-industry-icon">&#9992;&#65039;</div>
                            <div class="oc-industry-title">Aviation &amp; Transport</div>
                            <ul class="oc-industry-clients">
                                <li>Airlines and logistics companies</li>
                            </ul>
                            <div class="oc-use-cases">
                                <div class="oc-use-cases-label">Use Cases</div>
                                <p>Employee screening, operational risk monitoring, regulatory compliance</p>
                            </div>
                        </div>

                        <!-- Corporate -->
                        <div class="oc-industry-card">
                            <div class="oc-industry-icon">&#127970;</div>
                            <div class="oc-industry-title">Corporate &amp; Enterprise</div>
                            <ul class="oc-industry-clients">
                                <li>Multinationals across sectors</li>
                            </ul>
                            <div class="oc-use-cases">
                                <div class="oc-use-cases-label">Use Cases</div>
                                <p>Enterprise risk management, workforce integrity, vendor due diligence, combined assurance</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════ TECHNOLOGY ══════════ -->
    <section class="oc-section oc-section-alt" id="technology">
        <div class="container">

            <div class="oc-tech-hero">
                <span class="oc-section-label">Technology</span>
                <h2 class="oc-heading">Built for Scale, Security,<br>and Intelligence</h2>
                <p>Oysterchecks is powered by a modern, scalable architecture designed to integrate seamlessly into complex enterprise environments — enabling real-time intelligence at every layer.</p>
            </div>

            <div class="oc-tech-grid">

                <!-- API-First -->
                <div class="oc-tech-card">
                    <div class="oc-tech-card-icon teal">&#128279;</div>
                    <div class="oc-tech-card-title">API-First Architecture</div>
                    <ul class="oc-tech-features">
                        <li>Easy integration with ERP, HR, CRM, and compliance systems</li>
                        <li>Real-time and batch processing capabilities</li>
                        <li>Flexible deployment — cloud or hybrid</li>
                    </ul>
                </div>

                <!-- Cloud -->
                <div class="oc-tech-card">
                    <div class="oc-tech-card-icon navy">&#9729;&#65039;</div>
                    <div class="oc-tech-card-title">Cloud-Native Infrastructure</div>
                    <ul class="oc-tech-features">
                        <li>Built on AWS and Microsoft Azure</li>
                        <li>High availability and scalability</li>
                        <li>Secure data storage and processing</li>
                        <li>Global deployment capability</li>
                    </ul>
                </div>

                <!-- AI -->
                <div class="oc-tech-card">
                    <div class="oc-tech-card-icon gold">&#129302;</div>
                    <div class="oc-tech-card-title">AI &amp; Advanced Analytics</div>
                    <ul class="oc-tech-features">
                        <li>Behavioural analytics and anomaly detection</li>
                        <li>Risk scoring models</li>
                        <li>Pattern recognition for fraud and financial crime</li>
                        <li>Intelligent alerting and prioritisation</li>
                    </ul>
                </div>

            </div>

            <div class="oc-tech-grid-bottom">

                <!-- Security -->
                <div class="oc-tech-card">
                    <div class="oc-tech-card-icon teal">&#128272;</div>
                    <div class="oc-tech-card-title">Security &amp; Compliance by Design</div>
                    <ul class="oc-tech-features">
                        <li>End-to-end encryption</li>
                        <li>Role-based access control</li>
                        <li>Audit trails and logging</li>
                        <li>Compliance with global data protection standards</li>
                    </ul>
                </div>

                <!-- Global Data -->
                <div class="oc-tech-card">
                    <div class="oc-tech-card-icon navy">&#127758;</div>
                    <div class="oc-tech-card-title">Global Data Intelligence</div>
                    <ul class="oc-tech-features">
                        <li>Access to international databases and watchlists</li>
                        <li>Cross-border verification capabilities</li>
                        <li>Multi-jurisdiction compliance support</li>
                    </ul>
                </div>

            </div>

            <!-- Data Assurance Banner -->
            <div class="oc-assurance-banner">
                <div>
                    <span class="oc-section-label">Data-Driven Assurance Layer</span>
                    <h2 class="oc-heading">Beyond Verification.<br>Into Assurance.</h2>
                    <p>What differentiates Oysterchecks is its ability to evolve beyond point-in-time verification into continuous, independent assurance — giving organisations real-time confidence in their risk and compliance posture.</p>
                </div>
                <div class="oc-assurance-items">
                    <div class="oc-assurance-item">
                        <div class="oc-assurance-item-icon">&#128257;</div>
                        <div>
                            <div class="oc-assurance-item-title">Continuous Data Ingestion</div>
                            <p class="oc-assurance-item-desc">Ongoing data feeds from multiple systems — never relying on stale snapshots.</p>
                        </div>
                    </div>
                    <div class="oc-assurance-item">
                        <div class="oc-assurance-item-icon">&#9989;</div>
                        <div>
                            <div class="oc-assurance-item-title">Independent Validation of Controls</div>
                            <p class="oc-assurance-item-desc">Automated, independent checks that validate your risk controls are working as intended.</p>
                        </div>
                    </div>
                    <div class="oc-assurance-item">
                        <div class="oc-assurance-item-icon">&#128200;</div>
                        <div>
                            <div class="oc-assurance-item-title">Real-Time Risk Visibility</div>
                            <p class="oc-assurance-item-desc">Live visibility into your risk and compliance posture — across every operation, at all times.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

</div>

@endsection