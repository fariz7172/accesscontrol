$(document).ready(function() {
    $('#insertDataButton').on('click', function() {
        let userDataArray = [];
        let totalChecked = $('.user-checkbox:checked').length;
        let completedRequests = 0;

        // Ambil semua ID mesin yang dipilih
        let selectedMachineIds = $('input[type=checkbox][id^="mesin"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedMachineIds.length === 0) {
            alert("Pilih Mesin Setidak nya 1 untuk di input");
            return;
        }

        if (totalChecked === 0) {
            alert("Tidak ada data yang dichecklist");
            return;
        }

        $('#progressContainer').show();
        $('#progressBar').css('width', '0%');
        $('#progressText').text('0%');

        $('.user-checkbox:checked').each(function() {
            let userId = $(this).data('userid');
            let username = $(this).data('username');
            let cardNumber = $(this).data('cardnumber');
            let pictureUrl = $(this).data('picture');

            $.ajax({
                url: pictureUrl,
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(blob) {
                    let reader = new FileReader();
                    reader.onloadend = function() {
                        selectedMachineIds.forEach(function(machineId) {
                            let data = {
                                SN: machineId,
                                userid: userId,
                                username: username,
                                type: "50", // 50 untuk upload gambar , dan nol 0 untuk upload finger print
                                admin: "0",
                                cardnumber: cardNumber,
                                fdata: reader.result.split(',')[1]
                            };
                            userDataArray.push(data);
                            sendDataToApi(data, userId, machineId); // Panggil fungsi baru dengan parameter tambahan
                        });
                    };
                    reader.readAsDataURL(blob);
                },
                error: function(err) {
                    console.error('Error fetching image:', err);
                }
            });
        });

        function sendDataToApi(data, userId, machineId) {
            $.ajax({
                url: 'http://localhost:51776/sztimmy/registerface',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    completedRequests++;
                    let progressPercentage = (completedRequests / (totalChecked * selectedMachineIds.length)) * 100;
                    $('#progressBar').css('width', progressPercentage + '%');
                    $('#progressText').text(Math.round(progressPercentage) + '%');

                    if (completedRequests === totalChecked * selectedMachineIds.length) {
                        Swal.fire({
                            title: "Good job!",
                            text: "All selected data has been successfully sent to the API!",
                            icon: "success"
                        });
                        $('#progressContainer').hide();
                    }

                    // Panggil fungsi sendUserListToApi setelah data berhasil dikirim
                    sendUserListToApi(machineId, userId);
                },
                error: function(err) {
                    console.error('Error inserting data:', err);
                }
            });
        }

        // Fungsi baru untuk mengirim data ke API getuserlist
        function sendUserListToApi(machineId, userId) {
            let requestData = {
                sn: machineId,
                indexstart: userId, // Sesuai permintaan, gunakan machineId sebagai indexstart
                indexend: userId       // Sesuai permintaan, gunakan userId sebagai indexend
            };

            $.ajax({
                url: 'http://localhost:51776/sztimmy/getuserlist',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(requestData),
                success: function(response) {
                    console.log('User list data sent successfully:', response);
                },
                error: function(err) {
                    console.error('Error sending user list data:', err);
                }
            });
        }
    });
});
