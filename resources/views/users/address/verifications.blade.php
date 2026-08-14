@extends('layouts.app')
@section('style')
<style>
    .avf-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 4px;
    }

    .avf-header .page-title {
        margin-bottom: 4px;
    }

    .avf-subtitle {
        color: #9ba7ca;
        font-size: 13.5px;
        margin: 0;
    }

    .avf-date-btn {
        border-radius: 50px !important;
        padding: 8px 18px !important;
        font-weight: 500;
        border-width: 1px !important;
    }

    .avf-stat-card {
        border: 1px solid #e3ebf6 !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
        transition: transform .15s ease, box-shadow .15s ease;
        overflow: hidden;
        position: relative;
    }

    .avf-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(29, 44, 72, 0.08);
    }

    .avf-stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--avf-accent, #1761fd);
    }

    .avf-stat-label {
        color: #9ba7ca;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .02em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .avf-stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1d2c48;
        margin: 0;
    }

    .avf-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--avf-accent-soft, rgba(23, 97, 253, .12));
        color: var(--avf-accent, #1761fd);
    }

    .avf-table-card {
        border: 1px solid #e3ebf6 !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
    }

    .avf-table-card .card-header {
        background: transparent;
        border-bottom: 1px solid #eef2f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 18px 22px;
    }

    .avf-table-card .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .avf-table-card .card-title i {
        color: #1761fd;
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

    .avf-avatar {
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

    .avf-candidate {
        display: flex;
        align-items: center;
    }

    .avf-candidate-name {
        font-weight: 600;
        color: #1d2c48;
        font-size: 13.5px;
    }

    .badge.badge-soft-warning,
    .badge.badge-soft-success,
    .badge.badge-soft-dark,
    .badge.badge-soft-purple,
    .badge.badge-soft-danger {
        border-radius: 50px;
        padding: 6px 14px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: .02em;
    }

    .avf-action-toggle {
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

    .avf-action-toggle:hover {
        background: #f1f5fa;
        color: #1761fd;
    }

    .avf-table-card .dropdown-menu {
        border: 1px solid #e3ebf6;
        border-radius: 10px;
        box-shadow: 0 6px 20px rgba(29, 44, 72, .1);
        padding: 6px;
        min-width: 220px;
    }

    .avf-table-card .dropdown-item {
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 500;
        color: #1d2c48;
    }

    .avf-table-card .dropdown-item:hover {
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
                    <div class="avf-header">
                        <div>
                            <h4 class="page-title">{{$candidate}} Verifications</h4>
                            <p class="avf-subtitle">Address verification requests and their current status</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-primary avf-date-btn" id="Dash_Date">
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
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card avf-stat-card" style="--avf-accent:#1761fd; --avf-accent-soft: rgba(23, 97, 253, .12);">
                            <div class="card-body">
                                <div class="row d-flex justify-content-center align-items-center">
                                    <div class="col">
                                        <p class="avf-stat-label">Total Addresses</p>
                                        <h3 class="avf-stat-value">{{count($verification)}}</h3>
                                    </div>
                                    <div class="col-auto align-self-center">
                                        <div class="avf-stat-icon">
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
                        <div class="card report-card avf-stat-card" style="--avf-accent:#ffb822; --avf-accent-soft: rgba(255, 184, 34, .15);">
                            <div class="card-body">
                                <div class="row d-flex justify-content-center align-items-center">
                                    <div class="col">
                                        <p class="avf-stat-label">Pending Address</p>
                                        <h3 class="avf-stat-value">{{count($not_verified)}}</h3>
                                    </div>
                                    <div class="col-auto align-self-center">
                                        <div class="avf-stat-icon">
                                            <i data-feather="clock" class="align-self-center icon-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card avf-stat-card" style="--avf-accent:#03d87f; --avf-accent-soft: rgba(3, 216, 127, .15);">
                            <div class="card-body">
                                <div class="row d-flex justify-content-center align-items-center">
                                    <div class="col">
                                        <p class="avf-stat-label">Verified Addresses</p>
                                        <h3 class="avf-stat-value">{{count($verified)}}</h3>
                                    </div>
                                    <div class="col-auto align-self-center">
                                        <div class="avf-stat-icon">
                                            <i data-feather="check-circle" class="align-self-center icon-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <!--end col-->
                    {{-- <div class="col-md-6 col-lg-4">
                        <div class="card report-card">
                            <div class="card-body" >
                                <div class="row d-flex justify-content-center">
                                    <div class="col">
                                        <p class="text-black mb-0 fw-semibold">Cancelled Requests</p>
                                        <h3 class="m-0 text-black">{{$cancelled}}</h3>
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
</div>
<!--end col-->
<div class="col-md-6 col-lg-4">
    <div class="card report-card">
        <div class="card-body">
            <div class="row d-flex justify-content-center">
                <div class="col">
                    <p class="text-black mb-0 fw-semibold">Requests Awaiting Reschedule</p>
                    <h3 class="m-0 text-black">{{$awaiting_reschedule}}</h3>
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
</div>
<!--end col-->
<div class="col-md-6 col-lg-4">
    <div class="card report-card">
        <div class="card-body">
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
    <div class="col-12">
        <div class="card avf-table-card">
            <div class="card-header">
                <h4 class="card-title"><i data-feather="list" class="icon-sm"></i> Candidate Verificaton logs</h4>
            </div>
            <!--end card-header-->
            <div class="card-body">
                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap " style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Name</th>
                            <th>Verification ID</th>
                            <th>Status</th>
                            <th>Address Type</th>
                            <th>Date Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($verification as $verifications)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <div class="avf-candidate">
                                    <span class="avf-avatar">{{strtoupper(substr(isset($verifications?->candidate['firstname'])?$verifications?->candidate['firstname']: $verifications->first_name, 0, 1))}}{{strtoupper(substr(isset($verifications?->candidate['lastname'])?$verifications?->candidate['lastname']:$verifications->last_name, 0, 1))}}</span>
                                    <span class="avf-candidate-name">{{isset($verifications?->candidate['firstname'])?$verifications?->candidate['firstname']: $verifications->first_name}} {{isset($verifications?->candidate['lastname'])?$verifications?->candidate['lastname']:$verifications->last_name}}</span>
                                </div>
                            </td>
                            <td>{{$verifications->reference_id}}</td>
                            <td>
                                @if($verifications->status == 'pending')
                                <span class="badge badge-soft-warning">PENDING</span>
                                @elseif(strtolower($verifications->status) == 'completed' && strtolower($verifications->task_status) == 'verified')
                                <span class="badge badge-soft-success">COMPLETED & VERIFIED</span>
                                @elseif(strtolower($verifications->status) == 'completed' && strtolower($verifications->task_status) == 'completed')
                                <span class="badge badge-soft-success">COMPLETED & VERIFIED</span>
                                @elseif($verifications->status == 'awaiting_reschedule' || strtolower($verifications->status) == 'in_progress' )
                                <span class="badge badge-soft-dark">
                                    {{strtoupper(str_replace('_', ' ', $verifications->status))}}
                                </span>
                                @elseif(strtolower($verifications->status) == 'completed' && strtolower($verifications->task_status) != 'verified')
                                <span class="badge badge-soft-purple">COMPLETED NOT VERIFIED</span>
                                @elseif(strtolower($verifications->status) == 'invalid_address')
                                <span class="badge badge-soft-danger"> INVALID ADDRESS</span>
                                @elseif(strtolower($verifications->status) == 'wrong_address')
                                <span class="badge badge-soft-danger"> WRONG ADDRESS</span>
                                @else
                                <span class="badge badge-soft-danger"> {{$verifications->status}}</span>
                                @endif
                            </td>
                            <td>
                                @if($verifications->type =='guarantor') Guarantor Address @elseif($verifications->type == 'business') Business Address
                                @else Individual Address
                                @endif</td>
                            <td>{{$verifications->created_at}}</td>
                            <td> 
                                <div class="dropdown d-inline-block">
                                    <a class="dropdown-toggle arrow-none avf-action-toggle" id="seeMore" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h font-12"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="seeMore" style="">
                                        {{-- <a class="dropdown-item" href="#">Copy Reference Id</a> --}}
                                        {{-- @if($address->addressVerificationDetail()->exists()) --}}
                                        <a class="dropdown-item" href="{{route('showAddressReport',['slug' => $verifications->type, 'addressId' => $verifications->hashid ] )}}">
                                            <i data-feather="file-text" class="icon-xs me-1"></i> View Verification Report
                                        </a>
                                        {{-- @else --}}

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
