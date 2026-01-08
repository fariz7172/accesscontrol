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

    <h1 class="h3 mb-2 text-gray-800">Edit Holiday</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('holiday.update', $holiday->ID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="Name">Name</label>
                    <input type="text" class="form-control" name="Name" value="{{ old('Name', $holiday->Name) }}" required>
                    @error('Name')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="StartDate">Start Date</label>
                    <input type="date" class="form-control" name="StartDate" value="{{ old('StartDate', $holiday->StartDate) }}" required>
                    @error('StartDate')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="EndDate">End Date</label>
                    <input type="date" class="form-control" name="EndDate" value="{{ old('EndDate', $holiday->EndDate) }}" required>
                    @error('EndDate')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update Holiday</button>
                <a href="{{ route('holiday.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection