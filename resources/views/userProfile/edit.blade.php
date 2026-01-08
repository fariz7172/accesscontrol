@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Edit User Profile</h1>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit User</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('userProfile.update', encryptId($user->ID)) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="NAME">Name</label>
                            <input type="text" id="NAME" name="NAME" class="form-control" value="{{ old('NAME', $user->NAME) }}" required>
                            @error('NAME')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="NoIdentitas">No Identity</label>
                            <input type="number" id="NoIdentitas" name="NoIdentitas" class="form-control" value="{{ old('NoIdentitas', $user->NoIdentitas) }}" required>
                            @error('NoIdentitas')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="PASSWORD">Password (leave blank to keep unchanged)</label>
                    <input type="text" id="PASSWORD" name="PASSWORD" class="form-control" value="{{ old('PASSWORD', $user->PASSWORD) }}">
                    @error('PASSWORD')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="BIRTHDAY">Birthday</label>
                    <input type="date" id="BIRTHDAY" name="BIRTHDAY" class="form-control" value="{{ old('BIRTHDAY', $user->BIRTHDAY) }}" required>
                    @error('BIRTHDAY')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="Depid">Departemen</label>
                            <select id="Depid" name="Depid" class="form-control" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('Depid', $user->Depid) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('Depid')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="Branchid">Branch</label>
                            <select id="Branchid" name="Branchid" class="form-control" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branchItem)
                                <option value="{{ $branchItem->id }}" {{ old('Branchid', $user->Branchid) == $branchItem->id ? 'selected' : '' }}>
                                    {{ $branchItem->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('Branchid')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="user_type">User Type</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="">Select User Type</option>
                        <option value="0" {{ old('user_type', $user->user_type) == '0' ? 'selected' : '' }}>Staff/User</option>
                        <option value="1" {{ old('user_type', $user->user_type) == '1' ? 'selected' : '' }}>Member</option>
                        <option value="2" {{ old('user_type', $user->user_type) == '2' ? 'selected' : '' }}>Personal Trainer</option>
                    </select>
                    @error('user_type')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="BEGIN_DATE">Begin Date</label>
                            <input type="datetime-local" id="BEGIN_DATE" name="BEGIN_DATE" class="form-control"
                                value="{{ old('BEGIN_DATE', \Carbon\Carbon::parse($user->BEGIN_DATE)->format('Y-m-d\TH:i')) }}" required>
                            @error('BEGIN_DATE')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="END_DATE">End Date</label>
                            <input type="datetime-local" id="END_DATE" name="END_DATE" class="form-control"
                                value="{{ old('END_DATE', \Carbon\Carbon::parse($user->END_DATE)->format('Y-m-d\TH:i')) }}" required>
                            @error('END_DATE')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            <label for="formFile" class="form-label">Update Profile Picture</label>
                            <input class="form-control" type="file" id="formFile" name="photo">
                            @error('photo')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @if($user->photo)
                            <img src="{{ $user->photo }}" alt="Profile Picture" style="max-width: 100px; margin-top: 10px;">
                            @endif
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label class="form-label">Card / RFID Access</label>
                            
                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs mb-3" id="accessMethodTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="rfid-tab" data-toggle="tab" href="#rfid" role="tab" aria-controls="rfid" aria-selected="true">Using Tag RFID</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="qrcode-tab" data-toggle="tab" href="#qrcode-pane" role="tab" aria-controls="qrcode-pane" aria-selected="false">Generate QR Code</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="fingerprint-tab" data-toggle="tab" href="#fingerprint-pane" role="tab" aria-controls="fingerprint-pane" aria-selected="false">Data Fingerprint</a>
                                </li>
                            </ul>

                            <!-- Tabs Content -->
                            <div class="tab-content" id="accessMethodTabContent">
                                <!-- RFID Tab -->
                                <div class="tab-pane fade show active" id="rfid" role="tabpanel" aria-labelledby="rfid-tab">
                                    <div class="form-group">
                                        <label for="Card" class="form-label">Input Tag RFID (Decimal)</label>
                                        <input type="text" id="Card" name="Card" class="form-control" value="{{ old('Card', $user->Card) }}" required placeholder="Tap RFID Card or Input Decimal">
                                        <small class="text-muted">Scan user RFID card to autofill this field.</small>
                                    </div>
                                </div>

                                <!-- QR Code Tab -->
                                <div class="tab-pane fade" id="qrcode-pane" role="tabpanel" aria-labelledby="qrcode-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-success mb-3" onclick="generateQRCode()">
                                                <i class="fas fa-magic"></i> Generate Code
                                            </button>
                                            
                                            <div class="form-group">
                                                <label>Hex Code (For QR)</label>
                                                <input type="text" id="hexCodeDisplay" class="form-control" readonly>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Decimal (Stored in DB)</label>
                                                <input type="text" id="decimalDisplay" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <div id="qrcode" class="mb-2 d-flex justify-content-center"></div>
                                            <a id="downloadLink" href="#" class="btn btn-primary btn-sm disabled" download="qrcode.jpg" style="display: none;">
                                                <i class="fas fa-download"></i> Download QR
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fingerprint Tab -->
                                <div class="tab-pane fade" id="fingerprint-pane" role="tabpanel" aria-labelledby="fingerprint-tab">
                                    <div class="mb-3">
                                        <label>Data Fingerprint (Fp)</label>
                                        @if($user->userData->isNotEmpty())
                                            @if($user->userData->first()->Fp)
                                            <p class="text-success">Finger Print Tersedia</p>
                                            <div class="input-group mb-2">
                                                <input type="text" name="fp" class="form-control" value="{{ old('fp', $user->userData->first()->Fp) }}" placeholder="Masukkan Fingerprint" hidden>
                                                @error('fp')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            @else
                                            <p class="text-danger">Finger Print Tidak Tersedia</p>
                                            <div class="input-group mb-2">
                                                <input type="text" name="fp" class="form-control" value="{{ old('fp', '') }}" placeholder="Masukkan Fingerprint" hidden>
                                                @error('fp')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            @endif
                                        @else
                                        <p class="text-danger">No User Data Found</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @error('Card')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('userProfiles.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    flatpickr('#BEGIN_DATE', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        defaultDate: "{{ \Carbon\Carbon::parse($user->BEGIN_DATE)->format('Y-m-d H:i') }}"
    });
    flatpickr('#END_DATE', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        defaultDate: "{{ \Carbon\Carbon::parse($user->END_DATE)->format('Y-m-d H:i') }}"
    });

    // Run on tab switch to ensure container is visible
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'qrcode-tab') {
            var existingCard = "{{ $user->Card }}";
            // Allow DOM to settle (Bootstrap transition)
            setTimeout(function() {
                if (existingCard && existingCard != "0") {
                     initQRCode(existingCard);
                }
            }, 100);
        }
    });

    function initQRCode(decimalVal) {
        // Convert Decimal String to Hex
        // Use BigInt because card numbers can be large
        try {
            var hexCode = BigInt(decimalVal).toString(16).toUpperCase();
            // Pad with leading zeros if needed to match 8 chars format (optional, but good for consistency)
            while (hexCode.length < 8) {
                hexCode = "0" + hexCode;
            }
            
            // Populate inputs
            document.getElementById('hexCodeDisplay').value = hexCode;
            document.getElementById('decimalDisplay').value = decimalVal;
            
            // Render QR
            renderQR(decimalVal);
        } catch (e) {
            console.error("Error converting card value:", e);
        }
    }

    function generateQRCode() {
        // 1. Generate Random 8-char Hex
        const hexChars = "0123456789ABCDEF";
        let hexCode = "";
        for (let i = 0; i < 8; i++) {
            hexCode += hexChars.charAt(Math.floor(Math.random() * hexChars.length));
        }

        // 2. Convert to Decimal
        const decimalValue = parseInt(hexCode, 16);

        // 3. Update Inputs
        document.getElementById('hexCodeDisplay').value = hexCode;
        document.getElementById('decimalDisplay').value = decimalValue;
        document.getElementById('Card').value = decimalValue;

        // 4. Render QR
        renderQR(decimalValue.toString());
    }

    function renderQR(text) {
        const qrContainer = document.getElementById("qrcode");
        qrContainer.innerHTML = ""; // Clear existing
        
        new QRCode(qrContainer, {
            text: text,
            width: 128,
            height: 128,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // 5. Handle Download Link (Composite Canvas)
        setTimeout(() => {
            const qrCanvas = qrContainer.querySelector('canvas');
            const downloadLink = document.getElementById('downloadLink');
            const userName = document.getElementById('NAME').value || 'User';
            const fileName = userName.replace(/\s+/g, '_') + '_QRCODE.jpg';
            
            if (qrCanvas) {
                // Create a new canvas for the composite image
                const padding = 20;
                const textHeight = 30;
                const compositeCanvas = document.createElement('canvas');
                const ctx = compositeCanvas.getContext('2d');

                // Set dimensions: QR width + padding * 2, QR height + padding * 2 + text space
                const width = qrCanvas.width + (padding * 2);
                const height = qrCanvas.height + (padding * 2) + textHeight;

                compositeCanvas.width = width;
                compositeCanvas.height = height;

                // 1. Fill White Background
                ctx.fillStyle = "#FFFFFF";
                ctx.fillRect(0, 0, width, height);

                // 2. Draw QR Code
                ctx.drawImage(qrCanvas, padding, padding);

                // 3. Draw User Name
                ctx.font = "bold 14px Arial";
                ctx.fillStyle = "#000000";
                ctx.textAlign = "center";
                // Position text centered below QR code
                ctx.fillText(userName, width / 2, height - 10);

                // Export to JPG
                downloadLink.href = compositeCanvas.toDataURL("image/jpeg", 0.9);
                downloadLink.download = fileName;
                downloadLink.style.display = 'inline-block';
                downloadLink.classList.remove('disabled');
            }
        }, 500);
    }
</script>
@endsection