@extends('layout_background.app_layouts')
@section('content')
<div class="container-fluid " id="data-container">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Setting Database</h1>

    <div class="row">

        <div class="col-lg-12">

            <!-- Circle Buttons -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
                    <div class="py-2 px-2">

                        <!-- Input untuk nilai DB_DATABASE -->
                        <label for="">Name Database</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="input_text_DB_DATABASE" value="{{ $dbDatabase }}" aria-label="New DB_DATABASE value" aria-describedby="button-addon2">
                        </div>

                        <!-- Input untuk nilai DB_CONNECTION -->
                        <label for="">Name DB_CONNECTION</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="input_text_DB_CONNECTION" value="{{ $dbConnection }}" aria-label="New DB_CONNECTION value" aria-describedby="button-addon2">
                        </div>

                        <!-- Input untuk nilai DB_HOST -->
                        <label for="">Name DB_HOST</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="input_text_DB_HOST" value="{{ $dbHost }}" aria-label="New DB_HOST value" aria-describedby="button-addon2">
                        </div>

                        <!-- Input untuk nilai DB_PORT -->
                        <label for="">Name DB_PORT</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="input_text_DB_PORT" value="{{ $dbPort }}" aria-label="New DB_PORT value" aria-describedby="button-addon2">
                        </div>

                        <!-- Input untuk nilai DB_USERNAME -->
                        <label for="">Name DB_USERNAME</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="input_text_DB_USERNAME" value="{{ $dbUsername }}" aria-label="New DB_USERNAME value" aria-describedby="button-addon2">
                        </div>

                        <!-- Input untuk nilai DB_PASSWORD -->
                        <label for="">Name DB_PASSWORD</label>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" id="input_text_DB_PASSWORD" value="{{ $dbPassword }}" aria-label="New DB_PASSWORD value" aria-describedby="button-addon2">
                        </div>


                        <!-- Input untuk nilai DB_PASSWORD -->
                        <!-- <label for="">Register API ID</label> -->
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="registerAPI" value="{{ $dbRegisterAPI }}" aria-label="New REGISTER_API value" aria-describedby="button-addon2" hidden>
                        </div>


                        <!-- <label for="">Delete API ID</label> -->
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="deleteAPI" value="{{ old('DELETE_API', $dbDeleteAPI) }}" aria-label="New DELETE_API value" aria-describedby="button-addon2" hidden>
                        </div>

                        <button class="btn btn-outline-secondary" type="button" id="button-addon2">Update</button>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#button-addon2').on('click', function() {
            // Ambil semua nilai dari input
            var newDatabaseValue = $('#input_text_DB_DATABASE').val();
            var newConnectionValue = $('#input_text_DB_CONNECTION').val();
            var newHostValue = $('#input_text_DB_HOST').val();
            var newPortValue = $('#input_text_DB_PORT').val();
            var newUsernameValue = $('#input_text_DB_USERNAME').val();
            var newPasswordValue = $('#input_text_DB_PASSWORD').val();
            var newRegisterAPI = $('#registerAPI').val();
            var newDeleteAPIID = $('#deleteAPI').val();

            // Kirim data ke backend untuk mengupdate file .env
            $.ajax({
                url: '{{ route("updateEnv") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    DB_DATABASE: newDatabaseValue,
                    DB_CONNECTION: newConnectionValue,
                    DB_HOST: newHostValue,
                    DB_PORT: newPortValue,
                    DB_USERNAME: newUsernameValue,
                    DB_PASSWORD: newPasswordValue,
                    REGISTER_API: newRegisterAPI,
                    DELETE_API: newDeleteAPIID,
                },
                success: function(response) {
                    alert('Environment variables updated successfully!');
                },
                error: function() {
                    alert('Environment update environment variables.');
                }
            });
        });
    });
</script>
@endsection