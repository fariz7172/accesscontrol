@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <!-- Session Messages -->
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-warning alert-dismiss fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
    <div class="alert alert-danger alert-dismiss fade show">
        <strong>Validation Errors:</strong>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <h1 class="h3 mb-4 text-gray-800">Add Leave Type</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('leavetype.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="Name">Name</label>
                    <input type="text" class="form-control" id="Name" name="Name" value="{{ old('Name') }}" required aria-required="true">
                    @error('Name')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <input type="number" hidden class="form-control" id="Type" name="Type" value="{{ old('Type', 0) }}" required min="0" step="1" aria-label="Leave Type Number">


                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('leaveprocess.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection