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

    <h1 class="h3 mb-2 text-gray-800">Weekzone Management</h1>

    <!-- Tab Bar Menu -->
    <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('weekzone.index') ? 'active' : '' }}" href="{{ route('weekzone.index') }}">
            Weekzone
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('weekzonedetail.index') ? 'active' : '' }}" href="{{ route('weekzonedetail.index') }}">
            Weekzone Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('setusertimeone.index') ? 'active' : '' }}" href="{{ route('setusertimeone.index') }}">
            User Profiles
        </a>
    </li>
</ul>


    <!-- Weekzone Content -->
    @if(request()->routeIs('weekzone.index'))
    {{-- <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add Weekzone</button> --}}

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
                        @foreach($weekzones as $weekzone)
                        <tr>
                            <td>{{ $weekzone->ID }}</td>
                            <td>{{ $weekzone->Name }}</td>
                            <td>
                                <a href="{{ route('weekzone.edit', $weekzone->ID) }}" class="btn btn-warning btn-sm">Edit</a>
                                {{-- <form action="{{ route('weekzone.destroy', $weekzone->ID) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this weekzone?')">Delete</button>
                                </form> --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $weekzones->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('weekzone.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add Weekzone</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="ID">ID</label>
                            <input type="number" class="form-control @error('ID') is-invalid @enderror" name="ID" value="{{ old('ID') }}" required>
                            @error('ID')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="Name">Name</label>
                            <input type="text" class="form-control @error('Name') is-invalid @enderror" name="Name" maxlength="15" value="{{ old('Name') }}" required>
                            @error('Name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Weekzone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection