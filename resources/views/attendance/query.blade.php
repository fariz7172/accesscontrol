@extends('layout_background.app_layouts')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Soyal Access Control</h1>

    @if (session('alert'))
    <div class="alert alert-danger">
        {{ session('alert') }}
    </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <!-- Brand Buttons -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Soyal</h6>
                </div>

                <div class="card">
                    <div class="card-body" style="color:black">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{ route('attendance.query') }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="query">Tanyakan tentang data absensi:</label>
                                        <textarea class="form-control" id="query" name="query" rows="4" placeholder="Contoh: Absensi user John, siapa saja karyawan yang cuti, siapa yang cuti dan berapa lama, jumlah user yang cuti"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Kirim Pertanyaan</button>
                                </form>
                            </div>
                        </div>

                        @if (isset($result))
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Hasil Pencarian untuk: "{{ $query }}"</h5>
                                @if (isset($result['grok_analysis']))
                                    <div class="alert alert-info">
                                        <strong>Analisis AI:</strong> {!! nl2br(e(preg_replace('/```php.*?\n.*?```/s', '[Query diproses]', $result['grok_analysis']))) !!}
                                    </div>
                                @endif
                                @if (is_array($result['db_data']) && isset($result['db_data']['message']))
                                    <div class="alert alert-success">
                                        <strong>Hasil:</strong> {{ $result['db_data']['message'] }}
                                    </div>
                                @elseif ($result['db_data'] instanceof \Illuminate\Support\Collection && $result['db_data']->isEmpty())
                                    <div class="alert alert-warning">
                                        <strong>Hasil:</strong> Tidak ada data ditemukan untuk pertanyaan ini.
                                    </div>
                                @else
                                    <!-- Tabel untuk Absensi -->
                                    @if ($result['db_data']->first() instanceof \App\Models\AttendModel)
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Date</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                    <th>Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($result['db_data'] as $record)
                                                    <tr>
                                                        <td>{{ $record->userProfile->NAME ?? 'N/A' }}</td>
                                                        <td>{{ $record->AttDate }}</td>
                                                        <td>{{ $record->Time_In }}</td>
                                                        <td>{{ $record->Time_Out }}</td>
                                                        <td>{{ $record->Remark }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    <!-- Tabel untuk Ringkasan Absensi -->
                                    @elseif ($result['db_data']->first() instanceof \App\Models\AttendSumary)
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Period</th>
                                                    <th>Working Days</th>
                                                    <th>Present</th>
                                                    <th>Absent</th>
                                                    <th>Late In (Minutes)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($result['db_data'] as $record)
                                                    <tr>
                                                        <td>{{ $record->userProfile->NAME ?? 'N/A' }}</td>
                                                        <td>{{ $record->Period }}</td>
                                                        <td>{{ $record->WorkingDays }}</td>
                                                        <td>{{ $record->Present }}</td>
                                                        <td>{{ $record->Absent }}</td>
                                                        <td>{{ $record->LateInMinute }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    <!-- Tabel untuk Cuti -->
                                    @elseif ($result['db_data']->first() instanceof \App\Models\LeaveProcess)
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Leave Type</th>
                                                    <th>From Date</th>
                                                    <th>To Date</th>
                                                    <th>Status</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($result['db_data'] as $record)
                                                    <tr>
                                                        <td>{{ $record->userProfile->NAME ?? 'N/A' }}</td>
                                                        <td>{{ $record->leaveType->Name ?? 'N/A' }}</td>
                                                        <td>{{ $record->FromDate }}</td>
                                                        <td>{{ $record->ToDate }}</td>
                                                        <td>{{ $record->getStatusTextAttribute() }}</td>
                                                        <td>{{ $record->Notes }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    <!-- Tabel untuk Siapa yang Cuti dan Berapa Lama -->
                                    @elseif (is_array($result['db_data']) && isset($result['db_data'][0]['name']))
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Leave Type</th>
                                                    <th>From Date</th>
                                                    <th>To Date</th>
                                                    <th>Duration (Days)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($result['db_data'] as $record)
                                                    <tr>
                                                        <td>{{ $record['name'] }}</td>
                                                        <td>{{ $record['leave_type'] }}</td>
                                                        <td>{{ $record['from_date'] }}</td>
                                                        <td>{{ $record['to_date'] }}</td>
                                                        <td>{{ $record['duration'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    <!-- Tabel untuk Hari Libur -->
                                    @elseif ($result['db_data']->first() instanceof \App\Models\HolidayCal)
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Holiday Name</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($result['db_data'] as $record)
                                                    <tr>
                                                        <td>{{ $record->Name }}</td>
                                                        <td>{{ $record->StartDate }}</td>
                                                        <td>{{ $record->EndDate }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection