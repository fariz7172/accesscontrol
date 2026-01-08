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
                $.ajax({
                    url: '{{ route("delete.users") }}',
                    method: 'DELETE',
                    data: {
                        user_ids: selectedUserIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.success || 'Users have been deleted.',
                        });
                        location.reload(); // Reload the page after successful deletion
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.error || 'An error occurred while deleting users.',
                        });
                    }
                });
            }
        });
    });
});
