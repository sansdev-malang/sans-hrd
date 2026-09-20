<x-admin-layout>
    <div class="p-6 space-y-6 text-left" x-data="{
        searchQuery: '{{ request('search', '') }}',
        activeTab: '{{ $activeTab }}',
        selectedUnit: '{{ $unitId ?? '' }}'
    }">
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full">
            <div class="flex flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization">
                        Jadwal Piket Unit
                    </h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wider shrink-0 font-sans">
                        Read-Only (Unit Sync)
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Monitoring matriks penugasan jadwal piket guru dan riwayat tukar piket yang dikelola oleh masing-masing unit sekolah.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <form action="{{ route('picket-schedules.sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="h-9 px-3.5 inline-flex items-center gap-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs rounded-xl shadow-2xs transition-all hover:scale-[1.02] cursor-pointer">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Sinkronkan Data Unit</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- INFO BANNER -->
        <div class="p-3.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 flex items-start gap-3 text-xs text-indigo-900 dark:text-indigo-200 leading-relaxed">
            <i data-lucide="info" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5"></i>
            <div>
                <strong>Informasi Manajemen Piket:</strong> Jadwal piket dibuat dan dikelola langsung oleh admin/kepala sekolah pada masing-masing unit sekolah (SD, SMP, PAUD). Panel HRD Pusat menampilkan matriks jadwal ini untuk memantau penugasan dan evaluasi batas jam kehadiran <strong>06:30</strong> serta bonus kedisiplinan.
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Penugasan</p>
                        <h4 class="text-xl font-bold text-slate-900 dark:text-slate-50 mt-1 font-mono">{{ $stats['total_assignments'] }}</h4>
                    </div>
                    <div class="p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Guru Bertugas</p>
                        <h4 class="text-xl font-bold text-slate-900 dark:text-slate-50 mt-1 font-mono">{{ $stats['total_teachers'] }}</h4>
                    </div>
                    <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Area Piket</p>
                        <h4 class="text-xl font-bold text-slate-900 dark:text-slate-50 mt-1 font-mono">{{ $stats['total_areas'] }}</h4>
                    </div>
                    <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tukar Piket Aktif</p>
                        <h4 class="text-xl font-bold text-slate-900 dark:text-slate-50 mt-1 font-mono">{{ $stats['total_swaps'] }}</h4>
                    </div>
                    <div class="p-2.5 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400">
                        <i data-lucide="repeat" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTROLS & FILTER SECTION -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-4">
            <!-- Tabs & Search Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                <!-- Main Tab Navigation -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('picket-schedules.index', ['tab' => 'schedules', 'unit_id' => $unitId]) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $activeTab === 'schedules' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                        <span>Matriks Jadwal Piket</span>
                    </a>
                    <a href="{{ route('picket-schedules.index', ['tab' => 'swaps', 'unit_id' => $unitId]) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $activeTab === 'swaps' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <i data-lucide="repeat" class="w-3.5 h-3.5"></i>
                        <span>Riwayat Tukar Piket ({{ $swaps->count() }})</span>
                    </a>
                </div>

                <!-- Search Input with Live Alpine Highlight -->
                <div class="relative w-full md:max-w-xs">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama guru piket..." class="w-full text-xs pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                </div>
            </div>

            <!-- Unit Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 mr-1 flex items-center gap-1.5">
                    <i data-lucide="school" class="w-3.5 h-3.5"></i>
                    <span>Pilih Unit Sekolah:</span>
                </span>
                @foreach($activeUnits as $u)
                    <a href="{{ route('picket-schedules.index', ['tab' => $activeTab, 'unit_id' => $u->id]) }}" class="px-3.5 py-1.5 rounded-xl text-xs transition-all inline-flex items-center gap-1.5 {{ (string)$unitId === (string)$u->id ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 font-semibold' }}">
                        <span class="w-2 h-2 rounded-full {{ (string)$unitId === (string)$u->id ? 'bg-white' : 'bg-slate-400 dark:bg-slate-500' }}"></span>
                        <span>{{ $u->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- TAB 1: MATRIX BOARD VIEW PER UNIT -->
        @if($activeTab === 'schedules')
            <div class="space-y-8">
                @forelse($unitBoards as $board)
                    @php
                        $unitModel = $board['unit'];
                        $areas = $board['areas'];
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                        <!-- Unit Card Header -->
                        <div class="p-4 bg-slate-50/80 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-xs">
                                    {{ strtoupper(substr($unitModel->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                        <span>Matriks Jadwal Piket — {{ $unitModel->name }}</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Jam Kedatangan Piket: <strong class="text-amber-600 dark:text-amber-400 font-mono">06:30 WIB</strong> • Total {{ count($areas) }} Area Penugasan
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200/40 dark:border-indigo-800/40">
                                    {{ $board['total_schedules'] }} Petugas Terjadwal
                                </span>
                            </div>
                        </div>

                        <!-- DESKTOP BOARD MATRIX TABLE -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/40 dark:bg-slate-950/40 border-b border-slate-200 dark:border-slate-800">
                                        <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 min-w-[220px] max-w-xs w-72">
                                            Area & Tupoksi Piket
                                        </th>
                                        @foreach($days as $dayNum => $dayName)
                                            <th class="px-3 py-3.5 text-center font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 min-w-[150px]">
                                                {{ $dayName }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/70">
                                    @forelse($areas as $area)
                                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-colors">
                                            <!-- Area & Tupoksi Column -->
                                            <td class="px-4 py-4 align-top font-medium text-slate-800 dark:text-slate-200 border-r border-slate-100 dark:border-slate-800/50 space-y-3">
                                                <div class="flex items-start gap-2.5">
                                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100/20 shadow-2xs mt-0.5">
                                                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-slate-900 dark:text-slate-100 text-xs">{{ $area['name'] }}</span>
                                                        <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-bold mt-0.5 flex items-center gap-1">
                                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                                            {{ $area['duty_hours'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Tupoksi Under Area -->
                                                @if(!empty($area['jobs']))
                                                    <div class="p-2.5 rounded-xl bg-slate-50/80 dark:bg-slate-950/70 border border-slate-200/60 dark:border-slate-800/80 text-[10.5px] text-slate-600 dark:text-slate-400 leading-relaxed space-y-1.5">
                                                        <div class="text-[9.5px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                                            <i data-lucide="clipboard-list" class="w-3 h-3 text-indigo-500"></i>
                                                            <span>Tupoksi:</span>
                                                        </div>
                                                        @foreach(explode("\n", $area['jobs']) as $job)
                                                            @if(trim($job))
                                                                <div class="flex items-start gap-1.5">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5 shrink-0"></span>
                                                                    <p class="text-slate-600 dark:text-slate-300 leading-tight">{{ trim($job) }}</p>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Days Columns (1 to 6) -->
                                            @foreach($days as $dayNum => $dayName)
                                                <td class="px-3 py-4 align-top border-r border-slate-100 dark:border-slate-800/40">
                                                    <div class="flex flex-col gap-2">
                                                        @php
                                                            $daySchedules = $area['days'][$dayNum] ?? [];
                                                        @endphp
                                                        @forelse($daySchedules as $schedule)
                                                            <!-- Teacher Card Pill -->
                                                            <div class="p-2.5 border rounded-xl flex items-center gap-2.5 transition-all shadow-2xs"
                                                                :class="searchQuery.trim() !== '' && '{{ addslashes(strtolower($schedule['employee_name'] ?? '')) }}'.includes(searchQuery.toLowerCase())
                                                                    ? 'bg-indigo-500/10 border-indigo-500 dark:border-indigo-500 text-indigo-700 dark:text-indigo-300 scale-[1.03] shadow-xs'
                                                                    : 'bg-slate-50/70 dark:bg-slate-950/60 border-slate-200/60 dark:border-slate-800/80 text-slate-700 dark:text-slate-300'">
                                                                
                                                                <!-- Avatar -->
                                                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] flex items-center justify-center shrink-0 border border-indigo-200/40">
                                                                    {{ strtoupper(substr($schedule['employee_name'] ?? 'G', 0, 1)) }}
                                                                </div>

                                                                <div class="flex flex-col leading-tight min-w-0 flex-1">
                                                                    <span class="font-bold text-[11px] truncate text-slate-900 dark:text-slate-100"
                                                                        :class="searchQuery.trim() !== '' && '{{ addslashes(strtolower($schedule['employee_name'] ?? '')) }}'.includes(searchQuery.toLowerCase()) ? 'text-indigo-600 dark:text-indigo-400' : ''">
                                                                        {{ $schedule['employee_name'] ?? '-' }}
                                                                    </span>
                                                                    <span class="text-[9px] text-slate-400 font-medium truncate mt-0.5">
                                                                        {{ $schedule['employee_position'] ?? 'Guru / Pegawai' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="text-center py-3 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-slate-400 dark:text-slate-600 text-[10px] select-none font-medium">
                                                                Kosong
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-12 text-slate-400 dark:text-slate-500">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <i data-lucide="calendar-x" class="w-8 h-8 opacity-30"></i>
                                                    <p class="text-xs font-medium">Belum ada data area & jadwal piket untuk unit {{ $unitModel->name }}.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-400 dark:text-slate-500">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i data-lucide="school" class="w-10 h-10 opacity-30"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Belum ada unit sekolah aktif yang memiliki jadwal piket.</p>
                            <p class="text-xs text-slate-400">Silakan klik "Sinkronkan Data Unit" atau pastikan jadwal telah diinput di panel unit masing-masing.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        <!-- TAB 2: RIWAYAT TUKAR PIKET -->
        @if($activeTab === 'swaps')
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                <div class="p-4 bg-slate-50/80 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <i data-lucide="repeat" class="w-4 h-4 text-indigo-500"></i>
                        <span>Log Riwayat Permohonan & Persetujuan Tukar Piket</span>
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200/40">
                        Total {{ $swaps->count() }} Data
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50/40 dark:bg-slate-950/40 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">Unit Sekolah</th>
                                <th class="px-4 py-3">Guru Pemohon & Tanggal Asal</th>
                                <th class="px-4 py-3">Guru Pengganti & Tanggal Tugas</th>
                                <th class="px-4 py-3">Status Persetujuan</th>
                                <th class="px-4 py-3">Alasan / Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                            @forelse($swaps as $index => $swap)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-4 py-3.5 text-center text-slate-400 font-mono text-[11px]">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/40">
                                            {{ $swap['unit_name'] ?? 'Unit' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="space-y-0.5">
                                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $swap['requester_name'] ?? '-' }}</div>
                                            <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                                <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                                {{ !empty($swap['requested_date']) ? \Carbon\Carbon::parse($swap['requested_date'])->translatedFormat('d M Y') : '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="space-y-0.5">
                                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $swap['target_employee_name'] ?? '-' }}</div>
                                            <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                                <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                                {{ !empty($swap['target_date']) ? \Carbon\Carbon::parse($swap['target_date'])->translatedFormat('d M Y') : '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if(($swap['status'] ?? '') === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                <span>Disetujui Admin/Kepsek</span>
                                            </span>
                                        @elseif(($swap['status'] ?? '') === 'approved_by_target')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span>Menunggu Persetujuan Admin</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ ucfirst($swap['status'] ?? 'pending') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 text-xs italic">
                                        {{ $swap['notes'] ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <i data-lucide="repeat" class="w-8 h-8 opacity-40"></i>
                                            <p class="font-medium text-xs">Belum ada riwayat tukar piket.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
