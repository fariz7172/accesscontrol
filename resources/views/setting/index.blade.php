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

    <h1 class="h3 mb-2 text-gray-800">Make FIle CSV</h1>



    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <form action="{{ route('settingPath.submit') }}" method="POST">
                    @csrf <!-- Token CSRF untuk keamanan -->
                    <div class="mb-3">
                        <label for="SetPath" class="form-label">Set Path</label>
                        <input type="text" class="form-control" id="path" name="path" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

                @if(isset($displayCode))
                <div class="mt-4">
                    <h5>Generated PowerShell Code:</h5>
                    <pre><code>{{ $displayCode }}</code></pre>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection