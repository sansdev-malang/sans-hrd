<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export PDF - {{ $rosterName }} - {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        h2 { text-align: center; margin-bottom: 5px; font-size: 16px; }
        .info { margin-bottom: 15px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: center; font-size: 9px; }
        th { background-color: #f1f5f9; }
        .bg-weekend { background-color: #fee2e2; }
        .text-left { text-align: left; }
        .header-section { margin-bottom: 20px; }
        .legend-title { font-weight: bold; margin-bottom: 5px; font-size: 11px; }
        .legend-table { width: auto; min-width: 300px; }
        .legend-table th, .legend-table td { padding: 4px 8px; font-size: 10px; }
        .notes-section { margin-top: 20px; font-size: 11px; border: 1px solid #ccc; padding: 10px; background-color: #f9fafb; }
        .day-name { font-size: 7px; font-weight: normal; display: block; margin-top: 2px; }
    </style>
</head>
<body>
    @php
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $bulanIndo[$month] ?? '';
        
        $hariIndo = [
            'Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'
        ];
        
        $usedShiftIds = [];
        foreach($rosterData as $empData) {
            if(isset($empData['days'])) {
                foreach($empData['days'] as $d => $sId) {
                    if($sId) {
                        $usedShiftIds[$sId] = true;
                    }
                }
            }
        }
    @endphp

    <div class="header-section">
        <h2>JADWAL {{ mb_strtoupper($rosterName) }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" class="text-left" style="width: 150px;">Nama Pegawai</th>
                <th colspan="{{ $daysInMonth }}">Tanggal & Hari</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $timestamp = mktime(0,0,0,$month,$d,$year);
                        $isWeekend = (date('D', $timestamp) == 'Sun');
                        $dayNameEng = date('D', $timestamp);
                        $dayNameId = $hariIndo[$dayNameEng] ?? '';
                    @endphp
                    <th class="{{ $isWeekend ? 'bg-weekend' : '' }}">
                        {{ $d }}
                        <span class="day-name">{{ $dayNameId }}</span>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $emp)
                @php
                    $empId = $emp['id'];
                    $rowData = $rosterData[$empId] ?? null;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $emp['name'] }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $shiftId = $rowData['days'][$d] ?? '';
                            $shiftCode = '';
                            $shiftBg = '';
                            $shiftText = '';
                            if($shiftId) {
                                $shift = collect($shifts)->firstWhere('id', $shiftId);
                                if ($shift) {
                                    $shiftCode = $shift->short_code ?: strtoupper(last(explode('_', $shift->code)));
                                    $shiftBg = $shift->hex_bg ?? '';
                                    $shiftText = $shift->hex_text ?? '';
                                }
                            }
                            $timestamp = mktime(0,0,0,$month,$d,$year);
                            $isWeekend = (date('D', $timestamp) == 'Sun');
                        @endphp
                        <td class="{{ $isWeekend && !$shiftCode ? 'bg-weekend' : '' }}" {!! $shiftBg ? 'style="background-color: '.$shiftBg.'; color: '.$shiftText.'; border-color: #333;"' : '' !!}>
                            <strong>{{ $shiftCode }}</strong>
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 15px;">
        <tr style="border: none;">
            <td style="border: none; vertical-align: top; padding: 0; width: 65%; text-align: left;">
                <div class="legend-title" style="text-align: left;">Keterangan Shift:</div>
                <table class="legend-table" style="width: 100%; margin-top: 5px;">
                    <thead>
                        <tr>
                            <th class="text-center">Kode</th>
                            <th class="text-left">Nama Shift</th>
                            <th>Sen</th>
                            <th>Sel</th>
                            <th>Rab</th>
                            <th>Kam</th>
                            <th>Jum</th>
                            <th>Sab</th>
                            <th>Min</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasShift = false; @endphp
                        @foreach($shifts as $shift)
                            @if(isset($usedShiftIds[$shift->id]))
                                @php $hasShift = true; @endphp
                                <tr>
                                    <td class="text-center" style="background-color: {{ $shift->hex_bg }}; color: {{ $shift->hex_text }}; border: 1px solid #333;"><strong>{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}</strong></td>
                                    <td class="text-left">{{ $shift->name }}</td>
                                    @for($i = 1; $i <= 7; $i++)
                                        @php
                                            $detail = $shift->details->firstWhere('day_of_week', $i);
                                            $text = '-';
                                            if ($detail) {
                                                if ($detail->is_off) {
                                                    $text = '<span style="color: #ef4444;">Libur</span>';
                                                } else {
                                                    $text = \Carbon\Carbon::parse($detail->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($detail->end_time)->format('H:i');
                                                }
                                            }
                                        @endphp
                                        <td>{!! $text !!}</td>
                                    @endfor
                                </tr>
                            @endif
                        @endforeach
                        @if(!$hasShift)
                            <tr>
                                <td colspan="8" class="text-left">Belum ada shift yang dijadwalkan pada roster ini.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>
            
            <td style="border: none; vertical-align: top; padding: 0 0 0 20px; width: 35%; text-align: left;">
                @if($notes)
                <div class="legend-title" style="text-align: left;">Catatan Tambahan:</div>
                <div class="notes-section" style="margin-top: 5px; line-height: 1.4; text-align: left;">
                    {!! nl2br(e($notes)) !!}
                </div>
                <div style="margin-top: 40px; text-align: center;">
                @else
                <div style="margin-top: 120px; text-align: center;">
                @endif
                    <p style="margin: 0;">Mengetahui,</p>
                    <p style="margin: 5px 0 60px 0; font-weight: bold;">HRD</p>
                    <p style="margin: 0;">(_________________________)</p>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
