@extends('layouts.landing')

@section('content')

<div class="main">

    <!--header section start-->
    <section class="hero-section ptb-100 gradient-overlay"
             style="background: url('{{asset('/landing_assets/img/header-bg-5.jpg')}}')no-repeat center center / cover">
        <div class="hero-bottom-shape-two" style="background: url('img/hero-bottom-shape.svg')no-repeat bottom center"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="page-header-content text-white text-center pt-sm-5 pt-md-5 pt-lg-0">
                        <h1 class="text-white mb-0">Who We Are</h1>
                        <div class="custom-breadcrumb">
                            <ol class="breadcrumb d-inline-block bg-transparent list-inline py-0">
                                <li class="list-inline-item breadcrumb-item"><a href="#">Home</a></li>
                                <li class="list-inline-item breadcrumb-item"><a href="#">About Us</a></li>
                                <li class="list-inline-item breadcrumb-item active">Who We Are</li>
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
            <div class="row justify-content-between align-items-center">
                <div class="col-lg-4">
                    <div class="video-promo-content mb-md-4 mb-lg-0">
                        <h2><img src="{{asset('/landing_assets/img/who.png')}}" alt="Who We Are"></h2>
                    </div>
                </div>
                <div class="col-md-12 col-lg-8">
                    <div class="video-promo-content mb-md-4 mb-lg-0">
                        <h2>Who We Are</h2>
                        <p>Oysterchecks is a global risk intelligence and assurance technology company, empowering organisations to build trust, strengthen compliance, and make confident, informed decisions.</p>
                        <p>We operate at the critical intersection of identity, risk, compliance, and financial crime prevention. Our platform delivers real-time intelligence that enables organisations to verify, monitor, and manage risk across customers, employees, and transactions seamlessly.</p>
                        <p>In an era of increasingly complex, fast-moving, and cross-border risk, conventional compliance approaches are no longer sufficient. Oysterchecks was built to change that—providing modern, automated solutions that keep pace with today's evolving threat landscape.</p>
                        <p>Trading as The Morgans Consortium Consulting Limited (TMC), a privately registered company (Registration No. 09045481), we specialise in delivering comprehensive automated Background Screening, KYC, Transaction Monitoring, and AML solutions on a global scale. As one of the UK's fastest-growing companies in end-to-end recruitment and risk management solutions, we bring everything together on a single, unified platform.</p>
                        <p>Our services are both extensive and secure, designed to help you manage potential risks and enhance your recruitment and compliance processes. We are a team of dedicated professionals committed to walking with you throughout your journey. We begin by gaining a deep understanding of your business needs and the specific risk factors you face, then deliver tailor-made solutions aligned to your unique requirements.</p>
                        <p>Built on modern technology, our platform connects to thousands of databases worldwide, enabling truly international background screening. Our KYC and AML checks help safeguard your business against financial crime while protecting you from the consequences of regulatory non-compliance.</p>
                        <p>We hold a significant market presence in regions particularly exposed to financial crime, including South Florida, USA, where we have consistently delivered unbeatable service to businesses and organisations. With an undisputed track record spanning many years, we take pride in being a trusted partner in the fight against financial crime and in helping organisations achieve full regulatory compliance. We invite you to be part of a life-changing story—where integrity meets intelligence.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--about us section end-->

</div>

@endsection