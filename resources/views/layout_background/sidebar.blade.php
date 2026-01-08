<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Soyal Admin Dashboard</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Access User -->
    @if (auth()->user()->hasAccess('user'))
    <li class="nav-item {{ request()->is('userProfile','userAdmin','logUserData','import-csv','settingPath') ? 'active' : '' }}" id="access-user">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Collapsetilities" aria-expanded="true" aria-controls="Collapsetilities">
            <i class="fas fa-fw fa-solid fa-user-plus"></i>
            <span>Data User</span>
        </a>
        <div id="Collapsetilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ request()->is('branches') ? 'active' : '' }}" href="{{ url('/branches') }}">
                    <i class="fas fa-fw fa-solid fa-code-branch"></i>
                    <span>Branches Data</span>
                </a>
                <a class="collapse-item {{ request()->is('departemen') ? 'active' : '' }}" href="{{ url('/departemen') }}">
                    <i class="fas fa-fw fa-solid fa-building"></i>
                    <span>Departemen Data</span>
                </a>
                <a class="collapse-item {{ request()->is('userProfile') ? 'active' : '' }}" href="{{ url('/userProfile') }}">
                    <i class="fas fa-fw fa-solid fa-users"></i>
                    <span>Users Profile</span>
                </a>
                <a class="collapse-item {{ request()->is('import-csv') ? 'active' : '' }}" href="{{ url('/import-csv') }}">
                    <i class="fas fa-fw fa-solid fa-file"></i>
                    <span>Import Data</span>
                </a>
                <a class="collapse-item {{ request()->is('settingPath') ? 'active' : '' }}" href="{{ url('/settingPath') }}">
                    <i class="fas fa-fw fa-solid fa-pen"></i>
                    <span>Make File Csv</span>
                </a>
            </div>
        </div>
    </li>
    @endif

    <!-- Access Device -->
    @if (auth()->user()->hasAccess('device'))
    <li class="nav-item {{ request()->is('device') ? 'active' : '' }}" id="access-device">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#CollapsLogDevice" aria-expanded="true" aria-controls="CollapsLogDevice">
            <i class="fas fa-fw fa-solid fa-desktop"></i>
            <span>Device Management</span>
        </a>
        <div id="CollapsLogDevice" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ request()->is('deviceGroup') ? 'active' : '' }}" href="{{ url('/deviceGroup') }}">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Device Group Data</span>
                </a>
                <a class="collapse-item {{ request()->is('device') ? 'active' : '' }}" href="{{ url('/device') }}">
                    <i class="fas fa-fw fa-solid fa-file"></i>
                    <span>DeviceGate Data</span>
                </a>

                  <a class="collapse-item {{ request()->is('user-status') ? 'active' : '' }}" href="{{ url('/user-status') }}">
                    <i class="fas fa-fw fa-solid fa-file"></i>
                    <span>Status Entry</span>
                </a>

                  <a class="collapse-item {{ request()->is('dayzone') ? 'active' : '' }}" href="{{ url('/dayzone') }}">
                    <i class="fas fa-fw fa-solid fa-file"></i>
                    <span>Day Zone</span>
                </a>

                    <a class="collapse-item {{ request()->is('weekzone') ? 'active' : '' }}" href="{{ url('/weekzone') }}">
                    <i class="fas fa-fw fa-solid fa-file"></i>
                    <span>Week Zone </span>
                </a>
            </div>
        </div>
    </li>
    @endif

    <!-- Access Log -->
    @if (auth()->user()->hasAccess('log'))
    <li class="nav-item {{ request()->is('logData','logDevice','attendanceLog','') ? 'active' : '' }}" id="access-log">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#CollapsLog" aria-expanded="true" aria-controls="CollapsLog">
            <i class="fas fa-users-cog"></i>
            <span>Log Data</span>
        </a>
        <div id="CollapsLog" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ request()->is('log-user') ? 'active' : '' }}" href="{{ url('/log-user') }}">
                    <i class="fas fa-user-plus"></i>
                    <span>User Log</span>
                </a>

                <a class="collapse-item {{ request()->is('logInvalid') ? 'active' : '' }}" href="{{ url('/logInvalid') }}">
                    <i class="fas fa-user-times"></i>
                    <span>Invalid Log</span>
                </a>
                <a class="collapse-item {{ request()->is('attendanceLog') ? 'active' : '' }}" href="{{ url('/attendanceLog') }}">
                    <i class="fas fa-user-edit"></i>
                    <span>Attendance Data</span>
                </a>

            </div>
        </div>
    </li>
    @endif


    <!-- Attandance -->
    @if (auth()->user()->hasAccess('attendance'))
    <li class="nav-item {{ request()->is('holiday','leaveprocess','shift','absen') ? 'active' : '' }}" id="access-log">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#CollapAttendace" aria-expanded="true" aria-controls="CollapAttendace">
            <i class="fas fa-file"></i>
            <span>Attendance </span>
        </a>
        <div id="CollapAttendace" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

             {{-- <a class="collapse-item {{ request()->is('addshiftuser') ? 'active' : '' }}" href="{{ url('/addshiftuser') }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Add Shift User </span>
                </a> --}}

                <a class="collapse-item {{ request()->is('absen') ? 'active' : '' }}" href="{{ url('/absen') }}">
                    <i class="fas fa-user-edit"></i>
                    <span>Attendance Data</span>
                </a>


                <a class="collapse-item {{ request()->is('shift') ? 'active' : '' }}" href="{{ url('/shift') }}">

                    <i class="fas fa-fw fa-solid fa-file"></i>
                    <span>Shift Management</span>
                </a>

                <a class="collapse-item {{ request()->is('holiday') ? 'active' : '' }}" href="{{ url('/holiday') }}">
                    <i class="far fa-list-alt"></i>
                    <span>Holiday Management</span>
                </a>

                <a class="collapse-item {{ request()->is('leaveprocess') ? 'active' : '' }}" href="{{ url('/leaveprocess') }}">
                    <i class="far fa-share-square"></i>
                    <span>Leave Proccess </span>
                </a>

                <a class="collapse-item {{ request()->is('attendantSheet') ? 'active' : '' }}" href="{{ url('/attendantSheet') }}">
                    <i class="fas fa-share-square"></i>

                    <span>Attendent Sheet </span>
                </a>

                <a class="collapse-item {{ request()->is('attendance-summary') ? 'active' : '' }}" href="{{ url('/attendance-summary') }}">

                    <i class=" fas fa-regular fa-envelope"></i>
                    <span>Attendent Summary </span>
                </a>
            </div>
        </div>
    </li>
    @endif

    <!-- Access Setting -->
    @if (auth()->user()->hasAccess('setting'))
    <li class="nav-item {{ request()->is('setDatabase') ? 'active' : '' }}" id="access-setting">
        <a class="nav-link" href="{{ url('/setDatabase') }}">
            <i class="fas fa-fw fa-solid fa-database"></i>
            <span>Setting Database</span>
        </a>
    </li>
    <li class="nav-item {{ request()->is('api') ? 'active' : '' }}" id="access-setting">
        <a class="nav-link" href="{{ url('/api') }}">
            <i class="fas fa-cogs"></i>
            <span>Setting URL API</span>
        </a>
    </li>
    @endif

    <!-- Access Privilage -->
    @if (auth()->user()->hasAccess('userAdmin'))
    <li class="nav-item {{ request()->is('userAdmin') ? 'active' : '' }}" id="access-userAdmin">
        <a class="nav-link" href="{{ url('/userAdmin') }}">
            <i class="far fa-edit"></i>
            <span>Setting Privilage</span>
        </a>
    </li>

    @endif


      <!-- Access Privilage Shcedule  -->

        @if (auth()->user()->hasAccess('schedule'))
    <li class="nav-item {{ request()->is('schedule','pt_availability','pt_schedule') ? 'active' : '' }}" id="access-log">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Collapschedule" aria-expanded="true" aria-controls="Collapschedule">
           <i class="fas fa-portrait"></i>
            <span>Setting Schedule </span>
        </a>
        <div id="Collapschedule" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

          

                <a class="collapse-item {{ request()->is('pt_availability') ? 'active' : '' }}" href="{{ url('/pt_availability') }}">
                    <i class="fas fa-user-edit"></i>
                    <span>Setting Trainer</span>
                </a>


                <a class="collapse-item {{ request()->is('pt_schedule') ? 'active' : '' }}" href="{{ url('/pt_schedule') }}">

                    <i class="fas fa-fw fa-solid fa-file"></i>
                    <span>Setting Schedule</span>
                </a>

            
            </div>
        </div>
    </li>
    @endif


    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- Sidebar -->