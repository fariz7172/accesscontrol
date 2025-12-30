@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
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
    <h1 class="h3 mb-2 text-gray-800">User Detail</h1>

    <!-- Display user data -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <td>{{ $user->ADDR_of_CTL }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $user->NAME_FIRST }}</td>
                    </tr>

                    <tr>
                        <th>Departemen</th>
                        <td>{{ $user->department->Name }}</td>
                    </tr>
                    <tr>
                        <th>Device List</th>
                        <td>

                            <ul>
                                <select class="form-control">
                                    @foreach($faceNos as $faceNo)
                                    @php
                                    // Cari device yang SN-nya cocok dengan FaceNo
                                    $device = $devices->firstWhere('sn', $faceNo);
                                    @endphp
                                    @if($device)
                                    <option value="{{ $faceNo }}">{{ $faceNo }} - {{ $device->name }}</option>
                                    @else
                                    <option value="{{ $faceNo }}">{{ $faceNo }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </ul>


                        </td>

                    </tr>
                    <tr>
                        <th>Picture</th>
                        <td>
                            <img src="{{ route('picture.show', $user->picture->fid) }}" alt="User Picture" width="100" height="100">
                        </td>
                    </tr>


                </table>
            </div>
        </div>
    </div>

    <a href="{{ route('userProfile.index') }}" class="btn btn-secondary">Back to User Log</a>
</div>
@endsection