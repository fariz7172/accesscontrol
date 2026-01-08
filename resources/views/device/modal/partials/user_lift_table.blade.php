{{-- resources/views/device/modal/partials/user_lift_table.blade.php --}}
@forelse($users as $u)
    @php
        // Parse tanggal sekali saja
        $begin = $u->BEGIN_DATE ? \Carbon\Carbon::parse($u->BEGIN_DATE) : null;
        $end   = $u->END_DATE   ? \Carbon\Carbon::parse($u->END_DATE)   : null;

        // Hitung akses lift
        $liftAccess = [];
        $hasAccess  = false;

        foreach ($u->usersDevices as $ud) {
            if (!($ud->device ?? false)) continue;

            $deviceName = $ud->device->name ?? 'Device ID: ' . ($ud->gateId ?? 'N/A');
            $floors     = [];

            $map = [
                'Lift1' => [1,2,3,4,5,6,7,8],
                'Lift2' => [9,10,11,12,13,14,15,16],
                'Lift3' => [17,18,19,20,21,22,23,24],
                'Lift4' => [25,26,27,28,29,30,31,32]
            ];

            foreach ($map as $field => $range) {
                $val = $ud->{$field} ?? 0;
                for ($i = 0; $i < 8; $i++) {
                    if ($val & (1 << $i)) {
                        $floors[] = 'L' . $range[$i];
                        $hasAccess = true;
                    }
                }
            }

            if (!empty($floors)) {
                $liftAccess[] = ['device' => $deviceName, 'floors' => $floors];
            }
        }

        $badgeClass = $hasAccess ? 'bg-success' : 'bg-secondary';
        $badgeText  = $hasAccess ? 'Access Active' : 'Access Not Active';
    @endphp

    <tr class="user-row">
        <td class="text-center align-middle py-2">
            <div class="form-check">
                <input class="form-check-input user-check-db" type="checkbox"
                       value="{{ $u->ID }}"
                       data-name="{{ $u->NAME }}"
                       data-card="{{ $u->Card }}"
                       data-begin="{{ $begin?->toIso8601String() }}"
                       data-end="{{ $end?->toIso8601String() }}"
                       id="user_{{ $u->ID }}">
                <label class="form-check-label" for="user_{{ $u->ID }}"></label>
            </div>
        </td>
        <td class="text-muted fw-bold">{{ $u->ID }}</td>
        <td>{{ $u->NAME }}</td>
        <td><code class="small">{{ $u->Card }}</code></td>
        <td>
            @if($begin && $end)
                <small class="text-success">{{ $begin->format('d-m-Y') }}</small><br>
                <small class="text-danger">{{ $end->format('d-m-Y') }}</small>
            @else
                <span class="text-muted">Tidak ada</span>
            @endif
        </td>
        <td class="text-center">
            <a href="javascript:void(0)"
               class="badge {{ $badgeClass }} text-white lift-access-detail"
               data-name="{{ $u->NAME }}"
               data-access="{{ json_encode($liftAccess) }}">
                {{ $badgeText }}
            </a>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="6" class="text-center text-muted py-5">
            <i class="fas fa-search fa-3x mb-3"></i><br>
            <strong>Tidak ada user ditemukan</strong>
        </td>
    </tr>
@endforelse

{{-- Pagination --}}
@if($users->hasPages())
    <tr>
        <td colspan="6" class="bg-light border-0 py-3">
            <div class="d-flex justify-content-center">
                {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </td>
    </tr>
@endif