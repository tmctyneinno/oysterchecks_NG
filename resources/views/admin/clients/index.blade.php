 <?php
use App\Models\User as User;
?>

 @extends('layouts.admin')
 @section('content')
 <style>
     .acx-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 12px;
         margin-bottom: 4px;
     }

     .acx-subtitle {
         color: #9ba7ca;
         font-size: 13.5px;
         margin: 0;
     }

     .acx-actions {
         display: flex;
         align-items: center;
         gap: 8px;
     }

     .acx-pill-btn {
         border-radius: 50px !important;
         padding: 8px 18px !important;
         font-weight: 500;
         border-width: 1px !important;
     }

     .acx-icon-btn {
         width: 36px;
         height: 36px;
         border-radius: 50px !important;
         padding: 0 !important;
         display: inline-flex;
         align-items: center;
         justify-content: center;
     }

     .acx-table-card {
         border: 1px solid #e3ebf6 !important;
         border-radius: 14px !important;
         box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
     }

     .acx-table-card .card-header {
         background: transparent;
         border-bottom: 1px solid #eef2f9;
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 10px;
         padding: 18px 22px;
     }

     .acx-table-card .card-title {
         display: flex;
         align-items: center;
         gap: 10px;
         margin: 0 !important;
     }

     .acx-table-card .card-title i {
         color: #1761fd;
     }

     .acx-table-card .btn-primary {
         border-radius: 50px;
         padding: 8px 18px;
         font-weight: 500;
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

     .badge.bg-success,
     .badge.bg-danger,
     .badge.bg-warning {
         border-radius: 50px;
         padding: 6px 14px;
         font-weight: 600;
         font-size: 11px;
         letter-spacing: .02em;
     }

     .acx-action-toggle {
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

     .acx-action-toggle:hover {
         background: #f1f5fa;
         color: #1761fd;
     }

     .acx-table-card .dropdown-menu {
         border: 1px solid #e3ebf6;
         border-radius: 10px;
         box-shadow: 0 6px 20px rgba(29, 44, 72, .1);
         padding: 6px;
         min-width: 210px;
     }

     .acx-table-card .dropdown-item {
         border-radius: 8px;
         padding: 8px 12px;
         font-size: 13px;
         font-weight: 500;
         color: #1d2c48;
     }

     .acx-table-card .dropdown-item:hover {
         background: rgba(23, 97, 253, .08);
         color: #1761fd;
     }
 </style>
 <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="acx-header">
                                    <div>
                                        <h4 class="page-title">All Clients</h4>
                                        <p class="acx-subtitle">Manage client accounts and their verification status</p>
                                    </div>
                                    <div class="acx-actions">
                                        <a href="#" class="btn btn-sm btn-outline-primary acx-pill-btn" id="Dash_Date">
                                            <span class="ay-name" id="Day_Name">Today:</span>&nbsp;
                                            <span class="" id="Select_date">Jan 11</span>
                                            <i data-feather="calendar" class="align-self-center icon-xs ms-1"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-primary acx-icon-btn">
                                            <i data-feather="download" class="align-self-center icon-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                    </div>

            <div class="col-12 pt-4">
                <div class="card acx-table-card">
                    <div class="card-header">
                        <h4 class="card-title"><i data-feather="users" class="icon-sm"></i> All Clients</h4>
                        <a href="{{route('admin.client.create')}}" class="btn btn-primary"><i class="fa fa-user"></i> Create New Client</a>
                      </div><!--end card-header-->

                    <div class="card-body">
                        <table id="datatable-buttons" class=" orders table table-striped table-bordered dt-responsive nowrap " style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                {{-- <th>Last Login</th> --}}
                                <th>Login Ip</th>
                                <th>Created On</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($clients as $client )
                               <tr>
                                <td>{{$client->user->firstname .''.$client->user->lastname}}</td>
                                <td>{{$client->user->email}}</td>
                                <td>{{$client->user->phone}}</td>
                                <td>@if($client->is_admin_verified == User::ADMIN_VERIFIED) <span class="badge bg-success"> Verified</span>
                                @elseif($client->is_admin_verified == User::ADMIN_SUSPENDED) <span class="badge bg-danger">Suspended </span>
                                @else <span class="badge bg-warning"> Pending</span>
                                @endif</td>
                                {{-- <td> {{$client->user->activities->created_at->format('d/m/y H:I')}}</td>  --}}
                                <td></td>
                                {{-- <td> {{$client->user->activities->ip_address}}</td> --}}
                                <td> {{$client->user->created_at}}</td>
                                <td>
                                    <div class="dropdown kanban-main-dropdown">
                                        <a class="dropdown-toggle arrow-none acx-action-toggle" id="drop1" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                            <i class="las la-ellipsis-v font-24"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="drop1" style="">
                                            <a class="dropdown-item " href="{{route('admin.client.details', encrypt($client->id))}}" > View User  </a>
                                            <a class="dropdown-item " href="#" > Suspend User <span class="badge bg-danger"> X </span> </a>
                                            {{-- <a class="dropdown-item" href="{{route('client.company.details')}}">View Company</a> --}}
                                            <a class="dropdown-item" href="{{route('admin.client.candidates', encrypt($client->id))}}"> view Candidates</a>
                                            <a class="dropdown-item" href="#">View Payment History</a>
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

