@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <style>
    .lift-popover .popover-body {
        padding: 0 !important;
    }

    .lift-popover .popover-header {
        background: #0d6efd;
        color: white;
        font-weight: bold;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    .lift-popover .popover {
        max-width: 360px !important;
    }

    .lift-popover .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.65em;
    }
    </style>


    <style>
    .modal-fullscreen {
        width: 100vw;
        max-width: 100vw;
        height: 100vh;
        margin: 0;
    }

    .lift-floor-item {
        transition: all 0.2s;
    }

    .lift-floor-label {
        user-select: none;
        transition: all 0.2s;
    }

    .lift-floor-checkbox:disabled+.lift-floor-label {
        color: #ccc !important;
    }

    .modal-fullscreen .modal-content {
        height: 100vh;
        border-radius: 0;
    }
    </style>


    <h1 class="h3 mb-2 text-gray-800">Device Management</h1>

    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add Device</button>
    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addUser">Add User To Lift</button>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Name</th>
                            <th>SN</th>
                            <th>IP</th>
                            <th>Node ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                        <tr>
                            <td>{{ $device->number }}</td>
                            <td>{{ $device->name }}</td>
                            <td>{{ $device->sn }}</td>
                            <td>{{ $device->ip }}</td>
                            <td>{{ $device->nodeid }}</td>
                            <td>
                                @if($device->stat == 0)
                                IN
                                @elseif($device->stat == 1)
                                OUT
                                @else
                                {{ $device->stat }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('device.edit', ['encryptedId' => encryptId($device->id)]) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form
                                    action="{{ $device->type == 1 ? route('devices.openGate') : route('devices.open', $device->id) }}"
                                    method="POST" class="open-device-form" style="display: inline;"
                                    data-device-id="{{ $device->id }}" data-device-type="{{ $device->type }}"
                                    data-ip="{{ $device->ip }}" data-nodeid="{{ $device->nodeid }}">
                                    @csrf
                                    @if($device->type == 1)
                                    <input type="hidden" name="ip_address" value="{{ $device->ip }}">
                                    <input type="hidden" name="node_id" value="{{ $device->nodeid }}">
                                    @endif
                                    <button type="submit" class="btn btn-success btn-sm">Open</button>
                                </form>
                                <form action="{{ route('cleanDevice.clean', $device->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm">Clean Device</button>
                                </form>
                                <form action="{{ route('device.destroy', $device->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="confirm" value="yes">
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this device?')">Delete</button>
                                </form>
                                <form class="check-connection-form" style="display: inline;"
                                    data-device-sn="{{ $device->sn }}" data-device-type="{{ $device->type }}"
                                    data-ip="{{ $device->ip }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Check Connection</button>
                                </form>

                                {{--
                               @if($device->type == 1)
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#SetUserLiftModal"
                                       >Set User Lift</button>
                                @endif --}}

                                @if($device->type != 1)
                                <button class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#setTimeModal"
                                    data-device-sn="{{ $device->sn }}">Set Time</button>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#rebootModal"
                                    data-device-sn="{{ $device->sn }}">Reboot</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $devices->links() }}
            </div>
        </div>
    </div>


</div>

</div>

@include('device.modal.AddDataDevice')
@include('device.modal.AddUser')
@include('device.modal.SetTime')
@include('device.modal.Reboot')
@include('device.modal.SetUserLift')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Pastikan Laravel CSRF & route tersedia
window.Laravel = window.Laravel || {};
window.Laravel.csrfToken = '{{ csrf_token() }}';
window.Laravel.routes = window.Laravel.routes || {};
window.Laravel.routes['device.users.lift-search'] = '{{ route('
device.users.lift - search ') }}';
window.Laravel.routes.saveUserToDevice = '{{ route('
device.saveUserToDevice ') }}';
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.lift-access-detail').forEach(el => {
        // Hapus event listener lama kalau ada (biar tidak double)
        el.removeEventListener('click', el._liftPopoverHandler);

        el._liftPopoverHandler = function(e) {
            e.preventDefault();

            const name = this.dataset.name;
            const access = JSON.parse(this.dataset.access || '[]');

            let content =
                '<div class="p-3" style="min-width:260px;max-width:360px;font-size:0.9rem;">';
            if (access.length === 0) {
                content += '<em class="text-muted small">Belum ada akses lift</em>';
            } else {
                access.forEach(acc => {
                    content += '<div class="mb-3">';
                    content += '<div class="fw-bold text-primary small">' + acc.device +
                        '</div>';
                    content += '<div class="mt-2">';
                    acc.floors.forEach(f => {
                        content +=
                            '<span class="badge bg-info text-dark me-1 mb-1">' + f +
                            '</span>';
                    });
                    content += '</div></div>';
                });
            }
            content += '</div>';

            // HAPUS POPOVER LAMA DENGAN CARA AMAN (tanpa getInstance)
            if (this._popover) {
                this._popover.dispose();
                this._popover = null;
            }

            // Buat popover baru
            this._popover = new bootstrap.Popover(this, {
                title: '<strong>Akses Lift: ' + name + '</strong>',
                content: content,
                html: true,
                placement: 'left',
                trigger: 'manual',
                customClass: 'lift-popover',
                container: 'body',
                sanitize: false // tetap wajib kalau pakai HTML
            });

            this._popover.show();

            // Auto hide setelah 8 detik
            setTimeout(() => {
                if (this._popover) {
                    this._popover.hide();
                }
            }, 8000);
        };

        // Pasang event
        el.addEventListener('click', el._liftPopoverHandler);
    });
});
</script>


{{-- ======================== SET UNTUK SIMPAN USER LIFT ==================== --}}
<script src="{{ asset('js/device/addUserLift.js') }}"></script>
{{-- ======================== SET UNTUK SIMPAN DEVICE ======================= --}}
<script src="{{ asset('js/device/addUserDevice.js') }}"></script>
{{-- ======== SET UNTUK SIMPAN  SET TIMER, REBOOT, CHECK CONNECTION, ======== --}}
<script src="{{ asset('js/device/checkConnection.js') }}"></script>


{{-- //////////////////////// CONVERT DATA DESIMAL TO 09012:802102 /////////////////////// --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl =>
        new bootstrap.Popover(popoverTriggerEl, {
            trigger: 'hover focus'
        })
    );
});
</script>


<script>
$(document).ready(function() {

    // === KONVERSI KARTU SOYAL - 100% BENAR (6116459 → 00093:21611) ===
    function convertToSoyalCard(cardInput) {
        let numStr = cardInput.toString().replace(/\D/g, ''); // Hanya angka
        if (!numStr || numStr.length > 10) return null;

        // Pad jadi 10 digit
        numStr = numStr.padStart(10, '0').substring(0, 10);
        let num = parseInt(numStr.slice(-8), 10); // Ambil 8 digit terakhir

        let byte6 = (num >> 16) & 0xFF;
        let byte7 = (num >> 8) & 0xFF;
        let byte8 = num & 0xFF;

        let word1 = byte6; // byte5 = 0x00 → word1 = byte6
        let word2 = (byte7 << 8) | byte8;

        return word1.toString().padStart(5, '0') + ':' + word2.toString().padStart(5, '0');
    }

    // === Target SATU-SATUNYA input card_number ===
    const $cardField = $('#manual input[name="card_number"]');

    // Konversi otomatis saat blur
    $cardField.on('blur', function() {
        let val = $(this).val().trim();
        if (!val) return;

        let converted = convertToSoyalCard(val);
        if (converted && val !== converted) {
            $(this).val(converted);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Kartu dikonversi',
                text: `${val} → ${converted}`,
                timer: 3000,
                timerProgressBar: true
            });
        }
    });

    // Saat pilih user dari DB
    // === KETIKA PILIH USER DARI DATABASE (Tab "Pilih dari Database") ===
    // === KETIKA PILIH USER DARI DATABASE ===
    $('#userDbTable tbody').on('click', 'tr.user-row', function() {
        const row = $(this);
        row.addClass('table-primary').siblings().removeClass('table-primary');

        const userId = row.find('td').eq(1).text().trim();
        const userName = row.find('td').eq(2).text().trim();
        const rawCard = row.find('td').eq(3).text().trim(); // ini angka: 6116459
        const endDate = row.find('td').eq(4).text().trim();

        // === KONVERSI ANGKA DI DB → FORMAT SOYAL (00093:21611) ===
        let soyalCard = '';
        if (/^\d+$/.test(rawCard)) {
            // Jika angka → konversi ke XXXXX:YYYYY
            let num = parseInt(rawCard);
            let high = (num >> 16) & 0xFFFF;
            let low = num & 0xFFFF;
            soyalCard = high.toString().padStart(5, '0') + ':' + low.toString().padStart(5, '0');
        } else {
            soyalCard = rawCard; // sudah format soyal
        }

        // Isi form
        $('input[name="user_id"]').val(userId);
        $('#user_id_display').val(userId);
        $('input[name="user_name"]').val(userName);
        $('input[name="pin"]').val('0');
        $('#manual input[name="card_number"]').val(soyalCard);

        // Isi expire date
        if (endDate && endDate !== 'Tidak ada') {
            const [day, month, year] = endDate.split('-');
            $('input[name="expire_date"]').val(`${month}-${day}-${year}`);
        }

        // Tandai bahwa ini dari DB
        $('#user_id_display').closest('.input-group')
            .find('.input-group-text')
            .removeClass('bg-primary').addClass('bg-success')
            .html('Dari DB');

        $('#manual-tab').tab('show');
    });
    // Device select
    $('#deviceSelect').on('change', function() {
        const opt = $(this).find(':selected');
        if (!opt.val()) {
            $('#liftPreview').hide();
            return;
        }
        $('#ip_address').val(opt.data('ip'));
        $('#node_id').val(opt.data('node'));
        ['1', '2', '3', '4'].forEach(n => {
            const v = opt.data('lift' + n) ?? 255;
            $('#lift' + n).val(v);
            $('#previewLift' + n).text(`Lantai ${n}: ${v}`).toggleClass('badge-success', v ==
                255).toggleClass('badge-warning', v != 255);
        });
        $('#liftPreview').show();
    });

    $('#selectAllVisible').on('click', function() {
        $('.user-check-db:visible').prop('checked', true);
        $('#selectAllDb').prop('checked', true);
        updateSelectedCount();
    });

    $('#deselectAll').on('click', function() {
        $('.user-check-db').prop('checked', false);
        $('#selectAllDb').prop('checked', false);
        updateSelectedCount();
    });


});
</script>


@endsection