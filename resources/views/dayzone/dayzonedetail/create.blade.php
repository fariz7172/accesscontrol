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

    <h1 class="h3 mb-2 text-gray-800">Add Dayzone Detail</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('dayzonedetail.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="ID">ID</label>
                    <input type="number" class="form-control" name="ID" required>
                </div>
                <div class="form-group">
                    <label for="dz">Dayzone</label>
                    <select class="form-control" name="dz" required>
                        <option value="">Select Dayzone</option>
                        @foreach($dayzones as $dayzone)
                        <option value="{{ $dayzone->ID }}">{{ $dayzone->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="Stz1">Stz1</label>
                    <input type="time" class="form-control" name="Stz1" required>
                </div>
                <div class="form-group">
                    <label for="Etz1">Etz1</label>
                    <input type="time" class="form-control" name="Etz1" required>
                </div>
                <div class="form-group">
                    <label for="Stz2">Stz2</label>
                    <input type="time" class="form-control" name="Stz2" required>
                </div>
                <div class="form-group">
                    <label for="Etz2">Etz2</label>
                    <input type="time" class="form-control" name="Etz2" required>
                </div>
                <div class="form-group">
                    <label for="Stz3">Stz3</label>
                    <input type="time" class="form-control" name="Stz3" required>
                </div>
                <div class="form-group">
                    <label for="Etz3">Etz3</label>
                    <input type="time" class="form-control" name="Etz3" required>
                </div>
                <div class="form-group">
                    <label for="Stz4">Stz4</label>
                    <input type="time" class="form-control" name="Stz4" required>
                </div>
                <div class="form-group">
                    <label for="Etz4">Etz4</label>
                    <input type="time" class="form-control" name="Etz4" required>
                </div>
                <div class="form-group">
                    <label for="Stz5">Stz5</label>
                    <input type="time" class="form-control" name="Stz5" required>
                </div>
                <div class="form-group">
                    <label for="Etz5">Etz5</label>
                    <input type="time" class="form-control" name="Etz5" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Dayzone Detail</button>
                <a href="{{ route('dayzone.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection