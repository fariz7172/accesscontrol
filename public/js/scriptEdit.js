$(document).on('click', '.edit-button', function () {
    const userId = $(this).data('userid');
    const userName = $(this).data('username');
    const userDepartment = $(this).data('department');
    const faceNoList = $(this).data('faceno'); // Get the FaceNo values as an array
    const userPic = $(this).data('userpicture');

    $('#editUserId').val(userId);
    $('#editUserName').val(userName);
    $('#editUserDepartment').val(userDepartment);

    
    const faceNoSelect = $('#faceNo');
    faceNoSelect.empty(); // Clear previous options

    if (Array.isArray(faceNoList) && faceNoList.length > 0) {
        faceNoList.forEach(faceNo => {
            faceNoSelect.append(new Option(faceNo, faceNo)); // Add FaceNo to select
        });
    } else {
        faceNoSelect.append(new Option('No devices found', ''));
    }

    // Set the user's picture
    $('#userPic').attr('src', userPic ? userPic : 'path/to/default-image.jpg');
});
