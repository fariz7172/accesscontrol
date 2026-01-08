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

    <h1 class="h3 mb-2 text-gray-800">Edit Dayzone Detail</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('dayzonedetail.update', $dayzoneDetail->ID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="ID">ID</label>
                    <input type="number" class="form-control" name="ID" value="{{ old('ID', $dayzoneDetail->ID) }}" required readonly>
                </div>
                <div class="form-group">
                   <label for="dz">Dayzone</label>
                    <input type="hidden" name="dz" value="{{ old('dz', $dayzoneDetail->dz) }}">
                    <div class="form-control bg-light" style="pointer-events: none;">
                        <select class="border-0 bg-transparent w-100" name="dz" required disabled>
                            <option value="">Select Dayzone</option>
                            @foreach($dayzones as $dayzone)
                            <option value="{{ $dayzone->ID }}" {{ old('dz', $dayzoneDetail->dz) == $dayzone->ID ? 'selected' : '' }}>{{ $dayzone->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <label for="Stz1">Start Time 1</label>
                    <input type="time" class="form-control" name="Stz1" value="{{ old('Stz1', $dayzoneDetail->Stz1) }}" required>
                </div>
                <div class="form-group">
                    <label for="Etz1">End Time 1</label>
                    <input type="time" class="form-control" name="Etz1" value="{{ old('Etz1', $dayzoneDetail->Etz1) }}" required>
                </div>
                <div class="form-group">
                    <label for="Stz2">Start Time 2</label>
                    <input type="time" class="form-control" name="Stz2" value="{{ old('Stz2', $dayzoneDetail->Stz2) }}" required>
                </div>
                <div class="form-group">
                    <label for="Etz2">End Time 2</label>
                    <input type="time" class="form-control" name="Etz2" value="{{ old('Etz2', $dayzoneDetail->Etz2) }}" required>
                </div>
                <div class="form-group">
                    <label for="Stz3">Start Time 3</label>
                    <input type="time" class="form-control" name="Stz3" value="{{ old('Stz3', $dayzoneDetail->Stz3) }}" required>
                </div>
                <div class="form-group">
                    <label for="Etz3">End Time 3</label>
                    <input type="time" class="form-control" name="Etz3" value="{{ old('Etz3', $dayzoneDetail->Etz3) }}" required>
                </div>
                <div class="form-group">
                    <label for="Stz4">Start Time 4</label>
                    <input type="time" class="form-control" name="Stz4" value="{{ old('Stz4', $dayzoneDetail->Stz4) }}" required>
                </div>
                <div class="form-group">
                    <label for="Etz4">End Time 4</label>
                    <input type="time" class="form-control" name="Etz4" value="{{ old('Etz4', $dayzoneDetail->Etz4) }}" required>
                </div>
                <div class="form-group">
                    <label for="Stz5">Start Time 5</label>
                    <input type="time" class="form-control" name="Stz5" value="{{ old('Stz5', $dayzoneDetail->Stz5) }}" required>
                </div>
                <div class="form-group">
                    <label for="Etz5">End Time 5</label>
                    <input type="time" class="form-control" name="Etz5" value="{{ old('Etz5', $dayzoneDetail->Etz5) }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Dayzone Detail</button>
                <a href="{{ route('dayzone.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection