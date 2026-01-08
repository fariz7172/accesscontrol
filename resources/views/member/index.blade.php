<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/soyal.png') }}">
    <title>Soyal - Member Dashboard</title>
    <link href="{{ asset('template') }}/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/font.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/stackpath.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/checkbox.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/queri.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/buttonmargin.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/member') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Soyal Member Dashboard</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item {{ request()->is('member') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/member') }}">
                    <i class="fas fa-file"></i>
                    <span>My Leave Requests</span>
                </a>
            </li>
           
             <hr class="sidebar-divider d-none d-md-block">
               <li class="nav-item {{ request()->is('memberSchedule') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/memberSchedule') }}">
                    <i class="fas fa-file"></i>
                    <span>My Schedule Train</span>
                </a>
            </li>
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::guard('member')->user()->NAME ?? 'Member' }}</span>
                                <img class="img-profile rounded-circle" src="{{ asset('template') }}/img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="/sesi/logout" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {!! session('success') !!}
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <h1 class="h3 mb-2 text-gray-800">My Leave Requests</h1>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="{{ route('member.index') }}" method="GET" class="form-inline mb-3">
                                <div class="form-group mr-2">
                                    <label for="start_date" class="mr-2">Start Date:</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ $startDate ?? now()->format('Y-m-d') }}">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="end_date" class="mr-2">End Date:</label>
                                    <input type="date" class="form-control" name="end_date" value="{{ $endDate ?? now()->format('Y-m-d') }}">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                                <button type="submit" class="btn btn-success" formaction="{{ route('member.export.pdf') }}">Export to PDF</button>
                            </form>
                        </div>
                    </div>
                    <a href="{{ route('member.create') }}" class="btn btn-primary mb-3">Add Leave Request</a>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>From Date</th>
                                            <th>To Date</th>
                                            <th>Notes</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leaveProcesses as $leaveProcess)
                                        <tr>
                                            <td>{{ $leaveProcess->leaveType->Name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($leaveProcess->FromDate)->format('Y-m-d') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($leaveProcess->ToDate)->format('Y-m-d') }}</td>
                                            <td>{{ $leaveProcess->Notes ?? '-' }}</td>
                                            <td>{{ $leaveProcess->getStatusTextAttribute() }}</td>
                                            <td>
                                                @if($leaveProcess->STATUS == 0)
                                                <a href="{{ route('member.edit', $leaveProcess->Id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                <form action="{{ route('leaveprocessMember.destroy', $leaveProcess->Id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this leave request?')">Delete</button>
                                                </form>
                                                @else
                                                <span class="text-muted">No actions available</span>
                                                @endif
                                                <a href="{{ route('member.exportPDFById', $leaveProcess->Id) }}" class="btn btn-info btn-sm">Export PDF</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $leaveProcesses->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Soyal</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="/sesi/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('template') }}/vendor/jquery/jquery.min.js"></script>
    <script src="{{ asset('template') }}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('template') }}/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="{{ asset('template') }}/js/sb-admin-2.min.js"></script>
</body>
</html>