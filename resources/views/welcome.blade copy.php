<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Soyal - Login</title>
    <link rel="icon" type="image/png" href="{{ asset('img/soyal.png') }}">
    <link href="{{ asset('template') }}/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="{{ asset('template') }}/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-lg-block justify-content-center align-items-center">
                                <img src="{{ asset('img/soyal.png') }}" style="width: 25rem" class="rounded text-center ml-5" alt="...">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form action="/sesi/login" method="post">
                                        @csrf
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" placeholder="Masukan Username" name="username" value="{{ Session::get('username') }}">
                                            @error('username')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" placeholder="Password" name="password">
                                            @error('password')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Menampilkan pesan error umum -->
                                        @if ($errors->any())
                                        <div class="alert alert-danger">
                                            @foreach ($errors->all() as $error)
                                            <p>{{ $error }}</p>
                                            @endforeach
                                        </div>
                                        @endif

                                        <div class="mb-3 d-grid">
                                            <button name="submit" type="submit" class="btn btn-primary">Login</button>
                                            <a href="{{ url('/landing') }}" class="btn btn-warning">
                                                Check Member
                                            </a>

                                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#configModal">Config</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal for Data Input (baru) -->
    <div class="modal fade" id="dataInputModal" tabindex="-1" aria-labelledby="dataInputModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataInputModalLabel">Input Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="py-2 px-2">
                        <label for="inputData">Data Input</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Masukkan data" name="inputData" id="inputData">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitData">Submit</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal for Config Input -->
    <!-- Modal for Config Input -->
    <div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configModalLabel">Input Config Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="py-2 px-2">
                        <!-- Keep the "Masukan Password" field visible -->
                        <label for="">Masukan Password</label>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" placeholder="Masukan Password" name="password" id="passwordInput" value="">
                        </div>
                        <!-- Hide these elements initially -->
                        <div id="configInputs" style="display: none;">
                            <label for="">Name Database</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Name Database" name="DB_DATABASE" id="DB_DATABASE" value="{{ $dbDatabase }}">
                            </div>
                            <label for="">DB Connection</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="DB Connection" name="DB_CONNECTION" id="DB_CONNECTION" value="{{ $dbConnection }}">
                            </div>

                            <label for="">DB Host</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="DB Host" name="DB_HOST" id="DB_HOST" value="{{ $dbHost }}">
                            </div>

                            <label for="">DB Port</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="DB Port" name="DB_PORT" id="DB_PORT" value="{{ $dbPort }}">
                            </div>

                            <label for="">DB Username</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="DB Username" name="DB_USERNAME" id="DB_USERNAME" value="{{ $dbUsername }}">
                            </div>

                            <label for="">DB Password</label>
                            <div class="input-group mb-3">
                                <input type="password" class="form-control" placeholder="DB Password" name="DB_PASSWORD" id="DB_PASSWORD" value="{{ $dbPassword }}">
                            </div>

                            <label for="">REGISTER API</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="REGISTER API" name="REGISTER_API" id="REGISTER_API" value="{{ $dbRegisterAPI }}">
                            </div>

                            <label for="">DELETE API</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="DELETE API" name="DELETE_API" id="DELETE_API" value="{{ $dbDeleteAPI }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <!-- Initially hidden save button -->
                    <!-- Tombol Save changes yang disembunyikan -->
                    <button type="button" class="btn btn-primary" id="saveConfigBtn" style="display: none;">Save changes</button>

                    <button type="button" class="btn btn-primary" id="inputButton" disabled>Input Data</button>

                </div>
            </div>
        </div>
    </div>



    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="{{ asset('template') }}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('template') }}/js/sb-admin-2.min.js"></script>


</body>

</html>