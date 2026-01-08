@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Shift Patterns</h1>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Shift Pattern List</h6>
            <a href="{{ route('shiftpattern.create') }}" class="btn btn-primary btn-sm float-end">Create New Pattern</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Pattern Name</th>
                        <th>Pattern Type</th>
                        <th>Day 1</th>
                        <th>Day 2</th>
                        <th>Day 3</th>
                        <th>Day 4</th>
                        <th>Day 5</th>
                        <th>Day 6</th>
                        <th>Day 7</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($patterns as $pattern)
                    <tr>
                        <td>{{ $pattern->PatternName }}</td>
                        <td>{{ $pattern->PatternType ?? '-' }}</td>
                        <td>{{ $pattern->shiftDay1->ShiftName ?? '-' }}</td>
                        <td>{{ $pattern->shiftDay2->ShiftName ?? '-' }}</td>
                        <td>{{ $pattern->shiftDay3->ShiftName ?? '-' }}</td>
                        <td>{{ $pattern->shiftDay4->ShiftName ?? '-' }}</td>
                        <td>{{ $pattern->shiftDay5->ShiftName ?? '-' }}</td>
                        <td>{{ $pattern->shiftDay6->ShiftName ?? '-' }}</td>
                        <td>{{ $pattern->shiftDay7->ShiftName ?? '-' }}</td>
                        <td>
                            <a href="{{ route('shiftpattern.edit', $pattern->Id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('shiftpattern.destroy', $pattern->Id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection