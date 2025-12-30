@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <h1 class="h3 mb-2 text-gray-800">Holiday Management</h1>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('holiday.index') }}" method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request()->input('startDate', now()->format('Y-m-d')) }}">

                </div>
                <div class="form-group mr-2">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request()->input('endDate', now()->format('Y-m-d')) }}">

                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <button type="submit" class="btn btn-success" formaction="{{ route('holiday.export') }}">Export to Excel</button>
            </form>
        </div>
    </div>

    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add Holiday</button>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holiday as $item)
                        <tr>
                            <td>{{ $item->Name }}</td>
                            <td>{{ $item->StartDate }}</td>
                            <td>{{ $item->EndDate }}</td>
                            <td>
                                <a href="{{ route('holiday.edit', $item->ID) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('holiday.destroy', $item->ID) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this holiday?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $holiday->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('holiday.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add Holiday</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="Name">Name</label>
                            <input type="text" class="form-control" name="Name" required>
                        </div>
                        <div class="form-group">
                            <label for="StartDate">Start Date</label>
                            <input type="date" class="form-control" name="StartDate" required>
                        </div>
                        <div class="form-group">
                            <label for="EndDate">End Date</label>
                            <input type="date" class="form-control" name="EndDate" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection