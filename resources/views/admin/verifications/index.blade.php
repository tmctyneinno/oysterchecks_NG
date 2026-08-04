
@extends('layouts.admin')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .adm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 4px;
    }

    .adm-subtitle {
        color: #9ba7ca;
        font-size: 13.5px;
        margin: 0;
    }

    .adm-date-btn {
        border-radius: 50px !important;
        padding: 8px 18px !important;
        font-weight: 500;
        border-width: 1px !important;
    }

    .adm-stat-card {
        border: 1px solid #e3ebf6 !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
        transition: transform .15s ease, box-shadow .15s ease;
        overflow: hidden;
        position: relative;
    }

    .adm-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(29, 44, 72, 0.08);
    }

    .adm-stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--adm-accent, #1761fd);
    }

    .adm-stat-label {
        color: #9ba7ca;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: .02em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .adm-stat-value {
        font-size: 26px;
        font-weight: 700;
        color: #1d2c48;
        margin: 0;
    }

    .adm-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--adm-accent-soft, rgba(23, 97, 253, .12));
        color: var(--adm-accent, #1761fd);
    }

    .adm-card {
        border: 1px solid #e3ebf6 !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 10px rgba(29, 44, 72, 0.04);
    }

    .adm-card .card-header {
        background: transparent;
        border-bottom: 1px solid #eef2f9;
        padding: 18px 22px;
    }

    .adm-card .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .adm-card .card-title i {
        color: #1761fd;
    }

    .adm-chart-card .card-body {
        padding: 22px;
    }

    .adm-legend-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 4px;
        border-bottom: 1px solid #eef2f9;
    }

    .adm-legend-row:last-child {
        border-bottom: none;
    }

    .adm-legend-dot {
        width: 10px;
        height: 10px;
        min-width: 10px;
        border-radius: 50%;
    }

    .adm-legend-label {
        flex: 1;
        color: #3b4560;
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }

    .adm-legend-value {
        font-weight: 700;
        font-size: 14px;
    }
</style>
  <div class="page-content">
                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="adm-header">
                                    <div>
                                        <h4 class="page-title">Overall Analytics</h4>
                                        <p class="adm-subtitle">This Dashboard shows overview of your recent activities, verifications transactions</p>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-outline-primary adm-date-btn" id="Dash_Date">
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
                        <div class="col-lg-12">
                            <div class="row justify-content-center g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="card report-card adm-stat-card" style="--adm-accent:#03d87f; --adm-accent-soft: rgba(3, 216, 127, .15);">
                                        <div class="card-body " >
                                            <div class="row d-flex justify-content-center align-items-center">
                                                <div class="col">
                                                    <p class="adm-stat-label">Successful verifications</p>
                                                    <h3 class="adm-stat-value">{{count($success)}}</h3>
                                                </div>
                                                <div class="col-auto align-self-center">
                                                    <div class="adm-stat-icon">
                                                        <i data-feather="check-circle" class="align-self-center icon-sm"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card report-card adm-stat-card" style="--adm-accent:#f5325c; --adm-accent-soft: rgba(245, 50, 92, .15);">
                                        <div class="card-body" >
                                            <div class="row d-flex justify-content-center align-items-center">
                                                <div class="col">
                                                    <p class="adm-stat-label">Failed verifications</p>
                                                    <h3 class="adm-stat-value">{{count($failed)}}</h3>
                                                </div>
                                                <div class="col-auto align-self-center">
                                                    <div class="adm-stat-icon">
                                                        <i data-feather="x-circle" class="align-self-center icon-sm"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card report-card adm-stat-card" style="--adm-accent:#ffb822; --adm-accent-soft: rgba(255, 184, 34, .15);">
                                        <div class="card-body" >
                                            <div class="row d-flex justify-content-center align-items-center">
                                                <div class="col">
                                                    <p class="adm-stat-label">Pending Request</p>
                                                    <h3 class="adm-stat-value">{{count($pending)}}</h3>
                                                </div>
                                                <div class="col-auto align-self-center">
                                                    <div class="adm-stat-icon">
                                                        <i data-feather="clock" class="align-self-center icon-sm"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div> <!--end col-->

                                <!--end col-->
                            </div><!--end row-->

                        </div><!--end col-->

                    </div><!--end row-->

                    <div class="row mt-1">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="row">
                                    <div class="col">
                                        <h4 class="page-title">Verification</h4>

                                    </div><!--end col-->

                                </div><!--end row-->
                                <div class="row g-3">
                                    <div class="col-lg-9">
                                        <div class="card adm-card adm-chart-card">
                                            <div class="card-header">
                                                <h4 class="card-title"><i data-feather="trending-up" class="icon-sm"></i> Verification Trend</h4>
                                            </div>
                                            <div class="card-body">
                                                <div style="width: 100%; margin: auto;">
                                                    <canvas id="myLineChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            var ctx = document.getElementById('myLineChart').getContext('2d');
                                                var chart = new Chart(ctx, {
                                                    type: 'line',
                                                    data: {
                                                        labels: @json($chartData['labels']),
                                                        datasets: @json($chartData['datasets'])
                                                    },
                                                    options: {
                                                        scales: {
                                                            y: {
                                                                beginAtZero: true
                                                            }
                                                        }
                                                    }});
                                        </script>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="card adm-card">
                                            <div class="card-header">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <h4 class="card-title"><i data-feather="pie-chart" class="icon-sm"></i> Verifications</h4>
                                                    </div><!--end col-->
                                                <!--end col-->
                                                </div>  <!--end row-->
                                            </div><!--end card-header-->
                                            <div class="card-body">
                                                <div class="analytic-dash-activity" data-simplebar>
                                                    <div class="activity">



                                                        <div class="activity-info">
                                                            <div class="adm-legend-row">
                                                                <span class="adm-legend-dot" style="background:#03d87f;"></span>
                                                                <p class="adm-legend-label">Success</p>
                                                                <span class="adm-legend-value" style="color:#03d87f;">2.76%</span>
                                                            </div>
                                                        </div>
                                                        <div class="activity-info">
                                                            <div class="adm-legend-row">
                                                                <span class="adm-legend-dot" style="background:#ffb822;"></span>
                                                                <p class="adm-legend-label">Pending</p>
                                                                <span class="adm-legend-value" style="color:#ffb822;">2.76%</span>
                                                            </div>
                                                        </div>
                                                        <div class="activity-info">
                                                            <div class="adm-legend-row">
                                                                <span class="adm-legend-dot" style="background:#f5325c;"></span>
                                                                <p class="adm-legend-label">Failed</p>
                                                                <span class="adm-legend-value" style="color:#f5325c;">2.76%</span>
                                                            </div>
                                                        </div>


                                                    </div><!--end activity-->
                                                </div><!--end analytics-dash-activity-->
                                            </div>  <!--end card-body-->
                                        </div><!--end card-->
                                    </div> <!--end col-->
                                </div>
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                    </div><!--end row-->



                </div><!-- container -->
@endsection
