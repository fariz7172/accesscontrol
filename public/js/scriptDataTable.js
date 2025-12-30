// Fungsi untuk mengonversi nomor kartu seperti processCardNumber
function convertCardNumber(cardNo, machineType, facilityCode = 33) {
    cardNo = String(cardNo || '0');
    if (cardNo.includes(':')) return cardNo;

    let cardNumber = parseInt(cardNo, 10);
    if (isNaN(cardNumber)) return '0';

    let word1 = facilityCode;
    let word2 = cardNumber & 0xFFFF;

    if (cardNumber > 65535) {
        word1 = (cardNumber >> 16) & 0xFFFF;
    }

    let formatted = `${word1.toString().padStart(5, '0')}:${word2.toString().padStart(5, '0')}`;
    return formatted;
}

// Ambil tanggal + weekzone dari backend
async function fetchUserDatesAndCreateAccessData(userId, data) {
    try {
        const response = await $.ajax({
            url: `/get-user-profile-dates/${userId}`,
            method: 'GET'
        });

        // Ambil weekzone dari weekzoneuser jika type 0 atau 50
        let weekzone = 0;
        if (['0', '50'].includes(String(data.type))) {
            try {
                const wzResponse = await $.ajax({
                    url: `/get-weekzone/${userId}/${data.sn}`,
                    method: 'GET'
                });
                weekzone = wzResponse.weekzone || 0;
            } catch (err) {
                console.warn('No weekzone data:', err);
            }
        }

        const accessData = {
            sn: data.sn,
            userid: userId,
            starttime: response.begin_date || '2025-01-01 00:00:00',
            endtime: response.end_date || '2025-12-31 23:59:59',
            weekzone: weekzone
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
            $('#bulkDeleteButton').html('<i class="fa fa-trash"></i> Delete From Machine');
        }
    }

    $('#checkAll').on('change', function () {
        var isChecked = $(this).is(':checked');
        $('table tbody input.user-checkbox').prop('checked', isChecked);
    });

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
            Swal.fire({ title: 'Error', text: 'Please select at least one machine!', icon: 'error' });
            toggleButtons(false, false);
            return;
        }

        if (totalChecked === 0) {
            Swal.fire({ title: 'Error', text: 'No user selected!', icon: 'error' });
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
                    title: 'Warning',
                    html: `
                        <p>Completed with <strong>${failedRequests}</strong> failure(s):</p>
                        <ul style="text-align:left; margin:0 auto; display:inline-block;">
                            ${failedList || '<li><em>No machine data</em></li>'}
                        </ul>
                        <p class="mt-3">Please check network connection or machine status.</p>
                    `,
                    icon: 'warning',
                    width: '600px'
                });
            } else {
                Swal.fire({
                    title: 'Success',
                    text: 'All data successfully sent to the machine!',
                    icon: 'success'
                });
            }
        }

async function processMachines(userId, username, cardNumber, imageData, machines) {
    let accessPayloads = [];

    for (const machine of machines) {
        const isOnline = await checkDeviceConnection(machine.ip);

        if (!isOnline) {
            console.warn(`[OFFLINE] Machine ${machine.sn} (${machine.ip}) NOT CONNECTED`);
            failedRequests++;
            failedMachines.push({ sn: machine.sn, ip: machine.ip });

            saveToDeviceLog({
                log_date: moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss'),
                modul: JSON.stringify({ user_id: userId, sn: machine.sn, ip: machine.ip }),
                desc: `Failed: Machine ${machine.sn} (${machine.ip}) OFFLINE`,
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

        // Ambil fingerprint dari mesin (hanya untuk type 0)
        let fdataValue = "";
        if (String(machine.type) === '0') {
            const url = `/get-fp-data-by-sn/${userId}/${machine.sn}`;
            try {
                const fpResponse = await $.ajax({ url, method: 'GET', timeout: 10000 });
                fdataValue = fpResponse.Fp || "";
            } catch (err) {
                console.error('[FAILED] Fetch FP:', err);
                fdataValue = "";
            }
        }

        // Untuk type 50 (foto)
        let pictureValue = "";
        if (String(machine.type) === '50' && imageData) {
            pictureValue = imageData;
        }

        // Tentukan flag
        let fpFlag = "0";
        let picFlag = "0";

        // Untuk type 0: hanya set "1" jika benar-benar ada data fingerprint baru
        if (String(machine.type) === '0') {
            if (fdataValue && fdataValue.trim() !== "" && fdataValue !== "0") {
                fpFlag = "1";
            }
        }

        // Untuk type 50: hanya set "1" jika ada foto
        if (String(machine.type) === '50') {
            if (pictureValue) {
                picFlag = "1";
            }
        }

        // Simpan ke tbl_usersdata dengan logika update/insert yang aman
        if (String(machine.type) === '0' || String(machine.type) === '50') {
            await saveToUserDataModel(
                userId,
                machine.sn,
                machine.type,
                fpFlag,
                picFlag,
                fdataValue,       // hanya dikirim jika fpFlag = 1
                pictureValue      // hanya dikirim jika picFlag = 1
            );
        }

        // Payload untuk dikirim ke mesin
        let data = {
            sn: machine.sn,
            userid: userId,
            username: username,
            type: machine.type,
            admin: "0",
            cardnumber: processedCardNumber,
            fdata: fdataValue
        };

        try {
            if (String(machine.type) === '1') {
                // Soyal (type 1)
                let userDates = null;
                try {
                    const result = await fetchUserDatesAndCreateAccessData(userId, {
                        sn: machine.sn,
                        type: machine.type,
                        ip_address: machine.ip,
                        username: username,
                        cardnumber: cardNumber
                    });
                    userDates = result;
                } catch (err) {
                    console.error('Gagal ambil tanggal untuk Soyal:', err);
                    failedRequests++;
                    completedRequests++;
                    updateProgress(completedRequests, totalRequests);
                    continue;
                }

                const starttime = userDates.accessData.starttime;
                const endtime = userDates.accessData.endtime;
                const expireTime = endtime ? moment(endtime).format('HH:mm') : '00:00';

                await sendDataToSoyalApi(
                    userId,
                    username,
                    cardNumber,
                    processedCardNumber,
                    machine,
                    starttime,
                    endtime,
                    expireTime
                );
            } else {
                // Sztimmy (type 0 atau 50)
                const payload = await sendDataToSztimmyRegisterFace(data, userId, machine.id, machine.ip);
                if (payload) accessPayloads.push(payload);
            }
        } catch (err) {
            console.error(`[ERROR] Failed to send user ${userId} to ${machine.sn}:`, err);
            failedRequests++;
        }

        completedRequests++;
        updateProgress(completedRequests, totalRequests);
        await delay(100);
    }

    return accessPayloads;
}

        // === SOYAL API FUNCTION (FIXED) ===
   async function sendDataToSoyalApi(userId, username, originalCardNumber, convertedCardNumber, machine, starttime, endtime, expireTime, retries = 3) {
    const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');

    // FORMAT YANG DIPERLUKAN: m-d-Y → 01-01-2025
    const beginDate = starttime ? moment(starttime).format('MM-DD-YYYY') : '01-01-2025';  // MM = 01-12, DD = 01-31
    const expireDate = endtime ? moment(endtime).format('MM-DD-YYYY') : '12-31-2025';
    const expireTimeFormatted = expireTime ? moment(expireTime, 'HH:mm').format('HH:mm') : '00:00';

    const payload = {
        user_id: String(userId),
        card_number: convertedCardNumber,
        ip_address: String(machine.ip || '192.168.100.127'),
        node_id: String(machine.nodeid),
        timezone: '0',
        begin_date: beginDate,        // 01-01-2025
        expire_date: expireDate,      // 04-23-2026
        begin_time: '00:00',
        expire_time: expireTimeFormatted,
        pin: '1',
        lift1: '0',
        lift2: '0',
        lift3: '0',
        lift4: '0',
        user_name: String(username),
        type: String(machine.type)
    };

    console.log('[SOYAL PAYLOAD]', payload); // DEBUG

    for (let attempt = 1; attempt <= retries; attempt++) {
        try {
            const response = await $.ajax({
                url: soyalAddUserApiUrl,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload)
            });

            console.log('Soyal API Response:', response);
            completedRequests++;
            updateProgress(completedRequests, totalRequests);

            const logData = {
                log_date: currentTime,
                modul: JSON.stringify(payload),
                desc: `Success Insert User ${username.substring(0, 100)}`,
                status: 'Successfully'
            };
            saveToDeviceLog(logData);

            await $.ajax({
                url: '/save-to-usersxdevice',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ userId: userId, gateId: machine.id }),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            return;
        } catch (err) {
            console.error(`Attempt ${attempt} - Error sending to Soyal API:`, err);

            if (err.responseJSON) {
                console.error('Validation errors:', err.responseJSON);
            }

            if (attempt === retries) {
                failedRequests++;
                const logData = {
                    log_date: currentTime,
                    modul: JSON.stringify(payload),
                    desc: `Failed Insert User ${username.substring(0, 100)} - Validation Error`,
                    status: 'Failed'
                };
                saveToDeviceLog(logData);

                Swal.fire({
                    title: 'Error',
                    text: `Gagal kirim ke Soyal: ${err.responseJSON?.message || 'Format tanggal salah'}`,
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
    const payloads = [];

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

            // === KIRIM WEEKZONE JIKA TYPE 0 atau 50 ===
            if (['0', '50'].includes(String(data.type))) {
                try {
                    const weekzoneResponse = await $.ajax({
                        url: INSERT_WEEKZONE_URL,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            userId: userId,
                            deviceIds: [machineId]
                        }),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    console.log('[WEEKZONE] Sent successfully:', weekzoneResponse);

                    // Log sukses weekzone
                    const weekzoneLog = {
                        log_date: currentTime,
                        modul: JSON.stringify({ userId, deviceId: machineId, weekzone: accessData.weekzone }),
                        desc: `Weekzone synced: User ${data.username} → ${data.sn} (WZ: ${accessData.weekzone})`,
                        status: 'Successfully'
                    };
                    saveToDeviceLog(weekzoneLog);

                } catch (weekzoneErr) {
                    console.warn('[WEEKZONE] Failed to send:', weekzoneErr);
                    const weekzoneLog = {
                        log_date: currentTime,
                        modul: JSON.stringify({ userId, deviceId: machineId }),
                        desc: `Failed to sync weekzone: ${weekzoneErr.responseJSON?.message || weekzoneErr.message}`,
                        status: 'Failed'
                    };
                    saveToDeviceLog(weekzoneLog);
                }
            }

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
                    title: 'Warning',
                    text: `${failCount} data failed to send to user access.`,
                    icon: 'warning'
                });
            }
        }

       async function saveToUserDataModel(userId, sn, type, fpFlag, picFlag, fpData = "", pictureData = "") {
    const logDesc = `Register user ${userId} to machine ${sn} (type=${type})`;

    try {
        const payload = {
            fid: userId,
            type: String(type),
            sn: sn,
            fp: fpFlag === "1" ? fpData : "",           // hanya isi jika fpFlag = 1
            picture: picFlag === "1" ? pictureData : "", // hanya isi jika picFlag = 1
            desc: logDesc
        };

        const response = await $.ajax({
            url: '/save-to-user-data',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        console.log('[USERS_DATA] Saved/Updated:', response);
    } catch (xhr) {
        if (xhr.status === 500 && xhr.responseText.includes('Duplicate entry')) {
            console.warn('[USERS_DATA] Duplicate → Skip', { userId, sn, type });
        } else {
            console.error('Failed to save to tbl_usersdata:', xhr.responseText);
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

    // === BULK DELETE (tidak diubah) ===
// === BULK DELETE (bagian yang sudah diperbaiki) ===
$('#bulkDeleteButton').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'The selected data will be deleted from the selected machine!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete!',
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

        // Ambil user yang dipilih
        document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
            selectedUsers.push({
                userid: checkbox.getAttribute('data-userid'),
                username: checkbox.getAttribute('data-username') || 'Unknown',
                cardnumber: checkbox.getAttribute('data-cardnumber') || '0'
            });
        });

        // Ambil mesin yang dipilih
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

        const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');

        for (const user of selectedUsers) {
            for (const machine of selectedMachines) {
                let deleteData;

                // === SOYAL (type 1) ===
                if (String(machine.type) === '1') {
                    const formattedCardNumber = convertCardNumber(user.cardnumber || '0', machine.type);

                    deleteData = {
                        user_id: String(user.userid),
                        card_number: "00000:00000",           // tetap seperti yang kamu minta
                        card_numberConvert: formattedCardNumber, // yang benar dikirim ke mesin
                        ip_address: machine.ip || '192.168.100.127',
                        node_id: machine.nodeid || '3'
                    };

                    try {
                        const response = await fetch(soyalApiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            body: JSON.stringify(deleteData),
                            signal: AbortSignal.timeout(10000) // timeout 10 detik agar tidak lama
                        });

                        // Cek apakah respons benar-benar JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            const text = await response.text();
                            console.error('Soyal returned non-JSON:', text.substring(0, 200));
                            throw new Error('Soyal API returned HTML or invalid response');
                        }

                        const responseData = await response.json();

                        const logDesc = response.ok && responseData.message?.includes('Success')
                            ? `Success Delete User ${user.username} from Soyal ${machine.sn}`
                            : `Failed Delete User ${user.username} from Soyal ${machine.sn}`;

                        const logData = {
                            log_date: currentTime,
                            modul: JSON.stringify(deleteData),
                            desc: logDesc,
                            status: response.ok && responseData.message?.includes('Success') ? 'Successfully' : 'Failed'
                        };
                        await saveToDeviceLog(logData);

                        if (response.ok && responseData.message?.includes('Success')) {
                            // Hapus dari tabel usersxdevice
                            await $.ajax({
                                url: '/delete-from-usersxdevice',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    userId: user.userid,
                                    gateId: machine.id
                                }),
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                            });
                        } else {
                            failedRequests++;
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

                        if (error.name === 'TimeoutError') {
                            failedMachines.push({ sn: machine.sn, ip: machine.ip });
                        }
                    }
                }

                // === SZTIMMY (type 0 atau 50) - tetap seperti sebelumnya ===
                else {
                    deleteData = {
                        sn: machine.sn,
                        userid: user.userid
                    };

                    try {
                        const response = await fetch(sztimmyApiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            body: JSON.stringify(deleteData),
                            signal: AbortSignal.timeout(10000)
                        });

                        const responseData = await response.json();

                        const logData = {
                            log_date: currentTime,
                            modul: JSON.stringify(deleteData),
                            desc: response.ok && responseData.result !== 'fail'
                                ? `Success Delete User ${user.username} from ${machine.sn}`
                                : `Failed Delete User ${user.username} from ${machine.sn}`,
                            status: response.ok && responseData.result !== 'fail' ? 'Successfully' : 'Failed'
                        };
                        await saveToDeviceLog(logData);

                        if (response.ok && responseData.result !== 'fail') {
                            await $.ajax({
                                url: '/delete-from-usersxdevice',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    userId: user.userid,
                                    gateId: machine.id
                                })
                            });

                            await $.ajax({
                                url: '/delete-from-weekzoneuser',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    userId: user.userid,
                                    deviceId: machine.id
                                })
                            });
                        } else {
                            failedRequests++;
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
                await delay(500); // sedikit jeda agar tidak terlalu cepat
            }
        }

        $('#progressContainer').hide();
        toggleButtons(false, false);

        if (failedRequests > 0) {
            const failedList = failedMachines.map(m => `<li><strong>${m.sn}</strong> (${m.ip})</li>`).join('');
            Swal.fire({
                title: 'Warning',
                html: `
                    <p>${failedRequests} deletion(s) failed.</p>
                    <p><strong>Failed machines:</strong></p>
                    <ul style="text-align:left; margin:0 auto; display:inline-block;">
                        ${failedList || '<li><em>No machine data</em></li>'}
                    </ul>
                    <p class="mt-3">Please check network connection or machine status.</p>
                `,
                icon: 'warning',
                width: '600px'
            });
        } else {
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
            console.warn(`[PING] Failed to check ${ip}`, err);
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