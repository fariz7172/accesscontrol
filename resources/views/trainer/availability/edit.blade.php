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
    <title>Soyal - Edit Availability</title>
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
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/trainer') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Soyal Trainer Dashboard</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item {{ request()->is('trainer') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/trainer') }}">
                    <i class="fas fa-file"></i>
                    <span>My Leave Requests</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('trainer/availability*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('trainer.availability.index') }}">
                    <i class="fas fa-calendar"></i>
                    <span>My Availability</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('trainer/schedule*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('trainer.schedule.index') }}">
                    <i class="fas fa-clock"></i>
                    <span>My Schedules</span>
                </a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::guard('member')->user()->NAME ?? 'Trainer' }}</span>
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

                    <h1 class="h3 mb-2 text-gray-800">Edit Availability</h1>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="POST" action="{{ route('trainer.availability.update', $availability->ID) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="DOW">Day of Week</label>
                                    <select id="DOW" name="DOW" class="form-control" required>
                                        <option value="">Select Day</option>
                                        <option value="1" {{ old('DOW', $availability->DOW) == 1 ? 'selected' : '' }}>Senin</option>
                                        <option value="2" {{ old('DOW', $availability->DOW) == 2 ? 'selected' : '' }}>Selasa</option>
                                        <option value="3" {{ old('DOW', $availability->DOW) == 3 ? 'selected' : '' }}>Rabu</option>
                                        <option value="4" {{ old('DOW', $availability->DOW) == 4 ? 'selected' : '' }}>Kamis</option>
                                        <option value="5" {{ old('DOW', $availability->DOW) == 5 ? 'selected' : '' }}>Jumat</option>
                                        <option value="6" {{ old('DOW', $availability->DOW) == 6 ? 'selected' : '' }}>Sabtu</option>
                                        <option value="7" {{ old('DOW', $availability->DOW) == 7 ? 'selected' : '' }}>Minggu</option>
                                    </select>
                                    @error('DOW')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="START_TIME">Start Time</label>
                                    <input type="time" id="START_TIME" name="START_TIME" class="form-control" value="{{ old('START_TIME', \Carbon\Carbon::parse($availability->START_TIME)->format('H:i')) }}" required>
                                    @error('START_TIME')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="END_TIME">End Time</label>
                                    <input type="time" id="END_TIME" name="END_TIME" class="form-control" value="{{ old('END_TIME', \Carbon\Carbon::parse($availability->END_TIME)->format('H:i')) }}" required>
                                    @error('END_TIME')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="IS_ACTIVE">Status</label>
                                    <select id="IS_ACTIVE" name="IS_ACTIVE" class="form-control" required>
                                        <option value="1" {{ old('IS_ACTIVE', $availability->IS_ACTIVE) == 1 ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ old('IS_ACTIVE', $availability->IS_ACTIVE) == 0 ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    @error('IS_ACTIVE')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('trainer.availability.index') }}" class="btn btn-secondary">Cancel</a>
                            </form>
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