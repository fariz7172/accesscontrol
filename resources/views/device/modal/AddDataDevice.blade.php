<!-- Add Device Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('device.store') }}" method="POST" id="addDeviceForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add Device</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <!-- Nama & Number -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="number">Number <span class="text-danger">*</span></label>
                                <input type="number" name="number" class="form-control @error('number') is-invalid @enderror" value="{{ old('number', 1) }}" required>
                                @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Device Type -->
                    <div class="form-group">
                        <label for="type">Device Type <span class="text-danger">*</span></label>
                        <select name="type" id="deviceType" class="form-control @error('type') is-invalid @enderror" required>
                            <option value="50" {{ old('type', 50) == 50 ? 'selected' : '' }}>Face</option>
                            <option value="0" {{ old('type') == 0 ? 'selected' : '' }}>Finger</option>
                            <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>Soyal (Lift Controller)</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- SN, IP, Node ID -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sn">SN <span class="text-danger">*</span></label>
                                <input type="text" name="sn" class="form-control @error('sn') is-invalid @enderror" value="{{ old('sn') }}" required>
                                @error('sn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ip">IP <span class="text-danger">*</span></label>
                                <input type="text" name="ip" class="form-control @error('ip') is-invalid @enderror" value="{{ old('ip') }}" required>
                                @error('ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nodeid">Node ID <span class="text-danger">*</span></label>
                                <input type="number" name="nodeid" class="form-control @error('nodeid') is-invalid @enderror" value="{{ old('nodeid') }}" required>
                                @error('nodeid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Description & Status -->
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}">
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="stat">Status <span class="text-danger">*</span></label>
                        <select name="stat" class="form-control @error('stat') is-invalid @enderror" required>
                            <option value="0" {{ old('stat', 0) == 0 ? 'selected' : '' }}>IN</option>
                            <option value="1" {{ old('stat') == 1 ? 'selected' : '' }}>OUT</option>
                        </select>
                        @error('stat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <input type="hidden" name="flagstatus" value="1">

                    <!-- LIFT SETTINGS - HANYA MUNCUL JIKA TYPE = 1 -->
                    <div id="liftSettings" class="d-none" style="border:2px solid #007bff; border-radius:10px; padding:20px; margin-top:20px; background:#f8f9ff;">
                        <h5 class="text-primary mb-4">Konfigurasi Akses Lantai per Lift (Soyal)</h5>

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
                            <div class="col-md-3">
                                <div class="card border-primary shadow-sm h-100">
                                    <div class="card-header bg-primary text-white text-center">
                                        <strong>{{ $liftName }}</strong>
                                    </div>
                                    <div class="card-body">
                                        @foreach($floors as $bit => $floor)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input lift-floor-checkbox" type="checkbox"
                                                   name="{{ $prefix }}_floors[]" value="{{ $bit }}"
                                                   id="{{ $prefix }}_f{{ $bit }}">
                                            <label class="form-check-label small" for="{{ $prefix }}_f{{ $bit }}">
                                                {{ $floor['name'] }}
                                            </label>
                                        </div>
                                        @endforeach

                                        <hr>
                                        <div class="form-check">
                                            <input class="form-check-input lift-full-access" type="checkbox" id="{{ $prefix }}_full">
                                            <label class="form-check-label text-success font-weight-bold" for="{{ $prefix }}_full">
                                                Full Access (255)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center bg-light">
                                        Nilai: <strong id="{{ $prefix }}_value">0</strong>
                                        <input type="hidden" name="{{ $liftName }}" id="{{ $liftName }}_input" value="0">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- AKHIR LIFT SETTINGS -->

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Device</button>
                </div>
            </form>
        </div>
    </div>
</div>