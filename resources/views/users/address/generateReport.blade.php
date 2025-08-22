@extends('layouts.app')
@section('style')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col pt-5">
                            <h4 class="page-title">Address Report</h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"></li>
                            </ol>
                        </div>
                        <!--end col-->
                        <div class="col-auto align-self-center">
                            <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                <span class="ay-name" id="Day_Name">Today:</span>&nbsp;
                                <span class="" id="Select_date">Jan 11</span>
                                <i data-feather="calendar" class="align-self-center icon-xs ms-1"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i data-feather="download" class="align-self-center icon-xs"></i>
                            </a>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <!--end page-title-box-->
            </div>
            <!--end col-->
        </div>
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"> <span style="color:rgb(13, 100, 132); font-style:italics">  <strong> Address Verification Report  </strong> </span></h4>
                    </div>
                    <!--end card-header-->
                    <form method="post" action="{{route('user.generate-address-report')}}" id="form1" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body bootstrap-select-1">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <span> Select Address Type  </span> 
                                    <br>
                                    <div class="btn-group " role="group" aria-label="Basic radio toggle button group">
                                      
                                        <input type="radio" class="btn-check" value="individual-address"  name="query_type" id="btnradio1" autocomplete="off" checked="">
                                        <label class="btn btn-lg btn-outline-info"  for="btnradio1">Individual Address</label>
                                      &nbsp;   &nbsp;
                                        <input type="radio" class="btn-check" value="reference-address" name="query_type" id="btnradio2" autocomplete="off">
                                        <label class="btn btn-lg btn-outline-info" for="btnradio2">Reference Address</label>
                                        &nbsp;   &nbsp;
                                        <input type="radio" class="btn-check" value="business-address" name="query_type" id="btnradio3" autocomplete="off">
                                        <label class="btn btn-lg btn-outline-info" for="btnradio3">Business Address</label>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                 
                                        <label class="form-label" for="exampleInputEmai">Select Date Range</label> <br>
                                        <div class="input-group">  
                                            <input type="text" name="daterange"  id="reportrange" class="form-control" value="2024-01-01">
                                            <span class="input-group-text"><i class="ti ti-calendar font-16"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                 
                                        <label class="form-label" for="exampleInputEmai">Select Number of Reports</label> <br>
                                        <div class="input-group">  
                                         <select name="reportCount" class="form-control" required> 
                                            <option> 20</option>
                                            <option> 40</option>
                                            <option> 60</option>
                                            <option> 80</option>
                                            <option> 100</option>
                                         </select>
                                        </div>
                                    </div>

                                </div><!-- end col -->
                                <button class="btn btn-primary w-25 pt-2 pb-2" id="myButton"> Generate Address Report</button>
                            </div><!-- end row -->
                        </div><!-- end card-body -->
                    </form>
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div>

          <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-body">
                         <table id="datatable-buttons" class="table table-striped dt-responsive nowrap " style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                             <thead>
                                 <tr>
                                     <th>SN</th>
                                     <th>Date Requested</th>
                                     <th>Report Type</th>
                                     <th>Start Date</th>
                                     <th>End Date</th>
                                     <th>Download Path</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach ($reports as $transaction)
                                 <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$transaction->created_at}}</td>
                                     <td>{{$transaction->address_type}}</td>
                                     <td>{{$transaction->start_date}} </td>
                                     <td>{{$transaction->end_date}}</td>
                                     <td> <a target="_blank" class="btn btn-outline-info" href="{{asset($transaction->reports)}}"> Download Report </td>
                                 </tr>
                                 @endforeach
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div> <!-- end col -->
         </div>
    </div>
    @endsection

    @section('script')
    <script>

 $(function() {
    $('#reportrange').daterangepicker({
        minDate: new Date('2018-11-2'),
        locale: {
            format: minDate
        }
    });
});
   document.getElementById('myButton').addEventListener('click', function() {
    Swal.fire({
        title: 'warning!',
        text: 'Report is running, results will be made available once completed, you can continue with other activities while the system get this ready for you.',
        icon: 'warning',
        confirmButtonText: 'Close Model'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('home') }}";
        }
    });
});
    </script>
    @endsection