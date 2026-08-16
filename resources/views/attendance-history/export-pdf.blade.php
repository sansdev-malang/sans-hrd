<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Kehadiran Pegawai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.15;
        }
        @page {
            margin: 1.2cm 1cm;
        }
        h2 {
            text-align: center;
            margin-bottom: 2px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 15px;
            font-size: 9px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 0.5px solid #888;
            padding: 4px 5px;
            vertical-align: middle;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: left;
            font-size: 8.5px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 3px;
            text-align: center;
        }
        .badge-hadir { color: #047857; background-color: #ecfdf5; border: 0.5px solid #a7f3d0; }
        .badge-terlambat { color: #b45309; background-color: #fffbeb; border: 0.5px solid #fde68a; }
        .badge-alfa { color: #be123c; background-color: #fff1f2; border: 0.5px solid #fecdd3; }
        .badge-sakit { color: #b91c1c; background-color: #fef2f2; border: 0.5px solid #fee2e2; }
        .badge-izin { color: #c2410c; background-color: #fff7ed; border: 0.5px solid #ffedd5; }
        .badge-cuti { color: #1d4ed8; background-color: #eff6ff; border: 0.5px solid #dbeafe; }
        .badge-dinas { color: #4338ca; background-color: #e0e7ff; border: 0.5px solid #c7d2fe; }
        .badge-off { color: #64748b; background-color: #f8fafc; border: 0.5px solid #e2e8f0; }
        .badge-libur { color: #4b5563; background-color: #f3f4f6; border: 0.5px solid #e5e7eb; }
        .badge-pending { color: #9ca3af; background-color: #fafafa; border: 0.5px solid #f3f4f6; }

        .text-muted {
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <h2>Laporan Riwayat Kehadiran Pegawai</h2>
    <div class="subtitle">
        Periode Laporan: {{ $periodeStr }} <br>
        Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') }} WIB
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 35%;">Nama Pegawai</th>
                <th style="width: 18%;">Hari & Tanggal</th>
                <th style="width: 15%;">Jadwal Shift</th>
                <th style="width: 8%; text-align: center;">Masuk</th>
                <th style="width: 8%; text-align: center;">Keluar</th>
                <th style="width: 8%; text-align: center;">Status</th>
                <th style="width: 14%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historyList as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $row['employee_name'] }}</div>
                        @if($row['employee_nip'])
                            <div style="font-size: 7px; color: #666; font-family: monospace;">NIP/NUPTK: {{ $row['employee_nip'] }}</div>
                        @endif
                        <div style="font-size: 7.5px; color: #555;">{{ $row['unit_name'] }} - {{ $row['position'] }}</div>
                    </td>
                    <td>{{ $row['date_formatted'] }}</td>
                    <td>
                        <div class="font-bold" style="font-size: 8px;">{{ $row['shift_name'] }}</div>
                        @if($row['shift_start'])
                            <div style="font-size: 7px; color: #666;">{{ $row['shift_start'] }} - {{ $row['shift_end'] }}</div>
                        @endif
                    </td>
                    <td class="text-center font-bold" style="font-family: monospace;">{{ $row['check_in'] ?: '-' }}</td>
                    <td class="text-center font-bold" style="font-family: monospace;">{{ $row['check_out'] ?: '-' }}</td>
                    <td class="text-center">
                        @php
                            $badgeClass = match($row['status']) {
                                'Hadir' => 'badge-hadir',
                                'Terlambat' => 'badge-terlambat',
                                'Alfa' => 'badge-alfa',
                                'Sakit' => 'badge-sakit',
                                'Izin' => 'badge-izin',
                                'Cuti' => 'badge-cuti',
                                'Dinas' => 'badge-dinas',
                                'Off' => 'badge-off',
                                'Libur' => 'badge-libur',
                                'Pending' => 'badge-pending',
                                default => 'badge-off',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $row['status'] }}</span>
                    </td>
                    <td>
                        @if($row['status'] === 'Terlambat' && $row['late_minutes'] > 0)
                            <span style="color: #b45309; font-weight: bold;">Terlambat {{ $row['late_minutes'] }} mnt</span>
                        @elseif($row['notes'])
                            <span>{{ $row['notes'] }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data riwayat absensi yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 40px; width: 100%;">
        <table style="border: none; width: 100%;">
            <tr style="border: none;">
                <td style="border: none; text-align: left; width: 50%;"></td>
                <td style="border: none; text-align: center; width: 50%; font-size: 9.5px;">
                    Mengetahui,<br>
                    Kepala Bagian Kepegawaian<br><br><br><br><br>
                    <strong>(________________________)</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
