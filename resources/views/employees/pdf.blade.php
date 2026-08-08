<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Pegawai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 16pt;
        }
        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            color: #666;
            font-size: 11pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 9pt;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>Laporan Data Pegawai</h2>
    <p>Unit: {{ $unitName }}</p>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="10%">Unit</th>
                <th width="20%">Nama Lengkap</th>
                <th width="15%">Email</th>
                <th width="15%">Tipe Pegawai</th>
                <th width="15%">Jabatan</th>
                <th width="10%">Kontak</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $emp)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $emp['unit_name'] ?? '-' }}</td>
                    <td>{{ $emp['name'] ?? '-' }}</td>
                    <td>{{ $emp['email'] ?? '-' }}</td>
                    <td>{{ $emp['employee_type']['name'] ?? '-' }}</td>
                    <td>{{ $emp['position'] ?? '-' }}</td>
                    <td>{{ $emp['phone'] ?? '-' }}</td>
                    <td class="text-center">
                        @php
                            $statusText = 'Aktif';
                            if (($emp['status'] ?? '') == 'Leave') $statusText = 'Cuti';
                            if (($emp['status'] ?? '') == 'Inactive') $statusText = 'Nonaktif';
                        @endphp
                        {{ $statusText }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data pegawai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
