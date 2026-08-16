<x-admin-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @php
        $countTotal = count($reports);
        $countGreen = collect($reports)->filter(fn($r) => $r['percentage'] >= 95)->count();
        $countYellow = collect($reports)->filter(fn($r) => $r['percentage'] >= 90 && $r['percentage'] < 95)->count();
        $countRed = collect($reports)->filter(fn($r) => $r['percentage'] < 90)->count();
    @endphp

    <div class="p-6 space-y-6 relative animate-fade-in" x-data="{
        activeCategoryFilter: 'all',
        selectedReport: null,
        isDrawerOpen: false,
        openDrawer(report) {
            this.selectedReport = report;
            this.isDrawerOpen = true;
        },
        closeDrawer() {
            this.isDrawerOpen = false;
        }
    }" x-ref="container">
        
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Laporan Persentase Kehadiran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Analisis kehadiran pegawai dan pantau perbaikan performa kedisiplinan kerja secara komprehensif.</p>
            </div>
        </header>

        <!-- ZONA KATEGORI FILTER CARDS -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full text-left">
            <!-- Card 1: Semua Pegawai -->
            <button @click="activeCategoryFilter = 'all'" 
                :class="activeCategoryFilter === 'all' ? 'border-slate-900 dark:border-slate-100 ring-2 ring-slate-900/10 dark:ring-slate-100/10' : 'border-slate-200 dark:border-slate-850 hover:border-slate-400 dark:hover:border-slate-700'"
                class="bg-white dark:bg-slate-900 border rounded-xl p-5 shadow-sm flex items-center justify-between text-left cursor-pointer w-full hover:-translate-y-0.5 hover:shadow-md active:scale-98 transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Pegawai</span>
                    <h4 class="text-2xl font-black font-mono text-slate-900 dark:text-slate-50 leading-none">{{ $countTotal }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Seluruh unit terfilter</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </button>

            <!-- Card 2: Zona Hijau (Aman) -->
            <button @click="activeCategoryFilter = 'green'" 
                :class="activeCategoryFilter === 'green' ? 'border-emerald-600 dark:border-emerald-450 ring-2 ring-emerald-500/10' : 'border-slate-200 dark:border-slate-850 hover:border-emerald-500 dark:hover:border-emerald-600'"
                class="bg-white dark:bg-slate-900 border rounded-xl p-5 shadow-sm flex items-center justify-between text-left cursor-pointer w-full hover:-translate-y-0.5 hover:shadow-md active:scale-98 transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-wider block">Zona Aman (>= 95%)</span>
                    <h4 class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 leading-none">{{ $countGreen }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Performa sangat baik</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
            </button>

            <!-- Card 3: Zona Kuning (Waspada) -->
            <button @click="activeCategoryFilter = 'yellow'" 
                :class="activeCategoryFilter === 'yellow' ? 'border-amber-500 ring-2 ring-amber-500/10' : 'border-slate-200 dark:border-slate-850 hover:border-amber-550 dark:hover:border-amber-500'"
                class="bg-white dark:bg-slate-900 border rounded-xl p-5 shadow-sm flex items-center justify-between text-left cursor-pointer w-full hover:-translate-y-0.5 hover:shadow-md active:scale-98 transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-amber-500 uppercase tracking-wider block">Zona Waspada (90% - 94.9%)</span>
                    <h4 class="text-2xl font-black font-mono text-amber-500 leading-none">{{ $countYellow }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Perlu perhatian ringan</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-555">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
            </button>

            <!-- Card 4: Zona Merah (Evaluasi - Kritis) -->
            <button @click="activeCategoryFilter = 'red'" 
                :class="activeCategoryFilter === 'red' ? 'border-rose-600 dark:border-rose-455 ring-2 ring-rose-500/10' : 'border-slate-200 dark:border-slate-850 hover:border-rose-500 dark:hover:border-rose-600'"
                class="bg-white dark:bg-slate-900 border rounded-xl p-5 shadow-sm flex items-center justify-between text-left cursor-pointer w-full hover:-translate-y-0.5 hover:shadow-md active:scale-98 transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-rose-600 dark:text-rose-455 uppercase tracking-wider block">Zona Kritis (< 90%)</span>
                    <h4 class="text-2xl font-black font-mono text-rose-600 dark:text-rose-455 leading-none">{{ $countRed }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Memerlukan evaluasi kehadiran</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-rose-50 dark:bg-rose-950/40 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
            </button>
        </section>

        <!-- MODERN FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full text-left">
            <form method="GET" action="{{ route('attendance-percentage-reports.index') }}">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-end gap-3 w-full">
                    <!-- Search Input -->
                    <div class="flex-grow min-w-0">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Cari Pegawai</label>
                        <div x-data="{ searchVal: '{{ request('search') }}' }" class="w-full flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                            <div class="pl-3 text-slate-450 dark:text-slate-500">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="search" x-model="searchVal" x-ref="searchInput" placeholder="Cari nama pegawai..."
                                style="border: none !important; outline: none !important; box-shadow: none !important;"
                                class="w-full h-10 px-2.5 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-550 focus:ring-0">
                            
                            <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $refs.searchInput.focus();" class="h-10 px-2.5 text-slate-400 hover:text-slate-650 dark:hover:text-slate-250 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Mulai Tanggal -->
                    <div class="w-full lg:w-40 shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Mulai Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date', $startDateReq) }}" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer font-mono dark:[color-scheme:dark]">
                    </div>

                    <!-- Selesai Tanggal -->
                    <div class="w-full lg:w-40 shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Selesai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDateReq) }}" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer font-mono dark:[color-scheme:dark]">
                    </div>

                    <!-- Filter Unit -->
                    @if(isset($schoolUnits) && count($schoolUnits) > 0)
                    <div class="w-full lg:w-48 shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Unit Sekolah</label>
                        <select name="unit_id" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="">Semua Unit</option>
                            @foreach($schoolUnits as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Filter Jabatan -->
                    @if(isset($positions) && count($positions) > 0)
                    <div class="w-full lg:w-48 shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Jabatan / Posisi</label>
                        <select name="position" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="">Semua Jabatan</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>
                                    {{ $pos }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 justify-end w-full lg:w-auto lg:ml-auto pb-0.5">
                        <button type="submit" class="h-10 px-5 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                            Terapkan
                        </button>

                        @if(request()->hasAny(['unit_id', 'search', 'start_date', 'end_date', 'position']) && count(request()->except('page')) > 0)
                            <a href="{{ route('attendance-percentage-reports.index') }}" class="inline-flex items-center justify-center h-10 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg shadow-sm transition-colors gap-1.5">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </section>



        <!-- TABLE SECTION -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <!-- Table Header Legend -->
            <div class="px-6 py-3.5 border-b border-slate-150 dark:border-slate-800/80 bg-slate-500/5 dark:bg-slate-900/50 flex flex-wrap items-center justify-between gap-3 text-[11px] font-semibold text-slate-500">
                <span class="font-bold text-slate-900 dark:text-slate-150 flex items-center gap-2">
                    <i data-lucide="table-properties" class="w-4 h-4 text-slate-500"></i>
                    Tabel Evaluasi Kehadiran Pegawai
                </span>
                
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border border-indigo-150/30">
                    <i data-lucide="info" class="w-3.5 h-3.5 animate-bounce-slow"></i>
                    Klik baris pegawai untuk melihat rincian detail harian
                </span>
            </div>

            <!-- SCROLLABLE TABLE CONTAINER (RESTRICTED HEIGHT FOR INDEPENDENT SCROLLING) -->
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto block relative rounded-b-xl border-t border-slate-100 dark:border-slate-800/60 no-scrollbar">
                <table class="w-full text-xs">
                    <thead class="sticky top-0 z-10 border-b border-slate-200 dark:border-slate-800 shadow-xs">
                        <tr class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="px-6 py-3.5 text-center w-12 bg-slate-50 dark:bg-[#0d0d10]">No</th>
                            <th class="px-6 py-3.5 text-left bg-slate-50 dark:bg-[#0d0d10]">Pegawai</th>
                            <th class="px-6 py-3.5 text-center bg-slate-50 dark:bg-[#0d0d10]">Jadwal Kerja</th>
                            <th class="px-6 py-3.5 text-left bg-slate-50 dark:bg-[#0d0d10]">Rincian Absensi &amp; Izin</th>
                            <th class="px-6 py-3.5 text-right w-52 bg-slate-50 dark:bg-[#0d0d10]">Evaluasi Persentase</th>
                            <th class="px-6 py-3.5 text-center w-28 bg-slate-50 dark:bg-[#0d0d10]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-700 dark:text-slate-350 font-medium">
                        @forelse($reports as $index => $rep)
                            <tr data-report="{{ json_encode($rep) }}"
                                @click="openDrawer(JSON.parse($el.getAttribute('data-report')))"
                                x-show="activeCategoryFilter === 'all' || 
                                        (activeCategoryFilter === 'green' && {{ $rep['percentage'] }} >= 95) || 
                                        (activeCategoryFilter === 'yellow' && {{ $rep['percentage'] }} >= 90 && {{ $rep['percentage'] }} < 95) || 
                                        (activeCategoryFilter === 'red' && {{ $rep['percentage'] }} < 90)"
                                class="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition-all cursor-pointer group">
                                
                                <td class="px-6 py-4 text-center text-slate-400 dark:text-slate-650 font-mono">{{ $index + 1 }}</td>
                                
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        @if($rep['employee']['photo'] ?? null)
                                            @php
                                                $photoPath = str_contains($rep['employee']['photo'], 'photos/') ? $rep['employee']['photo'] : 'photos/' . $rep['employee']['photo'];
                                                $photoUrl = rtrim($rep['employee']['unit_url'], '/') . '/storage/' . $photoPath;
                                            @endphp
                                            <img src="{{ $photoUrl }}" class="w-10 h-10 rounded-full object-cover shrink-0 ring-2 ring-slate-100 dark:ring-slate-800 shadow-xs">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center text-xs font-bold text-indigo-700 dark:text-indigo-400 shrink-0 border border-indigo-100/30 shadow-xs">
                                                {{ strtoupper(substr($rep['employee']['name'] ?? 'P', 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="space-y-0.5">
                                            <span class="text-slate-900 dark:text-slate-50 font-bold tracking-tight block group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $rep['employee']['name'] }}</span>
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                                <span class="inline-flex px-1.5 py-0.5 text-[8px] font-extrabold uppercase bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-md tracking-wider max-w-fit">
                                                    {{ strtoupper($rep['employee']['unit_name'] ?? '-') }}
                                                </span>
                                                <span class="text-[9px] text-slate-450 dark:text-slate-500 font-medium">
                                                    {{ $rep['employee']['position'] ?? $rep['employee']['subject_position'] ?? 'Staf' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="text-xs font-bold text-slate-900 dark:text-slate-50 font-mono">{{ $rep['total_work_days'] }} Hari</span>
                                        <span class="text-[9px] text-slate-400 dark:text-slate-500">Kerja Wajib</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-left">
                                    <div class="flex flex-wrap gap-1.5 items-center">
                                        @php
                                            $hasAbsenceData = $rep['total_present'] > 0 || $rep['total_sakit'] > 0 || $rep['total_izin'] > 0 || $rep['total_cuti'] > 0 || $rep['total_absent'] > 0;
                                        @endphp

                                        @if(!$hasAbsenceData || ($rep['total_present'] == $rep['total_work_days'] && $rep['total_absent'] == 0 && $rep['total_sakit'] == 0 && $rep['total_izin'] == 0 && $rep['total_cuti'] == 0))
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-100/50 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                🟢 Kehadiran 100% (Sempurna)
                                            </span>
                                        @else
                                            @if($rep['total_present'] > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30">
                                                    Hadir: {{ $rep['total_present'] }}
                                                </span>
                                            @endif
                                            @if($rep['total_sakit'] > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200/30 dark:border-red-900/30">
                                                    Sakit: {{ $rep['total_sakit'] }}
                                                </span>
                                            @endif
                                            @if($rep['total_izin'] > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30">
                                                    Izin: {{ $rep['total_izin'] }}
                                                </span>
                                            @endif
                                            @if($rep['total_cuti'] > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200/30 dark:border-blue-900/30">
                                                    Cuti: {{ $rep['total_cuti'] }}
                                                </span>
                                            @endif
                                            @if($rep['total_absent'] > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-450 border border-rose-200/30 dark:border-rose-900/30">
                                                    Alpa: {{ $rep['total_absent'] }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    @php
                                        $percent = $rep['percentage'];
                                        if ($percent >= 95) {
                                            $percentClass = 'text-emerald-600 dark:text-emerald-450';
                                            $barColorClass = 'bg-gradient-to-r from-emerald-400 to-emerald-600';
                                            $badgeTheme = 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200/20';
                                            $badgeText = 'Aman';
                                        } elseif ($percent >= 90) {
                                            $percentClass = 'text-amber-500 dark:text-amber-450';
                                            $barColorClass = 'bg-gradient-to-r from-amber-400 to-amber-500';
                                            $badgeTheme = 'bg-amber-50 dark:bg-amber-950/40 text-amber-750 dark:text-amber-400 border-amber-200/20';
                                            $badgeText = 'Pantauan';
                                        } else {
                                            $percentClass = 'text-rose-650 dark:text-rose-455';
                                            $barColorClass = 'bg-gradient-to-r from-rose-400 to-rose-600';
                                            $badgeTheme = 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-450 border-rose-200/20';
                                            $badgeText = 'Evaluasi';
                                        }
                                    @endphp
                                    <div class="inline-flex flex-col items-end gap-1.5 min-w-[130px]">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase border {{ $badgeTheme }}">
                                                {{ $badgeText }}
                                            </span>
                                            <span class="text-sm font-black font-mono {{ $percentClass }} tracking-wide">{{ $percent }}%</span>
                                        </div>
                                        <div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full {{ $barColorClass }} rounded-full transition-all duration-300" style="width: {{ min(100, max(0, $percent)) }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- EXPLICIT ACTION COLUMN FOR HIGH DISCOVERY -->
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-150/30 rounded-md group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-650 dark:group-hover:text-white transition-all duration-150">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        Detail
                                    </span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2.5">
                                        <i data-lucide="database" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="font-medium text-xs">Tidak ada data pegawai untuk kriteria pencarian ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SLIDE-OVER DRAWER (LACI DETAIL) - FULL VIEWPORT OVERLAY WITH FIXED STACKING -->
        <div x-cloak x-show="isDrawerOpen" class="fixed inset-0 z-[9999] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <!-- Backdrop overlay (Covering Sidebar & Topbar cleanly) -->
                <div x-show="isDrawerOpen" 
                     x-transition:enter="ease-in-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in-out duration-300" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     @click="closeDrawer()"
                     class="fixed inset-0 transition-opacity z-[9999]" 
                     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);" 
                     aria-hidden="true"></div>

                <!-- Content Panel (Fixed position viewport relative) -->
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex z-[9999]">
                    <div x-show="isDrawerOpen" 
                         x-transition:enter="transform transition ease-in-out duration-300 sm:duration-300" 
                         x-transition:enter-start="translate-x-full" 
                         x-transition:enter-end="translate-x-0" 
                         x-transition:leave="transform transition ease-in-out duration-300 sm:duration-300" 
                         x-transition:leave-start="translate-x-0" 
                         x-transition:leave-end="translate-x-full" 
                         class="w-screen max-w-md bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col justify-between text-left">
                        
                        <!-- Header -->
                        <div class="px-6 py-5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex justify-between items-start">
                            <div class="flex items-center gap-3" x-show="selectedReport !== null">
                                <!-- Profile Photo or Initial Avatar -->
                                <template x-if="selectedReport && selectedReport.employee.photo">
                                    <img :src="selectedReport.employee.photo.startsWith('photos/') 
                                                ? selectedReport.employee.unit_url.replace(/\/$/, '') + '/storage/' + selectedReport.employee.photo
                                                : selectedReport.employee.unit_url.replace(/\/$/, '') + '/storage/photos/' + selectedReport.employee.photo" 
                                         class="w-11 h-11 rounded-full object-cover shrink-0 ring-2 ring-slate-100 dark:ring-slate-800 shadow-sm">
                                </template>
                                <template x-if="!selectedReport || !selectedReport.employee.photo">
                                    <div class="w-11 h-11 rounded-full bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center text-sm font-bold text-indigo-700 dark:text-indigo-400 shrink-0 border border-indigo-100/30 shadow-sm" x-text="selectedReport ? selectedReport.employee.name.substr(0,2).toUpperCase() : ''"></div>
                                </template>

                                <div class="space-y-0.5">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 leading-tight" x-text="selectedReport ? selectedReport.employee.name : ''"></h3>
                                    <span class="text-[9px] text-slate-450 dark:text-slate-500 font-medium" x-text="selectedReport ? 'Jabatan: ' + (selectedReport.employee.position || selectedReport.employee.subject_position || 'Staf') : ''"></span>
                                </div>
                            </div>
                            <button @click="closeDrawer()" class="p-1 rounded-lg text-slate-450 hover:bg-slate-100 dark:hover:bg-slate-850 hover:text-slate-650 cursor-pointer">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <!-- Body (Timeline) -->
                        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                            <div class="flex justify-between items-center mb-4 bg-slate-50/50 dark:bg-slate-850/20 p-3 rounded-lg border border-slate-200/50 dark:border-slate-800">
                                <div>
                                    <span class="text-[9px] text-slate-400 uppercase tracking-wider block font-bold">Persentase Kehadiran</span>
                                    <span class="text-xl font-mono font-black" :class="selectedReport && selectedReport.percentage >= 95 ? 'text-emerald-600' : (selectedReport && selectedReport.percentage >= 90 ? 'text-amber-500' : 'text-rose-600')" x-text="selectedReport ? selectedReport.percentage + '%' : ''"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[9px] text-slate-400 uppercase tracking-wider block font-bold">Total Kehadiran</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100" x-text="selectedReport ? selectedReport.total_present + ' / ' + selectedReport.total_work_days + ' Hari Kerja' : ''"></span>
                                </div>
                            </div>

                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Histori Harian Absensi</h4>
                            
                            <div class="relative border-l border-slate-200 dark:border-slate-800 ml-2.5 pl-5.5 space-y-5 py-2">
                                <template x-if="selectedReport">
                                    <template x-for="day in selectedReport.day_details">
                                        <div class="relative">
                                            <!-- Bullet marker on line -->
                                            <span class="absolute -left-7.5 top-1.5 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-slate-900 flex items-center justify-center shrink-0 shadow-xs"
                                                  :class="{
                                                      'bg-emerald-500': day.color === 'emerald',
                                                      'bg-red-500': day.color === 'red',
                                                      'bg-amber-500': day.color === 'amber',
                                                      'bg-blue-500': day.color === 'blue',
                                                      'bg-indigo-500': day.color === 'indigo',
                                                      'bg-rose-500': day.color === 'rose',
                                                      'bg-slate-400': day.color === 'slate'
                                                  }"></span>
                                            
                                            <!-- Details Card -->
                                            <div class="p-3 rounded-lg border border-slate-200/50 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/40 text-xs flex flex-col justify-between gap-1 shadow-2xs">
                                                <div class="flex justify-between items-start gap-2">
                                                    <span class="font-bold text-slate-900 dark:text-slate-50" x-text="day.date"></span>
                                                    
                                                    <!-- Badge Status -->
                                                    <span x-text="day.label" 
                                                          :class="{
                                                              'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/20': day.color === 'emerald',
                                                              'bg-red-500/10 text-red-700 dark:bg-red-950/30 dark:text-red-400 border border-red-200/20': day.color === 'red',
                                                              'bg-amber-500/10 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/20': day.color === 'amber',
                                                              'bg-blue-500/10 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border border-blue-200/20': day.color === 'blue',
                                                              'bg-indigo-500/10 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 border border-indigo-200/20': day.color === 'indigo',
                                                              'bg-rose-500/10 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200/20': day.color === 'rose',
                                                              'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200/30': day.color === 'slate'
                                                          }"
                                                          class="px-2 py-0.5 rounded-[4px] text-[9px] font-black uppercase tracking-wider font-mono"></span>
                                                </div>
                                                <p class="text-[10px] text-slate-450 dark:text-slate-500 leading-normal" x-text="day.detail || 'Tidak ada detail khusus'"></p>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 text-right">
                            <button @click="closeDrawer()" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                                Tutup Detail
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
