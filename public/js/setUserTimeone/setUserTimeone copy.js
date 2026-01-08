// public/js/setUserTimeone/setUserTimeone.js

document.addEventListener('DOMContentLoaded', function () {
    const routes = window.setUserTimeoneRoutes;

    // DOM Elements
    const deviceCheckboxes      = document.querySelectorAll('.device-checkbox');
    const showAllUsersCheckbox  = document.getElementById('showAllUsers');
    const userTableContainer    = document.getElementById('userTableContainer');
    const userTableBody         = document.getElementById('userTableBody');
    const weekzonePanel         = document.getElementById('weekzonePanel');
    const weekzoneContent       = document.getElementById('weekzoneContent');
    const selectedCount         = document.getElementById('selectedCount');
    const assignWeekzoneBtn     = document.getElementById('assignWeekzoneBtn');
    const modalUserCount        = document.getElementById('modalUserCount');
    const modalUserCount2       = document.getElementById('modalUserCount2');
    const modalDeviceCount      = document.getElementById('modalDeviceCount');
    const modalUserIds          = document.getElementById('modalUserIds');
    const assignForm            = document.getElementById('assignWeekzoneForm');
    const submitBtn             = document.getElementById('submitBtn');
    const submitText            = document.getElementById('submitText');
    const weekzoneSelect        = document.getElementById('weekzoneSelect');

    // State
    let selectedUserIds = new Set();       // user yang dipilih
    let allUsers = [];                     // semua user dari API
    let filteredUsers = [];                // user setelah filter
    let currentPage = 1;
    let perPage = 10;                      // default entries per page

    const MAX_USER = 20;                   // batas maksimal user yang bisa di-assign sekaligus
    const MAX_WZ   = 3;                    // batas maksimal weekzone per user-device
    const MAX_COMBINATION = 150;           // batas aman kombinasi user × device × weekzone

    // Helper: Update counter device terpilih
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.device-checkbox:checked').length;
        selectedCount.textContent = checked;
        modalDeviceCount.textContent = checked;
    }

    // Helper: Update tombol Assign Weekzone
    function updateAssignButton() {
        const userCount = selectedUserIds.size;
        const deviceCount = getSelectedDeviceIds().length;

        if (userCount > 0 && deviceCount > 0) {
            assignWeekzoneBtn.style.display = 'inline-block';

            modalUserCount.textContent = userCount;
            modalUserCount2.textContent = userCount;
            document.getElementById('selectedUserCount').textContent = userCount;

            let badgeClass = 'light';
            if (userCount > 15) badgeClass = 'danger';
            else if (userCount > 10) badgeClass = 'warning';
            else if (userCount > 5) badgeClass = 'info';

            assignWeekzoneBtn.innerHTML = `
                <i class="fas fa-calendar-alt"></i> Assign Weekzone 
                <span class="badge badge-${badgeClass} ml-1">${userCount}</span>
            `;
        } else {
            assignWeekzoneBtn.style.display = 'none';
        }
    }

    // Ambil device ID yang dipilih
    function getSelectedDeviceIds() {
        return Array.from(document.querySelectorAll('.device-checkbox:checked'))
            .map(cb => cb.value);
    }

    // Ganti jumlah data per halaman
    document.getElementById('entriesPerPage')?.addEventListener('change', function () {
        perPage = parseInt(this.value);
        currentPage = 1;
        applySearchAndPagination();
    });

    // --- EVENT: Select All / Clear All Devices ---
    document.getElementById('selectAllDevices').onclick = () => {
        if (!showAllUsersCheckbox.checked) {
            deviceCheckboxes.forEach(cb => cb.checked = true);
            updateSelectedCount();
            loadUsersFromSelectedDevices();
        }
    };

    document.getElementById('deselectAllDevices').onclick = () => {
        deviceCheckboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
        selectedUserIds.clear();
        userTableContainer.style.display = 'none';
        weekzonePanel.style.display = 'none';
        updateAssignButton();
    };

    // --- EVENT: Checkbox Device ---
    deviceCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            updateSelectedCount();
            if (!showAllUsersCheckbox.checked) {
                const anyChecked = getSelectedDeviceIds().length > 0;
                if (anyChecked) {
                    loadUsersFromSelectedDevices();
                } else {
                    userTableContainer.style.display = 'none';
                    weekzonePanel.style.display = 'none';
                    selectedUserIds.clear();
                    updateAssignButton();
                }
            }
        });
    });

    // --- EVENT: Show All Users ---
    showAllUsersCheckbox.addEventListener('change', function () {
        selectedUserIds.clear();
        if (this.checked) {
            loadAllUsers();
        } else {
            const anyChecked = getSelectedDeviceIds().length > 0;
            if (anyChecked) loadUsersFromSelectedDevices();
            else {
                userTableContainer.style.display = 'none';
                weekzonePanel.style.display = 'none';
            }
        }
        updateAssignButton();
    });

    // --- Load Users dari Device yang dipilih ---
    function loadUsersFromSelectedDevices() {
        const deviceIds = getSelectedDeviceIds();
        if (!deviceIds.length) return;

        userTableBody.innerHTML = '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';

        fetch(routes.users, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ device_ids: deviceIds })
        })
        .then(r => r.json())
        .then(users => {
            renderUserTable(users);
            userTableContainer.style.display = 'block';
        })
        .catch(err => {
            userTableBody.innerHTML = '<tr><td colspan="7" class="text-danger text-center">Gagal memuat user.</td></tr>';
            console.error(err);
        });
    }

    // --- Load Semua User ---
    function loadAllUsers() {
        userTableBody.innerHTML = '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';

        fetch(routes.allusers)
            .then(r => r.json())
            .then(users => {
                renderUserTable(users);
                userTableContainer.style.display = 'block';
            })
            .catch(err => {
                userTableBody.innerHTML = '<tr><td colspan="7" class="text-danger text-center">Gagal memuat user.</td></tr>';
                console.error(err);
            });
    }

    // --- Render Tabel User ---
    function renderUserTable(users) {
        allUsers = users;
        currentPage = 1;
        applySearchAndPagination();
    }

    // --- Apply Search + Pagination ---
    function applySearchAndPagination() {
        const term = document.getElementById('userSearchInput').value.toLowerCase().trim();

        filteredUsers = allUsers.filter(u =>
            u.NAME.toLowerCase().includes(term) ||
            (u.Card && u.Card.toString().includes(term)) ||
            (u.department?.name && u.department.name.toLowerCase().includes(term))
        );

        // Sort by ID ascending
        filteredUsers.sort((a, b) => a.ID - b.ID);

        const totalPages = Math.ceil(filteredUsers.length / perPage) || 1;
        currentPage = Math.min(currentPage, totalPages);

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const pageUsers = filteredUsers.slice(start, end);

        const tbody = userTableBody;
        tbody.innerHTML = '';

        if (!pageUsers.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada user ditemukan.</td></tr>';
        } else {
            pageUsers.forEach(user => {
                const checked = selectedUserIds.has(user.ID.toString()) ? 'checked' : '';
                const row = `
                <tr>
                    <td class="text-center align-middle px-2">
                        <div class="form-check m-0 d-inline-block">
                            <input class="form-check-input position-static user-checkbox" 
                                   type="checkbox" value="${user.ID}" id="user_${user.ID}" ${checked}>
                            <label class="form-check-label m-0" for="user_${user.ID}"></label>
                        </div>
                    </td>
                    <td><label for="user_${user.ID}" class="m-0 font-weight-medium">${user.NAME}</label></td>
                    <td>${user.Card || '—'}</td>
                    <td>${user.department?.name || '—'}</td>
                    <td>${user.BEGIN_DATE || '—'}</td>
                    <td>${user.END_DATE || '—'}</td>
                    <td>
                        ${user.photo 
                            ? `<img src="${user.photo}" class="img-thumbnail" style="width:40px;height:40px;object-fit:cover;">` 
                            : '<span class="text-muted small">No Photo</span>'
                        }
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
        }

        renderPagination(totalPages);
        document.getElementById('totalUsers').textContent = filteredUsers.length;
        document.getElementById('pageInfo').textContent = 
            `${start + 1} - ${Math.min(end, filteredUsers.length)}`;

        // Re-attach checkbox listener
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.onchange = handleUserCheckboxChange;
        });

        updateAssignButton();
    }

    // --- Pagination ---
    function renderPagination(totalPages) {
        const ul = document.getElementById('userPagination');
        ul.innerHTML = '';

        if (totalPages <= 1) return;

        ul.innerHTML += `<li class="page-item ${currentPage===1?'disabled':''}"><a class="page-link" href="#" data-page="${currentPage-1}">Previous</a></li>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= 1) {
                ul.innerHTML += `<li class="page-item ${i===currentPage?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                ul.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        ul.innerHTML += `<li class="page-item ${currentPage===totalPages?'disabled':''}"><a class="page-link" href="#" data-page="${currentPage+1}">Next</a></li>`;

        ul.querySelectorAll('a[data-page]').forEach(a => {
            a.onclick = function (e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page > 0 && page <= totalPages) {
                    currentPage = page;
                    applySearchAndPagination();
                }
            };
        });
    }

    // --- Search Input ---
    document.getElementById('userSearchInput').oninput = () => {
        currentPage = 1;
        applySearchAndPagination();
    };

    // --- Select All Users di halaman ini ---
    document.getElementById('selectAllUsers').onchange = function () {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) selectedUserIds.add(cb.value);
            else selectedUserIds.delete(cb.value);
        });
        loadWeekzonesForSelectedUsers();
        updateAssignButton();
    };

    // --- Checkbox User ---
    function handleUserCheckboxChange() {
        const userId = this.value;
        if (this.checked) selectedUserIds.add(userId);
        else selectedUserIds.delete(userId);
        loadWeekzonesForSelectedUsers();
        updateAssignButton();
    }

    // --- Preview Weekzone untuk user yang dipilih ---
    function loadWeekzonesForSelectedUsers() {
        if (!selectedUserIds.size) {
            weekzonePanel.style.display = 'none';
            return;
        }

        weekzonePanel.style.display = 'block';
        weekzoneContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success"></div></div>';

        const promises = Array.from(selectedUserIds).map(id =>
            fetch(`${routes.weekzones}/${id}`).then(r => r.json())
        );

        Promise.all(promises)
            .then(results => {
                let html = `<h6 class="text-primary mb-3">${selectedUserIds.size} Users</h6>`;
                results.forEach(data => {
                    const u = data.user;
                    html += `<div class="mb-4 p-3 border rounded bg-light">
                        <strong>${u.NAME}</strong> <small class="text-muted">(Card: ${u.Card||'N/A'})</small><br>
                        <small class="text-info">Device: ${data.device}</small>`;

                    const wzList = data.via_user || [];
                    if (wzList.length) {
                        html += `<div class="mt-2"><small class="font-weight-bold text-success">Weekzone Aktif:</small></div>`;
                        wzList.forEach(wz => {
                            html += `<span class="badge badge-success mr-1">WZ ${wz.wzid}</span>`;
                        });
                    } else {
                        html += `<p class="text-warning mt-2">Belum ada weekzone.</p>`;
                    }
                    html += `</div>`;
                });
                weekzoneContent.innerHTML = html;
            })
            .catch(() => {
                weekzoneContent.innerHTML = '<p class="text-danger">Gagal memuat weekzone.</p>';
            });
    }

    // --- Buka Modal Assign Weekzone ---
    assignWeekzoneBtn.onclick = function () {
        const userCount = selectedUserIds.size;
        const deviceCount = getSelectedDeviceIds().length;
        const wzCount = weekzoneSelect.selectedOptions.length;

        if (userCount > MAX_USER) {
            Swal.fire({
                icon: 'error',
                title: 'Terlalu banyak user',
                text: `Maksimal ${MAX_USER} user yang dapat di-assign sekaligus.`,
                confirmButtonText: 'OK'
            });
            return;
        }

        const totalCombination = userCount * deviceCount * wzCount;
        if (totalCombination > MAX_COMBINATION) {
            Swal.fire({
                icon: 'warning',
                title: 'Kombinasi terlalu besar',
                text: `Total ${totalCombination} kombinasi (user × device × weekzone). Proses mungkin lambat atau gagal. Lanjutkan?`,
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan'
            }).then((result) => {
                if (result.isConfirmed) {
                    openModal();
                }
            });
        } else {
            openModal();
        }
    };

    function openModal() {
        modalUserIds.value = Array.from(selectedUserIds).join(',');

        // Tambahkan hidden input device_ids[]
        assignForm.querySelectorAll('input[name="device_ids[]"]').forEach(el => el.remove());
        getSelectedDeviceIds().forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'device_ids[]';
            input.value = id;
            assignForm.appendChild(input);
        });

        weekzoneSelect.selectedIndex = -1;
        $('#assignWeekzoneModal').modal('show');
    }

    // --- Submit Form ---
// --- Submit Form (AJAX) ---
assignForm.onsubmit = async function (e) {
    e.preventDefault();

    const selectedOptions = weekzoneSelect.selectedOptions;
    const count = selectedOptions.length;

    if (count === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Weekzone',
            text: 'Pilih minimal satu weekzone!'
        });
        return false;
    }

    if (count > MAX_WZ) {
        Swal.fire({
            icon: 'error',
            title: 'Melebihi Kuota',
            text: `Maksimal ${MAX_WZ} weekzone per user-device. Anda memilih ${count}.`
        });
        return false;
    }

    submitBtn.disabled = true;
    submitText.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

    const formData = new FormData(assignForm);

    try {
        const response = await fetch(assignForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json' // penting!
            }
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message || 'Weekzone berhasil di-assign ke mesin.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                $('#assignWeekzoneModal').modal('hide');
                location.reload(); // refresh halaman agar preview update
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: result.message || 'Terjadi kesalahan saat menyimpan.'
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal terhubung ke server. Silakan coba lagi.'
        });
    } finally {
        submitBtn.disabled = false;
        submitText.innerHTML = 'Save';
    }
};

    // Inisialisasi
    updateSelectedCount();
    updateAssignButton();
});