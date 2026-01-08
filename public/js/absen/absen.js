document.addEventListener('DOMContentLoaded', function () {
    let isReportAdded = false;

    const patternSelect = document.getElementById('PatternID');
    const monthSelect = document.getElementById('month');
    const addReportButton = document.getElementById('addReportButton');
    const reportPatternID = document.getElementById('reportPatternID');
    const reportButton = document.getElementById('reportButton');
    const recapButton = document.getElementById('recapButton');

    // Select All Checkbox
    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = this.checked);
        updateReportUserIds();
    });

    document.querySelectorAll('.user-checkbox').forEach(cb => {
        cb.addEventListener('change', updateReportUserIds);
    });

    function updateReportUserIds() {
        const reportForm = document.getElementById('addReportForm');
        const checkedUserIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
            .map(cb => cb.value);

        // Bersihkan input hidden lama
        reportForm.querySelectorAll('.user-checkbox-hidden').forEach(el => el.remove());

        // Tambah user_ids[]
        checkedUserIds.forEach(userId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = userId;
            input.className = 'user-checkbox-hidden';
            reportForm.appendChild(input);
        });

        // Tambah month
        if (monthSelect.value) {
            const monthInput = document.createElement('input');
            monthInput.type = 'hidden';
            monthInput.name = 'month';
            monthInput.value = monthSelect.value;
            monthInput.className = 'user-checkbox-hidden';
            reportForm.appendChild(monthInput);
        }

        // Update tombol
        const canSubmit = patternSelect.value && checkedUserIds.length > 0 && monthSelect.value;
        addReportButton.disabled = !canSubmit;
        recapButton.style.display = canSubmit ? 'inline-block' : 'none';
    }

    // Pattern change → fetch detail
    patternSelect.addEventListener('change', function () {
        const patternId = this.value;
        const card = document.getElementById('shiftPatternCard');

        reportPatternID.value = patternId || '';
        updateReportUserIds();

        if (!patternId) {
            card.style.display = 'none';
            return;
        }

        // Gunakan URL dari window.absenConfig
        fetch(`${window.absenConfig.shiftPatternUrl}/${patternId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) throw new Error(data.error);

            document.getElementById('patternName').textContent = data.PatternName;
            document.getElementById('patternType').textContent = data.PatternType || 'N/A';

            const formatShift = (pola) => {
                if (!pola) return 'N/A';
                const type = pola.Tipe == 1 ? 'Working Days' : (pola.Tipe == 2 ? 'Off Days' : 'Unknown');
                return `${pola.ShiftName}, In: ${pola.Begin_Time}, Out: ${pola.Out_Time}, Type: ${type}`;
            };

            for (let i = 1; i <= 7; i++) {
                document.getElementById(`pola${i}`).textContent = formatShift(data[`pola${i}`]);
            }

            card.style.display = 'block';
        })
        .catch(err => {
            console.error(err);
            card.style.display = 'none';
        });
    });

    monthSelect.addEventListener('change', updateReportUserIds);

    // Add Report Form Submit
    document.getElementById('addReportForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (addReportButton.disabled) return;

        addReportButton.disabled = true;
        addReportButton.textContent = 'Processing...';

        fetch(window.absenConfig.tambahLaporanUrl, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.absenConfig.csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                isReportAdded = true;
                Swal.fire('Success', data.message, 'success');
            } else {
                Swal.fire('Error', data.error || 'Failed to create records', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Terjadi kesalahan', 'error');
        })
        .finally(() => {
            addReportButton.disabled = false;
            addReportButton.textContent = 'Add Report';
        });
    });

    // Report Button
    reportButton.addEventListener('click', function () {
        proceedIfReady(() => {
            const form = createHiddenForm(window.absenConfig.exportUrl, 'POST');
            addCheckedUsersAndParams(form);
            form.submit();
        });
    });

    // Recap Button
    recapButton.addEventListener('click', function () {
        const form = createHiddenForm(window.absenConfig.recapUrl, 'GET');
        addCheckedUsersAndParams(form);
        form.submit();
    });

    function addCheckedUsersAndParams(form) {
        const checked = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        checked.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        ['month', 'PatternID'].forEach(name => {
            const val = name === 'month' ? monthSelect.value : patternSelect.value;
            if (val) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = val;
                form.appendChild(input);
            }
        });

       if (form.method.toLowerCase() === 'post') {
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = window.absenConfig.csrfToken;
            form.appendChild(csrf);
        }
    }

    function createHiddenForm(action, method) {
        const form = document.createElement('form');
        form.action = action;
        form.method = method;
        form.style.display = 'none';
        document.body.appendChild(form);
        return form;
    }

    function proceedIfReady(callback) {
        const checked = document.querySelectorAll('.user-checkbox:checked').length;
        const month = monthSelect.value;
        const pattern = patternSelect.value;

        if (!checked || !month || !pattern) {
            Swal.fire('Warning', 'Pilih user, bulan, dan pola shift terlebih dahulu', 'warning');
            return;
        }

        if (!isReportAdded) {
            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi',
                text: 'Pastikan Add Report sudah dilakukan',
                showCancelButton: true,
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal'
            }).then(res => res.isConfirmed && callback());
        } else {
            callback();
        }
    }
});