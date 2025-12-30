


    //PESAN SUCCESS HILANG DALAM 5 DETIK
    // Check if the success message exists
    var successMessage = document.getElementById('successMessage');
    if (successMessage) {
        // Set a timeout to hide the success message after 3 seconds (3000 milliseconds)
        setTimeout(function() {
            successMessage.style.display = 'none';
        }, 5000); // 5000 ms = 5 seconds
    }

