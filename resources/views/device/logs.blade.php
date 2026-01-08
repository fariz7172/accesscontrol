@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <h1 class="h3 mb-2 text-gray-800">Device Logs</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Card Number</th>
                            <th>Device</th>
                            <th>IP</th>
                            <th>Node ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->TM_EVENT }}</td>
                            <td>{{ $log->userProfile ? $log->userProfile->NAME : 'Unknown' }}</td>
                            <td>{{ $log->userProfile ? $log->userProfile->Card : 'N/A' }}</td>
                            <td>{{ $log->deviceGate ? $log->deviceGate->name : 'Unknown' }}</td>
                            <td>{{ $log->deviceGate ? $log->deviceGate->ip : 'N/A' }}</td>
                            <td>{{ $log->deviceGate ? $log->deviceGate->nodeid : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- Tambahkan DataTables JS -->
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
@endsection