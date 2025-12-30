@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Add New Branch</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('holiday.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description">Start Date</label>
                    <textarea class="form-control @error('StartDate') is-invalid @enderror" name="StartDate" required></textarea>
                    @error('StartDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">End Date</label>
                    <textarea class="form-control @error('EndDate') is-invalid @enderror" name="EndDate" required></textarea>
                    @error('EndDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Add Branch</button>
                <a href="{{ route('holiday.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection