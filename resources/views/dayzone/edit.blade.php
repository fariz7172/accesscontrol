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

    <h1 class="h3 mb-2 text-gray-800">Edit Dayzone</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('dayzone.update', $dayzone->ID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="ID" >ID</label>
                    <input type="number" class="form-control" name="ID" value="{{ $dayzone->ID }}" required readonly>
                </div>
                <div class="form-group">
                    <label for="Name">Name</label>
                    <input type="text" class="form-control" name="Name" value="{{ $dayzone->Name }}" maxlength="15" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Dayzone</button>
                <a href="{{ route('dayzone.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection