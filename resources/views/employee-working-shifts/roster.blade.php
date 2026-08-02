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
    </style>

<div class="space-y-6" x-data="rosterGrid()">
    
    <!-- Header & Back Button -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('employee-working-shifts.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">{{ $rosterName ?: 'Roster Shift Bulanan' }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur jadwal shift harian secara visual seperti di kalender atau Excel.</p>
                @if ($errors->any())
                    <div class="mt-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <strong class="font-bold">Error!</strong>
                        <ul class="list-disc ml-5 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-start gap-3">
            <div class="p-1 bg-emerald-100 dark:bg-emerald-800 rounded-full text-emerald-600 dark:text-emerald-400 mt-0.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-xl flex items-start gap-3">
            <div class="p-1 bg-rose-100 dark:bg-rose-800 rounded-full text-rose-600 dark:text-rose-400 mt-0.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Controls -->
    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-4">
        <form method="GET" action="{{ route('employee-working-shifts.roster') }}" id="filterForm">
            @if(request('emp_ids'))
                @foreach(request('emp_ids') as $empId)
                    <input type="hidden" name="emp_ids[]" value="{{ $empId }}">
                @endforeach
            @endif
            <input type="hidden" name="roster_name" value="{{ $rosterName ?? request('roster_name') }}">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Unit Sekolah</label>
                    <select name="unit_id" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer" onchange="document.getElementById('filterForm').submit()">
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Bulan</label>
                    <select name="month" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer" onchange="document.getElementById('filterForm').submit()">
                        @php
                            $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        @endphp
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ $bulanIndo[$i] }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Tahun</label>
                    <input type="number" name="year" value="{{ $year }}" class="w-24 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="document.getElementById('filterForm').submit()">
                </div>
                
                <div class="relative" x-data="{ openFilter: false }">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Tampilkan Shift</label>
                    <button type="button" @click="openFilter = !openFilter" class="w-48 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg flex items-center justify-between text-sm text-slate-900 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                        <span class="font-medium whitespace-nowrap">{{ count($selectedShiftIds) > 0 ? count($selectedShiftIds) . ' Shift Terpilih' : 'Semua Shift' }}</span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="openFilter" style="display: none; width: 500px; max-width: 90vw;" @click.away="openFilter = false" class="absolute top-full left-0 mt-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-50 p-3">
                        <div class="gap-2 max-h-60 overflow-y-auto custom-scrollbar p-1" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));">
                            @foreach($allShifts as $s)
                                <label class="flex items-start gap-3 cursor-pointer p-2 hover:bg-slate-50 dark:hover:bg-slate-900 rounded-lg transition-colors border border-transparent hover:border-slate-100 dark:hover:border-slate-800">
                                    <div class="pt-0.5">
                                        <input type="checkbox" name="shift_ids[]" value="{{ $s->id }}" {{ in_array($s->id, $selectedShiftIds) || count($selectedShiftIds) == 0 ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 dark:bg-slate-800 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-slate-900">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 leading-tight">{{ $s->name }}</span>
                                        <span class="text-[10px] text-slate-500 mt-0.5">Kode: {{ $s->short_code ?: $s->code }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Instructions -->
        <div class="mt-6 mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-left text-blue-800 dark:text-blue-200 text-xs">
                <h5 class="font-bold mb-1">Panduan Pengisian Jadwal (Roster)</h5>
                <ul class="list-disc pl-4 space-y-1">
                    <li><strong>Mengisi Shift:</strong> Klik pada sel tanggal yang ingin diisi. Sel akan berganti shift secara berurutan sesuai dengan daftar di "Tampilkan Shift".</li>
                    <li><strong>Meliburkan Hari (OFF):</strong> Klik terus pada sel hingga muncul tanda <strong>"OFF"</strong>. Saat disimpan, hari tersebut akan dicatat sebagai libur/kosong.</li>
                    <li><strong>Hari Minggu:</strong> Sudah otomatis berwarna merah muda sebagai penanda hari libur, Anda tidak perlu mengisinya kecuali jika ada shift khusus.</li>
                </ul>
            </div>
        </div>

        <!-- Legend -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/60">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Keterangan Shift</h3>
            <div class="flex flex-wrap gap-2">

                @foreach($shifts as $index => $shift)
                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/50 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800">
                        <div class="px-1.5 rounded-sm {{ $shift->color }} text-[10px] font-bold shadow-sm">{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}</div>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $index + 1 }}: {{ $shift->name }}</span>
                    </div>
                @endforeach
                
                <!-- Legend Libur / Kosong -->
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/50 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800">
                    <div class="px-1.5 rounded-sm bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700 text-[10px] font-bold shadow-sm">OFF</div>
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Libur / Kosong</span>
                </div>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ route('employee-working-shifts.update-roster') }}">
        @csrf
        <input type="hidden" name="school_unit_id" value="{{ $selectedUnitId }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="old_roster_name" value="{{ $oldRosterName ?? '' }}">
        
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <!-- Table Tools -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
                <div class="relative w-full max-w-sm">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama pegawai untuk disaring..." class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 dark:text-slate-100">
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
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors group" x-show="'{{ addslashes(strtolower($emp['name'])) }}'.includes(searchQuery.toLowerCase())">
                                <td class="p-3 border-r border-slate-200 dark:border-slate-800 sticky left-0 z-10 bg-white dark:bg-slate-950 group-hover:bg-slate-50 dark:group-hover:bg-slate-900 shadow-[1px_0_0_0_rgba(226,232,240,1)] dark:shadow-[1px_0_0_0_rgba(30,41,59,1)]">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm whitespace-nowrap">{{ $emp['name'] }}</div>
                                </td>
                                <td class="p-2 border-r border-slate-200 dark:border-slate-800">
                                    <select name="roster[{{ $empId }}][bonus_schema_id]" class="w-full text-xs px-2 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500">
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
                                    <td class="p-0 border-r border-slate-200 dark:border-slate-800 relative select-none transition-colors"
                                        @mouseenter="hoveredCol = {{ $d }}" @mouseleave="hoveredCol = null"
                                        :class="{ 'bg-slate-50/50 dark:bg-slate-900/30': hoveredCol === {{ $d }} }">
                                        <div class="w-full h-full min-w-[40px] min-h-[40px] flex items-center justify-center cursor-pointer transition-colors"
                                            :class="getCellColor('{{ $empId }}', {{ $d }}, '{{ $shiftId }}', {{ $isWeekend ? 'true' : 'false' }})"
                                            @click="applyBrush('{{ $empId }}', {{ $d }})"
                                            title="Klik untuk ubah">
                                            
                                            <!-- Hidden select for actual form submission -->
                                            <select x-ref="sel_{{ $empId }}_{{ $d }}" name="roster[{{ $empId }}][days][{{ $d }}]" class="hidden" x-on:change="updateCellDisplay('{{ $empId }}', {{ $d }})">
                                                <option value=""></option>
                                                @foreach($shifts as $shift)
                                                    <option value="{{ $shift->id }}" {{ $shiftId == $shift->id ? 'selected' : '' }}>{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}</option>
                                                @endforeach
                                                <option value="OFF">OFF</option>
                                            </select>
                                            
                                            <span x-text="getCellCode('{{ $empId }}', {{ $d }}, '{{ $shiftId }}')" class="text-[10px] font-bold"></span>
                                        </div>
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
            <div class="p-4 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center gap-4">
                <div class="flex-1 max-w-sm">
                    <label for="roster_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Roster <span class="text-rose-500">*</span></label>
                    <input type="text" name="roster_name" id="roster_name" value="{{ $rosterName ?? '' }}" required placeholder="Contoh: Roster Satpam" class="w-full h-10 px-3 text-sm bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm">
                </div>
                <div class="flex items-end gap-3 h-full pt-5">
                    <a href="{{ route('employee-working-shifts.index') }}" onclick="return confirm('Apakah Anda yakin ingin membatalkan? Semua perubahan jadwal yang belum disimpan akan hilang.');" class="h-10 px-6 inline-flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 gap-2">
                        Batal
                    </a>
                    <button type="submit" class="h-10 px-6 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 dark:focus:ring-offset-slate-900 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Semua Jadwal
                    </button>
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
            activeBrush: null, // null means no brush active, acts as normal click cycle
            isDragging: false,
            cellsData: {},

            init() {
                // Initialize cellsData from DOM if needed, but we can just read from refs directly for performance
                // Add window event to stop drag if mouse leaves table
                window.addEventListener('mouseup', () => { this.stopDrag(); });
                
                // Keyboard events removed
            },

            getCellRef(empId, day) {
                return this.$refs['sel_' + empId + '_' + day];
            },

            getVal(empId, day, initialVal) {
                const ref = this.getCellRef(empId, day);
                return ref ? ref.value : initialVal;
            },

            setVal(empId, day, val) {
                const ref = this.getCellRef(empId, day);
                if (ref && ref.value !== val) {
                    ref.value = val;
                    // Trigger alpine reactivity hack
                    this.cellsData[empId + '_' + day] = val;
                }
            },

            applyBrush(empId, day) {
                // Pure cycle logic
                const ref = this.getCellRef(empId, day);
                if (ref) {
                    const opts = Array.from(ref.options);
                    const currIndex = opts.findIndex(o => o.selected);
                    let nextIndex = currIndex + 1;
                    if (nextIndex >= opts.length) nextIndex = 0;
                    this.setVal(empId, day, opts[nextIndex].value);
                }
            },

            getCellCode(empId, day, initialVal) {
                // Reactive dependency
                const val = this.cellsData[empId + '_' + day] !== undefined ? this.cellsData[empId + '_' + day] : initialVal;
                if (!val || val === 'OFF') return 'OFF';
                return shiftsData[val]?.code || '-';
            },

            getCellColor(empId, day, initialVal, isWeekend) {
                const val = this.cellsData[empId + '_' + day] !== undefined ? this.cellsData[empId + '_' + day] : initialVal;
                if (!val || val === 'OFF') return 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                return shiftsData[val]?.color || '';
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