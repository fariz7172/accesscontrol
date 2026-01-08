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
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <h1 class="h3 mb-2 text-gray-800">Device Group Management</h1>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add Device Group</button>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Device Gates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                        <tr>
                            <td>{{ $group->number }}</td>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->description }}</td>
                            <td>{{ $group->deviceGates->pluck('name')->implode(', ') }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $group->id }}" data-toggle="modal" data-target="#editModal">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $group->id }}">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $groups->links() }}
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add Device Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="addForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="addNumber">Number</label>
                            <input type="number" class="form-control" id="addNumber" name="number" required>
                        </div>
                        <div class="form-group">
                            <label for="addName">Name</label>
                            <input type="text" class="form-control" id="addName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="addDescription">Description</label>
                            <textarea class="form-control" id="addDescription" name="description"></textarea>
                        </div>
                        <div class="device-gates-container">
                            <h5>Device Gates</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Name</th>
                                            <th>Serial Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(\App\Models\deviceGateModel::whereNull('groupid')->orWhere('groupid', 0)->get() as $gate)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="device_gates[]" value="{{ $gate->id }}" class="device-gate-checkbox">
                                            </td>
                                            <td>{{ $gate->name }}</td>
                                            <td>{{ $gate->sn }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Device Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="form-group">
                            <label for="editNumber">Number</label>
                            <input type="number" class="form-control" id="editNumber" name="number" required>
                        </div>
                        <div class="form-group">
                            <label for="editName">Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="editDescription">Description</label>
                            <textarea class="form-control" id="editDescription" name="description"></textarea>
                        </div>
                        <div class="device-gates-container">
                            <h5>Device Gates</h5>
                            <div class="table-responsive device-gates-list">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Name</th>
                                            <th>Serial Number</th>
                                        </tr>
                                    </thead>
                                    <tbody class="device-gates-table">
                                        <!-- Device gates will be added here dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Di dalam <head> atau sebelum </body> --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Inject semua route yang dibutuhkan JS --}}
<script>
    window.deviceGroupRoutes = {
        store: "{{ route('deviceGroup.store') }}",
        show: "{{ route('deviceGroup.show', ':id') }}",
        update: "{{ route('deviceGroup.update', ':id') }}",
        destroy: "{{ route('deviceGroup.destroy', ':id') }}"
    };
</script>

{{-- Include JS eksternal --}}
<script src="{{ asset('js/deviceGroup/deviceGroup.js') }}?v={{ time() }}"></script>

{{-- jQuery & SweetAlert2 --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection