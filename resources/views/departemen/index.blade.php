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
    <h1 class="h3 mb-2 text-gray-800">Departemen Control</h1>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add Departemen</button>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>

                            <th>Number</th>
                            <th>Name</th>
                            <th>Description</th>

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departemen as $departemen)
                        <tr>

                            <td>{{ $departemen->number }}</td>
                            <td>{{ $departemen->name }}</td>
                            <td>{{ $departemen->description }}</td>
                            <td>
                                <a href="{{ route('departemen.edit', ['departeman' => encryptId($departemen->id)]) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('departemen.destroy', $departemen->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus departemen ini?')">Hapus</button>
                                </form>
                            </td>


                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('departemen.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add departemen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Form Input Fields -->
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Number</label>
                            <input type="number" class="form-control" name="number" value="" required>
                        </div>


                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control" name="description" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Departemen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection