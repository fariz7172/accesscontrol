@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Edit API URL</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('api.update', $api->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $api->name }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="desc">desc</label>
                    <textarea class="form-control @error('desc') is-invalid @enderror" name="desc" required>{{ $api->desc }}</textarea>
                    @error('desc')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update api</button>
                <a href="{{ route('api.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection