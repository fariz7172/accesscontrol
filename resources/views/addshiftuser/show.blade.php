@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">User Details</h1>

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Details for {{ $user->NAME }}</h6>
        </div>
        <div class="card-body">
            <p><strong>ID:</strong> {{ $user->ID }}</p>
            <p><strong>Name:</strong> {{ $user->NAME }}</p>
            <p><strong>Department:</strong> {{ $user->department ? $user->department->name : 'No Department' }}</p>
            <p><strong>Shift Pattern:</strong> {{ $user->shiftPattern ? $user->shiftPattern->PatternName : 'No Shift Pattern' }}</p>
            <a href="{{ route('addshiftuser.index') }}" class="btn btn-primary btn-sm">Back to List</a>
        </div>
    </div>
</div>
@endsection