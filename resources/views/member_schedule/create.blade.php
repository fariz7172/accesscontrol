@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Buat Jadwal Personal Trainer</h1>

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
            <form action="{{ route('member_schedule.store') }}" method="POST" id="scheduleForm">
                @csrf
                <div class="mb-3">
                    <label for="PT_ID" class="form-label">Personal Trainer</label>
                    <select name="PT_ID" id="PT_ID" class="form-control @error('PT_ID') is-invalid @enderror" required>
                        <option value="">Pilih Personal Trainer</option>
                        @if($personalTrainers->isEmpty())
                        <option value="" disabled>Tidak ada trainer tersedia</option>
                        @else
                        @foreach($personalTrainers as $pt)
                        <option value="{{ $pt->ID }}" {{ old('PT_ID') == $pt->ID ? 'selected' : '' }}>{{ $pt->NAME }} (Trainer)</option>
                        @endforeach
                        @endif
                    </select>
                    @error('PT_ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="START_TIME" class="form-label">Waktu Mulai</label>
                    <input type="datetime-local" name="START_TIME" id="START_TIME" class="form-control @error('START_TIME_formatted') is-invalid @enderror" value="{{ old('START_TIME') ? \Carbon\Carbon::parse(old('START_TIME'))->format('Y-m-d\TH:i') : '' }}" required>
                    <input type="hidden" name="START_TIME_formatted" id="START_TIME_formatted">
                    @error('START_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="END_TIME" class="form-label">Waktu Selesai</label>
                    <input type="datetime-local" name="END_TIME" id="END_TIME" class="form-control @error('END_TIME_formatted') is-invalid @enderror" value="{{ old('END_TIME') ? \Carbon\Carbon::parse(old('END_TIME'))->format('Y-m-d\TH:i') : '' }}" required>
                    <input type="hidden" name="END_TIME_formatted" id="END_TIME_formatted">
                    @error('END_TIME_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="BOOKED_AT" class="form-label">Waktu Pemesanan</label>
                    <input type="datetime-local" name="BOOKED_AT" id="BOOKED_AT" class="form-control @error('BOOKED_AT_formatted') is-invalid @enderror" value="{{ old('BOOKED_AT') ? \Carbon\Carbon::parse(old('BOOKED_AT'))->format('Y-m-d\TH:i') : now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" required>
                    <input type="hidden" name="BOOKED_AT_formatted" id="BOOKED_AT_formatted">
                    @error('BOOKED_AT_formatted')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="STATUS" class="form-label">Status</label>
                    <select name="STATUS" id="STATUS" class="form-control @error('STATUS') is-invalid @enderror" required>
                        <option value="0" {{ old('STATUS') == '0' ? 'selected' : '' }}>Booked</option>
                        <option value="1" {{ old('STATUS') == '1' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('STATUS')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="NOTE" class="form-label">Catatan</label>
                    <textarea name="NOTE" id="NOTE" class="form-control @error('NOTE') is-invalid @enderror">{{ old('NOTE') }}</textarea>
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
@endsection