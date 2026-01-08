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
            <li class="nav-item {{ request()->is('memberSchedule*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/memberSchedule') }}">
                    <i class="fas fa-calendar"></i>
                    <span>My Schedule Train</span>
                </a>
            </li>
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
           
        <div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Edit Jadwal Personal Trainer</h1>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('member_schedule.update', $schedule->ID) }}" method="POST" id="scheduleForm">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="PT_ID" class="form-label">Personal Trainer</label>
                    <select name="PT_ID" id="PT_ID" class="form-control @error('PT_ID') is-invalid @enderror" required>
                        <option value="">Pilih Personal Trainer</option>
                        @if($personalTrainers->isEmpty())
                        <option value="" disabled>Tidak ada trainer tersedia</option>
                        @else
                        @foreach($personalTrainers as $pt)
                        <option value="{{ $pt->ID }}" {{ old('PT_ID', $schedule->PT_ID) == $pt->ID ? 'selected' : '' }}>{{ $pt->NAME }} (Trainer)</option>
                        @endforeach
                        @endif
                    </select>
                    @error('PT_ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="START_TIME" class="form-label">Waktu Mulai</label>
                    <input type="datetime-local" name="START_TIME" id="START_TIME" class="form-control @error('START_TIME_formatted') is-invalid @enderror" value="{{ old('START_TIME', \Carbon\Carbon::parse($schedule->START_TIME)->format('Y-m-d\TH:i')) }}" required>
                    <input type="hidden" name="START_TIME_formatted" id="START_TIME_formatted">
                    @error('START_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="END_TIME" class="form-label">Waktu Selesai</label>
                    <input type="datetime-local" name="END_TIME" id="END_TIME" class="form-control @error('END_TIME_formatted') is-invalid @enderror" value="{{ old('END_TIME', \Carbon\Carbon::parse($schedule->END_TIME)->format('Y-m-d\TH:i')) }}" required>
                    <input type="hidden" name="END_TIME_formatted" id="END_TIME_formatted">
                    @error('END_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="BOOKED_AT" class="form-label">Waktu Pemesanan</label>
                    <input type="datetime-local" name="BOOKED_AT" id="BOOKED_AT" class="form-control @error('BOOKED_AT_formatted') is-invalid @enderror" value="{{ old('BOOKED_AT', \Carbon\Carbon::parse($schedule->BOOKED_AT)->format('Y-m-d\TH:i')) }}" required>
                    <input type="hidden" name="BOOKED_AT_formatted" id="BOOKED_AT_formatted">
                    @error('BOOKED_AT_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="STATUS" class="form-label">Status</label>
                    <select name="STATUS" id="STATUS" class="form-control @error('STATUS') is-invalid @enderror" required>
                        <option value="0" {{ old('STATUS', $schedule->STATUS) == '0' ? 'selected' : '' }}>Booked</option>
                        <option value="1" {{ old('STATUS', $schedule->STATUS) == '1' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('STATUS')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="NOTE" class="form-label">Catatan</label>
                    <textarea name="NOTE" id="NOTE" class="form-control @error('NOTE') is-invalid @enderror">{{ old('NOTE', $schedule->NOTE) }}</textarea>
                    @error('NOTE')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('member_schedule.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

    <!-- Daftar Ketersediaan Trainer -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ketersediaan Trainer</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Trainer</th>
                        <th>Hari</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @if($availabilities->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada ketersediaan trainer.</td>
                    </tr>
                    @else
                    @foreach($availabilities as $availability)
                    <tr>
                        <td>{{ $availability->personalTrainer->NAME ?? 'N/A' }}</td>
                        <td>{{ $availability->day_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($availability->START_TIME)->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($availability->END_TIME)->format('H:i') }}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
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

    <script>
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    const startTime = document.getElementById('START_TIME').value;
    const endTime = document.getElementById('END_TIME').value;
    const bookedAt = document.getElementById('BOOKED_AT').value;

    if (startTime) {
        document.getElementById('START_TIME_formatted').value = startTime.replace('T', ' ') + ':00';
    }
    if (endTime) {
        document.getElementById('END_TIME_formatted').value = endTime.replace('T', ' ') + ':00';
    }
    if (bookedAt) {
        document.getElementById('BOOKED_AT_formatted').value = bookedAt.replace('T', ' ') + ':00';
    }
});
</script>
</body>
</html>