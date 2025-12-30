<div class="modal fade" id="successModalMember" tabindex="-1" role="dialog" aria-labelledby="successModalMemberLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalMemberLabel">Check Member</h5>
                <button type="button" class="close" id="modalCloseBtn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Klik tombol di bawah untuk menyinkronkan semua data member.</p>
                <form id="checkMemberForm">
                    <button type="submit" class="btn btn-primary" id="checkButton">Cek</button>
                    <!-- <button type="button" class="btn btn-secondary" id="closeModalBtn" data-dismiss="modal">Tutup</button> -->
                </form>
                <!-- Status Proses -->
                <div id="syncStatus" class="mt-3" style="display: none;">
                    <p class="text-info">Sedang memproses sinkronisasi...</p>
                </div>
                <!-- Progress Bar -->
                <div id="syncProgressContainer" class="mt-3" style="display: none;">
                    <div class="progress">
                        <div id="syncProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div id="syncProgressText" class="mt-2 text-center">0%</div>
                </div>
            </div>
        </div>
    </div>
</div>