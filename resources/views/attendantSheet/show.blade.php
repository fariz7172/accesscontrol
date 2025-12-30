@extends('layout_background.app_layouts')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Attendance for {{ $userProfile->NAME }} ({{ $month }})</h1>

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <style>
        .red-text {
            color: white;
            background-color: red;
        }

        .green-text {
            color: white;
            background-color: green;
        }
    </style>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No Id</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Shift Name</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Total Work Hours</th>
                            <th>Leave Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attend as $item)
                        <tr class="{{ $item->DayType == 2 || $item->Remark === 'Holiday' ? 'red-text' : ($item->Remark === 'Leave' && $item->leaveType ? 'green-text' : '') }}">
                            <td>{{ $item->EmployeeID }}</td>
                            <td>{{ $item->userProfile ? $item->userProfile->NAME : 'N/A' }}</td>
                            <td>{{ $item->AttDate }}</td>
                            <td>{{ $item->shift ? $item->shift->ShiftName : 'N/A' }}</td>
                            <td>
                                @if ($item->Time_In && $item->shift && \Carbon\Carbon::parse($item->Time_In)->greaterThan(\Carbon\Carbon::parse($item->shift->Begin_Time)))
                                <span class="red-text">{{ $item->Time_In }}</span>
                                @else
                                {{ $item->Time_In ? $item->Time_In : 'N/A' }}
                                @endif
                            </td>
                            <td>
                                @if ($item->Time_Out && $item->shift && \Carbon\Carbon::parse($item->Time_Out)->lessThan(\Carbon\Carbon::parse($item->shift->Out_time)))
                                <span class="red-text">{{ $item->Time_Out }}</span>
                                @else
                                {{ $item->Time_Out ? $item->Time_Out : 'N/A' }}
                                @endif
                            </td>
                            <td>{{ $item->Present == 1 ? 'Present' : 'Not Present' }}</td>
                            <td>{{ $item->TotalWorkHour }}</td>
                            <td>
                                @if ($item->Remark === 'Holiday')
                                Holiday
                                @elseif ($item->Remark === 'Leave' && $item->leaveType)
                                {{ $item->leaveType->Name }}
                                @else
                                N/A
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="{{ route('attendantSheet.index') }}" class="btn btn-primary">Back to List</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection