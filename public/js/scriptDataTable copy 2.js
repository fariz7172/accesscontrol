// Fungsi untuk mengonversi nomor kartu (khusus Type 1)
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

    let word1 = byte6;
    let word2 = (byte7 << 8) | byte8;

    let formatted = `${word1.toString().padStart(5, '0')}:${word2}`;
    console.log(`Converted card number ${cardNo} to ${formatted} for type ${machineType}`);
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

        const logDataTemplate = {
            user_id: String(userId),
            sn: data.sn,
            username: String(data.username),
            card_number: String(data.cardnumber || '0'),
            ip_address: data.ip_address || '192.168.100.128'
        };

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
            $('#bulkDeleteButton').html('<i class="fa fa-trash"></i> Delete To Machine');
        }
    }

    // Check/Uncheck semua user
    $('#checkAll').on('change', function () {
        var isChecked = $(this).is(':checked');
        $('table tbody input.user-checkbox').prop('checked', isChecked);
    });

    // === INSERT DATA ===
    $('#insertDataButton').on('click', function () {
        toggleButtons(true, true);
        let userDataArray = [];
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
                const hasType50 = selectedMachines.some(m => m.type == '50');
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
                Swal.fire({
                    title: 'Peringatan',
                    text: `Selesai dengan ${failedRequests} kegagalan. Periksa koneksi atau data.`,
                    icon: 'warning'
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

            // Ambil FP data untuk Type 0
            let userFpData = null;
            try {
                const fpResponse = await $.ajax({
                    url: `/get-fp-data/${userId}`,
                    method: 'GET'
                });
                userFpData = fpResponse.fp || null;
                console.log(`[FP DATA] User ${userId} → Fp: ${userFpData}`);
            } catch (err) {
                console.warn(`[FP DATA] Gagal ambil Fp untuk user ${userId}:`, err);
            }

            for (const machine of machines) {
                // 1. CARDNUMBER: Konversi hanya untuk Type 1
                let processedCardNumber = cardNumber;
                if (machine.type === '1') {
                    processedCardNumber = convertCardNumber(cardNumber, machine.type);
                    console.log(`[CONVERT] Card ${cardNumber} → ${processedCardNumber}`);
                } else {
                    console.log(`[CARD] : ${cardNumber}`);
                }

                // 2. FDATA: Type 0 = FP, Type 50 = Foto
                let fdataValue = "";
                if (machine.type === '0') {
                    fdataValue = (userFpData && userFpData !== '0' && userFpData.trim()) ? userFpData : "";
                    console.log(`[FDATA] type=0 → ${fdataValue ? 'FP loaded' : 'FP kosong'}`);
                } else if (machine.type === '50' && imageData) {
                    fdataValue = imageData;
                    console.log(`[FDATA] type=50 → Foto loaded`);
                }

                // 3. PAYLOAD
                let data = {
                    sn: machine.sn,
                    userid: userId,
                    username: username,
                    type: machine.type,
                    admin: "0",
                    cardnumber: processedCardNumber,
                    fdata: fdataValue
                };

                // 4. KIRIM KE API
                try {
                    if (machine.type === '1') {
                        await sendDataToSoyalApi(userId, username, processedCardNumber, machine);
                    } else {
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

        // Kirim registerface + access data
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

        // Batch useraccess
        async function sendBatchUserAccess(payloads) {
            const currentTime = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss');
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
                    saveToDeviceLog({
                        log_date: currentTime,
                        modul: JSON.stringify(chunk),
                        desc: `Success Batch User Access (${chunk.length} records)`,
                        status: 'Successfully'
                    });
                } catch (err) {
                    failCount += chunk.length;
                    saveToDeviceLog({
                        log_date: currentTime,
                        modul: JSON.stringify(chunk),
                        desc: `Failed Batch User Access: ${err.responseJSON?.message || err.statusText || 'Unknown'}`,
                        status: 'Failed'
                    });
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

        // Kirim ke Soyal (Type 1)
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
                    updateProgress(completedRequests, totalRequests);

                    const logData = {
                        log_date: currentTime,
                        modul: JSON.stringify({
                            user_id: String(userId),
                            card_number: String(cardNumber || '0'),
                            ip_address: machine.ip || '192.168.100.127',
                            node_id: String(machine.nodeid),
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
                        data: JSON.stringify({ userId, gateId: machine.id }),
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
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
                                card_number: String(cardNumber || '0'),
                                ip_address: machine.ip || '192.168.100.127',
                                node_id: String(machine.nodeid),
                                user_name: String(username),
                                type: String(machine.type)
                            }),
                            desc: `Failed Insert User ${username.substring(0, 100)}`,
                            status: 'Failed'
                        };
                        saveToDeviceLog(logData);
                    }
                    await delay(1000);
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

        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        processUsers();
    });

    // === BULK DELETE (Hanya Type 0, 1, 50) ===
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

        const useridFromModal = $('#successModal').data('userid');
        if (useridFromModal) {
            const checkbox = $(`input[data-userid="${useridFromModal}"]`);
            selectedUsers.push({
                userid: useridFromModal,
                username: checkbox.data('username') || 'Unknown',
                cardnumber: checkbox.data('cardnumber') || '0'
            });
        } else {
            $('.user-checkbox:checked').each(function () {
                selectedUsers.push({
                    userid: $(this).data('userid'),
                    username: $(this).data('username') || 'Unknown',
                    cardnumber: $(this).data('cardnumber') || '0'
                });
            });
        }

        $('.machine-checkbox:checked').each(function () {
            selectedMachines.push({
                sn: $(this).val(),
                type: $(this).data('type'),
                ip: $(this).data('ip'),
                nodeid: $(this).data('nodeid'),
                id: $(this).attr('id').replace('mesin', '')
            });
        });

        if (selectedUsers.length === 0 || selectedMachines.length === 0) {
            Swal.fire({ title: 'Error', text: 'Pilih minimal satu user dan satu mesin!', icon: 'error' });
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

                if (machine.type === '1') {
                    const formattedCardNumber = convertCardNumber(user.cardnumber || '0', machine.type);
                    deleteData = {
                        user_id: String(user.userid),
                        card_number: "00000:00000",
                        card_numberConvert: formattedCardNumber,
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
                            body: JSON.stringify(deleteData)
                        });

                        const responseData = await response.json();
                        const logData = {
                            log_date: currentTime,
                            modul: JSON.stringify(deleteData),
                            desc: response.ok && responseData.result !== 'fail'
                                ? `Success Delete User ${user.username}`
                                : `Failed Delete User ${user.username}`,
                            status: response.ok && responseData.result !== 'fail' ? 'Successfully' : 'Failed'
                        };
                        saveToDeviceLog(logData);

                        if (response.ok && responseData.result !== 'fail') {
                            await $.ajax({
                                url: '/delete-from-usersxdevice',
                                method: 'POST',
                                contentType: 'application/json',
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                data: JSON.stringify({ userId: user.userid, gateId: machine.id })
                            });
                        } else {
                            failedRequests++;
                        }
                    } catch (error) {
                        failedRequests++;
                        saveToDeviceLog({
                            log_date: currentTime,
                            modul: JSON.stringify(deleteData),
                            desc: `Failed Delete User ${user.username}: ${error.message}`,
                            status: 'Failed'
                        });
                    }
                } else {
                    // Type 0 & 50
                    deleteData = { sn: machine.sn, userid: user.userid };

                    try {
                        const response = await fetch(sztimmyApiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            body: JSON.stringify(deleteData)
                        });

                        const responseData = await response.json();
                        const logData = {
                            log_date: currentTime,
                            modul: JSON.stringify(deleteData),
                            desc: response.ok && responseData.result !== 'fail'
                                ? `Success Delete User ${user.username}`
                                : `Failed Delete User ${user.username}`,
                            status: response.ok && responseData.result !== 'fail' ? 'Successfully' : 'Failed'
                        };
                        saveToDeviceLog(logData);

                        if (response.ok && responseData.result !== 'fail') {
                            if (machine.type === '50') {
                                await fetch('/delete-user-picture', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                                    },
                                    body: JSON.stringify({ userid: user.userid })
                                });
                            }

                            await $.ajax({
                                url: '/delete-from-usersxdevice',
                                method: 'POST',
                                contentType: 'application/json',
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                data: JSON.stringify({ userId: user.userid, gateId: machine.id })
                            });
                        } else {
                            failedRequests++;
                        }
                    } catch (error) {
                        failedRequests++;
                        saveToDeviceLog({
                            log_date: currentTime,
                            modul: JSON.stringify(deleteData),
                            desc: `Failed Delete User ${user.username}: ${error.message}`,
                            status: 'Failed'
                        });
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
                text: `Selesai dengan ${failedRequests} kegagalan.`,
                icon: 'warning'
            });
        } else {
            Swal.fire({
                title: 'Success',
                text: 'Semua data berhasil dihapus dari mesin!',
                icon: 'success'
            });
        }
    }

    function updateProgress(completed, total) {
        let percent = (completed / total) * 100;
        $('#progressBar').css('width', percent + '%');
        $('#progressText').text(Math.round(percent) + '%');
    }

    function delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function saveToDeviceLog(logData) {
        $.ajax({
            url: '/save-to-devicelog',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: JSON.stringify(logData)
        });
    }

    window.confirmDelete = function (event, userid) {
        event.preventDefault();
        $('#successModal').data('userid', userid);
        $('#successModal').modal('show');
    };
});