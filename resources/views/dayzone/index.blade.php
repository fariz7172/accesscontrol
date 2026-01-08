@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <h1 class="h3 mb-2 text-gray-800">Dayzone Management</h1>

    <!-- Tab Bar Menu -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dayzone.index') ? 'active' : '' }}" href="{{ route('dayzone.index') }}">Dayzone</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dayzonedetail.index') ? 'active' : '' }}" href="{{ route('dayzonedetail.index') }}">Dayzone Detail</a>
        </li>
    </ul>

    <!-- Dayzone Content -->
    @if(request()->routeIs('dayzone.index'))
    {{-- <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add Dayzone</button> --}}

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayzones as $dayzone)
                        <tr>
                            <td>{{ $dayzone->ID }}</td>
                            <td>{{ $dayzone->Name }}</td>
                            <td>
                                <a href="{{ route('dayzone.edit', $dayzone->ID) }}" class="btn btn-warning btn-sm">Edit</a>
                                {{-- <form action="{{ route('dayzone.destroy', $dayzone->ID) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this dayzone?')">Delete</button>
                                </form> --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $dayzones->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('dayzone.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add Dayzone</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="ID">ID</label>
                            <input type="number" class="form-control" name="ID" required>
                        </div>
                        <div class="form-group">
                            <label for="Name">Name</label>
                            <input type="text" class="form-control" name="Name" maxlength="15" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Dayzone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection