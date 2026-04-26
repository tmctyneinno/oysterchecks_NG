@extends('layouts.landing')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap');

    .oc-body {
        font-family: 'DM Sans', sans-serif;
    }

    .oc-hero-label {
        display: block;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #5DCAA5;
        margin-bottom: 16px;
    }

    .oc-hero-title {
        font-family: 'Playfair Display', serif;
        font-size: 52px;
        font-weight: 600;
        color: #fff;
        line-height: 1.15;
        margin: 0 0 20px;
    }

    .oc-hero-sub {
        font-size: 16px;
        font-weight: 300;
        color: rgba(255,255,255,0.6);
        line-height: 1.75;
        max-width: 480px;
        margin: 0 auto 28px;
    }

    .oc-section-heading {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        font-weight: 600;
        color: #1a1a2e;
        line-height: 1.2;
        margin: 0 0 24px;
    }

    .oc-lead {
        font-size: 17px;
        font-weight: 400;
        color: #2d3748;
        line-height: 1.85;
        margin-bottom: 20px;
    }

    .oc-body-text {
        font-size: 15px;
        line-height: 1.85;
        color: #4a5568;
        margin-bottom: 20px;
    }

    .oc-highlight-strip {
        margin: 32px 0;
        padding: 22px 28px;
        border-left: 3px solid #1D9E75;
        background: #f0faf6;
        border-radius: 0 10px 10px 0;
    }

    .oc-highlight-strip p {
        font-size: 16px;
        font-style: italic;
        color: #085041;
        margin: 0;
        line-height: 1.75;
    }

    .oc-stat-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .oc-stat-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px 22px;
    }

    .oc-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        margin-bottom: 12px;
    }

    .oc-stat-icon.blue  { background: #E6F1FB; }
    .oc-stat-icon.teal  { background: #E1F5EE; }
    .oc-stat-icon.amber { background: #FAEEDA; }

    .oc-stat-title {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0 0 4px;
    }

    .oc-stat-desc {
        font-size: 12px;
        color: #718096;
        margin: 0;
        line-height: 1.5;
    }
</style>

<div class="main oc-body">

    <!--header section start-->
    <section class="hero-section ptb-100 gradient-overlay"
             style="background: linear-gradient(135deg, #0C447C 0%, #042C53 55%, #04342C 100%) no-repeat center center / cover">
        <div class="hero-bottom-shape-two" style="background: url('img/hero-bottom-shape.svg') no-repeat bottom center"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="page-header-content text-white text-center pt-sm-5 pt-md-5 pt-lg-0">
                        <span class="oc-hero-label">Global Risk Intelligence</span>
                        <h1 class="oc-hero-title">Who We Are</h1>
                        <p class="oc-hero-sub">Helping organisations build trust, strengthen compliance, and make better decisions — with confidence.</p>
                        <div class="custom-breadcrumb">
                            <ol class="breadcrumb d-inline-block bg-transparent list-inline py-0">
                                <li class="list-inline-item breadcrumb-item"><a href="#">Home</a></li>
                                <li class="list-inline-item breadcrumb-item"><a href="#">About us</a></li>
                                <li class="list-inline-item breadcrumb-item active">Who we are</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--header section end-->

    <!--about us section start-->
    <section class="about-us-section ptb-100">
        <div class="container">
            <div class="row justify-content-between align-items-start">

                <!-- Sidebar -->
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="oc-stat-grid">
                        <div class="oc-stat-card">
                            <div class="oc-stat-icon blue">&#128274;</div>
                            <p class="oc-stat-title">Identity &amp; Risk</p>
                            <p class="oc-stat-desc">Verify and monitor risk across customers, employees, and transactions in real time.</p>
                        </div>
                        <div class="oc-stat-card">
                            <div class="oc-stat-icon teal">&#9878;</div>
                            <p class="oc-stat-title">Compliance Intelligence</p>
                            <p class="oc-stat-desc">Strengthen compliance frameworks with intelligence built for a fast-moving world.</p>
                        </div>
                        <div class="oc-stat-card">
                            <div class="oc-stat-icon amber">&#127758;</div>
                            <p class="oc-stat-title">Financial Crime Prevention</p>
                            <p class="oc-stat-desc">Cross-border intelligence that stops financial crime before it reaches your business.</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-12 col-lg-7">

                    <h2 class="oc-section-heading">Who We Are</h2>

                    <p class="oc-lead">
                        Oysterchecks is a global risk intelligence and assurance technology company helping organisations build trust, strengthen compliance, and make better decisions with confidence.
                    </p>

                    <p class="oc-body-text">
                        We sit at the intersection of identity, risk, compliance, and financial crime prevention, providing real-time intelligence that enables organisations to verify, monitor, and manage risk across customers, employees, and transactions.
                    </p>

                    <div class="oc-highlight-strip">
                        <p>"In a world where risk is increasingly complex, fast-moving, and cross-border, traditional approaches to compliance are no longer sufficient. Oysterchecks was built to change that."</p>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!--about us section end-->

</div>

@endsection