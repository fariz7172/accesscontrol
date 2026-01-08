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

    <h1 class="h3 mb-2 text-gray-800">Edit Weekzone</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('weekzone.update', $weekzone->ID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="ID">ID</label>
                    <input type="number" class="form-control @error('ID') is-invalid @enderror" name="ID" value="{{ old('ID', $weekzone->ID) }}" required readonly>
                    @error('ID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="Name">Name</label>
                    <input type="text" class="form-control @error('Name') is-invalid @enderror" name="Name" maxlength="15" value="{{ old('Name', $weekzone->Name) }}" required>
                    @error('Name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update Weekzone</button>
                <a href="{{ route('weekzone.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection