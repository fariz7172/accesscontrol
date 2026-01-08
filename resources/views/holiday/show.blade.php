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

    <h1 class="h3 mb-2 text-gray-800">Holiday Details</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $holiday->Name }}</h5>
            <p><strong>Start Date:</strong> {{ $holiday->StartDate }}</p>
            <p><strong>End Date:</strong> {{ $holiday->EndDate }}</p>
            <a href="{{ route('holiday.index') }}" class="btn btn-primary">Back to List</a>
            <a href="{{ route('holiday.edit', $holiday->ID) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('holiday.destroy', $holiday->ID) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this holiday?')">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection