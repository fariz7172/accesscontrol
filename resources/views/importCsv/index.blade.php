@extends('layout_background.app_layouts')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Soyal Access Control - Import CSV</h1>

    @if (session('alert'))
    <div class="alert alert-danger">
        {{ session('alert') }}
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Import Data CSV</h6>
                    </div>

                    <!-- Loader container, set to hidden by default -->
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <div class="loader" id="loader-container" style="height: 5vh; display: none;"></div>
                    </div>

                    <style>
                        .loader {
                            width: fit-content;
                            font-size: 40px;
                            font-family: system-ui, sans-serif;
                            font-weight: bold;
                            text-transform: uppercase;
                            color: #0000;
                            -webkit-text-stroke: 1px #000;
                            background: conic-gradient(#000 0 0) 0/0% 100% no-repeat text;
                            animation: l1 1s linear infinite;
                        }

                        .loader:before {
                            content: "Loading";
                        }

                        @keyframes l1 {
                            to {
                                background-size: 120% 100%;
                            }
                        }

                        .progress {
                            height: 30px;
                        }

                        .progress-bar {
                            font-size: 16px;
                            line-height: 30px;
                            /* Center the text vertically */
                        }
                    </style>


                    <div class="card-body">
                        <form id="import-form" action="{{ route('import.csv.post') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Unggah File CSV</label>
                                <input class="form-control" type="file" name="csv_file" id="csv_file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="progress d-none" id="progress-container"> <!-- Progress bar container -->
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                    style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    0%
                </div>
            </div>

            <p id="success-count" class="d-none">Data berhasil diimpor: 0</p> <!-- Success count -->
        </div>
    </div>
</div>


<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('#import-form').on('submit', function(event) {
            event.preventDefault();

            $('#loader-container').css('display', 'flex');
            $('#progress-container').removeClass('d-none');
            $('#success-count').removeClass('d-none').text('Data berhasil diimpor: 0');

            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        title: "Good job!",
                        text: "Import Data Success",
                        icon: "success"
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Terjadi kesalahan:", error);
                },
                complete: function() {
                    $('#loader-container').css('display', 'none');
                }
            });

            // Mulai polling progress
            const intervalId = setInterval(function() {
                $.get("{{ route('import.progress') }}", function(data) {
                    $('#progress-bar').css('width', data.progress + '%').attr('aria-valuenow', data.progress).text(Math.round(data.progress) + '%');
                    $('#success-count').text('Data berhasil diimpor: ' + data.success_count);

                    if (data.progress >= 100) {
                        clearInterval(intervalId); // Hentikan polling setelah selesai
                    }
                });
            }, 2000); // Ambil status setiap 2 detik
        });
    });
</script>

@endsection