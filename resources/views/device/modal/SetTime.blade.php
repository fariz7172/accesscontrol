 <!-- Set Time Modal -->
    <div class="modal fade" id="setTimeModal" tabindex="-1" role="dialog" aria-labelledby="setTimeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" id="setTimeForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="setTimeModalLabel">Set Device Time</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="sn" id="set-time-sn">
                        <div class="form-group">
                            <label for="time">Time</label>
                            <input type="datetime-local" class="form-control @error('time') is-invalid @enderror"
                                name="time" id="set-time-time" step="1" required>
                            @error('time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Set Time</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
