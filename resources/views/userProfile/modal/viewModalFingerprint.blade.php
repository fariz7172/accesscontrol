<div class="modal fade" id="successModalFingerprint" tabindex="-1" role="dialog" aria-labelledby="successModalFingerprint" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalFingerprint">Get Fingerprint</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="fingerprint">
                    <div class="form-group">
                        <label for="sn">Serial Number (SN)</label>
                        <select class="form-control" id="sn" name="sn" required>
                            <option value="">Pilih Serial Number</option>
                            @foreach ($devices as $device)
                            <option value="{{ $device->sn }}">{{ $device->sn }} || {{ $device->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="number" class="form-control" id="indexstart" name="indexstart" hidden>
                    <input type="number" class="form-control" id="indexend" name="indexend" hidden>
                    <button type="submit" class="btn btn-primary">Sync</button>
                </form>
                <div id="progressContainer" style="display: none;">
                    <div class="progress">
                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div id="progressText" style="text-align: center;">0%</div>
                </div>
                <div id="progressStatus" style="text-align: center; margin-bottom: 5px;"></div>
                <div id="memberResult" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>