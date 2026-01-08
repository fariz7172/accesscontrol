<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">View User Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="viewUserName">Name</label>
                        <input type="text" id="viewUserName" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label for="viewUserDepartment">Department</label>
                        <input type="text" id="viewUserDepartment" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label for="TAG64">RFID</label>
                        <input type="number" id="TAG64" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label for="viewFaceNo">Face Numbers with Device</label>
                        <textarea id="viewFaceNo" class="form-control" disabled rows="5"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group text-center h-100 d-flex flex-column justify-content-center">
                                <label>QR Code Access</label>
                                <div id="viewUserQRCode" class="d-flex justify-content-center mb-2"></div>
                                <div>
                                    <a id="downloadUserQRCode" href="#" class="btn btn-primary btn-sm" download="qrcode.jpg" style="display: none;">
                                        <i class="fas fa-download"></i> Download QR
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group text-center h-100 d-flex flex-column justify-content-center">
                                <label>Profile Picture</label>
                                <img id="viewUserPicture" src="" alt="User Picture" class="img-fluid" style="max-height: 200px; display: block; margin: 0 auto; border-radius: 5px;" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>