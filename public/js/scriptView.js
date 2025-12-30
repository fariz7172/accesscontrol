$(document).ready(function() {
    $(document).on('click', '.view-button', function () {
        const userName = $(this).data('username');
        const userDepartment = $(this).data('department');
        const tag64 = $(this).data('card'); // Sesuaikan dengan data-card
        const faceNoWithDevice = $(this).data('devicenames'); // Sesuaikan dengan data-devicenames
        const userPicture = $(this).data('picture'); // Gunakan data-picture

        $('#viewUserName').val(userName);
        $('#viewUserDepartment').val(userDepartment);
        $('#TAG64').val(tag64);

        // Format faceNoWithDevice jika diperlukan
        if (faceNoWithDevice) {
            $('#viewFaceNo').val(faceNoWithDevice); // Langsung gunakan devicenames
        } else {
            $('#viewFaceNo').val('');
        }

        // Set gambar jika ada
        if (userPicture) {
            $('#viewUserPicture').attr('src', userPicture);
        } else {
            $('#viewUserPicture').attr('src', '{{ asset('images/placeholder.jpg') }}'); // Fallback ke placeholder
        }
    });
});