document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const updateRemarkUrl = window.attendantSheetConfig?.updateRemarkUrl || '/attendant-sheet/update-remark';
    const getEmployeesUrl = window.attendantSheetConfig?.getEmployeesUrl || '/attendant-sheet/get-employees-by-department';

    // === 1. Filter by Department ===
    document.getElementById('department_filter')?.addEventListener('change', function () {
        const departmentId = this.value;
        const selectedEmployeeIds = Array.from(document.querySelectorAll('input.employee-checkbox:checked'))
            .map(cb => cb.value);

        fetch(getEmployeesUrl + '?' + new URLSearchParams({
            department_id: departmentId,
            employee_ids: selectedEmployeeIds
        }), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(response => {
            const container = document.getElementById('employee_list');
            container.innerHTML = '';

            response.employees.forEach(employee => {
                const isChecked = response.selected_employee_ids.includes(employee.ID.toString()) ? 'checked' : '';
                const div = document.createElement('div');
                div.className = 'col-md-4 employee-item';
                div.dataset.departmentId = employee.Depid || '';
                div.innerHTML = `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input employee-checkbox"
                               name="employee_ids[]" value="${employee.ID}"
                               id="employee_${employee.ID}" ${isChecked} form="attendanceForm">
                        <label class="form-check-label" for="employee_${employee.ID}">${employee.NAME}</label>
                    </div>
                `;
                container.appendChild(div);
            });

            updateSelectAllCheckbox();
        })
        .catch(() => {
            Swal.fire('Error', 'Gagal memuat data karyawan', 'error');
        });
    });

    // === 2. Select All Employees ===
    document.getElementById('select_all_employees')?.addEventListener('change', function () {
        document.querySelectorAll('.employee-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    function updateSelectAllCheckbox() {
        const allCheckboxes = document.querySelectorAll('.employee-checkbox');
        const checkedCount = document.querySelectorAll('.employee-checkbox:checked').length;
        const selectAll = document.getElementById('select_all_employees');
        if (selectAll) {
            selectAll.checked = allCheckboxes.length > 0 && checkedCount === allCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
        }
    }

    // === 3. Remark Select & Custom Input ===
    document.addEventListener('change', function (e) {
        if (e.target.matches('.remark-select') && !e.target.disabled) {
            const select = e.target;
            const id = select.dataset.id;
            const value = select.value;
            const input = document.querySelector(`input.remark-text[data-id="${id}"]`);

            if (value === 'Custom') {
                input.classList.remove('d-none');
                input.focus();
            } else {
                input.classList.add('d-none');
                submitRemarkForm(select.closest('form'));
            }
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.matches('.remark-text') && !e.target.disabled) {
            clearTimeout(window.remarkTimeout);
            window.remarkTimeout = setTimeout(() => {
                submitRemarkForm(e.target.closest('form'));
            }, 800);
        }
    });

    // === 4. Submit Remark via AJAX ===
    function submitRemarkForm(form) {
        if (!form) return;
        const select = form.querySelector('.remark-select');
        if (!select || select.disabled) return;

        const id = form.dataset.id;
        const remarkValue = select.value === 'Custom' 
            ? form.querySelector('.remark-text').value.trim() 
            : select.value;

        if (select.value === 'Custom' && !remarkValue) {
            Swal.fire('Peringatan', 'Custom remark tidak boleh kosong', 'warning');
            return;
        }

        fetch(updateRemarkUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _method: 'PATCH',
                id: id,
                remark_data: remarkValue
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Remark diperbarui',
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => {
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    } else {
                        location.reload();
                    }
                });
            } else {
                throw new Error(res.message || 'Gagal update');
            }
        })
        .catch(err => {
            Swal.fire('Error', err.message || 'Terjadi kesalahan saat update', 'error');
        });
    }

    // Inisialisasi status Select All saat load
    updateSelectAllCheckbox();
});