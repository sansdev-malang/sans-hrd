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

        <div id="attendance-report-container" class="space-y-6">

        <!-- DUAL SUMMARY BANNERS: KEHADIRAN & KETERLAMBATAN PER UNIT (SEJAJAR KIRI-KANAN) -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 w-full text-left">
            <!-- BANNER 1: RATA-RATA KEHADIRAN PER UNIT -->
            @if(isset($unitStats) && count($unitStats) > 0)
                <section class="bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-xl p-4 w-full flex flex-col justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <div class="p-1.5 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-650 dark:text-indigo-400 rounded-lg shrink-0">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight">Rata-rata Kehadiran per Unit</h4>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Persentase rata-rata kehadiran pegawai terdaftar per unit</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 w-full pt-1">
                        @foreach($unitStats as $uId => $stat)
                            @if($stat['count'] > 0)
                                @php
                                    $avg = $stat['average'];
                                    if ($avg >= 95) {
                                        $indicatorColor = 'bg-emerald-500';
                                        $textColor = 'text-emerald-600 dark:text-emerald-450';
                                    } elseif ($avg >= 90) {
                                        $indicatorColor = 'bg-amber-500';
                                        $textColor = 'text-amber-555 dark:text-amber-450';
                                    } else {
                                        $indicatorColor = 'bg-rose-500';
                                        $textColor = 'text-rose-650 dark:text-rose-455';
                                    }
                                @endphp
                                <div class="flex flex-col justify-between p-2.5 bg-white dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/60 rounded-xl shadow-2xs">
                                    <div class="flex items-center justify-between gap-1 pb-1.5 border-b border-slate-100 dark:border-slate-800/60 mb-1.5">
                                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide truncate" title="{{ $stat['name'] }}">{{ $stat['name'] }}</span>
                                        <span class="w-1.5 h-1.5 rounded-full {{ $indicatorColor }} shrink-0"></span>
                                    </div>
                                    <div class="flex flex-col gap-0.5 mt-auto">
                                        <div class="text-sm font-black font-mono {{ $textColor }} leading-none">{{ $avg }}%</div>
                                        <div class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">{{ $stat['count'] }} pegawai</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- BANNER 2: PEMETAAN KETERLAMBATAN PER UNIT -->
            @if(isset($unitLateStats) && count($unitLateStats) > 0)
                <section class="bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-xl p-4 w-full flex flex-col justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <div class="p-1.5 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-lg shrink-0">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight">Pemetaan Keterlambatan per Unit</h4>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Rincian pegawai zona waspada (kuning) &amp; zona kritis (merah)</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 w-full pt-1">
                        @foreach($unitLateStats as $uId => $stat)
                            @if($stat['count'] > 0)
                                @php
                                    $unitStatusDot = $stat['kritis_staff_count'] > 0 ? 'bg-rose-500' : ($stat['waspada_staff_count'] > 0 ? 'bg-amber-500' : 'bg-emerald-500');
                                @endphp
                                <div class="flex flex-col justify-between p-2.5 bg-white dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/60 rounded-xl shadow-2xs">
                                    <!-- Header: Unit Name & Status Dot -->
                                    <div class="flex items-center justify-between gap-1 pb-1.5 border-b border-slate-100 dark:border-slate-800/60 mb-1.5">
                                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide truncate" title="{{ $stat['name'] }}">{{ $stat['name'] }}</span>
                                        <span class="w-1.5 h-1.5 rounded-full {{ $unitStatusDot }} shrink-0" title="{{ $stat['kritis_staff_count'] > 0 ? 'Terdapat pegawai kritis' : ($stat['waspada_staff_count'] > 0 ? 'Terdapat pegawai terlambat' : 'Semua tepat waktu') }}"></span>
                                    </div>

                                    <!-- 2 Zona: Waspada (Kuning) & Kritis (Merah) -->
                                    <div class="space-y-1 text-[10px] mt-auto">
                                        <!-- Zona Waspada (Kuning) -->
                                        <div class="flex items-center justify-between text-amber-600 dark:text-amber-450" title="Zona Waspada: {{ $stat['waspada_staff_count'] }} pegawai pernah terlambat <= 07:25 / ada izin disetujui">
                                            <span class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                                <span class="text-[9px] font-bold">Terlambat</span>
                                            </span>
                                            <span class="font-mono font-black text-[10.5px]">{{ $stat['waspada_staff_count'] }} <span class="text-[8px] font-normal text-slate-400">org</span></span>
                                        </div>

                                        <!-- Zona Kritis (Merah) -->
                                        <div class="flex items-center justify-between text-rose-600 dark:text-rose-450" title="Zona Kritis: {{ $stat['kritis_staff_count'] }} pegawai pernah terlambat > 07:25 tanpa izin disetujui">
                                            <span class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                                <span class="text-[9px] font-bold">Kritis</span>
                                            </span>
                                            <span class="font-mono font-black text-[10.5px]">{{ $stat['kritis_staff_count'] }} <span class="text-[8px] font-normal text-slate-400">org</span></span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

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
                    <h4 class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 leading-none">
                        {{ $countGreen }} <span class="text-xs font-bold text-emerald-500/80 dark:text-emerald-500 font-sans tracking-normal lowercase ml-1">pegawai</span>
                    </h4>
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
                    <h4 class="text-2xl font-black font-mono text-amber-500 leading-none">
                        {{ $countYellow }} <span class="text-xs font-bold text-amber-550/80 dark:text-amber-500 font-sans tracking-normal lowercase ml-1">pegawai</span>
                    </h4>
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
                    <h4 class="text-2xl font-black font-mono text-rose-600 dark:text-rose-455 leading-none">
                        {{ $countRed }} <span class="text-xs font-bold text-rose-500/80 dark:text-rose-500/80 font-sans tracking-normal lowercase ml-1">pegawai</span>
                    </h4>
                    <p class="text-[10px] text-slate-400 font-medium">Memerlukan evaluasi kehadiran</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-rose-50 dark:bg-rose-950/40 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
            </button>
        </section>

        <!-- MODERN FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full text-left">
            <form method="GET" action="{{ route('attendance-percentage-reports.index') }}" id="attendance-filter-form" data-no-loader="true">
                <input type="hidden" name="unit_id" id="filter-unit-id" value="{{ request('unit_id', $unitId ?? null) }}">

                <!-- Unit Pills Filter -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 border-b border-slate-150 dark:border-slate-800/60 w-full mb-4">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider shrink-0 mr-1.5 flex items-center gap-1">
                        <i data-lucide="school" class="w-3.5 h-3.5"></i>
                        Unit / Kategori:
                    </span>
                    
                    <!-- Semua Unit Pill -->
                    <button type="button" 
                            onclick="selectUnitFilter('', this)"
                            class="h-7 px-3.5 inline-flex items-center justify-center text-xs font-bold rounded-lg border transition-all cursor-pointer {{ empty(request('unit_id', $unitId ?? null)) ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-900' }}">
                        Semua Unit
                    </button>
                    
                    @foreach($schoolUnits as $unit)
                        <button type="button"
                                onclick="selectUnitFilter('{{ $unit->id }}', this)"
                                class="h-7 px-3.5 inline-flex items-center justify-center text-xs font-bold rounded-lg border transition-all cursor-pointer {{ request('unit_id', $unitId ?? null) == (string)$unit->id ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-900' }}">
                            {{ $unit->name }} {{ in_array(strtoupper($unit->name), ['SD', 'SMP']) ? '(Reguler)' : '' }}
                        </button>
                    @endforeach

                    <!-- GPK Pill -->
                    <button type="button"
                            onclick="selectUnitFilter('gpk', this)"
                            class="h-7 px-3.5 inline-flex items-center justify-center text-xs font-bold rounded-lg border transition-all cursor-pointer {{ request('unit_id', $unitId ?? null) === 'gpk' ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-900' }}">
                        GPK (SD-SMP)
                    </button>

                    <!-- GPQ Pill -->
                    <button type="button"
                            onclick="selectUnitFilter('gpq', this)"
                            class="h-7 px-3.5 inline-flex items-center justify-center text-xs font-bold rounded-lg border transition-all cursor-pointer {{ request('unit_id', $unitId ?? null) === 'gpq' ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-900' }}">
                        GPQ (SD-SMP)
                    </button>
                </div>
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
                    <div class="w-full lg:w-36 shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Mulai Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date', $startDateReq) }}" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer font-mono dark:[color-scheme:dark]">
                    </div>

                    <!-- Selesai Tanggal -->
                    <div class="w-full lg:w-36 shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Selesai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDateReq) }}" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer font-mono dark:[color-scheme:dark]">
                    </div>

                    <!-- Filter Jabatan (Multi-Select Checkbox) -->
                    @if(isset($positions) && count($positions) > 0)
                    <div class="w-full lg:w-56 shrink-0 relative" x-data="{
                        open: false,
                        selectedPositions: {{ json_encode($selectedPositions ?? []) }},
                        allPositions: {{ json_encode($positions ?? []) }},
                        togglePosition(pos) {
                            if (this.selectedPositions.includes(pos)) {
                                this.selectedPositions = this.selectedPositions.filter(p => p !== pos);
                            } else {
                                this.selectedPositions.push(pos);
                            }
                        },
                        selectAll() {
                            this.selectedPositions = [...this.allPositions];
                        },
                        clearAll() {
                            this.selectedPositions = [];
                        }
                    }" @click.outside="open = false">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Jabatan / Posisi</span>
                            <span x-show="selectedPositions.length > 0" class="text-indigo-600 dark:text-indigo-400 font-mono font-bold" x-text="selectedPositions.length + ' dipilih'"></span>
                        </label>
                        
                        <!-- Trigger Button -->
                        <button type="button" @click="open = !open" 
                                class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer flex items-center justify-between text-left shadow-2xs hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                            <span class="truncate pr-2 font-medium" x-text="selectedPositions.length === 0 ? 'Semua Jabatan' : (selectedPositions.length === 1 ? selectedPositions[0] : selectedPositions.length + ' Jabatan Terpilih')"></span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Hidden real inputs for form submission -->
                        <template x-for="pos in selectedPositions" :key="pos">
                            <input type="hidden" name="positions[]" :value="pos">
                        </template>

                        <!-- Dropdown Panel -->
                        <div x-show="open" x-transition.opacity.duration.150ms style="display: none;"
                             class="absolute z-50 left-0 mt-1 w-72 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-2.5 space-y-2">
                            
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 text-[11px]">
                                <span class="font-bold text-slate-700 dark:text-slate-300">Pilih Jabatan</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="selectAll()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-[10px] cursor-pointer border-0 bg-transparent">Pilih Semua</button>
                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                    <button type="button" @click="clearAll()" class="text-slate-400 hover:text-rose-500 font-bold text-[10px] cursor-pointer border-0 bg-transparent">Reset</button>
                                </div>
                            </div>

                            <div class="max-h-56 overflow-y-auto space-y-0.5 pr-1 custom-scrollbar">
                                @foreach($positions as $pos)
                                    <label class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer text-xs select-none transition-colors">
                                        <input type="checkbox" value="{{ $pos }}" 
                                               :checked="selectedPositions.includes('{{ $pos }}')"
                                               @change="togglePosition('{{ $pos }}')"
                                               class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                        <span class="text-slate-700 dark:text-slate-200 font-medium truncate">{{ $pos }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                                <button type="button" @click="open = false; triggerFilterForm()" class="h-7 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer border-0">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 justify-end w-full lg:w-auto lg:ml-auto pb-0.5">
                        <button type="submit" class="h-10 px-5 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                            Terapkan
                        </button>

                        @if(request()->hasAny(['unit_id', 'search', 'start_date', 'end_date', 'position', 'positions']) && count(request()->except('page')) > 0)
                            <a href="{{ route('attendance-percentage-reports.index') }}" class="inline-flex items-center justify-center h-10 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg shadow-sm transition-colors gap-1.5 reset-filter-btn" data-no-loader="true">
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
            <div class="px-6 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-500/5 dark:bg-slate-900/50 flex flex-wrap items-center justify-between gap-3 text-[11px] font-semibold text-slate-500">
                <span class="font-bold text-slate-900 dark:text-slate-200 flex items-center gap-2">
                    <i data-lucide="table-properties" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                    Tabel Evaluasi Kehadiran Pegawai
                </span>
                
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30">
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
                                                🟢 Hadir Penuh ({{ $rep['total_present'] }} Hari)
                                            </span>
                                            @if(($rep['waspada_late_count'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30">
                                                    Terlambat: {{ $rep['waspada_late_count'] }}x
                                                </span>
                                            @endif
                                            @if(($rep['kritis_late_count'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-450 border border-rose-200/30 dark:border-rose-900/30">
                                                    Mengkhawatirkan: {{ $rep['kritis_late_count'] }}x
                                                </span>
                                            @endif
                                        @else
                                            @if($rep['total_present'] > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30">
                                                    Hadir: {{ $rep['total_present'] }}
                                                </span>
                                            @endif
                                            @if(($rep['waspada_late_count'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30">
                                                    Terlambat: {{ $rep['waspada_late_count'] }}x
                                                </span>
                                            @endif
                                            @if(($rep['kritis_late_count'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-450 border border-rose-200/30 dark:border-rose-900/30">
                                                    Mengkhawatirkan: {{ $rep['kritis_late_count'] }}x
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
    </div>

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
                            <div class="grid grid-cols-3 gap-2 mb-4 bg-slate-100/90 dark:bg-slate-800/80 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/70 shadow-xs">
                                <div>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider block font-bold">Persentase</span>
                                    <span class="text-xl font-mono font-black" :class="selectedReport && selectedReport.percentage >= 95 ? 'text-emerald-600 dark:text-emerald-400' : (selectedReport && selectedReport.percentage >= 90 ? 'text-amber-500 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')" x-text="selectedReport ? selectedReport.percentage + '%' : ''"></span>
                                </div>
                                <div class="text-center">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider block font-bold">Keterlambatan</span>
                                    <span class="text-xs font-bold font-mono" :class="selectedReport && selectedReport.total_late_minutes > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300'" x-text="selectedReport ? (selectedReport.total_late_minutes > 0 ? selectedReport.total_late_minutes + ' mnt (' + selectedReport.late_count + 'x)' : '0 mnt') : ''"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider block font-bold">Total Kehadiran</span>
                                    <span class="text-xs font-bold font-mono text-slate-800 dark:text-slate-100" x-text="selectedReport ? selectedReport.total_present + ' / ' + selectedReport.total_work_days + ' Hari' : ''"></span>
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
                                            <div class="p-3.5 rounded-lg border border-slate-200/60 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/40 text-xs flex flex-col gap-2 shadow-2xs">
                                                <div class="flex justify-between items-start gap-2">
                                                    <span class="font-bold text-slate-900 dark:text-slate-50 text-xs" x-text="day.date"></span>
                                                    
                                                    <!-- Badge Status (HADIR, SAKIT, IZIN, dll.) -->
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
                                                          class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider font-mono"></span>
                                                </div>

                                                <div class="space-y-1 text-[11px] text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/60 pt-2 font-mono">
                                                    <!-- Baris 2: Jadwal -->
                                                    <div class="flex items-start gap-1">
                                                        <span class="text-slate-400 dark:text-slate-500 shrink-0 w-20">Jadwal</span>
                                                        <span class="text-slate-400 dark:text-slate-500">:</span>
                                                        <span class="text-slate-800 dark:text-slate-200 font-semibold" x-text="(day.shift_name && day.shift_name !== 'Non-Shift') ? day.shift_name + (day.shift_schedule ? ' (' + day.shift_schedule + ')' : '') : (day.status === 'Libur' ? 'Libur' : 'Non-Shift')"></span>
                                                    </div>

                                                    <!-- Baris 3: Masuk & Pulang (Saat Hadir) -->
                                                    <template x-if="day.status === 'Hadir'">
                                                        <div class="flex items-start gap-1">
                                                            <span class="text-slate-400 dark:text-slate-500 shrink-0 w-20">Masuk</span>
                                                            <span class="text-slate-400 dark:text-slate-500">:</span>
                                                            <span class="text-slate-800 dark:text-slate-200 font-medium" x-text="(day.in_time || '-') + '  •  Pulang : ' + (day.out_time || '-')"></span>
                                                        </div>
                                                    </template>

                                                    <!-- Baris 4: Terlambat (Saat Hadir) -->
                                                    <template x-if="day.status === 'Hadir'">
                                                        <div class="flex items-start gap-1">
                                                            <span class="text-slate-400 dark:text-slate-500 shrink-0 w-20">Terlambat</span>
                                                            <span class="text-slate-400 dark:text-slate-500">:</span>
                                                            <span :class="day.late_minutes > 0 ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400'" x-text="day.late_minutes > 0 ? day.late_minutes + ' mnt' : '-'"></span>
                                                        </div>
                                                    </template>

                                                    <!-- Keterangan (Saat Izin/Sakit/Cuti/Alpa/Libur) -->
                                                    <template x-if="day.status !== 'Hadir' && day.notes">
                                                        <div class="flex items-start gap-1">
                                                            <span class="text-slate-400 dark:text-slate-500 shrink-0 w-20">Keterangan</span>
                                                            <span class="text-slate-400 dark:text-slate-500">:</span>
                                                            <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="day.notes"></span>
                                                        </div>
                                                    </template>
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

<!-- AJAX NAVIGATION & FILTER SCRIPT -->
<script>
    function triggerFilterForm(el) {
        const form = document.getElementById('attendance-filter-form');
        if (form) {
            form.requestSubmit();
        }
    }

    function selectUnitFilter(unitId, btnEl) {
        const input = document.getElementById('filter-unit-id');
        if (input) {
            input.value = unitId;
            triggerFilterForm(btnEl);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('attendance-report-container');

        function loadTableContent(url) {
            if (container) {
                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';
            }
            
            if (typeof NProgress !== 'undefined') {
                NProgress.start();
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('attendance-report-container');
                
                if (newContent && container) {
                    container.innerHTML = newContent.innerHTML;
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';

                    // Reinitialize Lucide Icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    // Sync URL in address bar without reload
                    window.history.pushState({}, '', url);
                } else {
                    window.location.href = url;
                }
            })
            .catch(err => {
                console.error('AJAX loading failed:', err);
                window.location.href = url;
            })
            .finally(() => {
                if (typeof NProgress !== 'undefined') {
                    NProgress.done();
                }
                const globalLoader = document.getElementById('global-loading-overlay');
                if (globalLoader) {
                    globalLoader.classList.add('hidden');
                }
            });
        }

        // Delegate submit event
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('#attendance-filter-form');
            if (!form) return;

            e.preventDefault();
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const action = form.getAttribute('action') || window.location.pathname;
            const url = new URL(action, window.location.origin);
            url.search = params.toString();

            loadTableContent(url);
        });

        // Delegate click on reset/pagination links inside container
        document.addEventListener('click', function (e) {
            const link = e.target.closest('#attendance-report-container a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (href && (href.startsWith(window.location.origin) || href.startsWith('/'))) {
                e.preventDefault();
                loadTableContent(href);
            }
        });
    });
</script>
