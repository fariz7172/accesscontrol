@extends('layout_background.app_layouts')


@section('content')
<link rel="stylesheet" href="{{ asset('css/card.css') }}">

<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if(isset($error))
    <div class="alert alert-danger">
        {{ $error }}
    </div>
    @endif

    <h1>Serial Port Communication</h1>
    <div class="row">
        <!-- Form Serial -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('serial.card') }}" id="comPortForm">
                        <p><strong>SET COM PORT: com1 - com9</strong></p>
                        <input
                            type="text"
                            name="comPort"
                            id="comPortInput"
                            class="form-control mb-3"
                            value="{{ old('comPort', $port ?? 'com9') }}"
                            data-default-value="{{ old('comPort', $port ?? 'com9') }}"
                            placeholder="e.g., com9">

                        @if(isset($cardNo))
                        <p><strong>Card Number:</strong></p>
                        <input
                            type="text"
                            class="form-control mb-3"
                            id="cardNoInput"
                            value="{{ $cardNo }}"
                            readonly>

                        <p><strong>Card Number (Formatted):</strong></p>
                        <input
                            type="text"
                            class="form-control mb-3"
                            id="cardNoLittleEndianInput"
                            value="{{ $cardNoLittleEndian }}"
                            readonly>

                        @if(isset($hexResponse))
                        <p><strong>Hex Response:</strong></p>
                        <textarea
                            class="form-control mb-3"
                            readonly
                            rows="4">{{ $hexResponse }}</textarea>
                        @endif
                        @else
                        <p>Waiting for response...</p>
                        @endif

                        <div>
                            <button type="submit" class="btn btn-primary mt-3" id="serialSubmitButton">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form Manual Input -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('process.card') }}" id="dataEndian">
                        @csrf
                        <p><strong>Input Card Number:</strong></p>
                        <input
                            type="text"
                            name="cardNoInput"
                            id="cardNoInput"
                            class="form-control mb-3"
                            value="{{ old('cardNoInput') }}"
                            placeholder="e.g., 8711447">

                        @if(isset($manualCardNo))
                        <p><strong>Card Number:</strong></p>
                        <input
                            type="text"
                            class="form-control mb-3"
                            id="dataEndianCardNo"
                            value="{{ $manualCardNo }}"
                            readonly>

                        <p><strong>Card Number (Formatted):</strong></p>
                        <input
                            type="text"
                            class="form-control mb-3"
                            id="dataEndianCardSymbol"
                            value="{{ $manualCardNoFormatted }}"
                            readonly>

                        <p><strong>Hex Response:</strong></p>
                        <textarea
                            class="form-control mb-3"
                            readonly
                            rows="4">{{ $manualHexResponse ?? '' }}</textarea>
                        @else
                        <p><strong>Card Number:</strong></p>
                        <input
                            type="text"
                            class="form-control mb-3"
                            id="dataEndianCardNo"
                            value=""
                            readonly>

                        <p><strong>Card Number (Formatted):</strong></p>
                        <input
                            type="text"
                            class="form-control mb-3"
                            id="dataEndianCardSymbol"
                            value=""
                            readonly>

                        <p><strong>Hex Response:</strong></p>
                        <textarea
                            class="form-control mb-3"
                            readonly
                            rows="4"></textarea>
                        @endif

                        <div>
                            <button type="submit" class="btn btn-primary mt-3" id="manualSubmitButton">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Simpan COM Port ke Local Storage untuk form serial
    document.getElementById('comPortInput').addEventListener('input', function(event) {
        const comPortValue = event.target.value;
        if (comPortValue) {
            localStorage.setItem('comPort', comPortValue);
        }
    });

    // Muat COM Port dari Local Storage saat halaman dimuat
    window.addEventListener('DOMContentLoaded', function() {
        const storedComPort = localStorage.getItem('comPort');
        const comPortInput = document.getElementById('comPortInput');
        const defaultValue = comPortInput.getAttribute('data-default-value');

        comPortInput.value = storedComPort || defaultValue;
    });
</script>
@endsection