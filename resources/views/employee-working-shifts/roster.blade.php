<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Roster Shift Bulanan') }}
        </h2>
    </x-slot>

    <style>
        @foreach($allShifts as $shift)
            .shift-color-{{ $shift->id }} {
                background-color: {{ $shift->hex_bg }};
                color: {{ $shift->hex_text }};
            }
            .dark .shift-color-{{ $shift->id }} {
                background-color: {{ $shift->hex_bg }}40;
                color: {{ $shift->hex_text }};
            }
        @endforeach
        select option {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }
        .dark select option {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>

<div class="p-6 space-y-6 animate-fade-in" x-data="rosterGrid()">
    <form method="POST" action="{{ route('employee-working-shifts.update-roster') }}">
        @csrf
        <input type="hidden" name="school_unit_id" value="{{ $selectedUnitId }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="old_roster_name" value="{{ $oldRosterName ?? '' }}">
    
    <!-- Header & Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
        <div class="flex items-center gap-3 text-left">
            <a href="{{ route('employee-working-shifts.index') }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 transition-all hover:scale-105 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-3xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-3 text-left">
                    <div class="relative flex items-center">
                        <input type="text" name="roster_name" value="{{ $rosterName }}" required placeholder="Nama Roster..."
                            class="text-xl font-bold text-slate-900 dark:text-slate-50 bg-white dark:bg-slate-900 border-2 border-indigo-300 dark:border-indigo-500 rounded-xl pl-9 pr-3 py-1.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-105 dark:focus:ring-indigo-900/30 transition-all font-sans tracking-wide w-72 shadow-3xs"
                            title="Klik untuk mengubah nama roster ini">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-500 dark:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wider shrink-0">Roster</span>
                </div>
                <!-- Context Badges -->
                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                    <span class="text-[10px] font-bold text-slate-450 dark:text-slate-500">Unit:</span>
                    <select id="filter_unit_id"
                        class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-750 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                        @change="window.location.href = '/employee-working-shifts/roster?unit_id=' + $event.target.value + '&month={{ $month }}&year={{ $year }}&roster_name=' + encodeURIComponent(document.querySelector('input[name=roster_name]').value)">
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }} class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">{{ $unit->name }}</option>
                        @endforeach
                    </select>

                    <span class="text-slate-300 dark:text-slate-700 text-[10px]">•</span>
                    <span class="text-[10px] font-bold text-slate-450 dark:text-slate-500">Periode:</span>
                    @php
                        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    @endphp
                    <select id="filter_month"
                        class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-750 dark:text-emerald-400 border border-emerald-100/30 dark:border-emerald-900/30 cursor-pointer focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
                        @change="window.location.href = '/employee-working-shifts/roster?unit_id={{ $selectedUnitId }}&month=' + $event.target.value + '&year={{ $year }}&roster_name=' + encodeURIComponent(document.querySelector('input[name=roster_name]').value)">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }} class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">{{ $bulanIndo[$m] }}</option>
                        @endfor
                    </select>

                    <input type="number" id="filter_year" value="{{ $year }}" required
                        class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-750 dark:text-emerald-400 border border-emerald-100/30 dark:border-emerald-900/30 w-16 focus:outline-none focus:ring-1 focus:ring-emerald-500/30 font-mono text-center"
                        @change="window.location.href = '/employee-working-shifts/roster?unit_id={{ $selectedUnitId }}&month={{ $month }}&year=' + $event.target.value + '&roster_name=' + encodeURIComponent(document.querySelector('input[name=roster_name]').value)">

                    <span class="text-slate-300 dark:text-slate-700 text-[10px]">•</span>
                    <span class="text-[10px] font-bold text-slate-450 dark:text-slate-500">Jumlah:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/40 dark:border-slate-700/40 font-bold text-[10px]">
                        <span x-text="activeEmployeeIds.length"></span>&nbsp;Orang
                    </span>
                </div>
            </div>
        </div>
        
        <button type="button" @click="showLegend = !showLegend" 
            class="h-9 px-4 inline-flex items-center justify-center bg-indigo-50/60 dark:bg-indigo-950/30 hover:bg-indigo-100/80 dark:hover:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/30 text-xs font-bold rounded-xl shadow-3xs cursor-pointer transition-all duration-150 gap-2 shrink-0 font-sans tracking-wide">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span x-text="showLegend ? 'Sembunyikan Panduan' : 'Lihat Panduan & Keterangan Roster'"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 text-indigo-500" :class="showLegend ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 9l-7 7-7-7"/></svg>
        </button>
    </div>

    <!-- Shift Diaktifkan Roster Info Panel -->
    <div class="bg-indigo-50/40 dark:bg-indigo-955/15 border border-indigo-100 dark:border-indigo-900/30 rounded-2xl p-4 text-left flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
        <div class="space-y-1">
            <span class="text-[11px] font-extrabold text-indigo-850 dark:text-indigo-400 flex items-center gap-1.5 uppercase tracking-wide">
                <i data-lucide="info" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                Shift Kerja yang Digunakan dalam Roster ini:
            </span>
            <div class="flex flex-wrap gap-1.5 mt-2.5">
                <template x-for="sh in getSelectedShiftsList()" :key="sh.id">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-colors shadow-3xs"
                        :class="sh.colorClass">
                        <span x-text="sh.code" class="px-1.5 py-0.5 rounded bg-white/40 dark:bg-black/20 font-black"></span>
                        <span x-text="sh.name"></span>
                    </span>
                </template>
                <template x-if="selectedShiftIds.length === 0">
                    <span class="text-xs text-slate-500 italic">Belum ada shift kerja yang diaktifkan. Klik tombol Kelola Shift Roster untuk menambahkan.</span>
                </template>
            </div>
        </div>
    </div>

    <!-- Collapsible Instructions & Legend -->
    <div x-cloak x-show="showLegend" x-collapse
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-5 grid grid-cols-1 md:grid-cols-2 gap-6 w-full text-left">
        <!-- Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-left text-blue-800 dark:text-blue-200 text-xs">
                <h5 class="font-bold mb-1">Panduan Pengisian Jadwal (Roster)</h5>
                <ul class="list-disc pl-4 space-y-1">
                    <li><strong>Mengisi Shift:</strong> Klik pada sel tanggal dan pilih shift yang diinginkan langsung dari dropdown. Warna sel akan berubah secara otomatis.</li>
                    <li><strong>Meliburkan Hari (OFF):</strong> Pilih opsi <strong>"-"</strong> pada sel tanggal. Hari tersebut akan disimpan sebagai libur/kosong (OFF).</li>
                    <li><strong>Hari Minggu:</strong> Sel tanggal berwarna merah muda sebagai penanda hari libur akhir pekan untuk mempermudah pemetaan visual Anda.</li>
                </ul>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-col justify-center">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wider">Keterangan Singkatan Shift</h3>
            <div class="flex flex-wrap gap-2.5">
                @foreach($shifts as $index => $shift)
                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/50 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800">
                        <div class="px-1.5 rounded-sm {{ $shift->color }} text-[10px] font-black shadow-sm">{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}</div>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $shift->name }}</span>
                    </div>
                @endforeach
                
                <!-- Legend Libur / Kosong -->
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/50 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800">
                    <div class="px-1.5 rounded-sm bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-350 dark:border-slate-700 text-[10px] font-black shadow-sm">OFF</div>
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Libur / Kosong</span>
                </div>
            </div>
        </div>
    </div>

        
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <!-- Table Tools -->
            <div class="p-3 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between gap-4">
                <div class="relative w-full max-w-sm">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama pegawai..." class="w-full pl-9 pr-8 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 dark:text-slate-100">
                    <button type="button" x-show="searchQuery.trim() !== ''" @click="searchQuery = ''" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-650 transition-colors border-0 bg-transparent cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showAddShiftModal = true"
                        class="h-9 px-4 inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-2xs cursor-pointer transition-all hover:scale-[1.02] duration-150 gap-1.5 border-0">
                        <i data-lucide="settings" class="w-3.5 h-3.5 shrink-0 text-white"></i>
                        <span>Kelola Shift Roster</span>
                    </button>

                    <button type="button" @click="showAddEmployeeModal = true"
                        class="h-9 px-4 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs cursor-pointer transition-all hover:scale-[1.02] duration-150 gap-1.5 border-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a6 6 0 0 0-3.44-5.32M19 19a1 1 0 0 0 1-1v-.72A6 6 0 0 0 16.56 12m-9 6.72a6 6 0 0 1 3.44-5.32M5 19a1 1 0 0 1-1-1v-.72a6 6 0 0 1 3.44-5.32M15 9.72a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <span>Kelola Pegawai Roster</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/80">
                            <th class="p-3 text-xs font-bold text-slate-900 dark:text-slate-100 border-b border-r border-slate-200 dark:border-slate-800 sticky left-0 z-10 bg-slate-50 dark:bg-slate-900/80 shadow-[1px_0_0_0_rgba(226,232,240,1)] dark:shadow-[1px_0_0_0_rgba(30,41,59,1)] min-w-[200px]">NAMA PEGAWAI</th>
                            <th class="p-3 text-xs font-bold text-slate-900 dark:text-slate-100 border-b border-r border-slate-200 dark:border-slate-800 min-w-[150px]">SKEMA BONUS</th>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $timestamp = mktime(0,0,0,$month,$d,$year);
                                    $dayNameEng = date('D', $timestamp);
                                    $dayNamesId = ['Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab', 'Sun' => 'Min'];
                                    $dayName = $dayNamesId[$dayNameEng] ?? $dayNameEng;
                                    $isWeekend = ($dayNameEng == 'Sun');
                                @endphp
                                <th class="p-2 text-center border-b border-r border-slate-200 dark:border-slate-800 {{ $isWeekend ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400' : 'text-slate-600 dark:text-slate-400' }} transition-colors"
                                    @mouseenter="hoveredCol = {{ $d }}" @mouseleave="hoveredCol = null"
                                    :class="{ 'bg-slate-100 dark:bg-slate-800/50': hoveredCol === {{ $d }} }">
                                    <div class="text-[9px] uppercase font-semibold">{{ $dayName }}</div>
                                    <div class="text-sm font-bold">{{ $d }}</div>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($employees as $emp)
                            @php
                                $empId = $emp['id'];
                                $rowData = $rosterData[$empId] ?? null;
                                $bonusSchemaId = $rowData['bonus_schema_id'] ?? '';
                            @endphp
                             <tr x-cloak class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors group" 
                                x-show="activeEmployeeIds.includes(String('{{ $empId }}')) && '{{ addslashes(strtolower($emp['name'])) }}'.includes(searchQuery.toLowerCase())">
                                <td class="p-3 border-r border-slate-200 dark:border-slate-800 sticky left-0 z-10 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-900 shadow-[1px_0_0_0_rgba(226,232,240,1)] dark:shadow-[1px_0_0_0_rgba(30,41,59,1)]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm whitespace-nowrap">{{ $emp['name'] }}</div>
                                        <button type="button" @click="activeEmployeeIds = activeEmployeeIds.filter(id => id !== String('{{ $empId }}'))"
                                            class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors border-0 bg-transparent cursor-pointer shrink-0"
                                            title="Keluarkan Pegawai dari Roster">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2 2 0 0 1-1.995 1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-2 border-r border-slate-200 dark:border-slate-800">
                                    <select name="roster[{{ $empId }}][bonus_schema_id]" :disabled="!activeEmployeeIds.includes(String('{{ $empId }}'))" class="w-full text-xs px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                        <option value="">Default/Aktif</option>
                                        @foreach($bonusSchemas as $schema)
                                            <option value="{{ $schema->id }}" {{ $bonusSchemaId == $schema->id ? 'selected' : '' }}>{{ $schema->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $shiftId = $rowData['days'][$d] ?? '';
                                        $timestamp = mktime(0,0,0,$month,$d,$year);
                                        $isWeekend = (date('D', $timestamp) == 'Sun');
                                    @endphp
                                    <td class="p-1 border-r border-slate-200 dark:border-slate-800 relative select-none transition-colors"
                                        @mouseenter="hoveredCol = {{ $d }}" @mouseleave="hoveredCol = null"
                                        :class="{ 
                                            'bg-slate-50/50 dark:bg-slate-900/30': hoveredCol === {{ $d }},
                                            'bg-rose-50/30 dark:bg-rose-950/10': {{ $isWeekend ? 'true' : 'false' }} && hoveredCol !== {{ $d }}
                                        }">
                                        <select id="sel_{{ $empId }}_{{ $d }}" 
                                            name="roster[{{ $empId }}][days][{{ $d }}]" 
                                            :disabled="!activeEmployeeIds.includes(String('{{ $empId }}'))"
                                            class="w-full h-8 min-w-[54px] text-center font-black text-[10.5px] rounded-lg border shadow-3xs focus:ring-2 focus:ring-indigo-500/40 cursor-pointer transition-all duration-150 p-0 text-slate-850 dark:text-slate-100 bg-transparent appearance-none hover:scale-[1.03] hover:shadow-2xs"
                                            :class="getCellColor('{{ $empId }}', {{ $d }}, '{{ $shiftId }}')"
                                            x-on:change="updateCellDisplay('{{ $empId }}', {{ $d }})">
                                            <option value="OFF" {{ !$shiftId || $shiftId == 'OFF' ? 'selected' : '' }}>OFF</option>
                                            @foreach($allShifts as $shift)
                                                <option value="{{ $shift->id }}" 
                                                    x-show="selectedShiftIds.map(Number).includes({{ $shift->id }})"
                                                    :disabled="!selectedShiftIds.map(Number).includes({{ $shift->id }})"
                                                    {{ $shiftId == $shift->id ? 'selected' : '' }}>
                                                    {{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 2 }}" class="p-8 text-center text-slate-500 text-sm">
                                    Tidak ada data pegawai di unit ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-200 dark:border-slate-800 flex justify-end items-center gap-3">
                <a href="{{ route('employee-working-shifts.index') }}" class="h-9 px-4 inline-flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 gap-1.5 cursor-pointer">
                    Batal
                </a>
                <button type="submit" class="h-9 px-4 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 dark:focus:ring-offset-slate-900 gap-1.5 cursor-pointer border-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    <span>Simpan Semua Jadwal</span>
                </button>
            <!-- Modal Kelola Pegawai Roster -->
            <div x-show="showAddEmployeeModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
                <div x-show="showAddEmployeeModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
                <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <div x-show="showAddEmployeeModal" @click.away="showAddEmployeeModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all w-full max-w-md border border-slate-200 dark:border-slate-800 flex flex-col max-h-[80vh]">
                            
                            <!-- Modal Header -->
                            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a6 6 0 0 0-3.44-5.32M19 19a1 1 0 0 0 1-1v-.72A6 6 0 0 0 16.56 12m-9 6.72a6 6 0 0 1 3.44-5.32M5 19a1 1 0 0 1-1-1v-.72a6 6 0 0 1 3.44-5.32M15 9.72a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Kelola Pegawai Roster</h3>
                                </div>
                                <button type="button" @click="showAddEmployeeModal = false" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 bg-transparent border-0 cursor-pointer flex items-center justify-center p-1 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="p-4 flex flex-col overflow-hidden min-h-[320px]">
                                <!-- Search Employee input in Modal -->
                                <div class="relative w-full mb-3 shrink-0">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input type="text" x-model="addEmployeeSearchQuery" placeholder="Cari nama pegawai..." 
                                        class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 dark:text-slate-100">
                                </div>

                                <!-- Employee List -->
                                <div class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scrollbar text-xs">
                                    @foreach($employees as $emp)
                                        @php
                                            $empId = $emp['id'];
                                        @endphp
                                        <label class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors cursor-pointer"
                                            x-show="addEmployeeSearchQuery.trim() === '' || '{{ addslashes(strtolower($emp['name'])) }}'.includes(addEmployeeSearchQuery.toLowerCase())">
                                            <div class="flex flex-col text-left min-w-0">
                                                <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $emp['name'] }}</span>
                                                <span class="text-[10px] text-slate-450 dark:text-slate-500 truncate">{{ ($emp['position'] ?? null) ?: (($emp['subject_position'] ?? null) ?: '-') }}</span>
                                            </div>
                                            <input type="checkbox" :value="String('{{ $empId }}')" x-model="activeEmployeeIds"
                                                class="rounded border-slate-350 text-indigo-650 w-4 h-4 cursor-pointer focus:ring-indigo-500">
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-100 dark:border-slate-800 flex justify-end shrink-0">
                                <button type="button" @click="showAddEmployeeModal = false" class="h-9 px-5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm text-xs cursor-pointer transition-colors font-sans">
                                    Selesai
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Kelola Shift Roster -->
            <div x-show="showAddShiftModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title-shift" role="dialog" aria-modal="true" x-cloak>
                <div x-show="showAddShiftModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
                <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <div x-show="showAddShiftModal" @click.away="showAddShiftModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all w-full max-w-md border border-slate-200 dark:border-slate-800 flex flex-col max-h-[80vh]">
                            
                            <!-- Modal Header -->
                            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="settings" class="w-5 h-5 text-emerald-650 dark:text-emerald-400"></i>
                                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Kelola Shift Roster</h3>
                                </div>
                                <button type="button" @click="showAddShiftModal = false" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 bg-transparent border-0 cursor-pointer flex items-center justify-center p-1 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="p-4 flex flex-col overflow-hidden min-h-[250px]">
                                <p class="text-[11px] text-slate-500 mb-3 text-left">Pilih shift mana saja yang ingin diaktifkan dan digunakan dalam pengisian tabel roster ini.</p>
                                
                                <!-- Shift List -->
                                <div class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scrollbar text-xs">
                                    <template x-for="sh in allAvailableShifts" :key="sh.id">
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors cursor-pointer">
                                            <div class="flex items-center gap-2 text-left min-w-0">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black shadow-xs" :class="sh.colorClass" x-text="sh.code"></span>
                                                <span class="font-bold text-slate-850 dark:text-slate-200 truncate" x-text="sh.name"></span>
                                            </div>
                                            <input type="checkbox" :value="sh.id" x-model="selectedShiftIds"
                                                class="rounded border-slate-350 text-indigo-650 w-4 h-4 cursor-pointer focus:ring-indigo-500">
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-100 dark:border-slate-800 flex justify-end shrink-0">
                                <button type="button" @click="showAddShiftModal = false" class="h-9 px-5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm text-xs cursor-pointer transition-colors font-sans">
                                    Selesai
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
</div>

<script>
    // Shift Data for JavaScript
    const shiftsData = {
        @foreach($allShifts as $shift)
            '{{ $shift->id }}': { code: '{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}', color: '{{ $shift->color }} dark:bg-opacity-20' },
        @endforeach
    };

    document.addEventListener('alpine:init', () => {
        Alpine.data('rosterGrid', () => ({
            searchQuery: '',
            hoveredCol: null,
            cellsData: {},
            showLegend: false,
            showAddEmployeeModal: false,
            showAddShiftModal: false,
            addEmployeeSearchQuery: '',
            activeEmployeeIds: @json(array_values(array_map('strval', !empty($empIdsParam) ? $empIdsParam : ($assignedEmployeeIds ?? [])))),
            allAvailableShifts: [
                @foreach($allShifts as $shift)
                    {
                        id: {{ $shift->id }},
                        code: '{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}',
                        name: '{{ addslashes($shift->name) }}',
                        colorClass: 'shift-color-{{ $shift->id }}'
                    },
                @endforeach
            ],
            selectedShiftIds: @json(array_map('intval', $shifts->pluck('id')->toArray())),

            init() {},

            getSelectedShiftsList() {
                return this.allAvailableShifts.filter(sh => this.selectedShiftIds.map(Number).includes(sh.id));
            },

            getCellRef(empId, day) {
                return document.getElementById('sel_' + empId + '_' + day);
            },

            getCellColor(empId, day, initialVal) {
                const val = this.cellsData[empId + '_' + day] !== undefined ? this.cellsData[empId + '_' + day] : initialVal;
                if (!val || val === 'OFF') return 'bg-slate-100/80 text-slate-500 border-slate-200/60 dark:bg-slate-800/40 dark:text-slate-400 dark:border-slate-700/60';
                return (shiftsData[val]?.color || '') + ' border-transparent';
            },

            updateCellDisplay(empId, day) {
                const ref = this.getCellRef(empId, day);
                if (ref) {
                    this.cellsData[empId + '_' + day] = ref.value;
                }
            }
        }))
    });
</script>
</x-admin-layout>