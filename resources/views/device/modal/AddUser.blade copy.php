<div class="modal fade" id="addUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content shadow-lg" style="height: 94vh;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">
                    Register User ke Mesin Lift (Soyal) – Multi User & Multi Device
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row align-items-center mb-3">

                    <div class="col-md-8">
                        <input type="text" id="searchUserDb" class="form-control form-control-lg"
                            placeholder="Cari nama, ID, atau kartu...">
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge bg-info text-white fs-6 px-4 py-3" id="selectedCount">0 user dipilih</span>
                    </div>
                </div>

                <div class="row" style="max-height: 70vh;">
                    <!-- ==================== DAFTAR USER (AJAX) ==================== -->
                    <div class="col-lg-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong>Daftar User</strong>
                                <div>
                                    <button type="button" id="selectAllVisible"
                                        class="btn btn-sm btn-outline-primary me-2">Pilih Semua Terlihat</button>
                                    <button type="button" id="deselectAll" class="btn btn-sm btn-outline-danger">Batal
                                        Semua</button>
                                    <select id="perPageSelect" class="form-select form-select-sm">
                                        <option value="5" selected>5 per halaman</option>
                                        <option value="10">10 per halaman</option>
                                        <option value="25">25 per halaman</option>
                                        <option value="50">50 per halaman</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 58vh;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="selectAllDb"></th>
                                                <th>ID</th>
                                                <th>Nama</th>
                                                <th>Kartu</th>
                                                <th>Tanggal Berlaku</th>
                                                <th class="text-center">Akses Lift</th>
                                            </tr>
                                        </thead>
                                        <tbody id="userSearchResults">
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i>Ketik untuk mencari user...</i>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== DEVICE + LIFT SETTINGS ==================== -->
                    <div class="col-lg-5">
                        <div class="h-100 d-flex flex-column">
                            <!-- Device List -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light"><strong>Pilih Device Tujuan</strong> <span
                                        class="text-danger">*</span></div>
                                <div class="card-body p-3" style="max-height: 300px; overflow-y: auto;">
                                    @foreach(\App\Models\deviceGateModel::where('type', 1)->orderBy('name')->get() as
                                    $d)
                                    @php
                                    $allowed = [];
                                    $liftMap = ['Lift1'=>[1,2,3,4,5,6,7,8], 'Lift2'=>[9,10,11,12,13,14,15,16],
                                    'Lift3'=>[17,18,19,20,21,22,23,24], 'Lift4'=>[25,26,27,28,29,30,31,32]];
                                    foreach (['Lift1','Lift2','Lift3','Lift4'] as $lift) {
                                    $value = $d->{$lift} ?? 0;
                                    $base = $liftMap[$lift];
                                    for ($i = 0; $i < 8; $i++) { if ($value & (1 << $i)) $allowed[]=$base[$i]; } }
                                        $allowedJson=json_encode($allowed); @endphp <div class="form-check mb-3">
                                        <input class="form-check-input device-check" type="checkbox"
                                            value="{{ $d->id }}" id="device_{{ $d->id }}" data-id="{{ $d->id }}"
                                            data-ip="{{ $d->ip }}" data-node="{{ $d->nodeid }}"
                                            data-lift1="{{ $d->Lift1 }}" data-lift2="{{ $d->Lift2 }}"
                                            data-lift3="{{ $d->Lift3 }}" data-lift4="{{ $d->Lift4 }}"
                                            data-allowed-floors="{{ $allowedJson }}">

                                        <label class="form-check-label d-block" for="device_{{ $d->id }}">
                                            <div><strong>{{ $d->name }}</strong></div>
                                            <small class="text-muted">{{ $d->ip }} | Node {{ $d->nodeid }}</small>
                                            <div class="mt-1">
                                                <small class="text-primary fw-bold">
                                                    Akses:
                                                    <span class="text-success">
                                                        {{ count($allowed) ? 'Lantai '.implode(', ', $allowed) : 'Tidak ada' }}
                                                    </span>
                                                </small>
                                            </div>
                                        </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Konfigurasi Lantai -->
                        <div class="card shadow-sm flex-grow-1">
                            <div class="card-header bg-primary text-white">
                                <strong>Konfigurasi Akses Lantai</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach(['lift1'=>'Lift 1', 'lift2'=>'Lift 2', 'lift3'=>'Lift 3', 'lift4'=>'Lift
                                    4'] as $prefix => $title)
                                    <div class="col-6 mb-4">
                                        <div class="border rounded p-3 bg-light h-100 d-flex flex-column">
                                            <h6 class="text-center text-primary fw-bold mb-3">{{ $title }}</h6>
                                            <div class="flex-grow-1">
                                                @for($i = 1; $i <= 8; $i++) @php $bit=pow(2, $i - 1);
                                                    $offsets=['lift1'=>0, 'lift2'=>8, 'lift3'=>16, 'lift4'=>24];
                                                    $floorNum = $i + $offsets[$prefix];
                                                    @endphp
                                                    <div
                                                        class="form-check form-check-inline w-100 mb-1 lift-floor-item">
                                                        <input class="form-check-input lift-floor-checkbox"
                                                            type="checkbox" name="{{ $prefix }}_floors[]"
                                                            value="{{ $bit }}" id="{{ $prefix }}_f{{ $bit }}"
                                                            data-floor-number="{{ $floorNum }}">
                                                        <label class="form-check-label small lift-floor-label"
                                                            for="{{ $prefix }}_f{{ $bit }}">
                                                            Lantai {{ $floorNum }}
                                                        </label>
                                                    </div>
                                                    @endfor
                                            </div>
                                            <hr class="my-2">
                                            <div class="text-center mt-2">
                                                <strong id="{{ $prefix }}_value" class="text-primary fs-4">0</strong>
                                                <input type="hidden" id="{{ $prefix }}_input" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer bg-light justify-content-between">
            <small class="text-muted">
                Total Device Lift: <strong>{{ \App\Models\deviceGateModel::where('type', 1)->count() }}</strong>
            </small>
            <!-- PROGRESS BAR - Tambahkan di dalam modal-footer atau di atas tombol -->
            <div class="mt-3 w-100" id="progressContainer" style="display: none;">
                <div class="d-flex justify-content-between mb-1">
                    <span id="progressText" class="text-muted small">Mengirim user...</span>
                    <span id="progressCounter">0 dari 0</span>
                </div>
                <div class="progress" style="height: 28px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%; background: linear-gradient(45deg, #0d6efd, #0dcaf0);"
                        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="fw-bold" id="progressPercent">0%</span>
                    </div>
                </div>
                <div class="mt-2 text-center">
                    <small class="text-success" id="successText">0 berhasil</small> |
                    <small class="text-danger" id="failedText">0 gagal</small>
                </div>
            </div>

            <div>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnKirimSoyal" class="btn btn-success btn-lg px-5" disabled>
                    Kirim ke Mesin Soyal
                </button>
            </div>
        </div>
    </div>
</div>
</div>