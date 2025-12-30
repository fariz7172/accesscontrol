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
                    <div class="form-group" style="text-align: center;">
                        <img id="viewUserPicture" src="" alt="User Picture" class="img-fluid" style="max-width: 100%; display: block; margin: 0 auto;" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>