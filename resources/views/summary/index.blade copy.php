@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <!-- Judul halaman untuk menambah laporan ke ringkasan -->
    <h1 class="h3 mb-4 text-gray-800">Tambah Laporan ke Ringkasan</h1>

    <!-- Menampilkan notifikasi sukses jika ada -->
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Menampilkan notifikasi error jika ada -->
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <!-- Bagian header kartu untuk form input -->
                <div class="card-header py-3 d-flex">
                    <!-- Form untuk mengirim data ke rute generate -->
                    <form method="POST" action="{{ route('attendantSheet.generate') }}" id="attendanceForm">
                        @csrf
                        <div class="form-group mb-3">
                            <div class="row d-flex">
                                <div class="col-md-12">
                                    <!-- Input untuk memilih tanggal mulai -->
                                    <div class="form-group mb-3">
                                        <label for="StartPeriod">Tanggal Mulai</label>
                                        <input type="date" class="form-control" name="StartPeriod" id="StartPeriod"
                                            value="{{ request()->input('StartPeriod', now()->startOfMonth()->format('Y-m-d')) }}">
                                    </div>

                                    <!-- Input untuk memilih tanggal akhir -->
                                    <div class="form-group mb-3">
                                        <label for="EndPeriod">Tanggal Akhir</label>
                                        <input type="date" class="form-control" name="EndPeriod" id="EndPeriod"
                                            value="{{ request()->input('EndPeriod', now()->endOfMonth()->format('Y-m-d')) }}">
                                    </div>

                                    <!-- Dropdown untuk memilih bulan -->
                                    <div class="form-group mb-3">
                                        <label for="month">Pilih Bulan</label>
                                        <select class="form-control" name="month" id="month">
                                            @foreach (range(1, 12) as $m)
                                            <!-- Menampilkan nama bulan dengan format lengkap (misalnya, Januari) -->
                                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol untuk membuka modal pemilihan karyawan -->
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#employeeModal">
                            Pilih Karyawan
                        </button>

                        <!-- Tombol untuk generate laporan -->
                        <button type="submit" name="generate" value="1" class="btn btn-primary">Generate</button>
                        <!-- Tombol untuk ekspor data -->
                        <button type="submit" name="export" value="1" class="btn btn-success" formaction="{{ route('attendantSheet.index') }}">Ekspor</button>

                        <!-- Input tersembunyi untuk menyimpan karyawan yang dipilih -->
                        @foreach ($selectedUsers as $userId)
                        <input type="hidden" name="selected_users[]" value="{{ $userId }}">
                        @endforeach
                    </form>
                </div>

                <!-- Bagian isi kartu untuk tabel data -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- Kolom untuk checkbox memilih semua -->
                                <th><input type="checkbox" id="selectAll"></th>
                                <!-- Kolom untuk nama dan departemen -->
                                <th>Nama</th>
                                <th>Departemen</th>
                                <!-- Kolom untuk menampilkan data bulan yang dipilih -->
                                <th>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($selectedUsers))
                            @foreach ($users as $user)
                            @if (in_array($user->ID, $selectedUsers))
                            <tr>
                                <!-- Checkbox untuk memilih karyawan -->
                                <td><input type="checkbox" name="selected_users[]" value="{{ $user->ID }}" class="user-checkbox" checked></td>
                                <td>{{ $user->NAME }}</td>
                                <td>{{ $user->department ? $user->department->name : 'N/A' }}</td>
                                <td>
                                    <!-- Menampilkan data ringkasan kehadiran untuk bulan yang dipilih -->
                                    @php
                                    $summary = $monthlyData[$month]->where('EmployeeID', $user->ID)->first();
                                    @endphp
                                    @if ($summary)
                                    Hadir: {{ $summary->Present }}<br>
                                    Absen: {{ $summary->Absent }}<br>
                                    Terlambat Masuk: {{ $summary->LateIn }}<br>
                                    Pulang Awal: {{ $summary->EarlyOut }}<br>
                                    Cuti Diambil: {{ $summary->LeaveTaken }}
                                    @else
                                    Tidak Ada Data
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @endforeach
                            @else
                            <!-- Pesan jika tidak ada karyawan yang dipilih -->
                            <tr>
                                <td colspan="4">Tidak ada karyawan yang dipilih.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk memilih karyawan -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalLabel">Pilih Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <!-- Checkbox untuk memilih semua karyawan di modal -->
                            <th><input type="checkbox" id="selectAllModal"></th>
                            <th>Nama</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <!-- Checkbox untuk memilih karyawan individu -->
                            <td><input type="checkbox" name="selected_users[]" value="{{ $user->ID }}" class="employee-checkbox"
                                    {{ in_array($user->ID, request()->input('selected_users', [])) ? 'checked' : '' }}></td>
                            <td>{{ $user->NAME }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">Tidak ada karyawan ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <!-- Tombol untuk menutup modal -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <!-- Tombol untuk menyimpan pilihan karyawan -->
                <button type="button" class="btn btn-primary" id="saveEmployees">Simpan Pilihan</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk fungsi Select All dan Save Selection -->
<script>
    // Event listener untuk checkbox "Select All" di modal
    document.getElementById('selectAllModal').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.employee-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Event listener untuk checkbox "Select All" di tabel utama
    document.getElementById('selectAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Event listener untuk tombol "Simpan Pilihan" di modal
    document.getElementById('saveEmployees').addEventListener('click', function() {
        let selectedUsers = [];
        document.querySelectorAll('.employee-checkbox:checked').forEach(checkbox => {
            if (checkbox.value && checkbox.value !== 'null') { // Lewati nilai null atau kosong
                selectedUsers.push(checkbox.value);
            }
        });

        console.log('Karyawan yang Dipilih:', selectedUsers); // Debug: Cek nilai yang dipilih

        // Hapus input tersembunyi yang sudah ada
        let form = document.getElementById('attendanceForm');
        let existingInputs = form.querySelectorAll('input[name="selected_users[]"]');
        existingInputs.forEach(input => input.remove());

        // Tambahkan input tersembunyi baru untuk setiap karyawan yang dipilih
        if (selectedUsers.length === 0) {
            Swal.fire('Error', 'Pilih setidaknya satu karyawan.', 'error');
            return;
        }

        selectedUsers.forEach(userId => {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_users[]';
            input.value = userId;
            form.appendChild(input);
        });

        // Kirim form untuk memperbarui tabel utama
        form.submit();

        // Tutup modal
        let modal = bootstrap.Modal.getInstance(document.getElementById('employeeModal'));
        modal.hide();
    });
</script>

<!-- Include SweetAlert2 dan Bootstrap JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection