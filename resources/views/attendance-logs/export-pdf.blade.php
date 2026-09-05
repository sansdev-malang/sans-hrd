<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Kehadiran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 7.5px; /* Very small to fit all dates */
            color: #333;
        }
        @page {
            margin: 0.8cm 0.5cm;
        }
        /* Color-coded Text for DomPDF (Text colors only, clean white background) */
        .cell-hadir { color: #047857; font-weight: bold; }
        .cell-late-amber { color: #d97706; font-weight: bold; }
        .cell-late { color: #e11d48; font-weight: bold; }
        .cell-alfa { color: #be123c; font-weight: bold; }
        .cell-cuti { color: #1d4ed8; font-weight: bold; }
        .cell-sakit { color: #d97706; font-weight: bold; }
        .cell-izin { color: #7c3aed; font-weight: bold; }
        .cell-dinas { color: #059669; font-weight: bold; }
        .cell-libur { color: #94a3b8; font-size: 5.5px; }
        .cell-off { color: #94a3b8; font-size: 5.5px; }
        .cell-pending { color: #9ca3af; }
        .time-out { color: #64748b; }
        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed; /* Force fixed table layout */
        }
        th, td {
            border: 0.5px solid #aaa;
            padding: 3px 2px;
            text-align: center;
            word-wrap: break-word;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
            font-size: 7px;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Specific Column Widths */
        .col-no { width: 3%; }
        .col-name { width: 14%; text-align: left; }
        .col-unit { width: 7%; }
        .col-date { width: auto; font-size: 7px; }
        
        .badge-green { color: #059669; font-weight: bold; }
        .badge-blue { color: #3b82f6; font-weight: bold; }
        .text-muted { color: #94a3b8; }
        .text-red { color: #ef4444; font-weight: bold; }
        .cell-content { font-size: 6.5px; line-height: 1.1; }
    </style>
</head>
<body>

    <h2>Laporan Data Kehadiran</h2>
    <div class="subtitle">
        Periode: {{ $periodeStr }} <br>
        Unit: {{ $unitId ? \App\Models\SchoolUnit::find($unitId)->name ?? 'Semua Unit' : 'Semua Unit' }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-name">Pegawai</th>
                <th class="col-unit">Unit</th>
                @foreach($dates as $date)
                    @php
                        $isSunday = $date->isSunday();
                        $colorClass = $isSunday ? 'text-red' : '';
                    @endphp
                    <th class="col-date {{ $colorClass }}">
                        {{ $date->translatedFormat('D') }}<br>
                        {{ $date->format('d/m') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">
                        <div class="font-bold">{{ $report['employee']['name'] }}</div>
                        <div style="font-size: 6px; color: #666;">{{ $report['employee']['nuptk'] ?? '-' }}</div>
                    </td>
                    <td>{{ $report['employee']['unit']['name'] ?? ($report['employee']['unit_name'] ?? '-') }}</td>
                    
                    @foreach($dates as $date)
                        @php
                            $dateStr = $date->format('Y-m-d');
                            $detail = $report['daily_details'][$dateStr] ?? null;
                            $cellClass = '';
                            $content = '';
                            
                            if ($detail) {
                                $status = $detail['status'];
                                if ($status === 'Hadir') {
                                    $inClass = 'cell-hadir';
                                    if (!empty($detail['is_red_late'])) {
                                        $inClass = 'cell-late';
                                    } elseif (!empty($detail['is_late'])) {
                                        $inClass = 'cell-late-amber';
                                    }
                                    $checkIn = isset($detail['check_in']) ? date('H:i', strtotime($detail['check_in'])) : '-';
                                    $checkOut = isset($detail['check_out']) ? date('H:i', strtotime($detail['check_out'])) : '-';
                                    
                                    $content = "<span class='{$inClass}'>{$checkIn}</span>";
                                    if (!empty($detail['pending_leave'])) {
                                        $content .= "<br><span class='cell-pending'>(" . $detail['pending_leave']['leave_code'] . ")</span>";
                                    }
                                    $content .= "<br><span class='time-out'>{$checkOut}</span>";
                                } elseif ($status === 'Alfa') {
                                    $cellClass = 'cell-alfa';
                                    $content = 'A';
                                    if (!empty($detail['pending_leave'])) {
                                        $content .= "<br><span class='cell-pending'>(" . $detail['pending_leave']['leave_code'] . ")</span>";
                                    }
                                } elseif ($status === 'Cuti/Izin') {
                                    $leaveCode = $detail['leave_code'] ?? 'I';
                                    $isPending = !empty($detail['is_pending']);
                                    if ($leaveCode === 'S') $codeClass = 'cell-sakit';
                                    elseif ($leaveCode === 'I') $codeClass = 'cell-izin';
                                    elseif ($leaveCode === 'C') $codeClass = 'cell-cuti';
                                    elseif ($leaveCode === 'H') $codeClass = 'cell-dinas';
                                    else $codeClass = 'cell-cuti';
                                    
                                    if ($isPending) $codeClass .= ' cell-pending';
                                    
                                    if (!empty($detail['check_in']) || !empty($detail['check_out'])) {
                                        $ci = isset($detail['check_in']) ? date('H:i', strtotime($detail['check_in'])) : '-';
                                        $co = isset($detail['check_out']) ? date('H:i', strtotime($detail['check_out'])) : '-';
                                        $inClass = !empty($detail['is_red_late']) ? 'cell-late' : (!empty($detail['is_late']) ? 'cell-late-amber' : 'cell-hadir');
                                        $content = "<span class='{$inClass}'>{$ci}</span><br><span class='{$codeClass}'>{$leaveCode}" . ($isPending ? ' (P)' : '') . "</span><br><span class='time-out'>{$co}</span>";
                                    } else {
                                        $cellClass = $codeClass;
                                        $content = $leaveCode . ($isPending ? ' (P)' : '');
                                    }
                                } elseif ($status === 'Libur' || $status === 'Off') {
                                    $cellClass = 'cell-off';
                                    $content = 'OFF';
                                } else {
                                    $content = '-';
                                }
                            } else {
                                if ($date->isSunday()) {
                                    $cellClass = 'cell-off';
                                    $content = 'OFF';
                                } else {
                                    $content = '-';
                                }
                            }
                        @endphp
                        <td class="{{ $cellClass }}">{!! $content !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($dates) + 3 }}" class="text-center">Tidak ada data pegawai pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; width: 100%; font-size: 10px;">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; text-align: left; width: 50%;"></td>
                <td style="border: none; text-align: center; width: 50%;">
                    Mengetahui,<br><br><br><br><br>
                    <strong>(________________________)</strong><br>
                    HRD Manager
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
