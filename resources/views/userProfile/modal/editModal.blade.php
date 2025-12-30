<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editUserId">
                    <div class="form-group">
                        <label for="editUserName">Name</label>
                        <input type="text" id="editUserName" name="name" class="form-control" required>

                    </div>

                    <div class="form-group">
                        <label for="editUserDepartment">Department</label>
                        <select id="editUserDepartment" class="form-control">
                            @foreach($departments as $department)
                            <option value="{{ $department->fid }}">{{ $department->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <!-- Device (FaceNo) as a select dropdown -->
                        <div class="col">
                            <div class="form-group">
                                <label for="faceNo">Device</label>
                                <select id="faceNo" class="form-control">
                                    @foreach ($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->sn }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <!-- Gambar User -->
                        <div class="col">
                            <div class="form-group">
                                <label for="userPic">User Picture</label>
                                <img id="userPic" src="" alt="User Picture" style="max-width: 100%; height: 500px;">
                            </div>
                        </div>
                    </div>

                    <!-- Optional File Upload Section -->
                    <!-- Uncomment if you want to allow updating of the profile picture -->
                    <!-- <div class="mb-3">
                        <label for="formFile" class="form-label">Select New Picture</label>
                        <input class="form-control" type="file" id="formFile" name="picture">
                    </div> -->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>