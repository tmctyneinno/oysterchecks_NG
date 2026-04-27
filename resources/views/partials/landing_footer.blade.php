<!--footer section start-->
<footer class="footer-section">
    <!--footer top start-->
    <div class="footer-top gradient-bg">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <div class="row footer-top-wrap">
                        <div class="col-md-4 col-sm-6">
                            <div class="footer-nav-wrap text-white">
                                <h4 class="text-white">      <img src="{{asset('/landing_assets/img/logo-white.png')}}" width="200px" alt="logo" class="img-fluid"/></h4>
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">We are an award-winning multinational company. We believe in quality and international standard.</a>
                                    </li>
                                    
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="footer-nav-wrap text-white">
                                <h4 class="text-white">Pages</h4>
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{route('terms')}}">Privacy Policy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{route('about-us')}}">About Us</a>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a class="nav-link" href="{{route('terms')}}">Terms of Service</a>
                                    </li> --}}
                                    
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{route('faqs')}}">FAQ</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="footer-nav-wrap text-white">
                                <h4 class="text-white">Quick Links</h4>
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Home</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{route('services')}}">Services</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{route('industry')}}">Industries we serve</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{route('kyc')}}">KYC</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{route('grc-assurance')}}">GRC &amp; Assurance</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="row footer-top-wrap">
                        <div class="col-12">
                            <div class="footer-nav-wrap text-white">
                                <h4 class="text-white">GET IN TOUCH</h4>
                                {{-- <h5 style="color:#fff">Locations</h5> --}}
                                <ul class="get-in-touch-list">
                                    <li class="d-flex align-items-center py-2"><span class="fas fa-map-marker-alt mr-2"></span> 
                                        85 Great Portland Street, 
                                        First Floor, London W1W 7LT,
                                         United Kingdom
                                        </li>
                                        
                                        <li class="d-flex align-items-center py-2"><span class="fas fa-map-marker-alt mr-2"></span> 
                                            Africa:  Nigeria</br> 
                                            2nd Floor, 1 Adeola Adeoye Street,
                                            Off Toyin Street,
                                            Ikeja, Lagos Nigeria
                                           +234- 915-341-4314 </li>
                                        <li class="d-flex align-items-center py-2"><span class="fas fa-map-marker-alt mr-2"></span>
                                            Europe - Ireland</br> 
                                            21 Gillabbey Terrace, Gillabbey Street, 
                                            Cork, T12 KPN4
                                            Republic of Ireland</br> 
                                             +353 877123968
                                             </li>

                                    <li class="d-flex align-items-center py-2"><span class="fas fa-envelope mr-2"></span> 
                                        All enquiries:   </br>  enquiries@oysterchecks.com </br> 
                                        Technical Assistance & Support </br>  support@oysterchecks.com</li>
                                    {{-- <li class="d-flex align-items-center py-2"><span class="fas fa-phone-alt mr-2"></span>UK Number: +447466588324 <br> Nigeria Number: 234(1)700-1770, 
                                    234(0)915-341-4314</li> --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--footer top end-->

    <!--footer copyright start-->
    <div class="footer-bottom gray-light-bg py-2">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-md-5 col-lg-5">
                    <p class="copyright-text pb-0 mb-0">Copyrights © {{date('Y')}}. All
                        Rights reserved</p>
                </div>
                <div class="col-md-7 col-lg-6 d-none d-md-block d-lg-block">
                    <div class="social-nav text-right">
                        <ul class="list-unstyled social-list mb-0">
                            <li class="list-inline-item tooltip-hover">
                                <a href="#" class="rounded"><span class="ti-facebook"></span></a>
                                <div class="tooltip-item">Facebook</div>
                            </li>
                            <li class="list-inline-item tooltip-hover"><a href="#" class="rounded"><span class="ti-twitter"></span></a>
                                <div class="tooltip-item">Twitter</div>
                            </li>
                            <li class="list-inline-item tooltip-hover"><a href="#" class="rounded"><span class="ti-linkedin"></span></a>
                                <div class="tooltip-item">Linkedin</div>
                            </li>
                            <li class="list-inline-item tooltip-hover"><a href="#" class="rounded"><span class="ti-dribbble"></span></a>
                                <div class="tooltip-item">Dribbble</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--footer copyright end-->
</footer>
<!--footer section end-->

<!--bottom to top button start-->
<button class="scroll-top scroll-to-target" data-target="html">
    <span class="ti-angle-up"></span>
</button>
<!--bottom to top button end-->