<div class="modal fade" id="SetUserLiftModal" tabindex="-1" role="dialog" aria-labelledby="SetUserLiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="height: 90vh;">
            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="SetUserLiftModalLabel">
                    <i class="fas fa-elevator mr-2"></i> Set User Lift Access
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0 d-flex flex-column">
                <div class="row no-gutters flex-fill">
                    <!-- ==================== KOLOM KIRI: Daftar User ==================== -->
                    <div class="col-lg-6 d-flex flex-column bg-light">
                        <!-- Header + Search -->
                        <div class="p-4 border-bottom bg-white">
                            <h6 class="mb-3 text-primary font-weight-bold">
                                <i class="fas fa-users mr-2"></i> Pilih User
                            </h6>
                            <input type="text" id="userSearch" class="form-control form-control-lg"
                                   placeholder="Cari nama, ID, atau nomor kartu...">
                        </div>

                        <!-- Container untuk AJAX (awalnya loading) -->
                        <div class="flex-fill position-relative">
                            <div id="user-lift-container" class="h-100 overflow-auto"
                                 style="min-height: 400px;">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Memuat daftar user...</p>
                                </div>
                            </div>
                        </div>

                        <!-- User Terpilih -->
                        <div class="p-4 bg-white border-top">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-check text-success fa-2x mr-3"></i>
                                <div>
                                    <strong>User Terpilih:</strong><br>
                                    <span id="selectedUserInfo" class="text-muted font-italic">
                                        Belum ada user yang dipilih
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== KOLOM KANAN: Daftar Device Soyal ==================== -->
                    <div class="col-lg-6 d-flex flex-column bg-white">
                        <!-- Header Device -->
                        <div class="p-4 border-bottom bg-light">
                            <h6 class="mb-0 text-primary font-weight-bold">
                                <i class="fas fa-building mr-2"></i> Pilih Lift Controller (Soyal)
                            </h6>
                        </div>

                        <!-- List Device -->
                        <div class="flex-fill overflow-auto px-3 pt-3">
                            <div class="list-group list-group-flush">
                              @forelse(\App\Models\deviceGateModel::where('type', 1)->orderBy('name')->get() as $device)
<label class="list-group-item list-group-item-action py-4 border-0"
       style="cursor:pointer; transition: all 0.2s;"
       data-lift1="{{ $device->Lift1 ?? 0 }}"
       data-lift2="{{ $device->Lift2 ?? 0 }}"
       data-lift3="{{ $device->Lift3 ?? 0 }}"
       data-lift4="{{ $device->Lift4 ?? 0 }}">
    <div class="d-flex w-100 justify-content-between align-items-center">
        <div class="custom-control custom-radio">
            <input type="radio" name="selected_device_id"
                   value="{{ $device->id }}" class="custom-control-input"
                   id="device-{{ $device->id }}">
            <label class="custom-control-label font-weight-bold"
                   for="device-{{ $device->id }}">
                {{ $device->name }}
            </label>
        </div>
        <small class="text-success">
            Online
        </small>
    </div>
    <small class="text-muted d-block mt-2">
        {{ $device->ip }} | Node: {{ $device->nodeid }} | SN: {{ $device->sn }}<br>
        <small class="text-info">
            Akses Lift: L1={{ $device->Lift1 ?? 0 }}, L2={{ $device->Lift2 ?? 0 }}, 
            L3={{ $device->Lift3 ?? 0 }}, L4={{ $device->Lift4 ?? 0 }}
        </small>
    </small>
</label>
@empty
<div class="text-center py-5 text-muted">
    <p>Tidak ada device Soyal (Lift Controller) yang terdaftar.</p>
</div>
@endforelse
                            </div>
                        </div>

                        <!-- Device Terpilih -->
                        <div class="p-4 bg-light border-top">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-microchip text-primary fa-2x mr-3"></i>
                                <div>
                                    <strong>Device Terpilih:</strong><br>
                                    <span id="selectedDeviceInfo" class="text-muted font-italic">
                                        Belum ada device yang dipilih
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer bg-light border-top justify-content-between">
                <button type="button" class="btn btn-secondary btn-lg px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i> Batal
                </button>
                <button type="button" id="proceedSetLift" class="btn btn-success btn-lg px-5" disabled>
                    <i class="fas fa-arrow-right mr-2"></i> Lanjut Set Lift Access
                </button>
            </div>
        </div>
    </div>
</div>
