@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <h1 class="h3 mb-2 text-gray-800">Edit Departemen</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('departemen.update', $departemen->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" name="name" value="{{ $departemen->name }}" required>
                </div>
                <div class="form-group">
                    <label for="number">Number</label>
                    <input type="text" class="form-control" name="number" value="{{ $departemen->number }}" required>
                </div>

                <div class="form-group">
                    <label for="ip">Description</label>
                    <input type="text" class="form-control" name="description" value="{{ $departemen->description }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Update departemen</button>
                <a href="{{ route('departemen.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection