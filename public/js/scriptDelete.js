// Setup CSRF token for every AJAX request
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$(document).on('click', '#deleteSelected', function(e) {
    e.preventDefault();

    var selectedUserIds = [];
    $('#dataTable tbody').find('input[type="checkbox"]:checked').each(function() {
        selectedUserIds.push($(this).data('userid'));
    });

    if (selectedUserIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Users Selected',
            text: 'Please select at least one user to delete.',
        });
        return;
    }

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
            $.ajax({
                url: deleteUsersUrl, // Pastikan ini sudah benar
                method: 'DELETE',
                data: {
                    user_ids: selectedUserIds
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.success || 'Users have been deleted.',
                    });
                    location.reload(); // Reload setelah sukses
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'An error occurred while deleting users.',
                    });
                }
            });
        }
    });
});

// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BULK DELETE BUTTON
$(document).on('click', '#bulkDeleteButton', function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: "This action will delete selected machines!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'No, cancel!',
    }).then((result) => {
        if (result.isConfirmed) {
            $('#deletUserID').submit();
        }
    });
});

// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// DELETE SEMUA USER
$(document).ready(function() {
    $('#defaultCheck1').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#dataTable tbody').find('input[type="checkbox"][id="userCheckBox"]').prop('checked', isChecked);
    });

    $('#DeleteAllUser').on('click', function(e) {
        e.preventDefault();

        var selectedUserIds = [];
        $('#dataTable tbody').find('input[type="checkbox"][id="userCheckBox"]:checked').each(function() {
            selectedUserIds.push($(this).closest('tr').find('td:first').text());
        });

        if (selectedUserIds.length === 0) {
            alert('Pilih setidaknya satu pengguna untuk dihapus.');
            return;
        }

        $.ajax({
            url: deleteBulkUsersUrl, // URL yang didefinisikan di Blade
            method: 'DELETE',
            data: {
                user_ids: selectedUserIds
            },
            success: function(response) {
                alert(response.success || response.error);

                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON.error || 'Terjadi kesalahan saat menghapus pengguna.');
            }
        });
    });
});
