@extends('layout_background.app_layouts')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    #successModalFingerprint #progressBar {
        width: 0% !important;
        height: 30px !important;
        background-color: #4caf50 !important;
        border-radius: 5px !important;
        transition: width 0.3s ease !important;
    }

    #progressContainer {
        margin-top: 20px;
    }

    #progressBar {
        height: 30px;
        background-color: #4caf50;
        border-radius: 5px;
        transition: width 0.3s ease;
    }

    #progressText {
        margin-top: 10px;
        font-weight: bold;
    }
</style>

<style>
    #syncProgressContainer {
        margin-top: 20px;
    }

    #syncProgressBar {
        height: 30px;
        background-color: #4caf50;
        border-radius: 5px;
        transition: width 0.3s ease;
    }

    #syncProgressText {
        margin-top: 10px;
        font-weight: bold;
        text-align: center;
    }

    #syncStatus {
        margin-top: 10px;
    }
</style>

<div class="container-fluid">
    @include('userProfile.modal.alert')




    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Access Control</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div id="dataTable" class="dataTables_wrapper dt-bootstrap4">
                    <div class="row d-flex">
                        <div class="col-sm-12 col-md-12 d-flex">
                            <form method="GET" action="{{ route('userProfiles.index') }}" class="d-flex">
                                <label>Show
                                    <select name="per_page" aria-controls="dataTable" class="custom-select custom-select-sm form-control form-control-sm" onchange="this.form.submit()">
                                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                                    </select> entries

                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                </label>
                                <div>
                                    <a href="#" class="btn btn-success btn-icon-split mt-4 ml-3 mb-3" data-toggle="modal" data-target="#successModal">
                                        <span class="icon text-white-50"><i class="fas fa-check"></i></span>
                                        <span class="text">Check Machine</span>
                                    </a>
                                    <a href="#" class="btn btn-warning btn-icon-split mt-4 ml-3 mb-3" data-toggle="modal" data-target="#successModalMember">
                                        <span class="icon text-white-50"><i class="fas fa-check"></i></span>
                                        <span class="text">Check Member</span>
                                    </a>

                                    <a href="#" class="btn btn-secondary btn-icon-split mt-4 ml-3 mb-3" data-toggle="modal" data-target="#successModalFingerprint">
                                        <span class="icon text-black-50"><i class="fas fa-checkfas fa-fw fa-fingerprint"></i></span>
                                        <span class="text">Get Finger Print</span>
                                    </a>
                                    <a href="{{ route('userProfiles.create') }}" class="btn btn-primary mt-4 ml-3 mb-3">Add User</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                                <thead>
                                    <tr role="row">

                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Card</th>
                                        <th>Picture</th>
                                        <th class="sorting " tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Picture: activate to sort column ascending" style="width: 225px;">
                                            Actions
                                            <button id="deleteSelected" class="btn btn-danger btn-sm ml-3 btn-no-bg "> <i class="fas fa-fw fa-trash-alt mr-2" id="deleteSelected"></i></button>

                                            <input type="checkbox" id="checkAll">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userprofiles as $userprofile)
                                    <tr id="userRow-{{ $userprofile->ID }}">

                                        <td>{{ $userprofile->ID }}</td>
                                        <td>{{ $userprofile->NAME }}</td>
                                        <td>{{ $userprofile->department->name ?? 'Tidak ada departemen' }}</td>
                                        <td>{{ $userprofile->BEGIN_DATE }}</td>
                                        <td>{{ $userprofile->END_DATE }}</td>
                                        <td>{{ $userprofile->Card }}</td>
                                        <td style="text-align: center;">
                                            @if($userprofile->photo)
                                            <img src="{{ route('userProfile.photo', $userprofile->ID) }}" alt="User Picture" width="50" height="50">
                                            @else
                                            <img src="{{ asset('images/placeholder.jpg') }}" alt="No Picture" width="50" height="50">
                                            @endif
                                        </td>
                                        <td style="width: 80px; ">
                                            <button id="deleteSelected" class="btn btn-danger btn-sm ml-3 btn-no-bg "> <i class="fas fa-fw fa-trash-alt mr-2" id="deleteSelected"></i></button>

                                            <button class="btn btn-info view-button btn-sm ml-3 btn-no-bg"
                                                data-username="{{ $userprofile->NAME }}"
                                                data-department="{{ $userprofile->department->name ?? '' }}"
                                                data-card="{{ $userprofile->Card ?? '' }}"
                                                data-picture="{{ $userprofile->photo ? route('userProfile.photo', $userprofile->ID) : asset('images/placeholder.jpg') }}"
                                                data-devicenames="{{ $userprofile->deviceNames ?? '' }}"
                                                data-toggle="modal"
                                                data-target="#viewModal">
                                                <i class="fas fa-fw fa-eye" style="color: #74C0FC;"></i>
                                            </button>

                                            <a href="{{ route('userProfiles.edit', encryptId($userprofile->ID)) }}" class="btn btn-primary btn-sm ml-3 btn-no-bg">
                                                <i class="fas fa-fw fa-edit" style="color: #f5e949;"></i>
                                            </a>
                                            <!-- <a href="#" class="btn btn-primary btn-sm ml-3 btn-no-bg" data-toggle="modal" data-target="#successModalFingerprint" data-userid="{{ $userprofile->ID }}">
                                                <i class="fas fa-fw fa-fingerprint" style="color: rgb(17, 17, 17);"></i>
                                            </a> -->

                                            <input type="checkbox" class="flat user-checkbox ml-3"
                                                data-userid="{{ $userprofile->ID }}"
                                                data-username="{{ $userprofile->NAME }}"
                                                data-cardnumber="{{ $userprofile->Card ?? '0' }}"
                                                data-picture="{{ $userprofile->photo ? route('userProfile.photo', $userprofile->ID) : '' }}">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        {{$userprofiles->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('userProfile.modal.mechineModal')
@include('userProfile.modal.viewModal')
@include('userProfile.modal.viewModalMember')
@include('userProfile.modal.viewModalFingerprint')
@include('userProfile.modal.deleteUsersModal')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="{{ asset('js/scriptDataTable.js') }}"></script>
<script src="{{ asset('js/scriptMassage.js') }}"></script>
<script src="{{ asset('js/fingerPrint.js') }}"></script>

<!-- <script src="{{ asset('js/scriptDeleteAllUser.js') }}"></script> -->
<!-- <script src="{{ asset('js/scriptbulkDeleteButton.js') }}"></script> -->
<script src="{{ asset('js/SyncMember.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
<script>
    window.bulkDeleteUrl = '{{ route("userLog.bulkDelete") }}';
</script>


<script>
    $(document).ready(function() {
        $('.view-button').on('click', function() {
            // Ambil data dari atribut data-*
            var username = $(this).data('username');
            var department = $(this).data('department');
            var card = $(this).data('card');
            var picture = $(this).data('picture');
            var deviceNames = $(this).data('devicenames');

            // Isi field modal dengan data
            $('#viewUserName').val(username);
            $('#viewUserDepartment').val(department);
            $('#TAG64').val(card);
            $('#viewFaceNo').val(deviceNames); // Isi textarea dengan deviceNames
            $('#viewUserPicture').attr('src', picture);
        });
    });
</script>



<script>
    // Definisikan URL API dari variabel PHP
    const soyalApiUrl = '{{ $soyalApiUrl }}';
    const sztimmyApiUrl = '{{ $sztimmyApiUrl }}';
    const soyalAddUserApiUrl = '{{ $soyalAddUserApiUrl }}';
    const sztimmyRegisterFaceApiUrl = '{{ $sztimmyRegisterFaceApiUrl }}';
    const sztimmyUserAccessApiUrl = '{{ $sztimmyUserAccessApiUrl }}';
    const sztimmyGetUserListApiUrl = '{{ $sztimmyGetUserListApiUrl }}';
</script>


<!-- DELETE TO DATABASE -->
<script>
    $(document).ready(function() {
        // DELETE USER YANG DIPILIH 
        $(document).on('click', '#deleteSelected', function(e) {
            e.preventDefault();

            var selectedUserIds = [];
            $('#dataTable tbody').find('input[type="checkbox"]:checked').each(function() {
                selectedUserIds.push($(this).data('userid')); // Gunakan data-userid sebagai ID
            });

            if (selectedUserIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Users Selected',
                    text: 'Please select at least one user to delete.',
                });
                return;
            }

            // SweetAlert2 confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buka modal deleteProgressModal sebelum memulai proses penghapusan
                    $('#deleteProgressModal').modal('show');
                    processDeleteUsers(selectedUserIds);
                }
            });

            async function processDeleteUsers(userIds) {
                let completedRequests = 0;
                let failedRequests = 0;
                const totalRequests = userIds.length;

                // Tampilkan progress bar di dalam modal deleteProgressModal
                $('#deleteProgressModal #progressContainer').show();
                $('#deleteProgressModal #progressBar').css('width', '0%');
                $('#deleteProgressModal #progressText').text('0%');

                for (const userId of userIds) {
                    try {
                        await $.ajax({
                            url: '{{ route("delete.users") }}',
                            method: 'DELETE',
                            data: {
                                user_ids: [userId], // Kirim satu userId pada satu waktu
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log(`User ${userId} deleted successfully:`, response);
                            },
                            error: function(xhr) {
                                console.error(`Error deleting user ${userId}:`, xhr);
                                failedRequests++;
                            }
                        });
                    } catch (err) {
                        console.error(`Error deleting user ${userId}:`, err);
                        failedRequests++;
                    }

                    completedRequests++;
                    updateProgress(completedRequests, totalRequests);
                    await delay(500); // Penundaan kecil untuk simulasi pemrosesan
                }

                // Sembunyikan progress bar di dalam modal
                $('#deleteProgressModal #progressContainer').hide();

                // Tutup modal setelah proses selesai
                $('#deleteProgressModal').modal('hide');

                // Tampilkan notifikasi hasil
                if (failedRequests > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: `Completed with ${failedRequests} failures. Please check your connection or data.`,
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'All selected users have been deleted successfully.',
                    });
                }

                location.reload(); // Reload halaman setelah penghapusan selesai
            }

            function updateProgress(completed, total) {
                let progressPercentage = Math.min((completed / total) * 100, 100);
                $('#deleteProgressModal #progressBar').css('width', progressPercentage + '%');
                $('#deleteProgressModal #progressText').text(Math.round(progressPercentage) + '%');
            }

            function delay(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Konfigurasi CSRF token untuk AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Ketika dropdown devicegroup_id berubah
        $('#devicegroup_id').on('change', function() {
            var deviceGroupId = $(this).val();

            // Kirim permintaan AJAX untuk mendapatkan devices berdasarkan devicegroup_id
            $.ajax({
                url: '{{ route("userProfiles.devicesByGroup", ":deviceGroupId") }}'.replace(':deviceGroupId', deviceGroupId),
                method: 'GET',
                success: function(response) {
                    // Kosongkan container checkbox
                    $('.row .col-md-6').empty();

                    // Jika tidak ada data, tampilkan pesan
                    if (response.length === 0) {
                        $('.row .col-md-6:first').append('<p>No devices found for this group.</p>');
                        return;
                    }

                    // Tentukan apakah checkbox harus checked (jika bukan "all")
                    var isChecked = deviceGroupId !== 'all' ? 'checked' : '';

                    // Bagi data menjadi dua kolom
                    var half = Math.ceil(response.length / 2);
                    var firstColumn = response.slice(0, half);
                    var secondColumn = response.slice(half);

                    // Isi kolom pertama
                    firstColumn.forEach(function(device) {
                        var checkboxHtml = `
                        <div class="col-md-12">
                            <span class="checkbox-group ml-3" style="padding-top: 10px;">
                                <input type="checkbox" class="machine-checkbox" id="mesin${device.id}"
                                    value="${device.sn}" data-type="${device.type}"
                                    data-nodeid="${device.nodeid}" data-ip="${device.ip}" ${isChecked}>
                                <label for="mesin${device.id}">${device.name} - ${device.sn}</label>
                            </span>
                        </div>
                    `;
                        $('.row .col-md-6:first').append(checkboxHtml);
                    });

                    // Isi kolom kedua
                    secondColumn.forEach(function(device) {
                        var checkboxHtml = `
                        <div class="col-md-12">
                            <span class="checkbox-group ml-3" style="padding-top: 10px;">
                                <input type="checkbox" class="machine-checkbox" id="mesin${device.id}"
                                    value="${device.sn}" data-type="${device.type}"
                                    data-nodeid="${device.nodeid}" data-ip="${device.ip}" ${isChecked}>
                                <label for="mesin${device.id}">${device.name} - ${device.sn}</label>
                            </span>
                        </div>
                    `;
                        $('.row .col-md-6:last').append(checkboxHtml);
                    });
                },
                error: function(xhr) {
                    console.error('Error fetching devices:', xhr);
                    $('.row .col-md-6').empty();
                    $('.row .col-md-6:first').append('<p>Error loading devices.</p>');
                }
            });
        });

        // Trigger change saat modal dibuka untuk memuat data awal
        $('#successModal').on('shown.bs.modal', function() {
            $('#devicegroup_id').trigger('change');
        });
    });
</script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@endsection