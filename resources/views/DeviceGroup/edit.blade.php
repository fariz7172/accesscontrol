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

    <h1 class="h3 mb-2 text-gray-800">Edit Device Group</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('deviceGroup.update', $deviceGroup->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="number">Number</label>
                    <input type="number" class="form-control @error('number') is-invalid @enderror"
                        name="number" value="{{ old('number', $deviceGroup->number) }}" required>
                    @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                        name="name" value="{{ old('name', $deviceGroup->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" class="form-control @error('description') is-invalid @enderror"
                        name="description" value="{{ old('description', $deviceGroup->description) }}" required>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Device Gates</label>
                    @foreach($deviceGates as $gate)
                    <div class="form-check">
                        <input type="checkbox"
                            class="form-check-input"
                            name="devicegroup_id[]"
                            value="{{ $gate->id }}"
                            {{ in_array($gate->id, $selectedGates) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $gate->name }}</label>
                    </div>
                    @endforeach
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Device Group</button>
                    <a href="{{ route('deviceGroup') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection