// Fungsi untuk mengonversi nomor kartu 6 digit
// function convertCardNumber(cardNo, machineType) {
//     cardNo = String(cardNo || '0');
//     console.log('Input to convertCardNumber:', cardNo, 'Machine Type:', machineType);

//     if (cardNo.includes(':')) {
//         console.log('Card number already formatted:', cardNo);
//         return cardNo;
//     }

//     let cardNumber = parseInt(cardNo, 10);
//     if (isNaN(cardNumber) || cardNumber < 0) {
//         console.error('Invalid card number:', cardNo);
//         return '0';
//     }

//     let byte6 = (cardNumber >> 16) & 0xFF;
//     let byte7 = (cardNumber >> 8) & 0xFF;
//     let byte8 = cardNumber & 0xFF;
//     let byte5 = 0x5A;

//     let word1 = byte6;                    
//     let word2 = (byte7 << 8) | byte8;     

//     let formatted = `${word1.toString().padStart(5, '0')}:${word2}`;
//     console.log(`Converted card number ${cardNo} to ${formatted} for machine type ${machineType}`);
//     return formatted;
// }

// Fungsi untuk mengonversi nomor kartu 8 digit
// function convertCardNumber(cardNo, machineType) {
//     cardNo = String(cardNo || '0');
//     console.log('Input to convertCardNumber:', cardNo, 'Machine Type:', machineType);

//     if (cardNo.includes(':')) {
//         console.log('Card number already formatted:', cardNo);
//         return cardNo;
//     }

//     let cardNumber = parseInt(cardNo, 10);
//     if (isNaN(cardNumber) || cardNumber < 0) {
//         console.error('Invalid card number:', cardNo);
//         return '0';
//     }

//     let byte6 = (cardNumber >> 16) & 0xFF;
//     let byte7 = (cardNumber >> 8) & 0xFF;
//     let byte8 = cardNumber & 0xFF;
//     let byte5 = 0x21;  // Ubah dari 0x5A ke 0x21 agar word1 = 8481 (08481 padded)

//     // Modifikasi: word1 sekarang seperti PHP, gabung byte5 dan byte6
//     let word1 = (byte5 << 8) | byte6;                    
//     let word2 = (byte7 << 8) | byte8;     

//     let formatted = `${word1.toString().padStart(5, '0')}:${word2}`;
//     console.log(`Converted card number ${cardNo} to ${formatted} for machine type ${machineType}`);
//     return formatted;
// }

// // Fungsi untuk mengonversi nomor kartu 8 atau 6 digit
// function convertCardNumber(cardNo, machineType) {
//     cardNo = String(cardNo || '0');
//     console.log('Input to convertCardNumber:', cardNo, 'Machine Type:', machineType);

//     // Kalau sudah format "xxxxx:yyyyy", langsung return
//     if (cardNo.includes(':')) {
//         return cardNo;
//     }

//     let cardNumber = parseInt(cardNo, 10);
//     if (isNaN(cardNumber) || cardNumber < 0) {
//         console.error('Invalid card number:', cardNo);
//         return '0';
//     }

//     // Sama seperti extractCardNumberData di PHP
//     let part1 = (cardNumber >> 16) & 0xFFFF; // high 16bit
//     let part2 = cardNumber & 0xFFFF;         // low 16bit

//     // Format dengan padding 5 digit di depan
//     let formatted = `${part1.toString().padStart(5, '0')}:${part2}`;

//     console.log(`Converted card number ${cardNo} to ${formatted} for machine type ${machineType}`);
//     return formatted;
// }


function convertCardNumber(cardNo, machineType) {
    cardNo = String(cardNo || '0');
    console.log('Input to convertCardNumber:', cardNo, 'Machine Type:', machineType);

    if (cardNo.includes(':')) {
        console.log('Card number already formatted:', cardNo);
        return cardNo;
    }

    let cardNumber = parseInt(cardNo, 10);
    if (isNaN(cardNumber) || cardNumber < 0) {
        console.error('Invalid card number:', cardNo);
        return '00000:00000';  // Default fallback ke zero-padded formatted
    }

    let byte6 = (cardNumber >> 16) & 0xFF;
    let byte7 = (cardNumber >> 8) & 0xFF;
    let byte8 = cardNumber & 0xFF;
    let byte5 = 0x21;  // Fixed seperti di PHP (untuk word1 = 8481 base, padded 08481)

    // Gabung byte seperti logika PHP inverse
    let word1 = (byte5 << 8) | byte6;                    
    let word2 = (byte7 << 8) | byte8;     

    let formatted = `${word1.toString().padStart(5, '0')}:${word2.toString().padStart(5, '0')}`;
    console.log(`Converted card number ${cardNo} to ${formatted} for machine type ${machineType}`);
    console.log(`Intermediate bytes: byte5=0x${byte5.toString(16)}, byte6=0x${byte6.toString(16)}, byte7=0x${byte7.toString(16)}, byte8=0x${byte8.toString(16)}`);
    return formatted;
}

async function fetchUserDatesAndCreateAccessData(userId, data) {
    try {
        const response = await $.ajax({
            url: `/get-user-dates/${userId}`,
            method: 'GET'
        });

        const accessData = {
            sn: data.sn,
            userid: userId,
            starttime: response.begin_date,
            endtime: response.end_date
        };

        let logDataTemplate;
        if (data.type == '0' || data.type == '50') {
            logDataTemplate = {
                user_id: String(userId),
                sn: data.sn,
                username: String(data.username),
                card_number: String(data.cardnumber || '0'),
                ip_address: data.ip_address || '192.168.100.128'
                // Tidak menyertakan fdata untuk menghindari data base64
            };
        } else {
            logDataTemplate = {
                user_id: String(userId),
                sn: data.sn,
                username: String(data.username),
                card_number: String(data.cardnumber || '0'),
                ip_address: data.ip_address || '192.168.100.128'
            };
        }

        return { accessData, logDataTemplate };
    } catch (err) {
        console.error('Error fetching user dates:', err);
        throw err;
    }
}

async function getBase64Image(imgElement) {
    return new Promise((resolve) => {
        if (!imgElement || !imgElement.src) {
            resolve(null);
        }
        let base64String = imgElement.src;
        if (base64String.startsWith('data:image')) {
            base64String = base64String.split(',')[1];
        }
        resolve(base64String);
    });
}

$(document).ready(function () {
    // Function to toggle button states, including the modal Close button
    function toggleButtons(insertDisabled, deleteDisabled) {
        $('#insertDataButton').prop('disabled', insertDisabled);
        $('#bulkDeleteButton').prop('disabled', deleteDisabled);
        $('#successModal .btn-secondary[data-dismiss="modal"]').prop('disabled', insertDisabled || deleteDisabled);

        if (insertDisabled) {
            $('#insertDataButton').html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        } else {
            $('#insertDataButton').html('Insert To Machine');
        }
        if (deleteDisabled) {
            $('#bulkDeleteButton').html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        } else {
            $('#bulkDeleteButton').html('<i class="fa fa-trash"> Delete To Machine</i>');
        }
    }

    // Check/Uncheck semua checkbox user
    $('#checkAll').on('change', function () {
        var isChecked = $(this).is(':checked');
        $('table tbody input.user-checkbox').prop('checked', isChecked);
    });

    // Tangani klik tombol hapus di baris tabel
    $('.delete-button').on('click', function () {
        const userid = $(this).data('userid');
        $('#successModal').data('userid', userid);
    });

    // Tombol Insert Data
    $('#insertDataButton').on('click', async function () {
        toggleButtons(true, true); // Disable both buttons
        let userDataArray = [];
        let totalChecked = $('.user-checkbox:checked').length;
        let completedRequests = 0;
        let failedRequests = 0;

        let selectedMachines = $('input[type=checkbox][id^="mesin"]:checked').map(function () {
            return {
                sn: $(this).val(),
                type: $(this).data('type'),
                id: $(this).attr('id').replace('mesin', ''),
                ip: $(this).data('ip'),
                nodeid: $(this).data('nodeid')
            };
        }).get();

        if (selectedMachines.length === 0) {
            Swal.fire({
                title: 'Error',
                text: 'Select at least one machine for input!',
                icon: 'error'
            });
            toggleButtons(false, false);
            return;
        }

        if (totalChecked === 0) {
            Swal.fire({
                title: 'Error',
                text: 'No users selected!',
                icon: 'error'
            });
            toggleButtons(false, false);
            return;
        }

        $('#progressContainer').show();
        $('#progressBar').css('width', '0%');
        $('#progressText').text('0%');

        async function processUsers() {
            const users = $('.user-checkbox:checked').map(function () {
                const row = $(this).closest('tr');
                const imgElement = row.find('td img')[0];
                // Ambil BEGIN_DATE dan END_DATE dari kolom tabel
                const beginDate = row.find('td:nth-child(4)').text().trim(); // Kolom BEGIN_DATE
                const endDate = row.find('td:nth-child(5)').text().trim();   // Kolom END_DATE
                return {
                    userId: $(this).data('userid'),
                    username: $(this).data('username'),
                    cardNumber: $(this).data('cardnumber') || '0',
                    beginDate: beginDate,
                    endDate: endDate,
                    imgElement: imgElement
                };
            }).get();

            let hasFailures = false;

            for (const user of users) {
                const hasType50 = selectedMachines.some(m => m.type == '50');
                let imageData = null;

                if (hasType50 && user.imgElement) {
                    imageData = await getBase64Image(user.imgElement);
                    if (!imageData) {
                        console.warn(`No picture data found for user with type 50`, { userid: user.userId, type: "50" });
                        hasFailures = true;
                        continue;
                    }
                }

                try {
                    await processMachines(user.userId, user.username, user.cardNumber, user.beginDate, user.endDate, imageData, selectedMachines);
                } catch (err) {
                    console.error('Error processing machines for user:', user.userId, err);
                    hasFailures = true;
                }
            }

            $('#progressContainer').hide();
            toggleButtons(false, false); // Re-enable both buttons
            if (hasFailures || failedRequests > 0) {
                Swal.fire({
                    title: 'Error',
                    text: `Data Failed to Insert. Please Check Your Connection Again.`,
                    icon: 'error'
                });
            } else {
                Swal.fire({
                    title: 'Success',
                    text: 'All selected data has been successfully sent to the API!',
                    icon: 'success'
                });
            }
        }

        await processUsers();

        async function processMachines(userId, username, cardNumber, beginDate, endDate, imageData, machines) {
            for (const machine of machines) {
                let processedCardNumber = cardNumber;
                if (machine.type == '1' || machine.type == '3') {
                    processedCardNumber = convertCardNumber(cardNumber, machine.type);
                }

                let data = {
                    sn: machine.sn,
                    userid: userId,
                    username: username,
                    type: machine.type.toString(),
                    admin: "0",
                    cardnumber: processedCardNumber,
                    fdata: ""
                };

                async function proceedWithData(fdataValue) {
                    if (machine.type == '50' && imageData) {
                        data.fdata = imageData;
                    } else {
                        data.fdata = fdataValue || "0";
                    }

                    if (machine.type != '50' && machine.type != '0' && machine.type != '1' && machine.type != '3') {
                        saveToUserDataModel(userId, machine.sn, machine.type);
                    }

                    userDataArray.push(data);

                    try {
                        if (machine.type == '1') {
                            await sendDataToSoyalApi(userId, username, processedCardNumber, machine);
                        } else {
                            await sendDataToApi(data, userId, machine.id, machine.ip);
                        }

                        // Jika machine.type adalah 0 atau 50, kirim data ke sztimmyUserAccessApiUrl
                        if (machine.type == '0' || machine.type == '50') {
                            // Format tanggal ke Y-m-d H:i:s jika belum dalam format tersebut
                            const formattedBeginDate = formatDateToApi(beginDate);
                            const formattedEndDate = formatDateToApi(endDate);

                            const accessData = {
                                sn: machine.sn,
                                userid: userId,
                                starttime: formattedBeginDate || '2025-03-01 00:00:00', // Fallback jika null
                                endtime: formattedEndDate || '2025-06-26 23:59:00'   // Fallback jika null
                            };

                            await $.ajax({
                                url: sztimmyUserAccessApiUrl,
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify(accessData),
                                success: function (response) {
                                    console.log(`Successfully sent to sztimmy/useraccess for user ${userId} and machine ${machine.sn}:`, response);
                                    const logData = {
                                        log_date: moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss'),
                                        modul: JSON.stringify(accessData),
                                        desc: `Success Insert User Access ${username.substring(0, 100)} to machine ${machine.sn}`,
                                        status: 'Successfully'
                                    };
                                    saveToDeviceLog(logData);
                                },
                                error: function (err) {
                                    console.error(`Error sending to sztimmy/useraccess for user ${userId} and machine ${machine.sn}:`, err);
                                    failedRequests++;
                                    const logData = {
                                        log_date: moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss'),
                                        modul: JSON.stringify(accessData),
                                        desc: `Failed Insert User Access ${username.substring(0, 100)} to machine ${machine.sn}`,
                                        status: 'Failed'
                                    };
                                    saveToDeviceLog(logData);
                                }
                            });
                        }

                        completedRequests++;
                        updateProgress();
                    } catch (err) {
                        console.error('Error in proceedWithData:', err);
                        throw err;
                    }

                    await delay(500);
                }

                if (machine.type == '0') {
                    console.log('Fetching Fp for User ID:', userId);
                    try {
                        const response = await $.ajax({
                            url: `/get-fp/${userId}`,
                            method: 'GET'
                        });
                        let fpData = response.fp || "0";
                        console.log('Fp Data Received:', fpData);
                        await proceedWithData(fpData);
                    } catch (err) {
                        console.error('Error fetching Fp data:', err);
                        await proceedWithData("0");
                    }
                } else {
                    await proceedWithData("");
                }
            }
        }

        // Fungsi untuk memformat tanggal ke Y-m-d H:i:s
        function formatDateToApi(dateStr) {
            if (!dateStr) {
                console.warn('Date is null, returning null');
                return null;
            }
            let date = moment(dateStr, ['YYYY-MM-DD', 'MM-DD-YYYY', 'DD-MM-YYYY']);
            if (!date.isValid()) {
                console.warn('Invalid date:', dateStr);
                return null;
            }
            return date.format('YYYY-MM-DD HH:mm:ss');
        }

        function updateProgress() {
            let progressPercentage = Math.min((completedRequests / (totalChecked * selectedMachines.length)) * 100, 100);
            $('#progressBar').css('width', progressPercentage + '%');
            $('#progressText').text(Math.round(progressPercentage) + '%');
        }

        async function sendDataToSoyalApi(userId, username, cardNumber, machine, retries = 3) {
            const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');
            for (let attempt = 1; attempt <= retries; attempt++) {
                try {
                    const response = await $.ajax({
                        url: soyalAddUserApiUrl,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            user_id: String(userId),
                            card_number: String(cardNumber || '0'),
                            ip_address: String(machine.ip || '192.168.100.127'),
                            node_id: String(machine.nodeid),
                            timezone: '0',
                            begin_date: '01-01-2025',
                            expire_date: '04-23-2026',
                            begin_time: '00:00',
                            expire_time: '00:00',
                            pin: '1',
                            lift1: '255',
                            lift2: '255',
                            lift3: '255',
                            lift4: '255',
                            user_name: String(username),
                            type: String(machine.type)
                        })
                    });

                    console.log('Soyal API Response:', response);
                    completedRequests++;
                    updateProgress();

                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify({
                            user_id: String(userId),
                            card_number: String(cardNumber || '0'),
                            ip_address: machine.ip || '192.168.100.127',
                            node_id: String(machine.nodeid),
                            timezone: '0',
                            user_name: String(username),
                            type: String(machine.type)
                        }),
                        desc: `Success Insert User ${username.substring(0, 100)}`, // Batasi panjang desc
                        status: 'Successfully'
                    };
                    saveToDeviceLog(logData);

                    await $.ajax({
                        url: '/save-to-usersxdevice',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            userId: userId,
                            gateId: machine.id
                        }),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            console.log('Data saved to usersxdevice:', response);
                        },
                        error: function (err) {
                            console.error('Error saving to usersxdevice:', err);
                        }
                    });

                    return;
                } catch (err) {
                    console.error(`Attempt ${attempt} - Error sending to Soyal API:`, err);
                    if (attempt === retries) {
                        console.error('All retries failed for user:', userId);
                        failedRequests++;
                        const logData = {
                            log_date: currentTime,
                            modul: JSON.stringify({
                                user_id: String(userId),
                                card_number: String(cardNumber || '0'),
                                ip_address: machine.ip || '192.168.100.127',
                                node_id: String(machine.nodeid),
                                timezone: '0',
                                user_name: String(username),
                                type: String(machine.type)
                            }),
                            desc: `Failed Insert User ${username.substring(0, 100)}`, // Batasi panjang desc
                            status: 'Failed'
                        };
                        saveToDeviceLog(logData);
                        Swal.fire({
                            title: 'Error',
                            text: `Failed to send data to Soyal API for user ${userId} after ${retries} attempts`,
                            icon: 'error'
                        });
                        completedRequests++;
                        updateProgress();
                    }
                    await delay(1000);
                }
            }
        }

        async function sendDataToApi(data, userId, machineId, machineIp, retries = 3) {
            const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');
            for (let attempt = 1; attempt <= retries; attempt++) {
                try {
                    const registerResponse = await $.ajax({
                        url: sztimmyRegisterFaceApiUrl,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data)
                    });

                    console.log(`Attempt ${attempt} - API Response:`, registerResponse);

                    await delay(500);
                    const { accessData, logDataTemplate } = await fetchUserDatesAndCreateAccessData(userId, data);

                    if (registerResponse.result === 'fail') {
                        throw new Error('API returned fail result');
                    }

                    completedRequests++;
                    updateProgress();

                    // Simpan log tanpa menyertakan fdata di desc
                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify(logDataTemplate),
                        desc: `Success Insert User ${data.username.substring(0, 100)}`, // Batasi panjang desc
                        status: 'Successfully'
                    };
                    saveToDeviceLog(logData);

                    await $.ajax({
                        url: sztimmyUserAccessApiUrl,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(accessData)
                    });

                    console.log('Successfully sent to useraccess:', accessData);

                    await sendUserListToApi(data.sn, userId);

                    await $.ajax({
                        url: '/save-to-usersxdevice',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            userId: userId,
                            gateId: machineId
                        }),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            console.log('Data saved to usersxdevice:', response);
                        },
                        error: function (err) {
                            console.error('Error saving to usersxdevice:', err);
                        }
                    });

                    return;
                } catch (err) {
                    console.error(`Attempt ${attempt} - Error inserting data:`, err);
                    if (attempt === retries) {
                        console.error('All retries failed for user:', userId);
                        failedRequests++;
                        const logData = {
                            log_date: currentTime,
                            modul: JSON.stringify({
                                user_id: String(userId),
                                sn: data.sn,
                                username: String(data.username),
                                card_number: String(data.cardnumber || '0'),
                                ip_address: machineIp || '192.168.100.127'
                            }),
                            desc: `Failed Insert User ${data.username.substring(0, 100)}`, // Batasi panjang desc
                            status: 'Failed'
                        };
                        saveToDeviceLog(logData);
                        completedRequests++;
                        updateProgress();
                    }
                    await delay(500);
                }
            }
        }

        function saveToUserDataModel(userId, sn, type) {
            $.ajax({
                url: '/save-to-user-data',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    fid: userId,
                    sn: sn,
                    type: type,
                    picture: "0",
                    fp: "0"
                }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log('Data saved to userDataModel:', response);
                },
                error: function (err) {
                    console.error('Error saving to userDataModel:', err);
                }
            });
        }

        function saveToDeviceLog(logData) {
            // Validasi panjang data untuk mencegah truncation
            if (logData.modul.length > 65535) {
                console.warn('Modul data too large, truncating...');
                logData.modul = logData.modul.substring(0, 65535);
            }
            if (logData.desc.length > 255) {
                console.warn('Desc data too large, truncating to 255 characters...');
                logData.desc = logData.desc.substring(0, 255);
            }

            $.ajax({
                url: '/save-to-devicelog',
                method: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify(logData),
                success: function (response) {
                    console.log('Device log saved:', response);
                },
                error: function (err) {
                    console.error('Error saving device log:', err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to save device log: ' + (err.responseJSON?.details || 'Unknown error'),
                        icon: 'error'
                    });
                }
            });
        }

        function formatDate(dateStr) {
            console.log('Formatting date:', dateStr);
            if (!dateStr) {
                console.log('Date is null, returning null');
                return null;
            }
            let date = new Date(dateStr);
            if (isNaN(date.getTime())) {
                console.log('Invalid date:', dateStr);
                return null;
            }
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            let formatted = `${month}-${day}-${year}`;
            console.log('Formatted date:', formatted);
            return formatted;
        }

        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    });

    // Tangani tombol Delete To Machine di modal
    $('#bulkDeleteButton').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'The selected data will be deleted from the selected machine!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Delete!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                validateAndSubmit();
            }
        });
    });

   async function validateAndSubmit() {
    toggleButtons(true, true);
    const selectedUsers = [];
    const selectedMachines = [];
    let completedRequests = 0;
    let failedRequests = 0;
    let totalRequests = 0;

    // Ambil data pengguna dan mesin
    const useridFromModal = $('#successModal').data('userid');
    if (useridFromModal) {
        selectedUsers.push({
            userid: useridFromModal,
            username: $(`input[data-userid="${useridFromModal}"]`).data('username') || 'Unknown',
            cardnumber: $(`input[data-userid="${useridFromModal}"]`).data('cardnumber') || '0'
        });
    } else {
        document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
            selectedUsers.push({
                userid: checkbox.getAttribute('data-userid'),
                username: checkbox.getAttribute('data-username') || 'Unknown',
                cardnumber: checkbox.getAttribute('data-cardnumber') || '0'
            });
        });
    }

    document.querySelectorAll('.machine-checkbox:checked').forEach(checkbox => {
        selectedMachines.push({
            sn: checkbox.value,
            type: checkbox.getAttribute('data-type'),
            ip: checkbox.getAttribute('data-ip'),
            nodeid: checkbox.getAttribute('data-nodeid'),
            id: checkbox.getAttribute('id').replace('mesin', '')
        });
    });

    if (selectedUsers.length === 0 || selectedMachines.length === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Please select at least one user and one machine!',
            icon: 'error'
        });
        toggleButtons(false, false);
        return;
    }

    totalRequests = selectedUsers.length * selectedMachines.length;
    $('#progressContainer').show();
    $('#progressBar').css('width', '0%');
    $('#progressText').text('0%');

    for (const user of selectedUsers) {
        for (const machine of selectedMachines) {
            const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');
            let deleteData;

            if (machine.type == '1') {
                const cardNumber = user.cardnumber || '0';
                const formattedCardNumber = convertCardNumber(cardNumber, machine.type);
                deleteData = {
                    user_id: String(user.userid),
                    card_number: "00000:00000",
                    card_numberConvert: formattedCardNumber,
                    ip_address: machine.ip || '192.168.100.127',
                    node_id: machine.nodeid || '3'
                };

                try {
                    // Pastikan soyalApiUrl didefinisikan
                    if (!soyalApiUrl) {
                        throw new Error('soyalApiUrl is not defined');
                    }

                    const response = await fetch(soyalApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(deleteData)
                    });

                    const responseData = await response.json();
                    console.log('Soyal API Response:', responseData);

                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify(deleteData),
                        desc: response.ok && responseData.result !== 'fail'
                            ? `Success Delete User ${user.username}`
                            : `Failed Delete User ${user.username} - Check Network Connection`,
                        status: response.ok && responseData.result !== 'fail' ? 'Successfully' : 'Failed'
                    };
                    await saveToDeviceLog(logData);

                    if (!response.ok || responseData.result === 'fail') {
                        failedRequests++;
                        Swal.fire({
                            title: 'Failed',
                            text: `Failed to delete user data ${user.username} from machine ${machine.sn}.`,
                            icon: 'error'
                        });
                    } else {
                        // Hapus dari usersxdevice
                        try {
                            await $.ajax({
                                url: '/delete-from-usersxdevice',
                                method: 'POST',
                                contentType: 'application/json',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                                },
                                data: JSON.stringify({
                                    userId: user.userid,
                                    gateId: machine.id
                                })
                            });
                            console.log(`Successfully deleted from usersxdevice for user ${user.userid} and gate ${machine.id}`);
                        } catch (err) {
                            console.error(`Error deleting from usersxdevice for user ${user.userid} and gate ${machine.id}:`, err);
                            failedRequests++;
                            Swal.fire({
                                title: 'Error',
                                text: `Failed to delete from usersxdevice for user ${user.userid}: ${err.message || 'Unknown error'}`,
                                icon: 'error'
                            });
                        }
                    }
                } catch (error) {
                    failedRequests++;
                    console.error('Error deleting from Soyal:', error);
                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify(deleteData),
                        desc: `Failed Delete User ${user.username}: ${error.message}`,
                        status: 'Failed'
                    };
                    await saveToDeviceLog(logData);
                }
            } else {
                deleteData = {
                    sn: machine.sn,
                    userid: user.userid
                };

                try {
                    // Pastikan sztimmyApiUrl didefinisikan
                    if (!sztimmyApiUrl) {
                        throw new Error('sztimmyApiUrl is not defined');
                    }

                    const response = await fetch(sztimmyApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(deleteData)
                    });

                    const responseData = await response.json();
                    console.log('Sztimmy API Response:', responseData);

                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify(deleteData),
                        desc: response.ok && responseData.result !== 'fail'
                            ? `Success Delete User ${user.username}`
                            : `Failed Delete User ${user.username} - Check Network Connection`,
                        status: response.ok && responseData.result !== 'fail' ? 'Successfully' : 'Failed'
                    };
                    await saveToDeviceLog(logData);

                    if (response.ok && responseData.result !== 'fail') {
                        // Hapus dari tbl_usersdata untuk mesin tipe 50
                        if (machine.type == '50') {
                            try {
                                const pictureResponse = await fetch('/delete-user-picture', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    },
                                    body: JSON.stringify({ userid: user.userid })
                                });

                                const pictureResponseData = await pictureResponse.json();
                                console.log(`Picture deletion response for user ${user.userid}:`, pictureResponseData);

                                if (pictureResponse.ok) {
                                    console.log(`Picture data deleted successfully for user ${user.userid} with type 50.`);
                                } else {
                                    console.warn(`No picture data found for user ${user.userid} with type 50.`);
                                }
                            } catch (pictureError) {
                                console.error('Error deleting picture data:', pictureError);
                                failedRequests++;
                            }
                        }

                        // Hapus dari usersxdevice
                        try {
                            await $.ajax({
                                url: '/delete-from-usersxdevice',
                                method: 'POST',
                                contentType: 'application/json',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                                },
                                data: JSON.stringify({
                                    userId: user.userid,
                                    gateId: machine.id
                                })
                            });
                            console.log(`Successfully deleted from usersxdevice for user ${user.userid} and gate ${machine.id}`);
                        } catch (err) {
                            console.error(`Error deleting from usersxdevice for user ${user.userid} and gate ${machine.id}:`, err);
                            failedRequests++;
                            Swal.fire({
                                title: 'Error',
                                text: `Failed to delete from usersxdevice for user ${user.userid}: ${err.message || 'Unknown error'}`,
                                icon: 'error'
                            });
                        }
                    } else {
                        failedRequests++;
                        Swal.fire({
                            title: 'Failed',
                            text: `Failed to delete user data ${user.username} from machine ${machine.sn}.`,
                            icon: 'error'
                        });
                    }
                } catch (error) {
                    failedRequests++;
                    console.error('Error deleting from Sztimmy:', error);
                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify(deleteData),
                        desc: `Failed Delete User ${user.username}: ${error.message}`,
                        status: 'Failed'
                    };
                    await saveToDeviceLog(logData);
                }
            }

            completedRequests++;
            updateProgress(completedRequests, totalRequests);
            await delay(1000);
        }
    }

    $('#progressContainer').hide();
    toggleButtons(false, false);

    if (failedRequests > 0) {
        Swal.fire({
            title: 'Warning',
            text: `Finished with ${failedRequests} failure(s), Please Check Your Connection`,
            icon: 'warning'
        });
    } else {
        Swal.fire({
            title: 'Success',
            text: 'All selected data has been successfully deleted from the machine!',
            icon: 'success'
        });
    }
}

    function updateProgress(completedRequests, totalRequests) {
        let progressPercentage = (completedRequests / totalRequests) * 100;
        $('#progressBar').css('width', progressPercentage + '%');
        $('#progressText').text(Math.round(progressPercentage) + '%');
    }

    function delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function saveToDeviceLog(logData) {
        $.ajax({
            url: '/save-to-devicelog',
            method: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(logData),
            success: function (response) {
                console.log('Device log saved:', response);
            },
            error: function (err) {
                console.error('Error saving device log:', err);
            }
        });
    }

    window.confirmDelete = function (event, userid) {
        event.preventDefault();
        $('#successModal').data('userid', userid);
        $('#successModal').modal('show');
    };
});

async function sendUserListToApi(machineId, userId) {
    let requestData = {
        sn: machineId,
        indexstart: userId,
        indexend: userId
    };

    try {
        const response = await $.ajax({
            url: sztimmyGetUserListApiUrl,
            method: 'POST',
            contentType: 'application/json',
            dataType: 'text',
            data: JSON.stringify(requestData)
        });
        console.log('User list data sent successfully:', response);
    } catch (err) {
        console.error('Error details:', {
            status: err.status,
            statusText: err.statusText,
            response: err.responseText
        });
        throw err;
    }
}