{{-- UNTUK ATTENDANE VIEW INI TIDAK DIGUNAKAN YANG DIGUNAKAN ADA PADA FOLDE ABSEN/INDEX.blade.php --}}

@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Add Report to Users</h1>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex">
                    <!-- Add Report Form -->
                    <form id="addReportForm" action="{{ route('addshiftuser.addReport') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm float-end mb-3" id="addReportButton" disabled>Add Report</button>
                        <button type="button" class="btn btn-success btn-sm float-end mb-3 me-2" id="reportButton">Report</button>
                        <button type="button" class="btn btn-info btn-sm float-end mb-3 me-2" id="recapButton" style="display: none;">View Recap</button>
                        <input type="hidden" name="PatternID" id="reportPatternID">
                    </form>

                    <form id="searchForm" action="{{ route('addshiftuser.index') }}" method="GET" class="form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <select name="search_department" class="form-control bg-light border-0 small ml-3">
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $department)
                                <option value="{{ $department->name }}" {{ request('search_department') == $department->name ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <form action="{{ route('addshiftuser.bulkAssign') }}" method="POST" id="bulkAssignForm">
                        @csrf
                        <div class="mb-3">
                            <label for="PatternID" class="form-label">Select Shift Pattern</label>
                            <select name="PatternID" class="form-control" id="PatternID">
                                <option value="">-- Select Pattern --</option>
                                @foreach (\App\Models\ShiftPattern::all() as $pattern)
                                <option value="{{ $pattern->Id }}">{{ $pattern->PatternName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="month" class="form-label">Select Month</label>
                            <select name="month" class="form-control" id="month">
                                <option value="">-- Select Month --</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Name</th>
                                    <th>Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($users->isEmpty())
                                <tr>
                                    <td colspan="3">No users found.</td>
                                </tr>
                                @else
                                @foreach ($users as $user)
                                <tr>
                                    <td><input type="checkbox" name="user_ids[]" value="{{ $user->ID }}" class="user-checkbox"></td>
                                    <td>{{ $user->NAME }}</td>
                                    <td>{{ $user->department ? $user->department->name : 'No Department' }}</td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4" id="shiftPatternCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Shift Pattern Details</h6>
                </div>
                <div class="card-body">
                    <h6 id="patternName"></h6>
                    <p><strong>Pattern Type:</strong> <span id="patternType"></span></p>
                    <p><strong>Monday:</strong> <span id="pola1"></span></p>
                    <p><strong>Tuesday:</strong> <span id="pola2"></span></p>
                    <p><strong>Wednesday:</strong> <span id="pola3"></span></p>
                    <p><strong>Thursday:</strong> <span id="pola4"></span></p>
                    <p><strong>Friday:</strong> <span id="pola5"></span></p>
                    <p><strong>Saturday:</strong> <span id="pola6"></span></p>
                    <p><strong>Sunday:</strong> <span id="pola7"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="{{ asset('js/addshiftuser/addshiftuser.js') }}"></script>
<!-- Include SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

</script>

@endsection