<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <!-- Main Content -->
    <div id="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Search Form -->
            @if (Request::route()->named('logUser'))
            <form id="searchForm" action="{{ route('logUser') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="date_from" value="{{ request('date_from', now()->toDateString()) }}">
                <input type="hidden" name="date_to" value="{{ request('date_to', now()->toDateString()) }}">
                <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                <input type="hidden" name="dep_id" value="{{ request('dep_id') }}">
                <div class="input-group">
                    <input type="text" class="form-control mr-2" name="search_query" id="search_query" value="{{ $searchQuery ?? '' }}" placeholder="Search by User Address, Device SN, or Name">
                    <div class="input-group-append">
                        <button id="searchButton" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @elseif (Request::route()->named('attendanceLog'))
            <form id="searchForm" action="{{ route('attendanceLog') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <div class="input-group">
                    <input type="text" name="search" class="form-control bg-light border-0 small" placeholder="Find by Username, ID, atau Departemen" aria-label="Search" aria-describedby="basic-addon2" value="{{ $search ?? '' }}">
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    <input type="hidden" name="branch_id" value="{{ $branchId }}">
                    <input type="hidden" name="dep_id" value="{{ $depId }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>


            @elseif (Request::route()->named('userProfiles.index') || Request::is('userProfile*') || Request::is('userProfiles*'))
            <form id="searchForm" action="{{ route('userProfiles.index') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <div class="input-group">
                    <input type="text" name="search" class="form-control bg-light border-0 small" placeholder="Find by Username, ID, atau Departemen" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>

           
            @elseif (Request::route()->named('user-status.index') || Request::is('user-status*') || Request::is('user-status*'))
            <form id="mainFilterForm" action="{{ route('user-status.index') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <div class="input-group">
                   <input type="text" id="search" class="form-control bg-light border-0 small" 
               placeholder="Find by name, ID, dept..." value="{{ $search ?? '' }}">
        <div class="input-group-append">
            <button class="btn btn-primary" type="button" onclick="$('#filterForm').trigger('submit')">
                <i class="fas fa-search fa-sm"></i>
            </button>
        </div>
                </div>
            </form>

            @elseif (Request::route()->named('userAdmin.index'))
            <form id="searchForm" action="{{ route('userAdmin.index') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('model') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control bg-light border-0 small" placeholder="Find by Username, ID, atau Departemen" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @elseif (Request::route()->named('importCsv'))
            <form id="searchForm" action="{{ route('importCsv') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('importCsv') }}">
                <div class="input-group">
                    <input type="text" name="search2" class="form-control bg-light border-0 small" placeholder="Find by Data Guest" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search2') }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @elseif (Request::route()->named('device'))
            <form id="searchForm" action="{{ route('device') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('device') }}">
                <div class="input-group">
                    <input type="text" name="search2" class="form-control bg-light border-0 small" placeholder="Find by Data Guest" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search2') }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @elseif (Request::route()->named('openDoor'))
            <form id="searchForm" action="{{ route('openDoor') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('openDoor') }}">
                <div class="input-group">
                    <input type="text" name="search2" class="form-control bg-light border-0 small" placeholder="Find by Data Guest" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search2') }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @elseif (Request::route()->named('logDevice'))
            <form id="searchForm" action="{{ route('logDevice') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('model') }}">
                <div class="input-group">
                    <input type="text" name="search2" class="form-control bg-light border-0 small" placeholder="Find by Data Guest" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search2') }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @elseif (Request::route()->named('logUserData'))
            <form id="searchForm" action="{{ route('logUserData') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('model') }}">
                <div class="input-group">
                    <input type="text" name="search2" class="form-control bg-light border-0 small" placeholder="Find by Data Guest" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search2') }}">
                    <div class="input-group-append">
                        <button id="searchButton2" class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @elseif (Request::route()->named('addshiftuser.index'))
            <form id="searchForm" action="{{ route('addshiftuser.index') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('model') }}">
                <div class="input-group">
                    <input type="text" name="search_name" class="form-control bg-light border-0 small" placeholder="Find by Name User" aria-label="Search Name" value="{{ request('search_name') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>


            @elseif (Request::route()->named('absen.index'))
            <form id="searchForm" action="{{ route('absen.index') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <input type="hidden" name="model" value="{{ request('model') }}">
                <div class="input-group">
                    <input type="text" name="search_name" class="form-control bg-light border-0 small" placeholder="Find by Name User" aria-label="Search Name" value="{{ request('search_name') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            @endif


            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <div class="topbar-divider d-none d-sm-block"></div>
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="/sesi/logout" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->username }}</span>
                        <img class="img-profile rounded-circle" src="{{ asset('template') }}/img/undraw_profile.svg">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="/setDatabase">
                            <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                            Settings
                        </a>
                        <a class="dropdown-item" href="/sesi/logout" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>
</div>