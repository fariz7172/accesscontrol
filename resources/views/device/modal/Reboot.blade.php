   <!-- Reboot Device Modal -->
    <div class="modal fade" id="rebootModal" tabindex="-1" role="dialog" aria-labelledby="rebootModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" id="rebootForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rebootModalLabel">Reboot Device</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="sn" id="reboot-sn">
                        <p>Are you sure you want to reboot the device with SN: <strong id="reboot-sn-display"></strong>?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reboot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

  