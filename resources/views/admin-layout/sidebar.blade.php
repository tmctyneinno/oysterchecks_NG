<body class="dark-sidenav navy-sidenav">
    <!-- Left Sidenav -->
    <div class="left-sidenav">
        <!-- LOGO -->
        <div class="brand">
            <a href="{{route('index')}}" class="logo">
                <span>
                    <img src="{{asset('/assets/images/logo.png')}}"  width="150px" alt="logo-large" class="logo-light"> 
                </span>  
            </a>
        </div>
        <div class="menu-content h-100" data-simplebar>
            <ul class="metismenu left-sidenav-menu">
                <li class="menu-label mt-0">Main</li>
            
                <li>
                    <a href="{{route('admin.index')}}"> <i data-feather="home" class="align-self-center menu-icon"></i><span>Dashboard</span></a>
                    
                </li>
                <hr class="hr-dashed hr-menu">
                
                <li class="menu-label my-2">Candidate Onboarding</li>
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="user-plus" class="align-self-center menu-icon"></i>
                        <span>Candidate Onboarding</span>
                        <span class="menu-arrow">
                            <i class="mdi mdi-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                         <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.candidate.index')}}">
                                <i class="ti-control-record"></i> Onboard Candidate
                            </a>
                        </li>
                    </ul>
                </li>
                <hr class="hr-dashed hr-menu">
                <li class="menu-label my-2">Verifications</li>
                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="user" class="align-self-center menu-icon"></i>
                        <span>Identity Verification</span>
                        <span class="menu-arrow">
                            <i class="mdi mdi-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        @foreach($sidebar as $menu)
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.verify',$menu->slug)}}">
                                <i class="ti-control-record"></i>
                                {{strlen($menu->slug) == 3 ? strtoupper($menu->slug) : ucwords(str_replace('-',' ', $menu->slug))}} Verification
                            </a>
                        </li>
                         @endforeach
                    </ul>
                </li> 
                 <li>
                    <a href="javascript: void(0);"><i data-feather="layers" class="align-self-center menu-icon"></i><span>Business Verifications</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                    <ul class="nav-second-level" aria-expanded="false">
                    @foreach($business as $biz )
                         <li class="nav-item"><a class="nav-link" href="{{route('admin.businessIndex',$biz->slug)}}"><i class="ti-control-record"></i>{{$biz->name}}</a></li>
                       @endforeach
                    </ul>
                </li> 
                     <li>
                    <a href="javascript: void(0);"><i data-feather="map-pin" class="align-self-center menu-icon"></i><span>Address Verification</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                    <ul class="nav-second-level" aria-expanded="false">
                         <li class="nav-item"><a class="nav-link" href="{{route('admin.addressIndex',$address[0]->slug)}}"><i class="ti-control-record"></i>Manage Candidate</a></li>
                    </ul>
                </li> 
                <hr class="hr-dashed hr-menu">
                <li class="menu-label my-2">AML Verification</li>
                @foreach ($aml as $amls)
                <li>
                    <a href="{{route('admin.aml_'.$amls->slug,$amls->slug)}}">
                        <i data-feather="search" class="align-self-center menu-icon"></i>
                        <span>{{$amls->name}}   </span>
                    </a>
                </li>   
                @endforeach 
                 <hr class="hr-dashed hr-menu">
                <li class="menu-label my-2">Administrative Task</li>
                <li>
                    <a href="{{route('admin.report')}}">
                        <i data-feather="trending-up" class="align-self-center menu-icon"></i>
                        <span>Audit Reports</span>
                    </a>
                </li> 
                <li>
                    <a href="{{route('admin.profile')}}">
                        <i data-feather="settings" class="align-self-center menu-icon"></i>
                        <span>Settings</span>
                    </a>
                </li> 
                <li>
                    <a href="">
                        <i data-feather="activity" class="align-self-center menu-icon"></i>
                        <span>Activity Log</span>
                    </a>
                </li>  
                 <li>
                    <a href="">
                        <i data-feather="credit-card" class="align-self-center menu-icon"></i>
                        <span>Billing</span>
                    </a>
                </li>      
            </ul>
        </div>
    </div>
    <!-- end left-sidenav-->