$(document).ready(function() {
    const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

    async function sendApiRequest(data, retryCount = 5) {
        for (let attempt = 1; attempt <= retryCount; attempt++) {
            try {
                // Ambil data dari URL API
                const response = await fetch(sztimmyGetUserListApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify(data),
                    signal: AbortSignal.timeout(10000) // Timeout 10 detik
                });

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, message: ${text || 'No response body'}`);
                }

                const text = await response.text();
                if (!text || text.trim() === '') {
                    console.warn(`Empty response for userId ${data.indexstart}, attempt ${attempt}`);
                    return { data: null };
                }

                try {
                    const parsedData = JSON.parse(text);
                    // Validasi apakah data benar-benar masuk (sesuaikan dengan struktur respons API Anda)
                    if (parsedData.result === 'success' || Object.keys(parsedData).length > 0) {
                        console.log(`Success for userId ${data.indexstart}:`, parsedData);
                        return { data: parsedData };
                    } else {
                        throw new Error(`Invalid response data for userId ${data.indexstart}`);
                    }
                } catch (e) {
                    console.warn(`Non-JSON response for userId ${data.indexstart}:`, text);
                    return { data: text };
                }
            } catch (error) {
                console.error(`Attempt ${attempt} failed for userId ${data.indexstart}:`, error.message);
                if (attempt === retryCount) {
                    return { error: error.message };
                }
                await delay(1000); // Jeda 1 detik sebelum retry
            }
        }
    }

    $('#successModalFingerprint').on('show.bs.modal', function(event) {
        $('#indexstart').val('');
        $('#indexend').val('');
        $('#progressBar').css('width', '0%');
        $('#progressText').text('0%');
        $('#progressStatus').text('');
        $('#memberResult').html('');
    });

    $('#fingerprint').on('submit', async function(e) {
        e.preventDefault();
$('#progressContainer').show();
        const checkedUsers = $('.user-checkbox:checked');
        if (checkedUsers.length === 0) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Minimal 1 User Yang DiTarik Datanya',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        const sn = $('#sn').val();
        if (!sn) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Silakan pilih Serial Number (SN)',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        const userIds = checkedUsers.map(function() {
            return $(this).data('userid');
        }).get();

        console.log('userIds:', userIds);
        console.log('totalUsers:', userIds.length);

        const resultDiv = $('#memberResult');
        let html = '';
        let successCount = 0;
        let failureCount = 0;
        const failedUsers = [];

        const totalUsers = userIds.length;
        let processedUsers = 0;

        for (const userId of userIds) {
            const data = {
                sn: sn,
                indexstart: userId.toString(),
                indexend: userId.toString()
            };

            $('#progressStatus').text(`Memproses User ID: ${userId}...`);
            console.log(`Memproses User ID ${userId}`);

            const result = await sendApiRequest(data);

            if (result.error) {
                failureCount++;
                failedUsers.push({ userId, error: result.error });
                html += `
                    <div class="alert alert-danger" role="alert">
                        Gagal untuk userId ${userId}: ${result.error}
                    </div>
                `;
            } else {
                successCount++;
                html += `
                    <div class="alert alert-success" role="alert">
                        Sukses untuk userId ${userId}! Silahkan Check Kedalam Database
                    </div>
                `;
                if (result.data === null) {
                    html += `<p>Tidak ada data ditemukan untuk userId ${userId}</p>`;
                } else if (typeof result.data === 'string') {
                    html += `<p>Respons dari server untuk userId ${userId}: ${result.data}</p>`;
                } else if (result.data && Object.keys(result.data).length > 0) {
                    html += `<pre>${JSON.stringify(result.data, null, 2)}</pre>`;
                } else {
                    html += `<p>Tidak ada data ditemukan untuk userId ${userId}</p>`;
                }
            }

            resultDiv.html(html);

            processedUsers++;
            const progressPercentage = Math.round((processedUsers / totalUsers) * 100);
            requestAnimationFrame(() => {
                if ($('#progressBar').length && $('#progressText').length) {
                    $('#progressBar').css('width', `${progressPercentage}%`);
                    $('#progressText').text(`${progressPercentage}%`);
                    $('#successModalFingerprint').modal('handleUpdate'); // Perbarui modal
                    console.log(`Mengatur width progressBar ke ${progressPercentage}%`);
                    console.log('Current progressBar width:', $('#progressBar').css('width'));
                    console.log('Current progressText:', $('#progressText').text());
                } else {
                    console.error('Elemen #progressBar atau #progressText tidak ditemukan');
                }
            });
            console.log(`Progres: ${progressPercentage}%`);

            await delay(500); // Jeda 500ms antar request
        }

        Swal.fire({
            title: 'Selesai',
            text: `Proses sinkronisasi selesai. Berhasil: ${successCount}, Gagal: ${failureCount}, Total: ${userIds.length}`,
            icon: failureCount > 0 ? 'warning' : 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            $('#progressStatus').text('');
            $('#progressBar').css('width', '0%');
            $('#progressText').text('0%');
        });
    });
});