<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Device Management</h5>
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button> -->
            </div>
            <div class="modal-body">
                <div id="progressContainer" style="display: none; margin-top: 20px;">
                    <div style="width: 100%; background-color: #f3f3f3; border-radius: 5px;">
                        <div id="progressBar" style="width: 0%; height: 30px; background-color: #4caf50; border-radius: 5px;"></div>
                    </div>
                    <div id="progressText" style="text-align: center; margin-top: 5px;">0%</div>
                </div>

                @php
                $totalDevices = count($devices);
                $half = ceil($totalDevices / 2);
                $firstColumnDevices = $devices->slice(0, $half);
                $secondColumnDevices = $devices->slice($half);
                @endphp

                <div class="form-group">
                    <label for="devicegroup_id">Device Group</label>
                    <select id="devicegroup_id" name="devicegroup_id" class="form-control" required>
                        <option value="all">All Device</option>
                        @foreach($deviceGroup as $item)
                        <option value="{{ $item->id }}">
                            {{ $item->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                @php
                $totalDevices = count($devices);
                $half = ceil($totalDevices / 2);
                $columns = [$devices->slice(0, $half), $devices->slice($half)];
                @endphp
                <div class="row">
                    @foreach($columns as $column)
                    <div class="col-md-6">
                        @foreach($column as $device)
                        <div class="col-md-12">
                            <span class="checkbox-group ml-3" style="padding-top: 10px;">
                                <input type="checkbox" class="machine-checkbox" id="mesin{{ $device->id }}"
                                    value="{{ $device->sn }}" data-type="{{ $device->type }}"
                                    data-nodeid="{{ $device->nodeid }}" data-ip="{{ $device->ip }}">
                                <label for="mesin{{ $device->id }}">{{ $device->name }} - {{ $device->sn }}</label>
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="insertDataButton" class="btn btn-success">Insert To Machine</button>
                <form action="{{ route('userLog.bulkDelete') }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="users" id="users">
                    <input type="hidden" name="machines" id="machines">
                    <button type="button" class="btn btn-danger" id="bulkDeleteButton">
                        <i class="fa fa-trash"> Delete To Machine</i>
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>