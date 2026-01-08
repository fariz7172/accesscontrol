<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 15mm;
            font-size: 10pt;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 14pt;
            margin-bottom: 10mm;
        }
        .header-info {
            text-align: center;
            margin-bottom: 10mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10mm;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid #333;
            padding: 4mm;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 10mm;
            font-size: 8pt;
            color: #666;
        }
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <h1>Leave Request Report</h1>
    <div class="header-info">
        <p><strong>Employee:</strong> {{ $user->NAME ?? 'N/A' }}</p>
        <p><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</p>
        <p><strong>Generated on:</strong> {{ now()->format('Y-m-d') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Leave Type</th>
                <th>Employee</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Notes</th>
                <th>Approver</th>
                <th>Approved At</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaveProcesses as $leave)
            <tr>
                <td>{{ $leave->leaveType->Name ?? 'N/A' }}</td>
                <td>{{ $leave->userProfile->NAME ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($leave->FromDate)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($leave->ToDate)->format('Y-m-d') }}</td>
                <td>{{ $leave->Notes ?? '-' }}</td>
                <td>{{ $leave->approver ? $leave->approver->username : 'Not Assigned' }}</td>
                <td>{{ $leave->approved_at ? \Carbon\Carbon::parse($leave->approved_at)->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($leave->updated_at)->format('Y-m-d') }}</td>
                <td>{{ $leave->getStatusTextAttribute() }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10">No leave requests found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Copyright &copy; Soyal {{ now()->year }}</p>
    </div>
</body>
</html>