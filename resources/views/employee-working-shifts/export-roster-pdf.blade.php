<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export PDF - {{ $rosterName }} - {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        h2 { text-align: center; margin-bottom: 12px; font-size: 15px; }
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .main-table th, .main-table td { border: 1px solid #333; padding: 3px 2px; text-align: center; font-size: 8.5px; }
        .main-table th { background-color: #f1f5f9; }
        .bg-weekend { background-color: #fee2e2; }
        .text-left { text-align: left !important; }
        .main-table th.text-left, .main-table td.text-left { text-align: left !important; }
        .legend-table th.text-left, .legend-table td.text-left { text-align: left !important; }
        .header-section { margin-bottom: 15px; }
        .legend-title { font-weight: bold; margin-bottom: 4px; font-size: 9.5px; }
        .day-name { font-size: 6.5px; font-weight: normal; display: block; margin-top: 1px; }

        .legend-table { border-collapse: collapse; width: auto; }
        .legend-table th, .legend-table td { border: 1px solid #333; padding: 2.5px 3px; font-size: 8px; text-align: center; }
        .legend-table th { background-color: #f1f5f9; }
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

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px; text-align: center;">No</th>
                <th rowspan="2" class="text-left" style="width: 140px; padding-left: 6px; text-align: left; white-space: nowrap;">Nama Pegawai</th>
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
                    <td style="width: 25px; text-align: center;">{{ $index + 1 }}</td>
                    <td class="text-left" style="width: 140px; padding-left: 6px; text-align: left; white-space: nowrap;">{{ $emp['name'] }}</td>
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
                            
                            if(!$shiftCode) {
                                $shiftCode = 'OFF';
                                $shiftBg = $isWeekend ? '#fee2e2' : '#e2e8f0';
                                $shiftText = $isWeekend ? '#e11d48' : '#334155';
                            }
                        @endphp
                        <td class="{{ $isWeekend && $shiftCode === 'OFF' ? 'bg-weekend' : '' }}" {!! $shiftBg ? 'style="background-color: '.$shiftBg.'; color: '.$shiftText.'; border-color: #333;"' : '' !!}>
                            <strong>{{ $shiftCode }}</strong>
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 5px;">
        <tr style="border: none;">
            <td style="border: none; vertical-align: top; padding: 0; text-align: left;">
                <div class="legend-title">Keterangan Shift:</div>
                <table class="legend-table">
                    <thead>
                        <tr>
                            <th style="width: 25px; text-align: center;">Kode</th>
                            <th class="text-left" style="width: 140px; padding-left: 6px; text-align: left;">Nama Shift</th>
                            <th style="width: 48px; text-align: center;">Sen</th>
                            <th style="width: 48px; text-align: center;">Sel</th>
                            <th style="width: 48px; text-align: center;">Rab</th>
                            <th style="width: 48px; text-align: center;">Kam</th>
                            <th style="width: 48px; text-align: center;">Jum</th>
                            <th style="width: 48px; text-align: center;">Sab</th>
                            <th style="width: 48px; text-align: center;">Min</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $activeShifts = collect($shifts)->filter(fn($s) => isset($usedShiftIds[$s->id]))->values();
                        @endphp
                        @if($activeShifts->count() > 0)
                            @foreach($activeShifts as $shift)
                                <tr>
                                    <td style="width: 25px; background-color: {{ $shift->hex_bg }}; color: {{ $shift->hex_text }}; text-align: center;"><strong>{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}</strong></td>
                                    <td class="text-left" style="width: 140px; padding-left: 6px; text-align: left;">{{ $shift->name }}</td>
                                    @for($i = 1; $i <= 7; $i++)
                                        @php
                                            $dayDb = ($i == 7) ? 0 : $i;
                                            $detail = $shift->details ? $shift->details->firstWhere('day_of_week', $dayDb) : null;
                                            $text = '-';
                                            if ($detail) {
                                                if ($detail->is_off) {
                                                    $text = '<span style="color: #64748b; font-size: 7.5px;">Libur</span>';
                                                } else {
                                                    $text = substr($detail->start_time, 0, 5) . '-' . substr($detail->end_time, 0, 5);
                                                }
                                            }
                                        @endphp
                                        <td style="width: 48px; font-size: 7.5px; white-space: nowrap; text-align: center;">{!! $text !!}</td>
                                    @endfor
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" style="padding: 4px; text-align: left; font-size: 8px;">Belum ada shift yang dijadwalkan pada roster ini.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>
            
            <td style="border: none; vertical-align: top; text-align: right; width: 220px; padding-top: 5px;">
                @if($notes)
                <div style="text-align: left; margin-bottom: 10px;">
                    <div style="font-weight: bold; font-size: 9px; margin-bottom: 2px;">Catatan:</div>
                    <div style="line-height: 1.25; font-size: 8px; border: 1px solid #ccc; padding: 4px 6px; background-color: #f9fafb;">
                        {!! nl2br(e($notes)) !!}
                    </div>
                </div>
                @endif
                
                <div style="display: inline-block; width: 170px; text-align: center; margin-top: {{ $notes ? '0px' : '15px' }};">
                    <p style="margin: 0; font-size: 9.5px;">Mengetahui,</p>
                    <p style="margin: 3px 0 45px 0; font-weight: bold; font-size: 9.5px;">HRD</p>
                    <p style="margin: 0; font-size: 9px;">( ________________________ )</p>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
