@extends('layouts.landing')

@section('content')
 

<div class="main">
    <!-- HERO SECTION -->
    <section class="hero-equal-height pt-165 pb-100" style="background: url('{{asset('/landing_assets/img/bg-shape.png')}}')no-repeat center center / cover; margin-bottom:-190px;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 col-lg-6">
                    <div class="hero-slider-content z-index position-relative">
                        <span class="text-white text-uppercase">AI-Driven Risk Intelligence</span>
                        <h1 class="text-white">Instant Verification. Intelligent Decisions.</h1>
                        <p class="text-white">
                          OysterChecks works like a smart backbone for trust and compliance.
                                It plugs into whatever system a business already uses — CRM, HRIS, ERP, onboarding platforms — and instantly adds real-time intelligence, verification, and automated decisioning.

                                No friction. No reinventing the wheel.
                        </p>
                        <div class="action-btns mt-3">
                            <a class="btn secondary-solid-btn" style="font-size: 12px; color:#162E66; background-color:#fff" href="{{ route('login') }}">
                                Get Started Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-6">
                    <div class="hero-animation-img">
                        <img src="{{asset('/landing_assets/fontpage.png')}}" alt="hero" class="custom-width img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- HERO END -->
  

    <!--promo section start-->
    <section class="promo-block ptb-100 ">
        <div class="container " >
            <div class="row no-gutters ">
                <div class="col-md-6 col-lg-4 box-1"  style="height: 100px; border: 2px solid white; border-radius: 10px;">
                    <div   class="d-flex flex-row mb-3 single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <img class="p-2" src="{{asset('/landing_assets/img/bpss.png')}}"/>
                        </div>
                        <div class="p-2 promo-block-content">
                            <h5>Verification & Vetting</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 box-1"  style="height: 100px">
                    <div class="d-flex flex-row mb-3 single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <img class="p-2 icon-md" src="{{asset('/landing_assets/img/es.png')}}"/> 
                        </div>
                        <div class="p-2 promo-block-content">
                            <h5>Real-time & AI-led</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 box-1"  style="height: 100px">
                    <div class="d-flex flex-row mb-3 single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <img class="p-2 icon-md" src="{{asset('/landing_assets/img/bs7858.png')}}"/> 
                        </div>
                        <div class="p-2 promo-block-content">
                            <h5>Transaction Monitoring</h5>
                        </div>
                    </div>
                </div>
                   <div class="col-md-6 col-lg-4 box-1"  style="height: 100px">
                    <div class="d-flex flex-row mb-3 single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <img class="p-2 icon-md" src="{{asset('/landing_assets/img/es.png')}}"/> 
                        </div>
                        <div class="p-2 promo-block-content">
                            <h5>Integration & Customisation</h5>
                        </div>
                    </div>
                </div>
                   <div class="col-md-6 col-lg-4 box-1"  style="height: 100px">
                    <div class="d-flex flex-row mb-3 single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <img class="p-2 icon-md" src="{{asset('/landing_assets/img/bs7858.png')}}"/> 
                        </div>
                        <div class="p-2 promo-block-content">
                            <h5>Employment Checks</h5>
                        </div>
                    </div>
                </div>
                      <div class="col-md-6 col-lg-4 box-1"  style="height: 100px">
                    <div class="d-flex flex-row mb-3 single-promo-block animated-hover p-5 text-center">
                        <div class="promo-block-icon mb-3">
                            <img class="p-2 icon-md" src="{{asset('/landing_assets/img/bpss.png')}}"/> 
                        </div>
                        <div class="p-2 promo-block-content">
                            <h5>Educationals Checks</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--promo section end-->



    
    <section class="about-us-section ptb-10">
        <div class="container">
                   <h2>What the platform delivers</h2>
            <div class="row align-items-center">
                <div class="col-12 mt-2"> <!-- Add mb-3 class for spacing -->
                    <div class="card shadow" style="border: 1px solid #162E66">
                        <div class="card-body">
                          <ul>
                            <li style="list-style:square">
                                Real-time & AI-led
                            </li>
                              <li style="list-style:square">
                                Instant identity authentication
                            </li>
                              <li style="list-style:square">
                            AI-enabled biometric checks (facial, fingerprint, liveness, behavioural)
                            </li>
                          </ul>
                        </div>
                    </div>
                </div>
           

                <div class="col-12 mt-2"> <!-- Add mb-3 class for spacing -->
             <div class="card shadow" style="border: 1px solid #162E66">
        <div class="card-body">
            <ul>
                <li style="list-style:square">Global background screening</li>
                <li style="list-style:square">Criminal, adverse media, compliance and sanctions checks</li>
                <li style="list-style:square">Employment referencing and education verification</li>
                <li style="list-style:square">BPSS, BS7858, right-to-work, right-to-rent</li>
                <li style="list-style:square">Tenancy and landlord referencing</li>
                <li style="list-style:square">KYC, KYB, AML, PEP & sanctions screening</li>
            </ul>
        </div>
    </div>
                </div>

                <div class="col-12 mt-2"> 
    <div class="card shadow" style="border: 1px solid #162E66">
        <div class="card-body">
            <ul>
                <li style="list-style:square">Real-time AML surveillance</li>
                <li style="list-style:square">Alerts, case management and audit trail</li>
                <li style="list-style:square">AI-driven fraud pattern detection</li>
                <li style="list-style:square">Behavioural analytics and thresholds</li>
            </ul>
        </div>
    </div>
</div>
<div class="col-12 mt-2"> 
    <div class="card shadow" style="border: 1px solid #162E66">
        <div class="card-body">
            <ul>
                <li style="list-style:square">API-first architecture</li>
                <li style="list-style:square">Connects seamlessly to CRMs (Salesforce, HubSpot, Zoho etc.)</li>
                <li style="list-style:square">Integrates with HR systems, onboarding tools, financial systems</li>
                <li style="list-style:square">Custom workflows for regulated sectors (banking, fintech, healthcare, security, government)</li>
                <li style="list-style:square">Configurable dashboards, triggers, rules and reporting</li>
                <li style="list-style:square">Enterprise-grade encryption</li>
                <li style="list-style:square">Role-based access controls</li>
                <li style="list-style:square">Scalable system design</li>
                <li style="list-style:square">Cloud, hybrid or on-prem deployments</li>
                <li style="list-style:square">GDPR, ISO27001 and region-specific compliance frameworks</li>
            </ul>
        </div>
    </div>
</div>

            </div>    
        </div>
    </section>
    <script>
        const cardText2 = document.getElementById('card-text2');
        const readMoreLink2 = document.getElementById('read-more-link2');
    
        const maxLength2 = 135; // Set the maximum character length
    
        if (cardText2.textContent.length > maxLength2) {
            const shortenedText2 = cardText2.textContent.substring(0, maxLength2);
            const remainingText2 = cardText2.textContent.substring(maxLength2);
    
            cardText2.innerHTML = shortenedText2 +
                `<span id="more-text2" style="display:none">${remainingText2}</span>` +
                '<a href="#" id="read-more-link2"';
    
            readMoreLink2.addEventListener('click', function (event) {
                event.preventDefault();
                const moreText2 = document.getElementById('more-text2');
                if (moreText2.style.display === 'none' || moreText2.style.display === '') {
                    moreText2.style.display = 'inline';
                    readMoreLink2.textContent = 'Read less';
                } else {
                    moreText2.style.display = 'none';
                    readMoreLink2.textContent = 'Read more';
                }
            });
        }
    </script>
    <script>
        const cardText = document.getElementById('card-text');
        const readMoreLink = document.getElementById('read-more-link');
    
        const maxLength = 135; // Set the maximum character length
    
        if (cardText.textContent.length > maxLength) {
            const shortenedText = cardText.textContent.substring(0, maxLength);
            const remainingText = cardText.textContent.substring(maxLength);
    
            cardText.innerHTML = shortenedText +
                `<span id="more-text" style="display:none">${remainingText}</span>` +
                '<a href="#" id="read-more-link"';
    
            readMoreLink.addEventListener('click', function (event) {
                event.preventDefault();
                const moreText = document.getElementById('more-text');
                if (moreText.style.display === 'none' || moreText.style.display === '') {
                    moreText.style.display = 'inline';
                    readMoreLink.textContent = 'Read less';
                } else {
                    moreText.style.display = 'none';
                    readMoreLink.textContent = 'Read more';
                }
            });
        }
    </script>

    <section class="about-us-section ptb-100 white-light-bg">
        <div class="container">
            
         <div class="row">
    <div class="col-md-12 col-lg-12">
        <div class="feature-tabs-wrap">
            <ul class="nav nav-tabs border-bottom-0 feature-tabs feature-tabs-center d-flex justify-content-center" data-tabs="tabs">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center active" href="#feature-tab-1" data-toggle="tab">
                        <h6 class="m-1">Why organisations choose OysterChecks</h6>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="#feature-tab-2" data-toggle="tab">
                        <h6 class="m-1">Who it’s built for</h6>
                    </a>
                </li>
            </ul>

            <div class="tab-content feature-tab-content">
                <!-- Why Choose Us Tab -->
                <div class="tab-pane active" id="feature-tab-1">
                    <div class="row justify-content-around">
                        <div class="col-md-12 col-lg-5 screening mix">
                            <div class="hero-slider-content" style="font-size: 16px">
                        
                                <ul>
                                    <li>Built within THE MORGANS CONSORTIUM ecosystem of governance and risk expertise</li>
                                    <li>End-to-end verification, monitoring and decision intelligence in one place</li>
                                    <li>Customisable for any industry, country or compliance framework</li>
                                    <li>Easy integration with existing workflows and tech</li>
                                    <li>Global data reach with local regulatory alignment</li>
                                    <li>AI that speeds up decisions without losing accuracy</li>
                                    <li>Suitable for high-stakes environments: finance, aviation, government, healthcare, security</li>
                                </ul>
                                <div class="action-btns mt-3" style="display: flex;">
                                    <a href="{{route('login')}}" class="btn secondary-solid-btn">
                                        Get Started Now <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-1 mix screening"></div>
                        <div class="col-md-12 col-lg-6 mix screening d-flex flex-row-reverse align-items-center">
                            <div class="img-wrap counter-number d-inline-flex align-items-center mb-4">
                                <img src="{{asset('/landing_assets/img/laptop.png')}}" alt="hero single" class="custom-width img-fluid">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Who It’s Built For Tab -->
                <div class="tab-pane" id="feature-tab-2">
                    <div class="row pt-4">
                        <div class="col-md-12 col-lg-6 mix screening d-flex flex-row-reverse align-items-center">
                            <div class="img-wrap counter-number d-inline-flex align-items-center mb-4">
                                <img src="{{asset('/landing_assets/img/laptop.png')}}" alt="hero single" class="custom-width img-fluid">
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-1 mix screening"></div>
                        <div class="col-md-12 col-lg-5 screening mix">
                            <div class="hero-slider-content" style="font-size: 16px">
                             
                                <ul>
                                    <li>Banks, fintech & payment companies</li>
                                    <li>HR, recruitment & staffing agencies</li>
                                    <li>Real estate, landlords & tenancy services</li>
                                    <li>Government & public sector agencies</li>
                                    <li>Healthcare & social care providers</li>
                                    <li>Security & defence contractors</li>
                                    <li>Professional services firms</li>
                                    <li>Any organisation needing trusted verification at scale</li>
                                </ul>
                                <div class="action-btns mt-3" style="display: flex;">
                                    <a href="{{route('login')}}" class="btn secondary-solid-btn">
                                        Get Started Now <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

        </div>
    </section>
</div>

@endsection