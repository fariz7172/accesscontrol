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

<!-- Include SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Track whether Add Report has been successfully submitted
        let isReportAdded = false;

        // Select All Checkbox Functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateReportUserIds();
        });

        // Update user_ids[] in report form when checkboxes change
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateReportUserIds);
        });

        function updateReportUserIds() {
            const reportForm = document.getElementById('addReportForm');
            const checkedUserIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                .map(checkbox => checkbox.value);
            // Remove existing hidden inputs
            document.querySelectorAll('.user-checkbox-hidden').forEach(input => input.remove());
            // Add new hidden inputs for checked users
            checkedUserIds.forEach(userId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userId;
                input.className = 'user-checkbox-hidden';
                reportForm.appendChild(input);
            });
            // Update hidden month input
            const monthInput = document.createElement('input');
            monthInput.type = 'hidden';
            monthInput.name = 'month';
            monthInput.value = monthSelect.value || '';
            monthInput.className = 'user-checkbox-hidden';
            reportForm.appendChild(monthInput);
            // Enable/disable Add Report button
            addReportButton.disabled = !patternSelect.value || checkedUserIds.length === 0 || !monthSelect.value;
            // Recap button visibility
            recapButton.style.display = (patternSelect.value && checkedUserIds.length > 0 && monthSelect.value) ? 'inline-block' : 'none';
        }

        // PatternID Dropdown Change Handler
        const patternSelect = document.getElementById('PatternID');
        const addReportButton = document.getElementById('addReportButton');
        const reportPatternID = document.getElementById('reportPatternID');
        const monthSelect = document.getElementById('month');
        const reportButton = document.getElementById('reportButton');
        const recapButton = document.getElementById('recapButton');

        patternSelect.addEventListener('change', function() {
            const patternId = this.value;
            const card = document.getElementById('shiftPatternCard');

            // Update reportPatternID
            if (patternId) {
                reportPatternID.value = patternId;
                updateReportUserIds(); // Update user_ids, Add Report button state, and Recap button visibility
            } else {
                addReportButton.disabled = true;
                reportPatternID.value = '';
                updateReportUserIds(); // Update Recap button visibility
            }

            if (!patternId) {
                card.style.display = 'none';
                return;
            }

            fetch('{{ url("/shift-pattern") }}/' + patternId, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        card.style.display = 'none';
                        return;
                    }

                    // Update card content
                    document.getElementById('patternName').textContent = data.PatternName;
                    document.getElementById('patternType').textContent = data.PatternType || 'N/A';

                    // Helper function to format shift details
                    const formatShift = (pola, day) => {
                        if (!pola) return 'N/A';
                        const type = pola.Tipe == 1 ? 'Working Days' : (pola.Tipe == 2 ? 'Off Days' : 'Unknown');
                        return `${pola.ShiftName}, In: ${pola.Begin_Time}, Out: ${pola.Out_Time}, Type: ${type}`;
                    };

                    // Format pola1 to pola7 with day names and Tipe
                    document.getElementById('pola1').textContent = formatShift(data.pola1, 'Monday');
                    document.getElementById('pola2').textContent = formatShift(data.pola2, 'Tuesday');
                    document.getElementById('pola3').textContent = formatShift(data.pola3, 'Wednesday');
                    document.getElementById('pola4').textContent = formatShift(data.pola4, 'Thursday');
                    document.getElementById('pola5').textContent = formatShift(data.pola5, 'Friday');
                    document.getElementById('pola6').textContent = formatShift(data.pola6, 'Saturday');
                    document.getElementById('pola7').textContent = formatShift(data.pola7, 'Sunday');

                    // Show the card
                    card.style.display = 'block';
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    card.style.display = 'none';
                });
        });

        // Month Dropdown Change Handler
        monthSelect.addEventListener('change', function() {
            updateReportUserIds(); // Update Add Report button state and Recap button visibility
        });

        // Handle form submission for Add Report
        document.getElementById('addReportForm').addEventListener('submit', function(e) {
            if (addReportButton.disabled) {
                e.preventDefault(); // Prevent submission if already processing
                return;
            }
            addReportButton.disabled = true;
            addReportButton.textContent = 'Processing...';

            // Perform the form submission via fetch
            fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Reset the Add Report button
                    addReportButton.disabled = false;
                    addReportButton.textContent = 'Add Report';
                    if (data.success) {
                        // Mark report as added
                        isReportAdded = true;
                        // Show success message with SweetAlert2
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Handle error with SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'Failed to create attendance records ,there are users not registered.',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Submission error:', error);
                    addReportButton.disabled = false;
                    addReportButton.textContent = 'Add Report';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonText: 'OK'
                    });
                });

            // Prevent default form submission since we're using fetch
            e.preventDefault();
        });

        // Report Button Click Handler
        reportButton.addEventListener('click', function() {
            const checkedUserIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                .map(checkbox => checkbox.value);
            const month = monthSelect.value;
            const patternId = patternSelect.value;

            // Validate required fields
            if (!checkedUserIds.length || !month || !patternId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    text: 'Please select users, month, and shift pattern.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Check if Add Report has been completed
            if (!isReportAdded) {
                Swal.fire({
                    icon: 'question',
                    title: 'Confirm',
                    text: 'Make sure the Add Report has been created?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return; // Stop if user clicks "Tidak"
                    }
                    // Proceed with export if user clicks "Ya"
                    proceedWithExport(checkedUserIds, month, patternId);
                });
            } else {
                // Proceed with export directly if report is added
                proceedWithExport(checkedUserIds, month, patternId);
            }
        });

        // Function to handle export form submission
        function proceedWithExport(checkedUserIds, month, patternId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("addshiftuser.export") }}';
            form.style.display = 'none';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add user_ids
            checkedUserIds.forEach(userId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userId;
                form.appendChild(input);
            });

            // Add month
            const monthInput = document.createElement('input');
            monthInput.type = 'hidden';
            monthInput.name = 'month';
            monthInput.value = month;
            form.appendChild(monthInput);

            // Add PatternID
            const patternInput = document.createElement('input');
            patternInput.type = 'hidden';
            patternInput.name = 'PatternID';
            patternInput.value = patternId;
            form.appendChild(patternInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Recap Button Click Handler
        recapButton.addEventListener('click', function() {
            const checkedUserIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                .map(checkbox => checkbox.value);
            const month = monthSelect.value;
            const patternId = patternSelect.value;

            if (!checkedUserIds.length || !month || !patternId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    text: 'Please select users, month, and shift pattern.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Create form to submit data for recap
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '{{ route("addshiftuser.recap") }}';
            form.style.display = 'none';

            // Add user_ids
            checkedUserIds.forEach(userId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userId;
                form.appendChild(input);
            });

            // Add month
            const monthInput = document.createElement('input');
            monthInput.type = 'hidden';
            monthInput.name = 'month';
            monthInput.value = month;
            form.appendChild(monthInput);

            // Add PatternID
            const patternInput = document.createElement('input');
            patternInput.type = 'hidden';
            patternInput.name = 'PatternID';
            patternInput.value = patternId;
            form.appendChild(patternInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });
    });
</script>

@endsection