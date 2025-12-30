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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::guard('member')->user()->NAME ?? 'Member' }}</span>
                                <img class="img-profile rounded-circle" src="{{ asset('template') }}/img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {!! session('success') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <h1 class="h3 mb-2 text-gray-800">My Schedule Train</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Jadwal Saya</h6>
                            
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Trainer</th>
                                        <th>Tanggal & Waktu</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($schedules->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada jadwal tersedia.</td>
                                    </tr>
                                    @else
                                    @foreach($schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->personalTrainer->NAME ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($schedule->START_TIME)->format('Y-m-d H:i') }} - {{ \Carbon\Carbon::parse($schedule->END_TIME)->format('H:i') }}</td>
                                        <td>{{ $schedule->getStatusTextAttribute() }}</td>
                                        <td>{{ $schedule->NOTE ?? '-' }}</td>
                                        <td>
                                            @if($schedule->STATUS != 1)
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editScheduleModal{{ $schedule->ID }}">Edit</button>
                                            {{-- <form action="{{ route('member_schedule.destroy', $schedule->ID) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form> --}}
                                            @endif
                                        </td>
                                    </tr>
                                    <!-- Edit Schedule Modal -->
                                   <!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal{{ $schedule->ID }}" tabindex="-1" aria-labelledby="editScheduleModalLabel{{ $schedule->ID }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editScheduleModalLabel{{ $schedule->ID }}">Edit Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('member_schedule.update', $schedule->ID) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="PT_ID_{{ $schedule->ID }}" class="form-label">Personal Trainer</label>
                        <select name="PT_ID" id="PT_ID_{{ $schedule->ID }}" class="form-control @error('PT_ID') is-invalid @enderror" disabled required>
                            <option value="">Pilih Personal Trainer</option>
                            @foreach($personalTrainers as $pt)
                            <option value="{{ $pt->ID }}" {{ old('PT_ID', $schedule->PT_ID) == $pt->ID ? 'selected' : '' }}>{{ $pt->NAME }} (Trainer)</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="PT_ID" value="{{ $schedule->PT_ID }}"> <!-- Hidden input to send PT_ID -->
                        @error('PT_ID')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="START_TIME_{{ $schedule->ID }}" class="form-label">Waktu Mulai</label>
                        <input type="datetime-local" name="START_TIME" id="START_TIME_{{ $schedule->ID }}" class="form-control @error('START_TIME') is-invalid @enderror" value="{{ old('START_TIME', \Carbon\Carbon::parse($schedule->START_TIME)->format('Y-m-d\TH:i')) }}" disabled required>
                        <input type="hidden" name="START_TIME" value="{{ \Carbon\Carbon::parse($schedule->START_TIME)->format('Y-m-d\TH:i') }}"> <!-- Hidden input to send START_TIME -->
                        @error('START_TIME')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="END_TIME_{{ $schedule->ID }}" class="form-label">Waktu Selesai</label>
                        <input type="datetime-local" name="END_TIME" id="END_TIME_{{ $schedule->ID }}" class="form-control @error('END_TIME') is-invalid @enderror" value="{{ old('END_TIME', \Carbon\Carbon::parse($schedule->END_TIME)->format('Y-m-d\TH:i')) }}" disabled required>
                        <input type="hidden" name="END_TIME" value="{{ \Carbon\Carbon::parse($schedule->END_TIME)->format('Y-m-d\TH:i') }}"> <!-- Hidden input to send END_TIME -->
                        @error('END_TIME')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="BOOKED_AT_{{ $schedule->ID }}" class="form-label">Waktu Pemesanan</label>
                        <input type="datetime-local" name="BOOKED_AT" id="BOOKED_AT_{{ $schedule->ID }}" class="form-control @error('BOOKED_AT') is-invalid @enderror" value="{{ old('BOOKED_AT', \Carbon\Carbon::parse($schedule->BOOKED_AT)->format('Y-m-d\TH:i')) }}" disabled required>
                        <input type="hidden" name="BOOKED_AT" value="{{ \Carbon\Carbon::parse($schedule->BOOKED_AT)->format('Y-m-d\TH:i') }}"> <!-- Hidden input to send BOOKED_AT -->
                        @error('BOOKED_AT')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="STATUS_{{ $schedule->ID }}" class="form-label">Status</label>
                        <select name="STATUS" id="STATUS_{{ $schedule->ID }}" class="form-control @error('STATUS') is-invalid @enderror" required>
                            <option value="0" {{ old('STATUS', $schedule->STATUS) == '0' ? 'selected' : '' }}>Booked</option>
                            <option value="1" {{ old('STATUS', $schedule->STATUS) == '1' ? 'selected' : '' }}>Selesai</option>
                            <option value="2" {{ old('STATUS', $schedule->STATUS) == '2' ? 'selected' : '' }}>Batal</option>
                        </select>
                        @error('STATUS')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="NOTE_{{ $schedule->ID }}" class="form-label">Catatan</label>
                        <textarea name="NOTE" id="NOTE_{{ $schedule->ID }}" class="form-control @error('NOTE') is-invalid @enderror">{{ old('NOTE', $schedule->NOTE) }}</textarea>
                        @error('NOTE')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <h2 class="h4 mb-2 text-gray-800">Ketersediaan Trainer</h2>
                    <div class="row">
                        @if($availabilities->isEmpty())
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-body text-center">
                                    Tidak ada ketersediaan trainer.
                                </div>
                            </div>
                        </div>
                        @else
                        @foreach($availabilities->groupBy('PT_ID') as $ptId => $trainerAvailabilities)
                        <div class="col-md-4 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        {{ $trainerAvailabilities->first()->personalTrainer->NAME ?? 'N/A' }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                   @foreach($trainerAvailabilities as $availability)
                                        <div class="mb-3">
                                            <p><strong>Hari:</strong> {{ $availability->day_name }}</p>
                                            <p><strong>Waktu:</strong> {{ \Carbon\Carbon::parse($availability->START_TIME) }} - {{ \Carbon\Carbon::parse($availability->END_TIME)->format('H:i') }}</p>
                                            <form action="{{ route('member_schedule.book') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="availability_id" value="{{ $availability->ID }}">
                                                <input type="hidden" name="PT_ID" value="{{ $availability->PT_ID }}">
                                                <input type="hidden" name="START_TIME_formatted" value="{{ \Carbon\Carbon::parse($availability->START_TIME)->format('Y-m-d H:i:s') }}">
                                                <input type="hidden" name="END_TIME_formatted" value="{{ \Carbon\Carbon::parse($availability->END_TIME)->format('Y-m-d H:i:s') }}">
                                                <div class="form-group">
                                                    <label for="NOTE_{{ $availability->ID }}">Catatan</label>
                                                    <textarea name="NOTE" id="NOTE_{{ $availability->ID }}" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="BOOKED_AT_{{ $availability->ID }}">Tanggal Pemesanan</label>
                                                    <input type="date" name="BOOKED_AT" id="BOOKED_AT_{{ $availability->ID }}" class="form-control" value="{{ \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d') }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="STATUS_{{ $availability->ID }}">Status</label>
                                                    <select name="STATUS" id="STATUS_{{ $availability->ID }}" class="form-control" required>
                                                        <option value="0">Booked</option>
                                                        <option value="1">Selesai</option>
                                                        <option value="2">Batal</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm">Book Now</button>
                                            </form>
                                        </div>
                                        <hr>
                                        @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
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
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Ready to Leave?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="/sesi/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('template') }}/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="{{ asset('template') }}/js/sb-admin-2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('[data-bs-toggle="modal"]').on('click', function() {
                var target = $(this).attr('data-bs-target');
                console.log('Modal trigger clicked, target:', target);
                $(target).modal('show');
            });
        });
    </script>
</body>
</html>