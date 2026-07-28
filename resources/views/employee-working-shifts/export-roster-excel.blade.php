<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Excel - {{ $unit->name }} - {{ $month }}/{{ $year }}</title>
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        .bg-weekend { background-color: #ffcccc; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <h2>ROSTER SHIFT BULANAN</h2>
    <p><strong>Unit:</strong> {{ $unit->name }}<br>
    <strong>Bulan/Tahun:</strong> {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="background-color: #f2f2f2;">No</th>
                <th rowspan="2" style="background-color: #f2f2f2;" class="text-left">Nama Pegawai</th>
                <th rowspan="2" style="background-color: #f2f2f2;">Skema Bonus</th>
                <th colspan="{{ $daysInMonth }}" style="background-color: #f2f2f2;">Tanggal</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $timestamp = mktime(0,0,0,$month,$d,$year);
                        $isWeekend = (date('D', $timestamp) == 'Sun');
                    @endphp
                    <th class="{{ $isWeekend ? 'bg-weekend' : '' }}" style="background-color: {{ $isWeekend ? '#ffcccc' : '#f2f2f2' }};">{{ $d }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $emp)
                @php
                    $empId = $emp['id'];
                    $rowData = $rosterData[$empId] ?? null;
                    $schemaId = $rowData['bonus_schema_id'] ?? '';
                    $schemaName = 'Default';
                    if($schemaId) {
                        $schema = collect($bonusSchemas)->firstWhere('id', $schemaId);
                        $schemaName = $schema ? $schema->name : 'Default';
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $emp['name'] }}</td>
                    <td>{{ $schemaName }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $shiftId = $rowData['days'][$d] ?? '';
                            $shiftCode = '';
                            if($shiftId) {
                                $shift = collect($shifts)->firstWhere('id', $shiftId);
                                $shiftCode = $shift ? $shift->code : '';
                            }
                            $timestamp = mktime(0,0,0,$month,$d,$year);
                            $isWeekend = (date('D', $timestamp) == 'Sun');
                        @endphp
                        <td class="{{ $isWeekend && !$shiftCode ? 'bg-weekend' : '' }}" style="{{ $isWeekend && !$shiftCode ? 'background-color:#ffcccc;' : '' }}">
                            {{ $shiftCode }}
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <h3>Keterangan Shift:</h3>
    <table>
        <thead>
            <tr>
                <th style="background-color: #f2f2f2;" class="text-left">Kode</th>
                <th style="background-color: #f2f2f2;" class="text-left">Nama Shift</th>
                <th style="background-color: #f2f2f2;" class="text-left">Jam Kerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td class="text-left"><strong>{{ $shift->code }}</strong></td>
                <td class="text-left">{{ $shift->name }}</td>
                <td class="text-left">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($notes)
    <br>
    <h3>Catatan Tambahan:</h3>
    <p>{!! nl2br(e($notes)) !!}</p>
    @endif
</body>
</html>
