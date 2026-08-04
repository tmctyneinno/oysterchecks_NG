
@extends('layouts.app')
@section('style')
<style>
    .dash-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 4px;
    }

    .dash-subtitle {
        color: #9ba7ca;
        font-size: 13.5px;
        margin: 0;
    }

    .dash-date-btn {
        border-radius: 50px !important;
        padding: 8px 18px !important;
        font-weight: 500;
        border-width: 1px !important;
    }

    .dash-stat-card {
        border: 1px solid #e3ebf6 !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
        transition: transform .15s ease, box-shadow .15s ease;
        overflow: hidden;
        position: relative;
    }

    .dash-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(29, 44, 72, 0.08);
    }

    .dash-stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--dash-accent, #1761fd);
    }

    .dash-stat-label {
        color: #9ba7ca;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: .02em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .dash-stat-value {
        font-size: 26px;
        font-weight: 700;
        color: #1d2c48;
        margin: 0;
    }

    .dash-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--dash-accent-soft, rgba(23, 97, 253, .12));
        color: var(--dash-accent, #1761fd);
    }

    .dash-card {
        border: 1px solid #e3ebf6 !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
    }

    .dash-card .card-header {
        background: transparent;
        border-bottom: 1px solid #eef2f9;
        padding: 18px 22px;
    }

    .dash-card .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .dash-card .card-title i {
        color: #1761fd;
    }

    #datatable-buttons thead th,
    .dash-card table thead th {
        background: #f8fafd;
        color: #6c7a99;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
        border-bottom: 1px solid #eef2f9 !important;
        border-top: none !important;
        white-space: nowrap;
    }

    #datatable-buttons tbody td,
    .dash-card table tbody td {
        vertical-align: middle;
        color: #3b4560;
    }

    #datatable-buttons tbody tr:hover,
    .dash-card table tbody tr:hover {
        background-color: #f8fafd;
    }

    .badge.badge-soft-success,
    .badge.badge-soft-warning,
    .badge.badge-soft-danger,
    .badge.badge-soft-purple {
        border-radius: 50px;
        padding: 6px 14px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: .02em;
    }

    .dash-activity {
        position: relative;
        padding-left: 6px;
    }

    .dash-activity .activity-info {
        display: flex;
        gap: 12px;
        position: relative;
        padding-bottom: 22px;
    }

    .dash-activity .activity-info:not(:last-child)::before {
        content: "";
        position: absolute;
        left: 15px;
        top: 32px;
        bottom: 0;
        width: 1px;
        background: #eef2f9;
    }

    .dash-activity .icon-info-activity {
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 50%;
        background: rgba(23, 97, 253, .12);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dash-activity .icon-info-activity i {
        background: transparent !important;
        color: #1761fd;
        font-size: 15px;
    }

    .dash-activity .activity-info-text {
        flex: 1;
        padding-top: 4px;
    }

    .dash-activity .activity-info-text p {
        color: #3b4560 !important;
        width: 100% !important;
    }

    .dash-empty-state {
        text-align: center;
        color: #9ba7ca;
        padding: 24px 0;
        font-size: 13.5px;
    }
</style>
@endsection
@section('content')
  <div class="page-content">
                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="dash-header">
                                    <div>
                                        <h4 class="page-title">Overall Analytics</h4>
                                        <p class="dash-subtitle">This Dashboard shows overview of your recent activities, verifications transactions</p>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-outline-primary dash-date-btn" id="Dash_Date">
                                        <span class="ay-name" id="Day_Name">Today:</span>&nbsp;
                                        <span class="" id="Select_date">Jan 11</span>
                                        <i data-feather="calendar" class="align-self-center icon-xs ms-1"></i>
                                    </a>
                                </div>
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                    </div><!--end row-->
                    <!-- end page title end breadcrumb -->
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="row justify-content-center g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="card report-card dash-stat-card" style="--dash-accent:#03d87f; --dash-accent-soft: rgba(3, 216, 127, .15);">
                                        <div class="card-body " >
                                            <div class="row d-flex justify-content-center align-items-center">
                                                <div class="col">
                                                    <p class="dash-stat-label">Successful verifications</p>
                                                    <h3 class="dash-stat-value">{{count($success)}}</h3>
                                                </div>
                                                <div class="col-auto align-self-center">
                                                    <div class="dash-stat-icon">
                                                        <i data-feather="check-circle" class="align-self-center icon-sm"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card report-card dash-stat-card" style="--dash-accent:#f5325c; --dash-accent-soft: rgba(245, 50, 92, .15);">
                                        <div class="card-body" >
                                            <div class="row d-flex justify-content-center align-items-center">
                                                <div class="col">
                                                    <p class="dash-stat-label">Failed verifications</p>
                                                    <h3 class="dash-stat-value">{{count($failed)}}</h3>
                                                </div>
                                                <div class="col-auto align-self-center">
                                                    <div class="dash-stat-icon">
                                                        <i data-feather="x-circle" class="align-self-center icon-sm"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card report-card dash-stat-card" style="--dash-accent:#ffb822; --dash-accent-soft: rgba(255, 184, 34, .15);">
                                        <div class="card-body" >
                                            <div class="row d-flex justify-content-center align-items-center">
                                                <div class="col">
                                                    <p class="dash-stat-label">Pending Request</p>
                                                    <h3 class="dash-stat-value">{{count($pending)}}</h3>
                                                </div>
                                                <div class="col-auto align-self-center">
                                                    <div class="dash-stat-icon">
                                                        <i data-feather="clock" class="align-self-center icon-sm"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div> <!--end col-->

                                <!--end col-->
                            </div><!--end row-->
                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="card dash-card">
                                        <div class="card-header">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h4 class="card-title"><i data-feather="activity" class="icon-sm"></i> Recent Verification</h4>
                                                </div><!--end col-->
                                            </div>  <!--end row-->
                                        </div><!--end card-header-->
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="datatable-buttons" class="table table-striped table-hover dt-responsive nowrap " style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                    <thead>
                                                    <tr>
                                                        <th class="px-2 py-3">S/N</th>
                                                        <th class="px-2 py-3">Reference</th>
                                                        <th class="px-2 py-3">Verification ID</th>
                                                        <th class="px-2 py-3">Name</th>
                                                        <th class="px-2 py-3">Status</th>
                                                        <th class="px-2 py-3">Verified by</th>
                                                        <th class="px-2 py-3">Initiated At</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>

                                                @foreach ($logs as $trans)
                                                    <tr>
                                                        <td class="px-0 py-0"><div class="px-2 py-3">{{$loop->iteration}}</div></td>
                                                        <td class="px-0 py-0"><div class="px-2 py-3">{{$trans->ref}}</div></td>
                                                        <td class="px-0 py-0"><div class="px-2 py-3">{{$trans->pin}}</div></td>
                                                        <td class="px-0 py-0"><div class="px-2 py-3">{{$trans->status == 'found' ? $trans->first_name.' '.$trans->last_name : 'N/A'}}</div></td>
                                                        <td class="px-0 py-0">
                                                            <div class="px-2 py-3">
                                                                @if($trans->status == $trans->status )
                                                                @if($trans->validations != null && $trans->validations->validationMessages != "")
                                                                <span class="badge badge-soft-warning">Found</span>
                                                                @else
                                                                <span class="badge badge-soft-success"> Found</span>
                                                                @endif
                                                                @elseif($trans->status == 'not_found')
                                                                <span class="badge badge-soft-danger">Not Found</span>
                                                                @else
                                                                <span class="badge badge-soft-purple"> {{$trans->status}}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-0 py-0"><div class="px-2 py-3">{{auth()->user()->firstname . ' '.auth()->user()->lastname}}</div></td>
                                                        <td class="px-0 py-0"><div class="px-2 py-3">{{date('d-M-Y h:iA', strtotime($trans->created_at))}}</div></td>
                                                        <td class="px-0 py-0"><div class="px-2 py-3"> @if($trans->status == 'found')

                                                         @endif
                                                        </div></td>
                                                    </tr>
                                                     @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div><!--end card-->
                                </div> <!--end col-->

                            </div><!--end row-->
                        </div><!--end col-->
                        <div class="col-lg-3">
                            <div class="card dash-card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="card-title"><i data-feather="list" class="icon-sm"></i> Activity Log</h4>
                                        </div><!--end col-->
                                      <!--end col-->
                                    </div>  <!--end row-->
                                </div><!--end card-header-->
                                <div class="card-body">
                                    <div class="analytic-dash-activity" data-simplebar>
                                        <div class="dash-activity">

                                            @if(count($activity) > 0)
                                            @foreach ($activity as  $logs)

                                            <div class="activity-info">
                                                <div class="icon-info-activity">
                                                    <i class="las la-user-clock"></i>
                                                </div>
                                                <div class="activity-info-text">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <p class="text-muted mb-0 font-13 w-75">
                                                           {{$logs->activity}}
                                                        </p>
                                                        <small class="text-muted">{{$logs->created_at}}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                            @else
                                                <div class="dash-empty-state">
                                                    You dont have any activity at this moment
                                                </div>

                                            @endif

                                        </div><!--end activity-->
                                    </div><!--end analytics-dash-activity-->
                                </div>  <!--end card-body-->
                            </div><!--end card-->
                        </div> <!--end col-->
                    </div><!--end row-->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card dash-card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="card-title"><i data-feather="credit-card" class="icon-sm"></i> Payment Logs</h4>
                                        </div><!--end col-->
                                    </div>  <!--end row-->
                                </div><!--end card-header-->
                                <div class="card-body">
                                    <div class="table-responsive browser_users">
                                        <table class="table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="border-top-0">#Ref</th>
                                                    <th class="border-top-0">User</th>
                                                    <th class="border-top-0">Service</th>
                                                    <th class="border-top-0">Purpose</th>
                                                    <th class="border-top-0">Type</th>
                                                    <th class="border-top-0">Amount</th>
                                                    <th class="border-top-0">Created At</th>
                                                </tr><!--end tr-->
                                            </thead>
                                            <tbody>
                                            @foreach ($transactions as $trans )
                                                  <tr>
                                                    <td><a href="#" class="text-primary">{{substr($trans->ref,0,100)}} </a></td>
                                                    <td>{{$trans->user->firstname}}</td>
                                                    <td>{{$trans->service_type}}</td>
                                                    <td>{{$trans->purpose}}</td>
                                                    <td>{{$trans->type}}</td>
                                                    <td>{{$trans->amount}}</td>
                                                    <td >{{$trans->created_at->format('d-m-y h:i:s a')}}</td>
                                                </tr>
                                            @endforeach
                                                <!--end tr-->
                                            </tbody>
                                        </table> <!--end table-->
                                    </div><!--end /div-->
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div> <!--end col-->
                    </div><!--end row-->


                </div><!-- container -->
@endsection
