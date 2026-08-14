<x-admin-layout>
    <style>
        [x-cloak] { display: none !important; }
        
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 10px !important;
            height: 10px !important;
            display: block !important;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc !important;
            border-radius: 8px !important;
            border: 1px solid #e2e8f0;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1 !important;
            border-radius: 8px !important;
            border: 2px solid #f8fafc !important;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8 !important;
        }

        .dark .custom-scrollbar {
            scrollbar-color: #475569 #0f172a;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #0f172a !important;
            border: 1px solid #1e293b;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #475569 !important;
            border: 2px solid #0f172a !important;
        }
        
        /* Hide scrollbars for layout containers but keep table scrollable */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .dark input[type="date"],
        html.dark input[type="date"],
        [class*="dark"] input[type="date"] {
            color-scheme: dark !important;
        }
        .dark input[type="date"]::-webkit-calendar-picker-indicator,
        html.dark input[type="date"]::-webkit-calendar-picker-indicator,
        [class*="dark"] input[type="date"]::-webkit-calendar-picker-indicator {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='4' rx='2' ry='2'/%3E%3Cline x1='16' x2='16' y1='2' y2='6'/%3E%3Cline x1='8' x2='8' y1='2' y2='6'/%3E%3Cline x1='3' x2='21' y1='10' y2='10'/%3E%3C/svg%3E") !important;
            filter: none !important;
            -webkit-filter: none !important;
            cursor: pointer;
        }
        tr:hover .group-name-link {
            color: #4f46e5 !important; /* indigo-600 */
            text-decoration: underline;
        }
        .dark tr:hover .group-name-link {
            color: #818cf8 !important; /* indigo-400 */
        }
    </style>

    @php
        $totalGroups = $batches instanceof \Illuminate\Pagination\LengthAwarePaginator ? $batches->total() : count($batches);
        
        // Count from current paginated items for statistics display
        $currentItems = $batches instanceof \Illuminate\Pagination\LengthAwarePaginator ? $batches->items() : $batches;
        $rosterCount = collect($currentItems)->filter(fn($b) => isset($b['type']) && $b['type'] === 'roster')->count();
        $permanentCount = collect($currentItems)->filter(fn($b) => !isset($b['type']) || $b['type'] !== 'roster')->count();
        $totalEmployeesScheduled = collect($currentItems)->flatMap(fn($b) => $b['employees'] ?? [])->unique('id')->count();
    @endphp

    <div class="p-6 space-y-6 relative animate-fade-in" x-data="{
        showModal: false,
        selectedBatch: null,
        searchModal: '',
        showFilters: {{ request()->filled('unit_id') || request()->filled('search') ? 'true' : 'false' }},
    
        showCreateModal: false,
        createUnitId: '{{ $units->first()->id ?? '' }}',
        empList: [],
        loadingEmp: false,
        empSearch: '',
        selectedEmps: [],
        selectAllEmp: false,
        showAssignmentModal: false,
    
        openModal(batch) {
            this.selectedBatch = batch;
            this.searchModal = '';
            this.showModal = true;
        },
    
        async loadEmployeesForUnit() {
            if (!this.createUnitId) return;
            this.loadingEmp = true;
            try {
                const response = await fetch(`/employee-working-shifts/unit/${this.createUnitId}/employees`);
                if (response.ok) {
                    this.empList = await response.json();
                } else {
                    this.empList = [];
                }
            } catch (e) {
                console.error(e);
                this.empList = [];
            }
            this.loadingEmp = false;
        },
    
        init() {
            this.$watch('selectAllEmp', value => {
                if (value) {
                    this.selectedEmps = this.empList.map(e => e.id);
                } else {
                    this.selectedEmps = [];
                }
            });
            this.$watch('showCreateModal', value => {
                if (!value) {
                    this.createUnitId = '{{ $units->first()->id ?? '' }}';
                    this.empList = [];
                    this.empSearch = '';
                    this.selectedEmps = [];
                    this.selectAllEmp = false;
                }
            });
        }
    }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization">Jadwal Kerja Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur penugasan dan rotasi shift kerja secara kolektif per unit sekolah.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="showCreateModal = true; loadEmployeesForUnit()"
                    class="h-9 px-4 inline-flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-650 dark:text-indigo-400 text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-all cursor-pointer gap-2">
                    <i data-lucide="calendar-range" class="w-4.5 h-4.5"></i>
                    Atur Jadwal Bergilir (Roster)
                </button>
                <button type="button" @click="showAssignmentModal = true"
                    class="h-9 px-4 inline-flex items-center justify-center bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer gap-2">
                    <i data-lucide="calendar-check" class="w-4.5 h-4.5"></i>
                    Tugaskan Jadwal Tetap (Batch)
                </button>
            </div>
        </header>

        <!-- SUMMARY STATISTICS CARDS -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full text-left">
            <!-- Total Grup Penugasan -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 rounded-xl p-5 shadow-sm flex items-center justify-between text-left hover:-translate-y-0.5 hover:shadow-md transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block">Total Grup Penugasan</span>
                    <h4 class="text-2xl font-black font-mono text-slate-900 dark:text-slate-50 leading-none">{{ $totalGroups }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Aktif terdaftar</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Roster Bulanan -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 rounded-xl p-5 shadow-sm flex items-center justify-between text-left hover:-translate-y-0.5 hover:shadow-md transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-wider block">Roster Bulanan</span>
                    <h4 class="text-2xl font-black font-mono text-indigo-600 dark:text-indigo-400 leading-none">{{ $rosterCount }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Halaman saat ini</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="calendar-range" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Shift Kerja Tetap -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 rounded-xl p-5 shadow-sm flex items-center justify-between text-left hover:-translate-y-0.5 hover:shadow-md transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-wider block">Shift Permanen</span>
                    <h4 class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 leading-none">{{ $permanentCount }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Tanpa batas tanggal</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Pegawai Terjadwal -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 rounded-xl p-5 shadow-sm flex items-center justify-between text-left hover:-translate-y-0.5 hover:shadow-md transition-all duration-250">
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-amber-500 uppercase tracking-wider block font-bold">Pegawai Terjadwal</span>
                    <h4 class="text-2xl font-black font-mono text-amber-550 leading-none">{{ $totalEmployeesScheduled }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Orang terdistribusi</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-550">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
        </section>

        <!-- FILTERS & SEARCH (MODERN COMMAND BAR STYLE) -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('employee-working-shifts.index') }}" class="space-y-4">
                <!-- TOP BAR: Unified Search + Toggle Filters -->
                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 justify-between">
                    <!-- Search Input -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex-1 flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                        <div class="pl-3 text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" x-model="searchVal" x-ref="searchInput" placeholder="Cari nama grup roster, nama shift, kode, atau unit..."
                            style="border: none !important; outline: none !important; box-shadow: none !important;"
                            class="w-full h-10 px-2.5 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-550 focus:ring-0">
                        
                        <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $refs.searchInput.focus();" class="h-10 px-2.5 text-slate-400 hover:text-slate-650 dark:hover:text-slate-250 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Action Bar -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Toggle Button for Advanced Filters -->
                        <button type="button" @click="showFilters = !showFilters" 
                            :class="showFilters ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-400 dark:border-indigo-900/30' : 'bg-white text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-355 dark:border-slate-800'"
                            class="h-10 px-4 flex items-center gap-2 text-xs font-bold border rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850 transition-all cursor-pointer">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span>Filter Lanjutan</span>
                            @if(request()->hasAny(['unit_id', 'search']) && (request('unit_id') || request('search')))
                                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-black bg-indigo-600 text-white leading-none">
                                     {{ count(array_filter(request()->only(['unit_id', 'search']))) }}
                                 </span>
                            @endif
                        </button>

                        <button type="submit" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="filter" class="w-4.5 h-4.5"></i>
                            Terapkan
                        </button>

                        @if(request()->hasAny(['unit_id', 'search', 'per_page']) && count(request()->except('page')) > 0)
                            <a href="{{ route('employee-working-shifts.index') }}" class="inline-flex items-center justify-center h-10 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg shadow-sm transition-colors gap-1.5">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                Reset
                            </a>
                        @endif
                    </div>
                </div>

                <!-- COLLAPSIBLE ADVANCED FILTER PANEL -->
                <div x-cloak x-show="showFilters" x-collapse class="pt-4 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Filter Unit -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Unit Sekolah</label>
                            <select name="unit_id" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                <option value="">Semua Unit Sekolah</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Per Page -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tampilkan Baris</label>
                            <select name="per_page" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 baris</option>
                                <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                                <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                                <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500 baris</option>
                                <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <!-- TABLE SECTION -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left w-full">
            <div class="px-6 py-3.5 border-b border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <span class="text-[11px] font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i data-lucide="table-properties" class="w-4 h-4 text-slate-500"></i>
                    Daftar Distribusi Penjadwalan Kerja
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border border-indigo-150/30 shrink-0 self-start sm:self-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    Klik nama jadwal atau jumlah pegawai untuk melihat rincian
                </span>
            </div>

            <!-- INDEPENDENT SCROLLABLE BODY CONTAINER -->
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar w-full max-h-[440px] relative">
                <table class="w-full text-xs border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] sticky top-0 z-10">
                        <tr>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-left w-[32%] whitespace-nowrap">Grup Penugasan</th>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-left w-[18%] whitespace-nowrap">Unit Sekolah</th>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-center w-[25%] whitespace-nowrap">Periode Jadwal</th>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-center w-[13%] whitespace-nowrap">Jumlah Pegawai</th>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-right w-[12%] whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($batches as $batch)
                            @if (isset($batch['type']) && $batch['type'] == 'roster')
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 text-left">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100/20 dark:border-indigo-900/10">
                                                <i data-lucide="calendar-range" class="w-4 h-4"></i>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <span @click="openModal({{ json_encode($batch) }})" class="font-semibold text-slate-800 dark:text-slate-200 text-xs cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors duration-150" title="Klik untuk melihat rincian daftar pegawai">{{ !empty($batch['roster_name']) ? $batch['roster_name'] : 'Roster Shift Bulanan' }}</span>
                                                <span class="inline-flex w-max px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wide">Roster Bulanan</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-left whitespace-nowrap">
                                        <span class="font-semibold text-slate-700 dark:text-slate-350 text-xs">{{ $batch['unit_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-xs font-semibold text-slate-900 dark:text-slate-200">{{ \Carbon\Carbon::create($batch['year'], $batch['month'], 1)->translatedFormat('F Y') }}</span>
                                            <span class="px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[9px] font-bold uppercase tracking-wider border border-blue-100/30 dark:border-blue-900/30">Roster</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div @click="openModal({{ json_encode($batch) }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-100/30 dark:border-indigo-900/30 cursor-pointer shadow-2xs font-bold text-xs whitespace-nowrap transition-colors" title="Klik untuk melihat rincian daftar pegawai">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            <span>{{ count($batch['employees']) }} Orang</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('employee-working-shifts.detail-roster', [
                                                'unit_id' => $batch['school_unit_id'],
                                                'month' => $batch['month'],
                                                'year' => $batch['year'],
                                                'roster_name' => $batch['roster_name'] ?? '',
                                            ]) }}"
                                                class="h-8 w-8 inline-flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-600 dark:hover:bg-indigo-650 hover:text-white text-indigo-700 dark:text-indigo-400 rounded-lg border border-indigo-200/30 dark:border-indigo-900/30 transition-all hover:-translate-y-0.5 hover:shadow-sm cursor-pointer"
                                                title="Lihat Detail Roster">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('employee-working-shifts.roster', [
                                                'unit_id' => $batch['school_unit_id'],
                                                'month' => $batch['month'],
                                                'year' => $batch['year'],
                                                'roster_name' => $batch['roster_name'] ?? '',
                                            ]) }}"
                                                class="h-8 w-8 inline-flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-sm"
                                                title="Edit Roster">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
                                            <form action="{{ route('employee-working-shifts.destroy-roster') }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus roster bulanan ini?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="unit_id"
                                                    value="{{ $batch['school_unit_id'] }}">
                                                <input type="hidden" name="month" value="{{ $batch['month'] }}">
                                                <input type="hidden" name="year" value="{{ $batch['year'] }}">
                                                <input type="hidden" name="roster_name"
                                                    value="{{ $batch['roster_name'] ?? '' }}">
                                                <button type="submit"
                                                    class="h-8 w-8 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-450 hover:text-white rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all hover:-translate-y-0.5 hover:shadow-sm cursor-pointer"
                                                    title="Hapus Roster">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 text-left">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100/20 dark:border-emerald-900/10">
                                                <i data-lucide="clock" class="w-4 h-4"></i>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <span @click="openModal({{ json_encode($batch) }})" class="font-semibold text-slate-800 dark:text-slate-200 text-xs cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors duration-150" title="Klik untuk melihat rincian daftar pegawai">{{ $batch['shift_name'] }}</span>
                                                <span class="inline-flex w-max px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-455 border border-emerald-100/30 dark:border-emerald-900/30 uppercase tracking-wide">Kode: {{ $batch['shift_code'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-left whitespace-nowrap">
                                        <span class="font-semibold text-slate-700 dark:text-slate-350 text-xs">{{ $batch['unit_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-900 dark:text-slate-200">
                                            <span>{{ \Carbon\Carbon::parse($batch['start_date'])->translatedFormat('d M Y') }}</span>
                                            <span class="text-slate-455 dark:text-slate-500 font-normal text-[10px]">s/d</span>
                                            @if ($batch['end_date'])
                                                <span>{{ \Carbon\Carbon::parse($batch['end_date'])->translatedFormat('d M Y') }}</span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-[9px] font-bold uppercase tracking-wider border border-emerald-100/30 dark:border-emerald-900/30 whitespace-nowrap">Seterusnya</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div @click="openModal({{ json_encode($batch) }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-100/30 dark:border-indigo-900/30 cursor-pointer shadow-2xs font-bold text-xs whitespace-nowrap transition-colors" title="Klik untuk melihat rincian daftar pegawai">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            <span>{{ count($batch['employees']) }} Orang</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('employee-working-shifts.edit-batch', [
                                                'unit_id' => $batch['school_unit_id'],
                                                'shift_id' => $batch['working_shift_id'],
                                                'start_date' => \Carbon\Carbon::parse($batch['start_date'])->format('Y-m-d'),
                                                'end_date' => $batch['end_date'] ? \Carbon\Carbon::parse($batch['end_date'])->format('Y-m-d') : 'null',
                                            ]) }}"
                                                class="h-8 w-8 inline-flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-sm"
                                                title="Edit Penugasan">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
 
                                            <form action="{{ route('employee-working-shifts.destroy-batch') }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus seluruh penugasan shift pada grup ini?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="unit_id"
                                                    value="{{ $batch['school_unit_id'] }}">
                                                <input type="hidden" name="shift_id"
                                                    value="{{ $batch['working_shift_id'] }}">
                                                <input type="hidden" name="start_date"
                                                    value="{{ \Carbon\Carbon::parse($batch['start_date'])->format('Y-m-d') }}">
                                                <input type="hidden" name="end_date"
                                                    value="{{ $batch['end_date'] ? \Carbon\Carbon::parse($batch['end_date'])->format('Y-m-d') : 'null' }}">
 
                                                <button type="submit"
                                                    class="h-8 w-8 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-450 hover:text-white rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all hover:-translate-y-0.5 hover:shadow-sm cursor-pointer"
                                                    title="Hapus Penugasan">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3 text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-450">
                                            <i data-lucide="calendar-x" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Belum ada jadwal shift</h4>
                                            <p class="text-xs mt-1">Silakan tugaskan shift baru untuk pegawai Anda.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($batches instanceof \Illuminate\Pagination\LengthAwarePaginator && $batches->total() > 0)
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium font-semibold">
                        Menampilkan
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $batches->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $batches->lastItem() }}</span>
                        dari
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $batches->total() }}</span>
                        data jadwal kerja
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        @if ($batches->onFirstPage())
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none font-semibold">Sebelumnya</span>
                        @else
                            <a href="{{ $batches->appends(request()->query())->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 font-semibold shadow-2xs">Sebelumnya</a>
                        @endif
 
                        <span class="px-2 font-bold text-slate-500 dark:text-slate-400">
                            Halaman {{ $batches->currentPage() }} dari {{ $batches->lastPage() }}
                        </span>
 
                        @if ($batches->hasMorePages())
                            <a href="{{ $batches->appends(request()->query())->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 font-semibold shadow-2xs">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none font-semibold">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- MODAL DETAIL PEGAWAI (SLIDE-OVER / CENTERING WITH BLUR AND PROPER BACKDROP) -->
        <template x-teleport="body">
            <div x-cloak x-show="showModal" 
                 class="fixed inset-0 z-[9999] overflow-hidden" style="display: none;">
                
                <!-- Backdrop overlay -->
                <div x-show="showModal" 
                     x-transition:enter="ease-in-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in-out duration-300" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     @click="showModal = false"
                     class="fixed inset-0 transition-opacity" 
                     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);" 
                     aria-hidden="true"></div>

                <!-- Content Panel -->
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                    <div x-show="showModal" 
                         x-transition:enter="transform transition ease-in-out duration-300" 
                         x-transition:enter-start="translate-x-full" 
                         x-transition:enter-end="translate-x-0" 
                         x-transition:leave="transform transition ease-in-out duration-300" 
                         x-transition:leave-start="translate-x-0" 
                         x-transition:leave-end="translate-x-full" 
                         class="w-screen max-w-md bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col justify-between text-left">
                        
                        <!-- Header -->
                        <div class="px-6 py-5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex justify-between items-start shrink-0">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                    Daftar Pegawai Terjadwal
                                </h3>
                                 <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 leading-relaxed">
                                     Jadwal: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedBatch?.type === 'roster' ? (selectedBatch?.roster_name || 'Roster Shift Bulanan') : (selectedBatch?.shift_name || '-')"></span><br>
                                     Unit: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedBatch?.unit_name"></span>
                                 </p>
                            </div>
                            <button type="button" @click="showModal = false" class="p-1 rounded-lg text-slate-400 dark:text-slate-500 hover:text-slate-650 dark:hover:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-850 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4 flex flex-col min-h-0">
                            <!-- Search -->
                            <div class="relative flex items-center shrink-0 w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-450 dark:text-slate-550 absolute left-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                <input type="text" x-model="searchModal" x-ref="searchModalInput" placeholder="Cari nama pegawai..."
                                    class="w-full text-xs px-3.5 py-2.5 pl-9 pr-9 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all">
                                <button type="button" x-show="searchModal.trim() !== ''" @click="searchModal = ''; $refs.searchModalInput.focus();" class="absolute right-3 h-8 px-1 text-slate-400 hover:text-slate-650 dark:hover:text-slate-350 transition-colors border-0 bg-transparent cursor-pointer flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>

                            <!-- List -->
                            <div class="flex-1 overflow-y-auto custom-scrollbar pr-1 -mr-1">
                                <div class="grid grid-cols-1 gap-2.5">
                                    <template x-for="emp in (selectedBatch?.employees || []).filter(e => e.name.toLowerCase().includes(searchModal.toLowerCase()) || (e.nip && e.nip.toLowerCase().includes(searchModal.toLowerCase())))" :key="emp.id">
                                        <div class="flex items-center gap-3.5 p-3.5 bg-slate-50/50 dark:bg-slate-900/30 border border-slate-200/60 dark:border-slate-800/80 rounded-xl transition-all hover:-translate-y-0.5 hover:shadow-xs hover:border-slate-350 dark:hover:border-slate-700">
                                             <!-- Avatar -->
                                             <div class="w-10 h-10 rounded-full overflow-hidden bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-indigo-100/25 dark:border-indigo-900/10 shadow-3xs">
                                                 <template x-if="emp.photo && emp.unit_url">
                                                     <img :src="emp.photo.includes('photos/') ? emp.unit_url.replace(/\/$/, '') + '/storage/' + emp.photo : emp.unit_url.replace(/\/$/, '') + '/storage/photos/' + emp.photo" 
                                                          class="w-full h-full object-cover">
                                                 </template>
                                                 <template x-if="!emp.photo || !emp.unit_url">
                                                     <span x-text="emp.name.substring(0, 2).toUpperCase()"></span>
                                                 </template>
                                             </div>
                                             <!-- Identity Info -->
                                             <div class="flex flex-col min-w-0">
                                                 <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="emp.name"></span>
                                                 <span class="text-[10px] text-slate-500 dark:text-slate-455 mt-0.5 truncate" x-text="emp.position || emp.subject_position || '-'"></span>
                                             </div>
                                        </div>
                                    </template>

                                    <div x-show="(selectedBatch?.employees || []).filter(e => e.name.toLowerCase().includes(searchModal.toLowerCase()) || (e.nip && e.nip.toLowerCase().includes(searchModal.toLowerCase()))).length === 0"
                                         class="py-16 text-center text-xs text-slate-450 dark:text-slate-500 italic">
                                        Pegawai tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                            <span class="text-[11px] font-bold text-slate-450 dark:text-slate-550" x-text="`Total: ${selectedBatch?.employees?.length || 0} Pegawai`"></span>
                            <button type="button"
                                class="h-9 px-4 inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-250 text-white dark:text-slate-900 text-xs font-bold shadow-sm transition-colors cursor-pointer"
                                @click="showModal = false">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- MODAL TUGASKAN SHIFT BARU -->
        <template x-teleport="body">
            <div x-cloak x-show="showAssignmentModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showAssignmentModal = false"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4 text-left" style="display: none;">
                
                <!-- Backdrop overlay -->
                <div class="fixed inset-0 transition-opacity" 
                     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);" 
                     @click="showAssignmentModal = false"></div>
                
                <!-- Content Box -->
                <div x-show="showAssignmentModal"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full sm:max-w-4xl rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden text-left text-xs z-10"
                     x-data="{ 
                         selectedUnit: '', 
                         workingShiftId: '',
                         bonusSchemaId: '',
                         startDate: '',
                         endDate: '',
                         employees: [], 
                         searchQuery: '', 
                         isLoadingEmployees: false, 
                         selectedEmps: [], 
                         selectAll: false, 
                         selectedPosition: '',
                         async fetchEmployees() { 
                             if (!this.selectedUnit) { this.employees = []; return } 
                             this.isLoadingEmployees = true; 
                             try { 
                                 let r = await fetch('/employee-working-shifts/unit/' + this.selectedUnit + '/employees');
                                 this.employees = await r.json();
                                 this.selectedEmps = [];
                                 this.selectAll = false;
                                 this.searchQuery = '';
                                 this.selectedPosition = '';
                             } finally { 
                                 this.isLoadingEmployees = false; 
                             } 
                         }, 
                         get uniquePositions() {
                             const posSet = new Set();
                             this.employees.forEach(e => {
                                 const p = e.position || e.subject_position || 'Staf';
                                 if (p) posSet.add(p);
                             });
                             return Array.from(posSet).sort();
                         },
                         get filteredEmployees() { 
                             return this.employees.filter(e => {
                                 const matchesSearch = !this.searchQuery || e.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                                 const pos = e.position || e.subject_position || 'Staf';
                                 const matchesPosition = !this.selectedPosition || pos === this.selectedPosition;
                                 return matchesSearch && matchesPosition;
                             });
                         },
                         toggleAll() {
                             const filteredIds = this.filteredEmployees.map(e => e.id);
                             if (this.selectAll) {
                                 this.selectedEmps = Array.from(new Set([...this.selectedEmps, ...filteredIds]));
                             } else {
                                 this.selectedEmps = this.selectedEmps.filter(id => !filteredIds.includes(id));
                             }
                         }
                     }"
                     x-init="$watch('showAssignmentModal', value => {
                         if (!value) {
                             selectedUnit = '';
                             workingShiftId = '';
                             bonusSchemaId = '';
                             startDate = '';
                             endDate = '';
                             employees = [];
                             searchQuery = '';
                             selectedPosition = '';
                             selectedEmps = [];
                             selectAll = false;
                         }
                     })">
                    
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                        <div>
                             <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                                 <span>Tugaskan Jadwal Tetap (Batch) Baru</span>
                             </h3>
                             <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Pilih unit, pegawai, dan template jam kerja tetap yang akan ditugaskan.</p>
                         </div>
                         <button type="button" @click="showAssignmentModal = false" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 bg-transparent border-0 cursor-pointer flex items-center justify-center p-1 rounded-lg">
                             <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                         </button>
                    </div>

                    <form method="POST" action="{{ route('employee-working-shifts.store') }}" 
                          @submit.prevent="if (selectedEmps.length === 0) { alert('Silakan centang minimal satu pegawai sebelum menyimpan penugasan jadwal tetap.'); } else { $el.submit(); }"
                          class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-4">
                                    <!-- Unit Sekolah -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Unit Sekolah</label>
                                        <select name="school_unit_id" required x-model="selectedUnit" @change="fetchEmployees()"
                                            class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                            <option value="">Pilih Unit Sekolah</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Template Jadwal Tetap -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Template Jadwal Tetap</label>
                                            <select name="working_shift_id" required x-model="workingShiftId"
                                                class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                                <option value="">Pilih Template Jadwal Tetap</option>
                                                @foreach ($shifts as $shift)
                                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Bonus Schema -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Skema Bonus (Opsional)</label>
                                            <select name="bonus_schema_id" x-model="bonusSchemaId"
                                                class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                                <option value="">Ikuti Default/Aktif</option>
                                                @if (isset($bonusSchemas))
                                                    @foreach ($bonusSchemas as $schema)
                                                        <option value="{{ $schema->id }}">{{ $schema->name }} {{ $schema->is_active ? '(Aktif)' : '' }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Mulai</label>
                                            <input type="date" name="start_date" required x-model="startDate"
                                                x-bind:style="document.documentElement.classList.contains('dark') ? 'color-scheme: dark !important;' : ''"
                                                class="text-xs w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono dark:[color-scheme:dark]">
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Selesai (Opsional)</label>
                                            <input type="date" name="end_date" x-model="endDate"
                                                x-bind:style="document.documentElement.classList.contains('dark') ? 'color-scheme: dark !important;' : ''"
                                                class="text-xs w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono dark:[color-scheme:dark]">
                                        </div>
                                    </div>

                                    <!-- Panduan Alur Penugasan (Split & Resume) -->
                                    <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100/60 dark:border-indigo-900/40 rounded-xl space-y-2 mt-4 text-left">
                                        <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-[11px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                            <span>Panduan Alur Penugasan (Split & Resume)</span>
                                        </div>
                                        <ul class="list-disc pl-4 space-y-2 text-[10px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                            <li>
                                                <span><strong>Penugasan Sementara (Overlap)</strong>: Jika pegawai memiliki jadwal aktif permanen, jadwal tersebut akan dipotong secara otomatis berakhir pada <span class="text-indigo-600 dark:text-indigo-400 font-semibold">H-1</span> tanggal mulai baru.</span>
                                            </li>
                                            <li>
                                                <span><strong>Kelanjutan Otomatis (Resume)</strong>: Jika penugasan sementara selesai (rentang tanggal selesai diisi), jadwal permanen yang digantikan akan otomatis dilanjutkan kembali pada <span class="text-indigo-600 dark:text-indigo-400 font-semibold">H+1</span> setelah tanggal selesai berakhir.</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Pegawai List -->
                                <div class="flex flex-col h-full">
                                    <label class="font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex justify-between items-center shrink-0">
                                        <span>
                                            Pilih Pegawai
                                            <span x-show="isLoadingEmployees" class="text-[10px] text-indigo-500 ml-2 animate-pulse font-normal">Memuat data...</span>
                                        </span>
                                        <label x-show="employees.length > 0" class="flex items-center gap-1.5 cursor-pointer text-[11px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer">
                                            Pilih Semua
                                        </label>
                                    </label>

                                    <div class="flex flex-col bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex-1 min-h-[280px]">
                                        <!-- Search & Filter Bar -->
                                        <div class="p-2 border-b border-slate-200/60 dark:border-slate-800/80 shrink-0 bg-white dark:bg-slate-900/40 space-y-2">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <!-- Search Input -->
                                                <div class="relative flex items-center bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 pointer-events-none"></i>
                                                    <input type="text" x-model="searchQuery" x-ref="searchQueryInput" :disabled="employees.length === 0" placeholder="Cari nama..."
                                                        style="border: none !important; outline: none !important; box-shadow: none !important;"
                                                        class="text-xs w-full h-8 px-2 pl-8 bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-0">
                                                    <button type="button" x-show="searchQuery.trim() !== ''" @click="searchQuery = ''; $refs.searchQueryInput.focus();" class="h-8 px-2 text-slate-400 hover:text-slate-600 transition-colors border-0 bg-transparent cursor-pointer flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                    </button>
                                                </div>
                                                
                                                <!-- Position Filter -->
                                                <select x-model="selectedPosition" :disabled="employees.length === 0"
                                                    class="text-xs h-8 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                                    <option value="">Semua Jabatan</option>
                                                    <template x-for="pos in uniquePositions" :key="pos">
                                                        <option :value="pos" x-text="pos"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- List -->
                                        <div class="p-3 space-y-2 custom-scrollbar overflow-y-auto max-h-[250px] flex-1">
                                            <div x-show="employees.length === 0 && !isLoadingEmployees" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-500 italic text-[11px] py-12">
                                                Silakan pilih unit sekolah terlebih dahulu.
                                            </div>

                                            <div x-show="employees.length > 0 && filteredEmployees.length === 0" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-500 italic text-[11px] py-12">
                                                Pegawai tidak ditemukan.
                                            </div>
                                            
                                            <template x-for="emp in filteredEmployees" :key="emp.id">
                                                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-900/80 border border-slate-200 dark:border-slate-900 rounded-xl transition-all shadow-2xs hover:border-slate-300 dark:hover:border-slate-800">
                                                    <input type="checkbox" name="employee_ids[]" :value="emp.id" x-model="selectedEmps"
                                                        class="employee-checkbox w-4.5 h-4.5 rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 shrink-0 cursor-pointer">
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="text-xs text-slate-900 dark:text-slate-100 font-bold leading-snug truncate" x-text="emp.name"></span>
                                                        <span class="text-[10px] text-slate-450 mt-0.5 truncate" x-text="emp.position || emp.subject_position || '-'"></span>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-5 border-t border-slate-100 dark:border-slate-850 flex gap-2.5 justify-end bg-slate-50 dark:bg-slate-900/40 shrink-0">
                             <button type="button" @click="showAssignmentModal = false"
                                 class="h-9 px-4 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold rounded-lg cursor-pointer transition-colors shadow-2xs hover:shadow-xs">
                                 Batal
                             </button>
                             <button type="submit"
                                 class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm transition-all cursor-pointer flex items-center justify-center dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:text-white">
                                 Simpan Penugasan Jadwal Tetap
                             </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL BUAT ROSTER BARU -->
        <template x-teleport="body">
            <div x-cloak x-show="showCreateModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showCreateModal = false"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4 text-left" style="display: none;">
                
                <!-- Backdrop overlay -->
                <div class="fixed inset-0 transition-opacity" 
                     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);" 
                     @click="showCreateModal = false"></div>
                
                <!-- Content Box -->
                <div x-show="showCreateModal"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full sm:max-w-2xl rounded-2xl bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden text-left text-xs z-10">
                    
                    <form action="{{ route('employee-working-shifts.roster') }}" method="GET" class="flex flex-col flex-1 overflow-hidden">
                        <!-- Header -->
                        <div class="p-5 border-b border-slate-100 dark:border-slate-855 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                             <div>
                                 <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-650 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                     <span>Buat Jadwal Bergilir (Roster) Baru</span>
                                 </h3>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Pilih grup pegawai yang akan dibuatkan jadwal roster.</p>
                            </div>
                            <button type="button" @click="showCreateModal = false" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 bg-transparent border-0 cursor-pointer flex items-center justify-center p-1 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unit Sekolah</label>
                                    <select x-model="createUnitId" name="unit_id" @change="loadEmployeesForUnit()"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Roster <span class="text-rose-500">*</span></label>
                                    <input type="text" name="roster_name" required placeholder="Misal: Roster Satpam"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bulan</label>
                                    <select name="month"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                        @php
                                            $bulanIndo = [
                                                '',
                                                'Januari',
                                                'Februari',
                                                'Maret',
                                                'April',
                                                'Mei',
                                                'Juni',
                                                'Juli',
                                                'Agustus',
                                                'September',
                                                'Oktober',
                                                'November',
                                                'Desember',
                                            ];
                                        @endphp
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>{{ $bulanIndo[$i] }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tahun</label>
                                    <input type="number" name="year" value="{{ date('Y') }}"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono">
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300">Pilih Pegawai</label>
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex items-center">
                                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2 pointer-events-none"></i>
                                            <input type="text" x-model="empSearch" placeholder="Cari..."
                                                class="w-32 pl-7 pr-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-[11px] focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-medium">
                                        </div>
                                        <button type="button" @click="selectAllEmp = !selectAllEmp"
                                            class="text-[11px] text-indigo-650 dark:text-indigo-400 font-bold hover:underline bg-transparent border-0 cursor-pointer">Pilih Semua</button>
                                    </div>
                                </div>
                                <div class="border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900 h-44 overflow-y-auto p-2.5 custom-scrollbar">
                                    <div x-show="loadingEmp" class="text-center py-8 text-xs text-slate-450 animate-pulse font-medium">
                                        Memuat data pegawai...
                                    </div>
                                    <div x-show="!loadingEmp && empList.length === 0" class="text-center py-8 text-xs text-slate-400 italic">
                                        Tidak ada pegawai di unit ini.
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <template x-for="emp in empList" :key="emp.id">
                                            <label x-show="empSearch === '' || emp.name.toLowerCase().includes(empSearch.toLowerCase())"
                                                class="flex items-center gap-2.5 p-2 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-xl cursor-pointer transition-all border border-transparent hover:border-slate-200/50 dark:hover:border-slate-850">
                                                <input type="checkbox" name="emp_ids[]" :value="emp.id" x-model="selectedEmps"
                                                    class="w-4.5 h-4.5 text-indigo-650 border-slate-350 rounded focus:ring-indigo-500 dark:focus:ring-offset-slate-900 cursor-pointer">
                                                <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold truncate" x-text="emp.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-5 border-t border-slate-100 dark:border-slate-850 flex items-center justify-end gap-2.5 bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button"
                                class="h-9 px-4 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-955 hover:bg-slate-50 dark:hover:bg-slate-900 font-bold rounded-lg cursor-pointer transition-colors shadow-2xs"
                                @click="showCreateModal = false">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-9 px-4 bg-indigo-650 hover:bg-indigo-705 text-white font-bold rounded-lg shadow-sm transition-colors cursor-pointer">
                                Lanjutkan ke Grid
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
</x-admin-layout>
