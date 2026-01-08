$(document).ready(function() {
    $(document).on('click', '#bulkDeleteButton', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Show SweetAlert2 confirmation dialog
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
                // If confirmed, submit the form
                $('#deletUserID').submit();
            }
        });
    });
});
