{{-- resources/views/device/edit.blade.php --}}
@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">×</button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="h3 mb-4 text-gray-800">Edit Device</h1>

    <div class="card shadow">
        <div class="card-body">

            <form action="{{ route('device.update', $device->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Basic Fields -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $device->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Number <span class="text-danger">*</span></label>
                            <input type="text" name="number" class="form-control @error('number') is-invalid @enderror"
                                   value="{{ old('number', $device->number) }}" required>
                            @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <input type="hidden" name="flagstatus" value="{{ old('flagstatus', $device->flagstatus) }}">

                <div class="form-group">
                    <label>Device Type <span class="text-danger">*</span></label>
                    <select name="type" id="edit-type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="50" {{ old('type', $device->type) == 50 ? 'selected' : '' }}>Face ID</option>
                        <option value="0"  {{ old('type', $device->type) == 0  ? 'selected' : '' }}>Finger Print</option>
                        <option value="1"  {{ old('type', $device->type) == 1  ? 'selected' : '' }}>Soyal (Lift Controller)</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>SN <span class="text-danger">*</span></label>
                            <input type="text" name="sn" class="form-control @error('sn') is-invalid @enderror"
                                   value="{{ old('sn', $device->sn) }}" required>
                            @error('sn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>IP Address <span class="text-danger">*</span></label>
                            <input type="text" name="ip" class="form-control @error('ip') is-invalid @enderror"
                                   value="{{ old('ip', $device->ip) }}" required>
                            @error('ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Node ID <span class="text-danger">*</span></label>
                            <input type="number" name="nodeid" class="form-control @error('nodeid') is-invalid @enderror"
                                   value="{{ old('nodeid', $device->nodeid) }}" required>
                            @error('nodeid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                           value="{{ old('description', $device->description) }}" required>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="stat" class="form-control @error('stat') is-invalid @enderror" required>
                        <option value="0" {{ old('stat', $device->stat) == 0 ? 'selected' : '' }}>IN</option>
                        <option value="1" {{ old('stat', $device->stat) == 1 ? 'selected' : '' }}>OUT</option>
                    </select>
                </div>

                <!-- ==================== LIFT SETTINGS ==================== -->
                <div id="liftSettings" class="d-none" style="border:2px solid #007bff; border border-radius:10px; padding:20px; margin-top:20px; background:#f8f9ff;">
                    <h5 class="text-primary mb-4">Konfigurasi Akses Lantai per Lift</h5>

                    @php
                        $floors = [
                            1   => ['bit' => 1,   'name' => 'Lantai 1'],
                            2   => ['bit' => 2,   'name' => 'Lantai 2'],
                            4   => ['bit' => 4,   'name' => 'Lantai 3'],
                            8   => ['bit' => 8,   'name' => 'Lantai 4'],
                            16  => ['bit' => 16,  'name' => 'Lantai 5'],
                            32  => ['bit' => 32,  'name' => 'Lantai 6'],
                            64  => ['bit' => 64,  'name' => 'Lantai 7'],
                            128 => ['bit' => 128, 'name' => 'Lantai 8'],
                        ];
                    @endphp

                    <div class="row">
                        @foreach(['Lift1' => 'lift1', 'Lift2' => 'lift2', 'Lift3' => 'lift3', 'Lift4' => 'lift4'] as $liftName => $prefix)
                        <div class="col-md-3 mb-4">
                            <div class="card border-primary shadow-sm h-100">
                                <div class="card-header bg-primary text-white text-center py-2">
                                    <strong>{{ $liftName }}</strong>
                                </div>
                                <div class="card-body">
                                    @php
                                        $currentValue = old($liftName, $device->{$liftName} ?? 0);
                                    @endphp

                                    @foreach($floors as $bit => $floor)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input lift-floor-checkbox" type="checkbox"
                                               name="{{ $prefix }}_floors[]" value="{{ $bit }}"
                                               id="{{ $prefix }}_f{{ $bit }}"
                                               {{ ($currentValue != 255 && ($currentValue & $bit)) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="{{ $prefix }}_f{{ $bit }}">
                                            {{ $floor['name'] }}
                                        </label>
                                    </div>
                                    @endforeach

                                    <hr class="my-3">
                                    <div class="form-check">
                                        <input class="form-check-input lift-full-access" type="checkbox"
                                               id="{{ $prefix }}_full"
                                               {{ $currentValue == 255 ? 'checked' : '' }}>
                                        <label class="form-check-label text-success font-weight-bold" for="{{ $prefix }}_full">
                                            Full Access (255)
                                        </label>
                                    </div>
                                </div>

                                <div class="card-footer text-center bg-light small">
                                    Nilai: <strong id="{{ $prefix }}_value">{{ $currentValue }}</strong>
                                    <input type="hidden" name="{{ $liftName }}" id="{{ $liftName }}_input" value="{{ $currentValue }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <!-- ==================== END LIFT SETTINGS ==================== -->

                <!-- Tombol Submit -->
                <div class="mt-5 text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        Update Device
                    </button>
                    <a href="{{ route('device') }}" class="btn btn-secondary btn-lg px-5 ml-3">
                        Kembali
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- jQuery & Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    // 1. Toggle Lift Settings
    function toggleLiftSettings() {
        if ($('#edit-type').val() == '1') {
            $('#liftSettings').removeClass('d-none').hide().slideDown(400);
        } else {
            $('#liftSettings').slideUp(400, function () {
                $(this).addClass('d-none');
            });
        }
    }

    toggleLiftSettings();               // jalankan saat load
    $('#edit-type').on('change', toggleLiftSettings);

    // 2. Hitung nilai bitmask
    function calculateLiftValue(prefix) {
        let total = $(`#${prefix}_full`).is(':checked') ? 255 : 0;
        if (total === 0) {
            $(`input[name="${prefix}_floors[]"]:checked`).each(function () {
                total += parseInt(this.value);
            });
        }
        $(`#${prefix}_value`).text(total);
        $(`#Lift${prefix.slice(-1)}_input`).val(total);
    }

    // 3. Event checkbox
    $(document).on('change', '.lift-floor-checkbox, .lift-full-access', function () {
        const prefix = this.id.replace('_full', '').replace(/_f\d+$/, '');
        if ($(this).hasClass('lift-full-access') && this.checked) {
            $(`input[name="${prefix}_floors[]"]`).prop('checked', false);
        }
        if ($(this).hasClass('lift-floor-checkbox') && this.checked) {
            $(`#${prefix}_full`).prop('checked', false);
        }
        calculateLiftValue(prefix);
    });

    // 4. Inisialisasi nilai dari database
    ['lift1', 'lift2', 'lift3', 'lift4'].forEach(function (prefix) {
        const value = parseInt($(`#Lift${prefix.slice(-1)}_input`).val()) || 0;
        if (value === 255) {
            $(`#${prefix}_full`).prop('checked', true);
        } else {
            [1,2,4,8,16,32,64,128].forEach(function (bit) {
                if (value & bit) {
                    $(`#${prefix}_f${bit}`).prop('checked', true);
                }
            });
        }
        calculateLiftValue(prefix);
    });

    // 5. Pastikan data terkirim saat submit
    $('form').on('submit', function () {
        $('#liftSettings').show(); // agar input tidak ter-skip
        if ($('#edit-type').val() != '1') {
            $('#Lift1_input, #Lift2_input, #Lift3_input, #Lift4_input').val(0);
        }
    });
});
</script>
@endsection