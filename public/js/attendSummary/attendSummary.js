// public/js/attendSummary/attendSummary.js
document.addEventListener('DOMContentLoaded', function () {
    const $ = jQuery;
    const routes = window.attendSummaryRoutes;

    // Setup CSRF untuk semua AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });

    // === SELECT ALL di Modal ===
    $('#select_all_employees').on('change', function () {
        const isChecked = this.checked;
        $('#employee_list .employee-checkbox').prop('checked', isChecked);
    });

    // === SELECT ALL di Tabel Utama (jika ada) ===
    $('#selectAll').on('change', function () {
        $('.user-checkbox').prop('checked', this.checked);
    });

    // === FILTER BY DEPARTMENT (AJAX + Fallback) ===
    $('#department_filter').on('change', function () {
        const departmentId = $(this).val();
        const selectedEmployeeIds = $('.employee-checkbox:checked')
            .map(function () { return this.value; })
            .get();

        // Tampilkan loading
        $('#employee_list').html('<div class="col-12 text-center py-4"><div class="spinner-border text-primary"></div></div>');

        $.ajax({
            url: routes.getEmployeesByDepartment,
            type: 'GET',
            data: {
                department_id: departmentId || '',
                employee_ids: selectedEmployeeIds
            },
            timeout: 15000,
            success: function (response) {
                renderEmployeeList(response.employees || [], response.selected_employee_ids || []);
            },
            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: xhr.responseJSON?.message || 'Tidak dapat memuat karyawan. Menggunakan filter sisi klien.',
                    timer: 3000
                });

                // Fallback: filter manual dari DOM asli
                if (!departmentId) {
                    $('.employee-item').show();
                } else {
                    $('.employee-item').hide();
                    $(`.employee-item[data-department-id="${departmentId}"]`).show();
                }
                $('#employee_list').html($('#employee_list').data('original-html') || 'Gagal memuat.');
            }
        });
    });

    // === Render Daftar Karyawan dari AJAX ===
    function renderEmployeeList(employees, selectedIds) {
        const container = $('#employee_list');
        container.empty();

        if (!employees || employees.length === 0) {
            container.html('<div class="col-12 text-center text-muted py-4">Tidak ada karyawan di departemen ini.</div>');
            $('#select_all_employees').prop('checked', false);
            return;
        }

        employees.forEach(employee => {
            const isChecked = selectedIds.includes(String(employee.ID)) ? 'checked' : '';
            const depId = employee.Depid || '';

            const html = `
                <div class="col-md-4 employee-item" data-department-id="${depId}">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input employee-checkbox"
                               name="selected_users[]" value="${employee.ID}"
                               id="employee_${employee.ID}" form="attendanceForm" ${isChecked}>
                        <label class="form-check-label" for="employee_${employee.ID}">
                            ${employee.NAME}
                        </label>
                    </div>
                </div>
            `;
            container.append(html);
        });

        // Update status "Select All"
        const total = $('.employee-checkbox').length;
        const checked = $('.employee-checkbox:checked').length;
        $('#select_all_employees').prop('checked', total > 0 && total === checked);
    }

    // Simpan HTML awal sebagai fallback (opsional)
    $('#employee_list').data('original-html', $('#employee_list').html());
});