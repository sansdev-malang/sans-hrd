<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Pegawai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #333;
            line-height: 1.35;
        }
        h2 {
            text-align: center;
            margin-bottom: 2px;
            font-size: 14pt;
        }
        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 15px;
            color: #666;
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .data-item {
            margin-bottom: 2.5px;
        }
        .label {
            color: #64748b;
            font-weight: 500;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <h2>Laporan Data Lengkap Pegawai</h2>
    <p>Unit: {{ $unitName }}</p>

    <table>
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="20%">Pegawai & Unit</th>
                <th width="18%">Dokumen Identitas</th>
                <th width="22%">Pendidikan & Jabatan</th>
                <th width="22%">Administrasi Kerja</th>
                <th width="15%">Kontak & Alamat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $emp)
                @php
                    $rawName = $emp['name'] ?? '';
                    $nameWithoutTitles = $emp['raw_name'] ?? $rawName;
                    $genderText = ($emp['gender'] ?? '') === 'Female' ? 'Perempuan' : 'Laki-laki';
                    
                    $statusText = 'Aktif';
                    if (($emp['status'] ?? '') == 'Leave') $statusText = 'Cuti';
                    if (($emp['status'] ?? '') == 'Inactive') $statusText = 'Nonaktif';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    
                    <!-- Pegawai & Unit -->
                    <td>
                        <div class="data-item">
                            <span class="label">Nama:</span>
                            <span class="val">{{ $emp['name'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Unit:</span>
                            <span class="val">{{ $emp['unit_name'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Gender:</span>
                            <span class="val">{{ $genderText }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Lahir:</span>
                            <span class="val">{{ $emp['birth_place'] ?? '-' }}, {{ $emp['birth_date'] ? \Carbon\Carbon::parse($emp['birth_date'])->format('d/m/Y') : '-' }}</span>
                        </div>
                    </td>
                    
                    <!-- Dokumen Identitas -->
                    <td>
                        <div class="data-item">
                            <span class="label">NIK:</span>
                            <span class="val">{{ $emp['nik'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">NIY:</span>
                            <span class="val">{{ $emp['niy'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">NUPTK:</span>
                            <span class="val">{{ $emp['nuptk'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">No UKG:</span>
                            <span class="val">{{ $emp['no_ukg'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">NRG:</span>
                            <span class="val">{{ $emp['nrg'] ?? '-' }}</span>
                        </div>
                    </td>
                    
                    <!-- Pendidikan & Jabatan -->
                    <td>
                        <div class="data-item">
                            <span class="label">Tipe:</span>
                            <span class="val">{{ $emp['employee_type']['name'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Jabatan:</span>
                            <span class="val">{{ $emp['position'] ?? '-' }}</span>
                        </div>
                        @if(!empty($emp['additional_position']))
                        <div class="data-item">
                            <span class="label">Tambahan:</span>
                            <span class="val">{{ $emp['additional_position'] }}</span>
                        </div>
                        @endif
                        <div class="data-item">
                            <span class="label">Pendidikan:</span>
                            <span class="val">{{ $emp['last_education'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Jurusan:</span>
                            <span class="val">{{ $emp['major'] ?? '-' }}</span>
                        </div>
                    </td>
                    
                    <!-- Administrasi Kerja -->
                    <td>
                        <div class="data-item">
                            <span class="label">TMT:</span>
                            <span class="val">{{ $emp['task_start_date'] ? \Carbon\Carbon::parse($emp['task_start_date'])->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Status:</span>
                            <span class="val">{{ $emp['employment_status'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Golongan:</span>
                            <span class="val">{{ $emp['pangkat_golongan'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Masa Kerja:</span>
                            <span class="val">{{ $emp['work_period'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">SK Terakhir:</span>
                            <span class="val" style="font-size: 7.5pt;">{{ $emp['last_sk_number'] ?? '-' }} ({{ $emp['last_sk_date'] ? \Carbon\Carbon::parse($emp['last_sk_date'])->format('d/m/Y') : '-' }})</span>
                        </div>
                    </td>
                    
                    <!-- Kontak & Alamat -->
                    <td>
                        <div class="data-item">
                            <span class="label">HP/WA:</span>
                            <span class="val">{{ $emp['phone'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Email:</span>
                            <span class="val" style="font-size: 7.5pt;">{{ $emp['email'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">PIN Finger:</span>
                            <span class="val">{{ $emp['zkteco_uid'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Alamat:</span>
                            <span class="val" style="font-size: 7pt; font-weight: normal; color: #334155;">{{ $emp['address'] ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="label">Keaktifan:</span>
                            <span class="val" style="font-size: 7.5pt; color: {{ ($emp['status'] ?? '') == 'Active' ? 'green' : (($emp['status'] ?? '') == 'Leave' ? 'orange' : 'red') }};">{{ $statusText }}</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data pegawai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
