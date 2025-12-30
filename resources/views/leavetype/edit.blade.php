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

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <h1 class="h3 mb-2 text-gray-800">Edit Leave Type</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('leavetype.update', $leaveType->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="Name">Name</label>
                    <input type="text" class="form-control" name="Name" value="{{ old('Name', $leaveType->Name) }}" required>
                    @error('Name')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <input type="number" class="form-control" name="Type" hidden value="{{ old('Type', $leaveType->Type) }}" required>


                <button type="submit" class="btn btn-primary">Update Leave Type</button>
                <a href="{{ route('leaveprocess.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection