@extends('layout_background.app_layouts')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Soyal Access Control</h1>

    @if (session('alert'))
    <div class="alert alert-danger">
        {{ session('alert') }}
    </div>
    @endif

    <div class="row">

        <div class="col-lg-12">



            <!-- Brand Buttons -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Soyal</h6>
                </div>

                <div class="card">
                    <div class="card-body" style=" color:black">
                        <div class="row">


                            <!-- Card 1: Total Employee -->
                            <div class=" col-md-3 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Total Employee</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-employee">{{ $totalEmployee }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-users fa-2x text-gray-300" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Present Today -->
                            <div class=" col-md-3 mb-4">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Present Today </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="present-count">{{ $presentCount }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-check-circle fa-2x text-gray-300" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Late Today -->
                            <div class=" col-md-3 mb-4">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    Late To Day </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="late-count">{{ $lateCount }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-clock fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Absent -->
                              <div class=" col-md-3 mb-4">
                                <div class="card border-left-danger shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                    Absent </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="absent-count">{{ $absentCount }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-times-circle fa-2x text-gray-300"></i>
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
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateDashboardMetrics() {
            fetch('{{ route("dashboard.metrics") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total-employee').innerText = data.totalEmployee;
                    document.getElementById('present-count').innerText = data.presentCount;
                    document.getElementById('late-count').innerText = data.lateCount;
                    document.getElementById('absent-count').innerText = data.absentCount;
                })
                .catch(error => console.error('Error fetching dashboard metrics:', error));
        }

        // Poll every 5 seconds
        setInterval(updateDashboardMetrics, 5000);
    });
</script>
@endsection