 @extends('layouts.app')
 @section('style')
 <style>
     .acl-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 12px;
         margin-bottom: 4px;
     }

     .acl-subtitle {
         color: #9ba7ca;
         font-size: 13.5px;
         margin: 0;
     }

     .acl-date-btn {
         border-radius: 50px !important;
         padding: 8px 18px !important;
         font-weight: 500;
         border-width: 1px !important;
     }

     .acl-stat-card {
         border: 1px solid #e3ebf6 !important;
         border-radius: 14px !important;
         box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
         transition: transform .15s ease, box-shadow .15s ease;
         overflow: hidden;
         position: relative;
     }

     .acl-stat-card:hover {
         transform: translateY(-3px);
         box-shadow: 0 10px 24px rgba(29, 44, 72, 0.08);
     }

     .acl-stat-card::before {
         content: "";
         position: absolute;
         left: 0;
         top: 0;
         bottom: 0;
         width: 4px;
         background: var(--acl-accent, #1761fd);
     }

     .acl-stat-label {
         color: #9ba7ca;
         font-size: 12.5px;
         font-weight: 600;
         letter-spacing: .02em;
         text-transform: uppercase;
         margin-bottom: 6px;
     }

     .acl-stat-value {
         font-size: 26px;
         font-weight: 700;
         color: #1d2c48;
         margin: 0;
     }

     .acl-stat-icon {
         width: 48px;
         height: 48px;
         border-radius: 12px;
         display: flex;
         align-items: center;
         justify-content: center;
         background: var(--acl-accent-soft, rgba(23, 97, 253, .12));
         color: var(--acl-accent, #1761fd);
     }

     .acl-cta-card {
         border: none !important;
         border-radius: 14px !important;
         background: linear-gradient(135deg, #eef3fe 0%, #f1f5fa 100%) !important;
     }

     .acl-cta-icon {
         width: 54px;
         height: 54px;
         border-radius: 14px;
         background: rgba(23, 97, 253, .12);
         color: #1761fd;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-bottom: 14px;
     }

     .acl-cta-card .btn-primary {
         border-radius: 50px;
         padding: 10px 24px;
         font-weight: 500;
     }

     .acl-table-card {
         border: 1px solid #e3ebf6 !important;
         border-radius: 14px !important;
         box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
     }

     .acl-table-card .card-header {
         background: transparent;
         border-bottom: 1px solid #eef2f9;
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 10px;
         padding: 18px 22px;
     }

     .acl-table-card .card-title {
         display: flex;
         align-items: center;
         gap: 10px;
         margin: 0;
     }

     .acl-table-card .card-title i {
         color: #1761fd;
     }

     .acl-table-card .btn-info {
         border-radius: 50px;
         padding: 8px 18px;
         font-weight: 500;
         border: none;
     }

     #datatable-buttons thead th {
         background: #f8fafd;
         color: #6c7a99;
         font-size: 12px;
         font-weight: 600;
         text-transform: uppercase;
         letter-spacing: .03em;
         border-bottom: 1px solid #eef2f9 !important;
         white-space: nowrap;
     }

     #datatable-buttons tbody td {
         vertical-align: middle;
         color: #3b4560;
     }

     #datatable-buttons tbody tr:hover {
         background-color: #f8fafd;
     }

     .acl-candidate {
         display: flex;
         align-items: center;
     }

     .acl-avatar {
         width: 34px;
         height: 34px;
         min-width: 34px;
         border-radius: 50%;
         background: rgba(23, 97, 253, .12);
         color: #1761fd;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         font-size: 12.5px;
         font-weight: 700;
         margin-right: 10px;
     }

     .acl-candidate-name {
         font-weight: 600;
         color: #1d2c48;
         font-size: 13.5px;
     }

     .badge.badge-soft-success,
     .badge.badge-soft-info,
     .badge.badge-soft-primary,
     .badge.badge-soft-secondary,
     .badge.badge-soft-warning,
     .badge.badge-soft-danger {
         border-radius: 50px;
         padding: 6px 14px;
         font-weight: 600;
         font-size: 11px;
         letter-spacing: .02em;
     }

     .acl-action-toggle {
         width: 34px;
         height: 34px;
         border-radius: 8px;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         border: 1px solid #e3ebf6;
         color: #6c7a99;
         transition: background .15s ease, color .15s ease;
     }

     .acl-action-toggle:hover {
         background: #f1f5fa;
         color: #1761fd;
     }

     .acl-table-card .dropdown-menu {
         border: 1px solid #e3ebf6;
         border-radius: 10px;
         box-shadow: 0 6px 20px rgba(29, 44, 72, .1);
         padding: 6px;
         min-width: 230px;
     }

     .acl-table-card .dropdown-item {
         border-radius: 8px !important;
         padding: 8px 12px;
         font-size: 13px;
         font-weight: 500;
         color: #1d2c48;
         border-bottom: none !important;
     }

     .acl-table-card .dropdown-item:hover {
         background: rgba(23, 97, 253, .08);
         color: #1761fd;
     }
 </style>
 @endsection
 @section('content')
 <div class="page-content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-sm-12">
                 <div class="page-title-box">
                     <div class="acl-header">
                         <div>
                             <h4 class="page-title">Candidate List</h4>
                             <p class="acl-subtitle">Address verification requests grouped by candidate</p>
                         </div>
                         <a href="#" class="btn btn-sm btn-outline-primary acl-date-btn" id="Dash_Date">
                             <span class="ay-name" id="Day_Name">Today:</span>&nbsp;
                             <span class="" id="Select_date">Jan 11</span>
                             <i data-feather="calendar" class="align-self-center icon-xs ms-1"></i>
                         </a>
                     </div>
                 </div>
                 <!--end page-title-box-->
             </div>
             <!--end col-->
         </div>
         <div class="row ">
             <div class="col-lg-12">
                 <div class="row justify-content-left g-3">
                     <div class="col-md-6 col-lg-4 ">
                         <div class="card report-card acl-stat-card" style="--acl-accent:#1761fd; --acl-accent-soft: rgba(23, 97, 253, .12);">
                             <div class="card-body " >
                                 <div class="row d-flex justify-content-center align-items-center">
                                     <div class="col ">
                                         <p class="acl-stat-label">Total Address Verifications</p>
                                         <h3 class="acl-stat-value">{{$all??0}}</h3>
                                     </div>
                                     <div class="col-auto align-self-center">
                                         <div class="acl-stat-icon">
                                             <i data-feather="map-pin" class="align-self-center icon-sm"></i>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <!--end card-body-->
                         </div>
                         <!--end card-->
                     </div>
                     <div class="col-md-6 col-lg-4">
                         <div class="card report-card acl-stat-card" style="--acl-accent:#03d87f; --acl-accent-soft: rgba(3, 216, 127, .15);">
                             <div class="card-body" >
                                 <div class="row d-flex justify-content-center align-items-center">
                                     <div class="col">
                                         <p class="acl-stat-label">Completed Address Verifications</p>
                                         <h3 class="acl-stat-value">{{$completed??0}}</h3>
                                     </div>
                                     <div class="col-auto align-self-center">
                                         <div class="acl-stat-icon">
                                             <i data-feather="check-circle" class="align-self-center icon-sm"></i>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <!--end card-body-->
                         </div>
                         <!--end card-->
                     </div>
                     <div class="col-md-6 col-lg-4">
                         <div class="card report-card acl-stat-card" style="--acl-accent:#12a4ed; --acl-accent-soft: rgba(18, 164, 237, .15);">
                             <div class="card-body" >
                                 <div class="row d-flex justify-content-center align-items-center">
                                     <div class="col">
                                         <p class="acl-stat-label">Verifications in Progress</p>
                                         <h3 class="acl-stat-value">{{$IN_PROGRESS??''}}</h3>
                                     </div>
                                     <div class="col-auto align-self-center">
                                         <div class="acl-stat-icon">
                                             <i data-feather="activity" class="align-self-center icon-sm"></i>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <!--end card-body-->
                         </div>
                         <!--end card-->
                     </div>
                     <!--end col-->
                    <div class="col-md-6 col-lg-4">
                         <div class="card report-card acl-stat-card" style="--acl-accent:#ffb822; --acl-accent-soft: rgba(255, 184, 34, .15);">
                             <div class="card-body" >
                                 <div class="row d-flex justify-content-center align-items-center">
                                     <div class="col">
                                         <p class="acl-stat-label">Requests Pending</p>
                                         <h3 class="acl-stat-value">{{$pending??0}}</h3>
                                     </div>
                                     <div class="col-auto align-self-center">
                                         <div class="acl-stat-icon">
                                             <i data-feather="clock" class="align-self-center icon-sm"></i>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <!--end card-body-->
                         </div>
                         <!--end card-->
                     </div>
                     <!--end col-->
                  <div class="col-md-6 col-lg-4">
                         <div class="card report-card acl-stat-card" style="--acl-accent:#f5325c; --acl-accent-soft: rgba(245, 50, 92, .15);">
                             <div class="card-body" >
                                 <div class="row d-flex justify-content-center align-items-center">
                                     <div class="col">
                                          <p class="acl-stat-label">Address Cancelled or Rejected</p>
                                         <h3 class="acl-stat-value">{{$cancelled??0}}</h3>
                                     </div>
                                     <div class="col-auto align-self-center">
                                         <div class="acl-stat-icon">
                                             <i data-feather="x-circle" class="align-self-center icon-sm"></i>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <!--end card-body-->
                         </div>
                         <!--end card-->
                     </div>
                     <!--end col-->
                       {{--   <div class="col-md-6 col-lg-4">
                         <div class="card report-card">
                             <div class="card-body" >
                                 <div class="row d-flex justify-content-center">
                                     <div class="col">
                                         <p class="text-black mb-0 fw-semibold">Verification not Requested</p>
                                         <h3 class="m-0 text-black">{{$not_requested}}</h3>
                                     </div>
                                     <div class="col-auto align-self-center">
                                         <div class="report-main-icon bg-light-alt">
                                             <i data-feather="users" class="align-self-center text-muted icon-sm"></i>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <!--end card-body-->
                         </div>
                         <!--end card-->
                     </div> --}}
                     <!--end col-->
                     <!--end col-->
                 </div>
                 <!--end row-->
             </div>
         </div>
         <div class="row">
             <div class="col-lg-12">
                 <div class="card mb-3 acl-cta-card">
                     <div class="row">
                         <div class="col-md-6">
                             <div class="card-body">
                                 <div class="acl-cta-icon">
                                     <i data-feather="user-check" class="icon-sm"></i>
                                 </div>
                                 <h5 class="card-title">Verify @if($slug->slug == 'individual_address') an Individual @elseif ($slug->slug == 'reference_address') a Guarantor @else a Business @endif </h5>
                                 <p class="card-text mb-0">Wether you are verifying a business, a guarantor or an individual's address, we provide key insights and overall analysis of any verification request made.</p>
                                 <p class="card-text mb-0"><small class="text-muted">Use the "Create Candidate" button to initiate the {{$slug->name}} process.</small></p>
                             </div>
                         </div>
                         <div class="col-md-6 align-self-center">
                             <div class="card-body d-flex justify-content-lg-end justify-content-center">
                                 <a type="button" class="btn btn-primary " href="{{route('showCreateCandidate', $slug->slug)}}">Create Candidate</a>

                             </div>
                         </div>
                         <!--end col-->
                         <!--end col-->
                     </div>
                     <!--end row-->
                 </div>
                 <!--end card-->
             </div>
         </div>
         <div class="row">
             <div class="col-12">
                 <div class="card acl-table-card">
                     <div class="card-header">
                         <h4 class="card-title"><i data-feather="list" class="icon-sm"></i> {{$slug->name}} log</h4>
                       <a href="{{route('client-generate-report')}}" class="btn btn-info">Generate Address Report</a>
                     </div>

                     <!--end card-header-->
                     <div class="card-body">
                         <table id="datatable-buttons" class="table table-striped dt-responsive nowrap " style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                             <thead>
                                 <tr>
                                     <th>SN</th>
                                     <th>Address Candidate</th>
                                     <th>Reference Id</th>
                                     <th>Address Status</th>
                                     <th>Initiated by</th>

                                     <th>Date Created</th>
                                     <th>Action</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach ($verifications as $transaction)
                                 <tr>
                                     <td>{{$loop->iteration}}</td>
                                     <td>
                                         <div class="acl-candidate">
                                             <span class="acl-avatar">{{strtoupper(substr($transaction->first_name, 0, 1))}}{{strtoupper(substr($transaction->last_name, 0, 1))}}</span>
                                             <span class="acl-candidate-name">{{$transaction->first_name}} {{$transaction->last_name}}</span>
                                         </div>
                                     </td>
                                     <td>{{$transaction->service_reference}}</td>
                                        <td>
                                             @if($transaction->addressVerificationDetail()->exists())
                                         @php
                                            $details = $transaction->addressVerificationDetail;
                                            $total = $details->count();
                                            $completed = $details->where('status', 'COMPLETED')->count();
                                        @endphp

                                        @if($completed > 0)
                                            <span class="badge badge-soft-success">{{ $completed }}/{{ $total }} Completed</span>
                                         @elseif($transaction->addressVerificationDetail->first()->status == 'IN_PROGRESS')
                                         <span class="badge badge-soft-info"> Verification in Progress</span>
                                         @else
                                         <span class="badge badge-soft-primary">{{$transaction->addressVerificationDetail->first()->status}}</span>
                                         @endif
                                         @else
                                         <span class="badge badge-soft-secondary">No verification Request Yet</span>
                                         @endif
                                            </td>
                                     {{-- <td>
                                         @if($transaction->addressVerificationDetail()->exists())
                                         @if($transaction->addressVerificationDetail->first()->status == 'pending')
                                         <span class="badge badge-soft-purple">Pending</span>
                                         @elseif($transaction->addressVerificationDetail->first()->status == 'completed' && $transaction->addressVerificationDetail->first()->task_status == 'VERIFIED')
                                         <span class="badge badge-soft-success">Completed & Verified</span>
                                         @elseif($transaction->addressVerificationDetail->first()->status == 'awaiting_reschedule')
                                         <span class="badge badge-soft-dark">Awaiting Reschedule</span>
                                         @elseif($transaction->addressVerificationDetail->first()->status == 'completed' && $transaction->addressVerificationDetail->first()->task_status == 'NOT_VERIFIED')
                                         <span class="badge badge-soft-warning">Completed but Not Verified</span>
                                         @elseif($transaction->addressVerificationDetail->first()->status == 'cancelled')
                                         <span class="badge badge-soft-danger"> {{$transaction->addressVerificationDetail->first()->status}}</span>
                                         @elseif($transaction->addressVerificationDetail->first()->status == 'IN_PROGRESS')
                                         <span class="badge badge-soft-info"> Verification in Progress</span>
                                         @endif
                                          <span class="badge badge-soft-purple">Pending</span>
                                         @else
                                         <span class="badge badge-soft-secondary">No verification Request Yet</span>
                                         @endif
                                     </td> --}}
                                     <td>{{$transaction->user->firstname}}</td>
                                     <td>{{$transaction->created_at}}</td>
                                     <td>
                                         <div class="dropdown d-inline-block">
                                             <a class="dropdown-toggle arrow-none acl-action-toggle" id="seeMore" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                                 <i class="fa fa-ellipsis-h font-12"></i>
                                             </a>
                                             <div class="dropdown-menu dropdown-menu-right" aria-labelledby="seeMore" style="">
                                             {{-- <a class="dropdown-item" href="#">Copy Reference Id</a> --}}

                                                 @if($transaction->addressVerificationDetail()->exists())
                                                 <a class="dropdown-item" href="{{route('ViewCandidateAddresses', $transaction->hashid)}}">
                                                     <i data-feather="eye" class="icon-xs me-1"></i> View Verifications
                                                 </a>
                                                 @endif
                                                 <a class="dropdown-item" href="{{route('showVerificationDetailsForm', ['slug' => $slug->slug, 'service_ref' => $transaction->service_reference])}}">
                                                     <i data-feather="plus-circle" class="icon-xs me-1"></i> Make a Verification Request
                                                 </a>
                                                 {{-- @endif --}}

                                             </div>
                                         </div>
                                     </td>
                                 </tr>
                                 @endforeach
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div> <!-- end col -->
         </div>
         @endsection
         @section('script')
         <script>

         </script>

         @endsection
