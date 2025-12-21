@extends('layouts.landing')

@section('content')

<div class="main">
    <!--hero section start-->
    <section class="hero-equal-height pt-165 pb-100" style="background: url('{{asset('/landing_assets/img/bg-shape.svg')}}')no-repeat bottom center / cover; margin-bottom:-200px">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 col-lg-6">
                    <div class="hero-slider-content text-white">
                        <span class="text-uppercase" style="color:#fff">AI-Driven Risk Intelligence</span>
                        <h1 class="text-white">Instant Verification. Intelligent Decisions.</h1>
                        <p class="lead">
                            OysterChecks is the smart backbone for trust, compliance and risk.<br>
                            Plug into your existing systems and activate real-time verification,
                            monitoring and automated decision intelligence.
                        </p>
                        <div class="action-btns mt-3">
                            <a href="{{route('login')}}" class="btn secondary-solid-btn">Get Started Now</a>
                        </div>
                    </div>
                </div> 
                <div class="col-md-12 col-lg-6">
                    <div class="img-wrap counter-number d-inline-flex align-items-center mb-4">
                        <img src="{{asset('/landing_assets/fontpage.png')}}" alt="hero image" class="custom-width img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--hero section end-->

    <!--promo section start-->
    <section class="promo-block ptb-100">
        <div class="container">
            <div class="row no-gutters">
                <div class="col-md-6 col-lg-6 box-1">
                    <div class="single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <span class="fab fa-superpowers icon-md color-primary"></span>
                        </div>
                        <div class="promo-block-content">
                            <h5>Trust & Compliance Without Friction</h5>
                            <p>
                                OysterChecks instantly adds identity verification, background screening,
                                risk scoring and continuous monitoring into your existing workflows.
                                No disruption. No rebuilding systems. Just smarter decisions at scale.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 box-2">
                    <div class="single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <span class="far fa-clock icon-md color-primary"></span>
                        </div>
                        <div class="promo-block-content">
                            <h5>AI-Led Risk & Fraud Intelligence</h5>
                            <p>
                                From KYC, KYB and AML to behavioural analytics and transaction monitoring,
                                our AI detects anomalies, flags risks and automates decisions in real time —
                                with full audit trails and regulatory alignment.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--promo section end-->

    <!--about us section start-->
    <section class="about-us-section gray-light-bg p-3">
        <div class="container">
            <div class="row justify-content-around">
                <div class="col-md-12 col-lg-6">
                    <div class="about-img-wrap">
                        <img src="{{asset('/landing_assets/fontpage2.png')}}" width="400px" alt="about image" class="img-fluid rounded shadow-sm">
                    </div>
                </div>
                <div class="col-md-12 col-lg-5">
                    <div class="about-content-right mb-md-4 mb-lg-0">
                        <h2>Do You Really Know Who You’re Dealing With?</h2>
                        <p>
                            Verifying people, businesses and transactions at scale is complex.
                            OysterChecks combines global data, AI intelligence and automated workflows
                            to deliver fast, accurate and compliant verification — continuously.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--about us section end-->

    <!--feature section start-->
    <section class="about-us-section ptb-100" style="background:#fff no-repeat center center / cover">
        <div class="container">
            <div class="row justify-content-around">
                <div class="col-md-12 col-lg-6">
                    <div class="about-img-wrap">
                        <img src="{{asset('/landing_assets/fontpage2.png')}}" alt="features image" class="img-fluid rounded shadow-sm">
                    </div>
                </div>
                <div class="col-md-12 col-lg-5">
                    <div class="about-content-right mb-md-4 mb-lg-0">
                        <h2>Built for Any Industry. Ready for Every Regulation.</h2>
                        <p>
                            A single, unified platform delivering verification, monitoring and
                            decision intelligence — customised for regulated sectors and deployed
                            across countries, compliance frameworks and operational needs.
                        </p>
                        <ul class="list-unstyled tech-feature-list">
                            <li class="py-1"><span class="ti-control-forward mr-2 color-secondary"></span>Banking, Fintech & Payments</li>
                            <li class="py-1"><span class="ti-control-forward mr-2 color-secondary"></span>HR, Recruitment & Staffing</li>
                            <li class="py-1"><span class="ti-control-forward mr-2 color-secondary"></span>Healthcare & Social Care</li>
                            <li class="py-1"><span class="ti-control-forward mr-2 color-secondary"></span>Real Estate & Tenancy Services</li>
                            <li class="py-1"><span class="ti-control-forward mr-2 color-secondary"></span>Government & Public Sector</li>
                            <li class="py-1"><span class="ti-control-forward mr-2 color-secondary"></span>Security, Defence & Aviation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--feature section end-->

    <!--counter section start-->
    <section class="call-to-action" style="background: url('{{asset('/landing_assets/img/cta-bg.svg')}}')no-repeat center center / cover">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-12">
                    <div class="call-to-action-content text-white text-center mb-4">
                        <h2 class="text-white mb-1">Why Organisations Choose OysterChecks</h2>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-3">
                    <div class="single-counter rounded p-4 text-center text-white">
                        <h4 class="text-white">Instant Decisions</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="single-counter rounded p-4 text-center text-white">
                        <h4 class="text-white">Real-Time Intelligence</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="single-counter rounded p-4 text-center text-white">
                        <h4 class="text-white">Continuous Monitoring</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="single-counter rounded p-4 text-center text-white">
                        <h4 class="text-white">Seamless Integration</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--counter section end-->

    <!--work process section start-->
    <section class="work-process-new ptb-100 gray-light-bg">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-lg-8">
                    <div class="section-heading mb-5">
                        <h2>What the Platform Delivers</h2>
                        <p class="lead">End-to-end verification, monitoring and decision intelligence</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="work-process-wrap">
                        <div class="single-work-process">
                            <div class="work-process-icon-wrap primary-bg rounded">
                                <i class="ti-vector icon-md text-white"></i>
                            </div>
                            <div class="work-process-content mt-4">
                                <h5>Instant Identity Authentication</h5>
                                <p>AI-enabled biometric, document and liveness checks in real time</p>
                            </div>
                        </div>
                        <div class="single-work-process">
                            <div class="work-process-icon-wrap primary-bg rounded">
                                <i class="ti-layout-list-thumb icon-md text-white"></i>
                            </div>
                            <div class="work-process-content mt-4">
                                <h5>Global Background Screening</h5>
                                <p>Criminal, sanctions, adverse media, employment and education checks</p>
                            </div>
                        </div>
                        <div class="single-work-process">
                            <div class="work-process-icon-wrap primary-bg rounded">
                                <i class="ti-palette icon-md text-white"></i>
                            </div>
                            <div class="work-process-content mt-4">
                                <h5>Business & Regulatory Verification</h5>
                                <p>KYC, KYB, AML, PEP, CAC, TIN and compliance screening</p>
                            </div>
                        </div>
                        <div class="single-work-process">
                            <div class="work-process-icon-wrap primary-bg rounded">
                                <i class="ti-cup icon-md text-white"></i>
                            </div>
                            <div class="work-process-content mt-4">
                                <h5>Continuous Risk Monitoring</h5>
                                <p>Ongoing monitoring of people, vendors, partners and transactions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--work process section end-->

    <!--why us section start-->
    <section class="about-us-section ptb-100 border-bottom">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-md-12 col-lg-8">
                    <div class="video-promo-content mb-md-4 mb-lg-0">
                        <h3>Why OysterChecks</h3>
                        <ul class="list-unstyled tech-feature-list">
                            <li class="py-1"><span class="ti-check-box mr-2 color-secondary"></span>Built within THE MORGANS CONSORTIUM ecosystem</li>
                            <li class="py-1"><span class="ti-check-box mr-2 color-secondary"></span>API-first, easily integrates with existing systems</li>
                            <li class="py-1"><span class="ti-check-box mr-2 color-secondary"></span>Custom workflows for regulated industries</li>
                            <li class="py-1"><span class="ti-check-box mr-2 color-secondary"></span>Enterprise-grade security and role-based access</li>
                            <li class="py-1"><span class="ti-check-box mr-2 color-secondary"></span>GDPR, ISO27001 and regional compliance ready</li>
                            <li class="py-1"><span class="ti-check-box mr-2 color-secondary"></span>Global data reach with local regulatory alignment</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4">
                    <img src="{{asset('/landing_assets/fontpage2.png')}}" alt="why us image" class="img-fluid rounded shadow-sm">
                </div>
            </div>
        </div>
    </section>
    <!--why us section end-->

    <!--call to action section start-->
    <section class="call-to-action py-5">
        <div class="container">
            <div class="row justify-content-around align-items-center">
                <div class="col-md-7">
                    <div class="subscribe-content">
                        <h3 class="mb-1">Don’t Have an Account?</h3>
                        <p>Our team will onboard you quickly with a setup tailored to your business.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="action-btn text-lg-right text-sm-left">
                        <a href="#" class="btn secondary-solid-btn">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--call to action section end-->

</div>

@endsection

{{-- <section class="pricing-section ptb-100 gray-light-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="section-heading text-center mb-5">
                        <h2>Our Pricing</h2>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md">
                    <div class="card text-center single-pricing-pack">
                        <div class="pt-4"><h5>BS7858 Vetting</h5></div>
                       
                        <div class="card-header py-4 border-0 pricing-header" style="color: #000">
                            <div class="h1 text-center mb-0">₦<span class="price font-weight-bolder" >150,000</span></div>
                            
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled text-sm mb-4 .pricing-feature-para" style="text-align: left;">
                                <li>Description of the tier list will go here, copy should be concise and impactful.</li>
                            </ul>
                            
                            <ul class="list-unstyled text-sm mb-4 pricing-feature-list">
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Amazing feature one.</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Wonderful feature two</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Priceless feature three</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Splended feature four</li>
                            </ul>
                            <a href="{{route('register')}}" class="btn outline-btn mb-3" target="_blank">Try for free</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md">
                    <div class="card popular-price text-center single-pricing-pack">
                        <div class="pt-4"><h5>BPSS clearance</h5></div>
                        
                        <div class="card-header py-4 border-0 pricing-header" style="color: #000">
                            <div class="h1 text-center mb-0">₦<span class="price font-weight-bolder">250,000</span><sub style="color:#1A1A1A; font-size:12px; line-height:16.8px">/ month</sub></div>


                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled text-sm mb-4 .pricing-feature-para" style="text-align: left;">
                                <li>Description of the tier list will go here, copy should be concise and impactful.</li>
                            </ul>
                            <hr>
                           <p style="text-align: left">Everything in the Free plan, plus</p>
                           
                            <ul class="list-unstyled text-sm mb-4 pricing-feature-list">
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Amazing feature one.</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Wonderful feature two</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Priceless feature three</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Splended feature four</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Delightful feature five</li>
                            </ul>
                            <a href="{{route('register')}}" class="btn secondary-solid-btn mb-3" style="border-radius: 5px;" target="_blank">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md">
                    <div class="card text-center single-pricing-pack">
                        <div class="pt-4"><h5>Employment screening</h5></div>
                        <div class="card-header py-4 border-0 pricing-header" style="color: #000">
                            <div class="h1 text-center mb-0"><span class="price font-weight-bolder">Custom</span><sub style="color:#1A1A1A; font-size:12px; line-height:16.8px">yearly billing only</sub></div>

                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled text-sm mb-4 .pricing-feature-para" style="text-align: left;">
                                <li>Description of the tier list will go here, copy should be concise and impactful.</li>
                            </ul>
                            <hr>
                           <p style="text-align: left">Everything in the Pro plan, plus</p>
                           
                            <ul class="list-unstyled text-sm mb-4 pricing-feature-list">
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Amazing feature one.</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Wonderful feature two</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Priceless feature three</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Splended feature four</li>
                                <li style="d-flex flex-row"><img src="{{asset('/landing_assets/img/Check_icon.png')}}" style="width:18px; height:18px; padding-top:3px" > Delightful feature five</li>
                            </ul>
                            <a href="{{route('contact-us')}}" class="btn outline-btn mb-3" target="_blank">Contact Sales</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}
    