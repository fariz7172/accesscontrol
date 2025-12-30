// Fungsi untuk mengonversi nomor kartu seperti processCardNumber
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
        return '0';
    }

    let byte6 = (cardNumber >> 16) & 0xFF;
    let byte7 = (cardNumber >> 8) & 0xFF;
    let byte8 = cardNumber & 0xFF;
    let byte5 = 0x5A;

    let word1 = byte6;
    let word2 = (byte7 << 8) | byte8;

    let formatted = `${word1.toString().padStart(5, '0')}:${word2}`;
    console.log(`Converted card number ${cardNo} to ${formatted} for machine type ${machineType}`);
    return formatted;
}

// Ambil tanggal + weekzone dari backend
async function fetchUserDatesAndCreateAccessData(userId, data) {
    try {
        const response = await $.ajax({
            url: `/get-user-profile-dates/${userId}`,
            method: 'GET'
        });

        const accessData = {
            sn: data.sn,
            userid: userId,
            starttime: response.begin_date || '2025-01-01 00:00:00',
            endtime: response.end_date || '2025-12-31 23:59:59',
            weekzone: response.weekzone ?? 0
        };

        let logDataTemplate;
        if (data.type == '0' || data.type == '50') {
            logDataTemplate = {
                user_id: String(userId),
                sn: data.sn,
                username: String(data.username),
                card_number: String(data.cardnumber || '0'),
                ip_address: data.ip_address || '192.168.100.128'
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
    // Toggle tombol
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

    // Check/Uncheck semua user
    $('#checkAll').on('change', function () {
        var isChecked = $(this).is(':checked');
        $('table tbody input.user-checkbox').prop('checked', isChecked);
    });

    // Tombol Insert Data
    $('#insertDataButton').on('click', function () {
        toggleButtons(true, true);
        let totalChecked = $('.user-checkbox:checked').length;
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
            Swal.fire({ title: 'Error', text: 'Pilih minimal satu mesin!', icon: 'error' });
            toggleButtons(false, false);
            return;
        }

        if (totalChecked === 0) {
            Swal.fire({ title: 'Error', text: 'Tidak ada user yang dipilih!', icon: 'error' });
            toggleButtons(false, false);
            return;
        }

        $('#progressContainer').show();
        $('#progressBar').css('width', '0%');
        $('#progressText').text('0%');

        let completedRequests = 0;
        let failedRequests = 0;
let failedMachines = [];
        let totalRequests = totalChecked * selectedMachines.length;
        let allAccessPayloads = [];

        async function processUsers() {
            const users = $('.user-checkbox:checked').map(function () {
                const row = $(this).closest('tr');
                const imgElement = row.find('td img')[0];
                return {
                    userId: $(this).data('userid'),
                    username: $(this).data('username'),
                    cardNumber: $(this).data('cardnumber') || '0',
                    imgElement: imgElement
                };
            }).get();

            for (const user of users) {
                const hasType50 = selectedMachines.some(m => String(m.type) === '50');
                let imageData = null;

                if (hasType50 && user.imgElement) {
                    imageData = await getBase64Image(user.imgElement);
                    if (!imageData) {
                        console.warn(`No picture for user ${user.userId}`);
                        failedRequests++;
                        continue;
                    }
                }

                try {
                    const payloads = await processMachines(user.userId, user.username, user.cardNumber, imageData, selectedMachines);
                    allAccessPayloads = allAccessPayloads.concat(payloads);
                } catch (err) {
                    console.error('Error processing user:', user.userId, err);
                    failedRequests++;
                }
            }

            if (allAccessPayloads.length > 0) {
                await sendBatchUserAccess(allAccessPayloads);
            }

            $('#progressContainer').hide();
            toggleButtons(false, false);

           if (failedRequests > 0) {
    const failedList = failedMachines
        .map(m => `<li><strong>${m.sn}</strong> (${m.ip})</li>`)
        .join('');

    Swal.fire({
        title: 'Peringatan',
        html: `
            <p>Selesai dengan <strong>${failedRequests}</strong> kegagalan:</p>
            <ul style="text-align:left; margin:0 auto; display:inline-block;">
                ${failedList}
            </ul>
            <p class="mt-3">Periksa koneksi jaringan atau status mesin.</p>
        `,
        icon: 'warning',
        width: '600px'
    });
} else {
    Swal.fire({
        title: 'Sukses',
        text: 'Semua data berhasil dikirim ke mesin!',
        icon: 'success'
    });
}
        }

        async function processMachines(userId, username, cardNumber, imageData, machines) {
            let accessPayloads = [];
          
                for (const machine of machines) {
                // CEK HANYA DENGAN PING
                const isOnline = await checkDeviceConnection(machine.ip);

              if (!isOnline) {
    console.warn(`[OFFLINE] Mesin ${machine.sn} (${machine.ip}) TIDAK TERHUBUNG (PING GAGAL)`);
    
    failedRequests++;
    failedMachines.push({ sn: machine.sn, ip: machine.ip }); // SIMPAN SN + IP

    saveToDeviceLog({
        log_date: moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss'),
        modul: JSON.stringify({ user_id: userId, sn: machine.sn, ip: machine.ip }),
        desc: `Failed: Mesin ${machine.name || machine.sn} (${machine.ip}) OFFLINE (tidak bisa diping)`,
        status: 'Failed'
    });

    completedRequests++;
    updateProgress(completedRequests, totalRequests);
    continue;
}
                let processedCardNumber = cardNumber;
                if (String(machine.type) === '1') {
                    processedCardNumber = convertCardNumber(cardNumber, machine.type);
                    console.log(`[CONVERT] Card ${cardNumber} → ${processedCardNumber}`);
                } else {
                    console.log(`[CARD] : ${cardNumber}`);
                }

                let fdataValue = "";
                if (String(machine.type) === '0') {
                    const url = `/get-fp-data-by-sn/${userId}/${machine.sn}`;
                    console.log('%c[DEBUG] Ambil Fp dari:', 'color: orange; font-weight: bold', url);

                    try {
                        const fpResponse = await $.ajax({
                            url: url,
                            method: 'GET',
                            timeout: 10000
                        });

                        fdataValue = fpResponse.Fp || "";
                        console.log('%c[SUKSES] Fp length:', 'color: green; font-weight: bold', fdataValue.length);
                    } catch (err) {
                        console.error('%c[GAGAL] Ambil Fp:', 'color: red', err);
                        fdataValue = "";
                    }
                } else if (String(machine.type) === '50' && imageData) {
                    fdataValue = imageData;
                    console.log(`[FDATA] type=50 → Foto loaded`);
                }

                let data = {
                    sn: machine.sn,
                    userid: userId,
                    username: username,
                    type: machine.type,
                    admin: "0",
                    cardnumber: processedCardNumber,
                    fdata: fdataValue
                };
                console.log('Payload ke registerface:', data);

                if (String(machine.type) === '0' || String(machine.type) === '50') {
                    let fpFlag = (String(machine.type) === '0' && fdataValue) ? "1" : "0";
                    let picFlag = (String(machine.type) === '50' && imageData) ? "1" : "0";
                    saveToUserDataModel(userId, machine.sn, machine.type, fpFlag, picFlag);
                }

                try {
                   if (String(machine.type) === '1') {
                        await sendDataToSoyalApi(userId, username, cardNumber, processedCardNumber, machine);
                    }else {
                        const payload = await sendDataToSztimmyRegisterFace(data, userId, machine.id, machine.ip);
                        if (payload) accessPayloads.push(payload);
                    }
                } catch (err) {
                    console.error(`[ERROR] Gagal kirim user ${userId} ke ${machine.sn}:`, err);
                    failedRequests++;
                }

                completedRequests++;
                updateProgress(completedRequests, totalRequests);
                await delay(500);
            }

            return accessPayloads;
        }

        // === FUNGSI SOYAL API DARI KODE 2 (LEBIH BAIK) ===
     async function sendDataToSoyalApi(userId, username, originalCardNumber, convertedCardNumber, machine, retries = 3) {
    const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');

    for (let attempt = 1; attempt <= retries; attempt++) {
        try {
            const response = await $.ajax({
                url: soyalAddUserApiUrl,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    user_id: String(userId),
                    card_number: convertedCardNumber, // Kirim yang sudah diconvert
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
            updateProgress(completedRequests, totalRequests);

            const logData = {
                log_date: currentTime,
                modul: JSON.stringify({
                    user_id: String(userId),
                    card_number: originalCardNumber,           // ASLI
                    card_number_convert: convertedCardNumber,  // CONVERT
                    ip_address: machine.ip || '192.168.100.127',
                    node_id: String(machine.nodeid),
                    timezone: '0',
                    user_name: String(username),
                    type: String(machine.type)
                }),
                desc: `Success Insert User ${username.substring(0, 100)}`,
                status: 'Successfully'
            };
            saveToDeviceLog(logData);

            await $.ajax({
                url: '/save-to-usersxdevice',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ userId: userId, gateId: machineId }),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    console.log(`[USERSXDEVICE] Saved: user ${userId} → gate ${machineId}`, response);
                },
                error: function (err) {
                    console.error(`[USERSXDEVICE] Failed: user ${userId} → gate ${machineId}`, err);
                }
            });

            return;
        } catch (err) {
            console.error(`Attempt ${attempt} - Error sending to Soyal API:`, err);
            if (attempt === retries) {
                failedRequests++;
                const logData = {
                    log_date: currentTime,
                    modul: JSON.stringify({
                        user_id: String(userId),
                        card_number: originalCardNumber,
                        card_number_convert: convertedCardNumber,
                        ip_address: machine.ip || '192.168.100.127',
                        node_id: String(machine.nodeid),
                        timezone: '0',
                        user_name: String(username),
                        type: String(machine.type)
                    }),
                    desc: `Failed Insert User ${username.substring(0, 100)}`,
                    status: 'Failed'
                };
                saveToDeviceLog(logData);

                Swal.fire({
                    title: 'Error',
                    text: `Failed to send data to Soyal API for user ${userId} after ${retries} attempts`,
                    icon: 'error'
                });
                completedRequests++;
                updateProgress(completedRequests, totalRequests);
            }
            await delay(1000);
        }
    }
}

        async function sendDataToSztimmyRegisterFace(data, userId, machineId, machineIp) {
            const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');
            for (let attempt = 1; attempt <= 3; attempt++) {
                try {
                    const response = await $.ajax({
                        url: sztimmyRegisterFaceApiUrl,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data)
                    });

                    if (response.result === 'fail') throw new Error('Register face failed');

                    await sendUserListToApi(data.sn, userId);

                    const { accessData, logDataTemplate } = await fetchUserDatesAndCreateAccessData(userId, data);
                    accessData.weekzone = accessData.weekzone ?? 0;

                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify(logDataTemplate),
                        desc: `Success Insert User ${data.username.substring(0, 100)}`,
                        status: 'Successfully'
                    };
                    saveToDeviceLog(logData);

                    await $.ajax({
                        url: '/save-to-usersxdevice',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ userId, gateId: machineId }),
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                    });

                    return accessData;
                } catch (err) {
                    if (attempt === 3) {
                        const logData = {
                            log_date: currentTime,
                            modul: JSON.stringify(data),
                            desc: `Failed Insert User ${data.username}: ${err.message}`,
                            status: 'Failed'
                        };
                        saveToDeviceLog(logData);
                    }
                    await delay(500);
                }
            }
            return null;
        }

        async function sendBatchUserAccess(payloads) {
            const chunkSize = 5;
            const chunks = [];
            for (let i = 0; i < payloads.length; i += chunkSize) {
                chunks.push(payloads.slice(i, i + chunkSize));
            }

            let successCount = 0;
            let failCount = 0;

            for (const chunk of chunks) {
                try {
                    await $.ajax({
                        url: sztimmyUserAccessApiUrl,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(chunk),
                        timeout: 30000
                    });
                    successCount += chunk.length;
                } catch (err) {
                    failCount += chunk.length;
                }
                await delay(500);
            }

            completedRequests += payloads.length;
            updateProgress(completedRequests, totalRequests);

            if (failCount > 0) {
                Swal.fire({
                    title: 'Peringatan',
                    text: `${failCount} data gagal dikirim ke useraccess.`,
                    icon: 'warning'
                });
            }
        }

      async function saveToUserDataModel(userId, sn, type, fpData = "0", pictureData = "0") {
    const logDesc = `Register user ${userId} to machine ${sn} (type=${type})`;
    
    try {
        const response = await $.ajax({
            url: '/save-to-user-data',
            method: 'POST', // Backend harus handle UPSERT
            contentType: 'application/json',
            data: JSON.stringify({
                fid: userId,
              type: String(type),
                sn: sn,
                fp: fpData,
                picture: pictureData,
                desc: logDesc
            }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        console.log('[USERS_DATA] Saved/Updated:', response);
    } catch (xhr) {
        if (xhr.status === 500 && xhr.responseText.includes('Duplicate entry')) {
            console.warn('[USERS_DATA] Duplicate → Skip (sudah ada)', { userId, sn, type });
            // TIDAK ERROR → hanya skip
        } else {
            console.error('Gagal simpan ke tbl_usersdata:', xhr.responseText);
        }
    }
}

        function saveToDeviceLog(logData) {
            if (logData.modul.length > 65535) logData.modul = logData.modul.substring(0, 65535);
            if (logData.desc.length > 255) logData.desc = logData.desc.substring(0, 255);

            $.ajax({
                url: '/save-to-devicelog',
                method: 'POST',
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: JSON.stringify(logData)
            });
        }

        function updateProgress(completed, total) {
            let percent = Math.min((completed / total) * 100, 100);
            $('#progressBar').css('width', percent + '%');
            $('#progressText').text(Math.round(percent) + '%');
        }

        async function sendUserListToApi(machineId, userId) {
            let requestData = { sn: machineId, indexstart: userId, indexend: userId };
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
                console.error('Error details:', err);
                throw err;
            }
        }

        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        processUsers();
    });

    // === BULK DELETE (TIDAK DIUBAH) ===
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
    let failedMachines = [];
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
    const failedSNList = failedMachines.map(sn => `<li><strong>${sn}</strong></li>`).join('');
    
    Swal.fire({
        title: 'Peringatan',
        html: `
            <p>Tidak Ada Koneksi Jaringan  <strong>${failedRequests} ${machine.sn} (${machine.ip})</strong> kegagalan.</p>
            <p><strong>Mesin yang gagal:</strong></p>
            <ul style="text-align:left; margin:0 auto; display:inline-block;">
                ${failedSNList || '<li><em>Tidak ada data SN </em></li>'}
            </ul>
            <p class="mt-3">Periksa koneksi jaringan atau status mesin.</p>
        `,
        icon: 'warning',
        width: '600px'
    });
}else {
        Swal.fire({
            title: 'Success',
            text: 'All selected data has been successfully deleted from the machine!',
            icon: 'success'
        });
    }
}


async function checkDeviceConnection(ip) {
    try {
        const response = await $.ajax({
            url: '/check-device-status',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ ip: ip }),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            timeout: 5000
        });
        return response.online;
    } catch (err) {
        console.warn(`[PING] Gagal cek ${ip}`, err);
        return false;
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