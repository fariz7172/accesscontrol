<div class="modal fade" id="deleteProgressModal" tabindex="-1" role="dialog" aria-labelledby="deleteProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProgressModalLabel">Deleting Users</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="progressContainer" style="display: none; margin-top: 20px;">
                    <div style="width: 100%; background-color: #f3f3f3; border-radius: 5px;">
                        <div id="progressBar" style="width: 0%; height: 30px; background-color: #4caf50; border-radius: 5px; transition: width 0.3s ease;"></div>
                    </div>
                    <div id="progressText" style="text-align: center; margin-top: 5px;">0%</div>
                </div>
                <p>Processing user deletion, please wait...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" disabled>Close</button>
            </div>
        </div>
    </div>
</div>