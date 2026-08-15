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

    <div class="p-6 space-y-6 relative animate-fade-in" x-data="{
        showModal: false,
        selectedBatch: null,
        searchModal: '',

        formatDate(dateStr) {
            if (!dateStr) return '';
            const cleanDate = dateStr.substring(0, 10);
            const parts = cleanDate.split('-');
            if (parts.length !== 3) return dateStr;
            const day = parseInt(parts[2], 10);
            const monthIdx = parseInt(parts[1], 10) - 1;
            const year = parts[0];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${day} ${months[monthIdx] || ''} ${year}`;
        },
        getLocalYmd(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        formatRosterPeriod(month, year) {
            if (!month || !year) return '';
            const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const mIdx = parseInt(month, 10);
            return `${months[mIdx] || ''} ${year}`;
        },
    
        showCreateModal: false,
        createUnitId: '',
        createRosterName: '',
        createOldRosterName: '',
        createMonth: '{{ date('n') }}',
        createYear: '{{ date('Y') }}',
        empList: [],
        loadingEmp: false,
        empSearch: '',
        selectedEmps: [],
        selectAllEmp: false,
        createSelectedPosition: '',
        selectedShifts: {{ json_encode($shifts->where('is_shift', true)->pluck('id')->toArray()) }},

        toggleCreateAll() {
            const filteredIds = this.filteredCreateEmployees.map(e => e.id);
            if (this.selectAllEmp) {
                this.selectedEmps = Array.from(new Set([...this.selectedEmps, ...filteredIds]));
            } else {
                this.selectedEmps = this.selectedEmps.filter(id => !filteredIds.includes(id));
            }
        },

        get uniqueCreatePositions() {
            const posSet = new Set();
            this.empList.forEach(e => {
                const p = e.position || e.subject_position || '-';
                if (p) posSet.add(p);
            });
            return Array.from(posSet).sort();
        },

        get filteredCreateEmployees() { 
            return this.empList.filter(e => {
                const matchesSearch = !this.empSearch || e.name.toLowerCase().includes(this.empSearch.toLowerCase());
                const pos = e.position || e.subject_position || '-';
                const matchesPosition = !this.createSelectedPosition || pos === this.createSelectedPosition;
                return matchesSearch && matchesPosition;
            });
        },
        showAssignmentModal: false,
        showNeglectedModal: false,
        createShowError: false,
        assignShowError: false,
        editShowError: false,

        showEditModal: false,
        editUnitId: '',
        editWorkingShiftId: '',
        editBonusSchemaId: '',
        editStartDate: '',
        editEndDate: '',
        editOldUnitId: '',
        editOldWorkingShiftId: '',
        editOldStartDate: '',
        editOldEndDate: '',
        editEmployees: [],
        editSelectedEmps: [],
        editSearchQuery: '',
        editSelectAll: false,
        editSelectedPosition: '',
        isLoadingEditEmployees: false,

        async openEditModal(batch) {
            this.editOldUnitId = batch.school_unit_id;
            this.editOldWorkingShiftId = batch.working_shift_id;
            this.editOldStartDate = batch.start_date ? this.getLocalYmd(batch.start_date) : '';
            this.editOldEndDate = (batch.end_date && batch.end_date !== 'null') ? this.getLocalYmd(batch.end_date) : 'null';

            this.editUnitId = batch.school_unit_id;
            this.editWorkingShiftId = batch.working_shift_id;
            this.editBonusSchemaId = batch.bonus_schema_id || '';
            this.editStartDate = this.editOldStartDate;
            this.editEndDate = (batch.end_date && batch.end_date !== 'null') ? this.editOldEndDate : '';
            
            this.editSearchQuery = '';
            this.editSelectedPosition = '';
            this.editEmployees = [];
            this.editSelectedEmps = batch.employees.map(e => e.id);
            this.editSelectAll = false;
            
            this.showEditModal = true;
            this.isLoadingEditEmployees = true;
            try {
                let response = await fetch(`/employee-working-shifts/unit/${this.editUnitId}/employees`);
                if (response.ok) {
                    this.editEmployees = await response.json();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isLoadingEditEmployees = false;
            }
        },

        async loadEditEmployeesForUnit() {
            if (!this.editUnitId) { this.editEmployees = []; return; }
            this.isLoadingEditEmployees = true;
            try {
                const response = await fetch(`/employee-working-shifts/unit/${this.editUnitId}/employees`);
                if (response.ok) {
                    this.editEmployees = await response.json();
                } else {
                    this.editEmployees = [];
                }
            } catch (e) {
                console.error(e);
                this.editEmployees = [];
            }
            this.isLoadingEditEmployees = false;
        },

        toggleEditAll() {
            const filteredIds = this.filteredEditEmployees.map(e => e.id);
            if (this.editSelectAll) {
                this.editSelectedEmps = Array.from(new Set([...this.editSelectedEmps, ...filteredIds]));
            } else {
                this.editSelectedEmps = this.editSelectedEmps.filter(id => !filteredIds.includes(id));
            }
        },

        get uniqueEditPositions() {
            const posSet = new Set();
            this.editEmployees.forEach(e => {
                const p = e.position || e.subject_position || '-';
                if (p) posSet.add(p);
            });
            return Array.from(posSet).sort();
        },

        get filteredEditEmployees() { 
            return this.editEmployees.filter(e => {
                const matchesSearch = !this.editSearchQuery || e.name.toLowerCase().includes(this.editSearchQuery.toLowerCase());
                const pos = e.position || e.subject_position || '-';
                const matchesPosition = !this.editSelectedPosition || pos === this.editSelectedPosition;
                return matchesSearch && matchesPosition;
            });
        },
    
        openModal(batch) {
            this.selectedBatch = batch;
            this.searchModal = '';
            this.showModal = true;
        },
    
        async loadEmployeesForUnit() {
            if (!this.createUnitId) return;
            this.loadingEmp = true;
            try {
                const response = await fetch(`/employee-working-shifts/unit/${this.createUnitId}/employees?month=${this.createMonth}&year=${this.createYear}`);
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

        async openEditRosterModal(unitId, month, year, rosterName) {
            this.createUnitId = unitId;
            this.createMonth = month;
            this.createYear = year;
            this.createRosterName = rosterName;
            this.createOldRosterName = rosterName;
            this.showCreateModal = true;
            
            this.loadingEmp = true;
            try {
                const response = await fetch(`/employee-working-shifts/unit/${unitId}/employees?month=${month}&year=${year}`);
                if (response.ok) {
                    this.empList = await response.json();
                    
                    const activeRosterResponse = await fetch(`/employee-working-shifts/roster-employees?unit_id=${unitId}&month=${month}&year=${year}&roster_name=${encodeURIComponent(rosterName)}`);
                    if (activeRosterResponse.ok) {
                        const activeEmpIds = await activeRosterResponse.json();
                        this.selectedEmps = activeEmpIds.map(id => String(id));
                    }
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
            this.$watch('showCreateModal', value => {
                if (!value) {
                    this.createUnitId = '';
                    this.createRosterName = '';
                    this.createOldRosterName = '';
                    this.createMonth = '{{ date('n') }}';
                    this.createYear = '{{ date('Y') }}';
                    this.empList = [];
                    this.empSearch = '';
                    this.selectedEmps = [];
                    this.selectAllEmp = false;
                    this.createSelectedPosition = '';
                    this.createShowError = false;
                    this.selectedShifts = {{ json_encode($shifts->where('is_shift', true)->pluck('id')->toArray()) }};
                }
            });
            this.$watch('showAssignmentModal', value => {
                if (!value) {
                    this.assignShowError = false;
                }
            });
            this.$watch('showEditModal', value => {
                if (!value) {
                    this.editUnitId = '';
                    this.editWorkingShiftId = '';
                    this.editBonusSchemaId = '';
                    this.editStartDate = '';
                    this.editEndDate = '';
                    this.editOldUnitId = '';
                    this.editOldWorkingShiftId = '';
                    this.editOldStartDate = '';
                    this.editOldEndDate = '';
                    this.editEmployees = [];
                    this.editSelectedEmps = [];
                    this.editSearchQuery = '';
                    this.editSelectAll = false;
                    this.editSelectedPosition = '';
                    this.editShowError = false;
                }
            });

            this.$watch('editSelectedEmps', () => {
                if (this.filteredEditEmployees.length === 0) {
                    this.editSelectAll = false;
                } else {
                    this.editSelectAll = this.filteredEditEmployees.every(e => this.editSelectedEmps.includes(e.id));
                }
            });
            this.$watch('editSearchQuery', () => {
                if (this.filteredEditEmployees.length === 0) {
                    this.editSelectAll = false;
                } else {
                    this.editSelectAll = this.filteredEditEmployees.every(e => this.editSelectedEmps.includes(e.id));
                }
            });
            this.$watch('editSelectedPosition', () => {
                if (this.filteredEditEmployees.length === 0) {
                    this.editSelectAll = false;
                } else {
                    this.editSelectAll = this.filteredEditEmployees.every(e => this.editSelectedEmps.includes(e.id));
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

            @if(count($neglectedEmployees) > 0)
                <div @click="showNeglectedModal = true" class="flex-1 max-w-md mx-0 md:mx-4 px-3 py-1.5 bg-rose-50/60 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 rounded-xl flex items-center justify-between gap-3 text-left shadow-3xs hover:bg-rose-100/60 dark:hover:bg-rose-900/35 transition-all cursor-pointer">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1 bg-rose-500/10 text-rose-600 dark:text-rose-455 rounded-lg shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-rose-800 dark:text-rose-400 leading-tight">Peringatan</span>
                            <span class="text-[9.5px] text-slate-500 dark:text-slate-400 leading-tight">Terdapat {{ count($neglectedEmployees) }} pegawai tanpa jadwal hari ini.</span>
                        </div>
                    </div>
                    <span class="text-[9.5px] font-bold text-rose-600 dark:text-rose-400 shrink-0 flex items-center gap-0.5">
                        Lihat <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            @endif

            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <button type="button" @click="showCreateModal = true"
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



        <!-- TABS NAVIGATOR (MODERN SEGMENTED CONTROL) -->
        <div class="flex items-center w-full text-left mt-2 mb-4">
            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-inner gap-1">
                <a href="{{ route('employee-working-shifts.index', array_merge(request()->query(), ['tab' => 'roster', 'page' => 1])) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold rounded-lg transition-all cursor-pointer {{ $activeTab === 'roster' ? 'bg-white dark:bg-slate-800 text-indigo-650 dark:text-indigo-400 shadow-sm border border-slate-200/50 dark:border-slate-700/50 font-extrabold' : 'text-slate-500 hover:text-slate-750 dark:text-slate-400 dark:hover:text-slate-350' }}">
                    <i data-lucide="calendar-range" class="w-4 h-4"></i>
                    Roster Bulanan
                </a>
                <a href="{{ route('employee-working-shifts.index', array_merge(request()->query(), ['tab' => 'batch', 'page' => 1])) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold rounded-lg transition-all cursor-pointer {{ $activeTab === 'batch' ? 'bg-white dark:bg-slate-800 text-indigo-650 dark:text-indigo-400 shadow-sm border border-slate-200/50 dark:border-slate-700/50 font-extrabold' : 'text-slate-500 hover:text-slate-750 dark:text-slate-400 dark:hover:text-slate-350' }}">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    Jadwal Kerja Tetap / Sementara (Batch)
                </a>
            </div>
        </div>

        <!-- FILTERS & SEARCH (MODERN COMMAND BAR STYLE) -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('employee-working-shifts.index') }}">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 w-full">
                    <!-- Search Input -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="w-full md:w-96 shrink-0 flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                        <div class="pl-3 text-slate-400">
                            <i data-lucide="search" class="w-4.5 h-4.5"></i>
                        </div>
                        <input type="text" name="search" x-model="searchVal" x-ref="searchInput" placeholder="Cari nama grup roster, nama shift, kode, atau unit..."
                            style="border: none !important; outline: none !important; box-shadow: none !important;"
                            class="w-full h-10 px-2.5 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-550 focus:ring-0">
                        
                        <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $refs.searchInput.focus();" class="h-10 px-2.5 text-slate-400 hover:text-slate-650 dark:hover:text-slate-250 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Filter Unit -->
                    <div class="w-full md:w-48 shrink-0">
                        <select name="unit_id" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Per Page -->
                    <div class="w-full md:w-32 shrink-0">
                        <select name="per_page" class="w-full text-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 baris</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                            <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                            <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500 baris</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                        </select>
                    </div>

                    <!-- Action Bar -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="filter" class="w-4.5 h-4.5"></i>
                            Cari
                        </button>

                        @if(request()->hasAny(['unit_id', 'search', 'per_page']) && count(request()->except('page')) > 0)
                            <a href="{{ route('employee-working-shifts.index') }}" class="inline-flex items-center justify-center h-10 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg shadow-sm transition-colors gap-1.5">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                Reset
                            </a>
                        @endif
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
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar w-full max-h-[70vh] relative">
                <table class="w-full text-xs border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] sticky top-0 z-10">
                        <tr>
                            @if ($activeTab === 'roster')
                                <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-left w-[32%] whitespace-nowrap">Grup Penugasan</th>
                            @else
                                <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-left w-[32%] whitespace-nowrap">Template Shift (Jam Kerja)</th>
                            @endif
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-left w-[18%] whitespace-nowrap">Unit Sekolah</th>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-center w-[25%] whitespace-nowrap">Periode Jadwal</th>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-center w-[13%] whitespace-nowrap">Jumlah Pegawai</th>
                            <th class="bg-slate-50 dark:bg-slate-900 px-6 py-4 text-right w-[12%] whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($batches as $batch)
                            @if ($activeTab === 'roster')
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
                                                data-no-loader
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="tab" value="{{ $activeTab }}">
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
                                                <span class="inline-flex w-max px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-955/30 text-emerald-700 dark:text-emerald-455 border border-emerald-100/30 dark:border-emerald-900/30 uppercase tracking-wide">Kode: {{ $batch['shift_code'] }}</span>
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
                                            <button type="button" @click="openEditModal({{ json_encode($batch) }})"
                                                class="h-8 w-8 inline-flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-sm cursor-pointer"
                                                title="Edit Penugasan">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>

                                            <form action="{{ route('employee-working-shifts.destroy-batch') }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus seluruh penugasan shift pada grup ini?')"
                                                data-no-loader
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="tab" value="{{ $activeTab }}">
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
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-455">
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
                                     Unit: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedBatch?.unit_name"></span><br>
                                     Periode: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedBatch?.type === 'roster' ? formatRosterPeriod(selectedBatch?.month, selectedBatch?.year) : (formatDate(selectedBatch?.start_date) + ' s/d ' + (selectedBatch?.end_date ? formatDate(selectedBatch?.end_date) : 'Seterusnya'))"></span>
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
                         assignShowError: false,
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
                      x-init="
                            $watch('showAssignmentModal', value => {
                                if (!value) {
                                    $data.selectedUnit = '';
                                    $data.workingShiftId = '';
                                    $data.bonusSchemaId = '';
                                    $data.startDate = '';
                                    $data.endDate = '';
                                    $data.employees = [];
                                    $data.searchQuery = '';
                                    $data.selectedPosition = '';
                                    $data.selectedEmps = [];
                                    $data.selectAll = false;
                                    $data.assignShowError = false;
                                }
                            });
                            $watch('selectedEmps', () => {
                                if ($data.filteredEmployees.length === 0) {
                                    $data.selectAll = false;
                                } else {
                                    $data.selectAll = $data.filteredEmployees.every(e => $data.selectedEmps.includes(e.id));
                                }
                            });
                            $watch('searchQuery', () => {
                                if ($data.filteredEmployees.length === 0) {
                                    $data.selectAll = false;
                                } else {
                                    $data.selectAll = $data.filteredEmployees.every(e => $data.selectedEmps.includes(e.id));
                                }
                            });
                            $watch('selectedPosition', () => {
                                if ($data.filteredEmployees.length === 0) {
                                    $data.selectAll = false;
                                } else {
                                    $data.selectAll = $data.filteredEmployees.every(e => $data.selectedEmps.includes(e.id));
                                }
                            });
                        ">
                    
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
                          @submit.prevent="if (selectedEmps.length === 0) { assignShowError = true; } else { assignShowError = false; $el.submit(); }"
                          class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <!-- Hidden inputs for selected employee IDs to ensure they are all submitted regardless of filter state -->
                        <template x-for="empId in selectedEmps" :key="empId">
                            <input type="hidden" name="employee_ids[]" :value="empId">
                        </template>
                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-4">
                                    <!-- Unit Sekolah -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unit Sekolah</label>
                                        <select x-model="selectedUnit" name="school_unit_id" @change="fetchEmployees()"
                                            class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                            <option value="">Pilih Unit...</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Jam Kerja Shift -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Kerja Shift (Template)</label>
                                        <select x-model="workingShiftId" name="working_shift_id" required
                                            class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                            <option value="">Pilih Shift...</option>
                                            @foreach ($shifts as $shift)
                                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Skema Bonus -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Skema Bonus (Opsional)</label>
                                        <select x-model="bonusSchemaId" name="bonus_schema_id"
                                            class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                            <option value="">Gunakan Default/Aktif</option>
                                            @foreach ($bonusSchemas as $schema)
                                                <option value="{{ $schema->id }}">{{ $schema->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Tanggal Mulai -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                                            <input type="date" x-model="startDate" name="start_date" required
                                                class="text-xs w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono dark:[color-scheme:dark]">
                                        </div>

                                        <!-- Tanggal Selesai -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Selesai</label>
                                            <input type="date" x-model="endDate" name="end_date" placeholder="Seterusnya"
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
                                        <span class="flex items-center gap-1.5">
                                            <span>Pilih Pegawai</span>
                                            <span class="text-[11px] text-slate-500 font-normal" x-show="selectedEmps.length > 0"> (<span x-text="selectedEmps.length" class="font-bold text-indigo-650 dark:text-indigo-400"></span> terpilih)</span>
                                            <span x-show="isLoadingEmployees" class="text-[10px] text-indigo-500 animate-pulse font-normal" x-cloak>Memuat data...</span>
                                            <span x-show="assignShowError && selectedEmps.length === 0" class="text-[10px] text-rose-500 font-bold animate-pulse" x-cloak>* Pilih minimal 1 pegawai</span>
                                        </span>
                                        <label x-show="employees.length > 0" class="flex items-center gap-1.5 cursor-pointer text-[11px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer">
                                            Pilih Semua
                                        </label>
                                    </label>

                                    <div class="flex flex-col bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex-1 min-h-[420px] md:h-full">
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
                                        <div class="p-3 space-y-2 custom-scrollbar overflow-y-auto max-h-[380px] md:max-h-[420px] flex-1">
                                            <div x-show="employees.length === 0 && !isLoadingEmployees" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-500 italic text-[11px] py-12">
                                                Silakan pilih unit sekolah terlebih dahulu.
                                            </div>

                                            <div x-show="employees.length > 0 && filteredEmployees.length === 0" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-500 italic text-[11px] py-12">
                                                Pegawai tidak ditemukan.
                                            </div>
                                            
                                            <template x-for="emp in filteredEmployees" :key="emp.id">
                                                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-900/80 border border-slate-200 dark:border-slate-900 rounded-xl transition-all shadow-2xs hover:border-slate-300 dark:hover:border-slate-800">
                                                    <input type="checkbox" :value="emp.id" x-model="selectedEmps"
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

        <!-- MODAL EDIT BATCH SHIFT -->
        <template x-teleport="body">
            <div x-cloak x-show="showEditModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showEditModal = false"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4 text-left" style="display: none;">
                
                <!-- Backdrop overlay -->
                <div class="fixed inset-0 transition-opacity" 
                     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);" 
                     @click="showEditModal = false"></div>
                
                <!-- Content Box -->
                <div x-show="showEditModal"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full sm:max-w-4xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden text-left text-xs z-10">
                    
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                        <div>
                             <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                 <span>Edit Batch Penugasan Shift</span>
                             </h3>
                             <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Ubah tanggal, shift, atau tambah/kurangi pegawai dalam grup jadwal ini.</p>
                         </div>
                         <button type="button" @click="showEditModal = false" class="text-slate-400 dark:text-slate-500 hover:text-slate-655 dark:hover:text-slate-350 bg-transparent border-0 cursor-pointer flex items-center justify-center p-1 rounded-lg">
                             <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                         </button>
                    </div>

                    <form method="POST" action="{{ route('employee-working-shifts.update-batch') }}" 
                          @submit.prevent="if (editSelectedEmps.length === 0) { editShowError = true; } else { editShowError = false; $el.submit(); }"
                          class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <!-- Hidden inputs for selected employee IDs to ensure they are all submitted regardless of filter state -->
                        <template x-for="empId in editSelectedEmps" :key="empId">
                            <input type="hidden" name="employee_ids[]" :value="empId">
                        </template>

                        <input type="hidden" name="old_school_unit_id" :value="editOldUnitId">
                        <input type="hidden" name="old_working_shift_id" :value="editOldWorkingShiftId">
                        <input type="hidden" name="old_start_date" :value="editOldStartDate">
                        <input type="hidden" name="old_end_date" :value="editOldEndDate">

                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-4">
                                    <!-- Unit Sekolah -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unit Sekolah</label>
                                        <select name="school_unit_id" required x-model="editUnitId" @change="loadEditEmployeesForUnit()"
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
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Template Shift Kerja</label>
                                            <select name="working_shift_id" required x-model="editWorkingShiftId"
                                                class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                                <option value="">Pilih Template Shift</option>
                                                @foreach ($shifts as $shift)
                                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Bonus Schema -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Skema Bonus (Opsional)</label>
                                            <select name="bonus_schema_id" x-model="editBonusSchemaId"
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
                                            <input type="date" name="start_date" required x-model="editStartDate"
                                                x-bind:style="document.documentElement.classList.contains('dark') ? 'color-scheme: dark !important;' : ''"
                                                class="text-xs w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono dark:[color-scheme:dark]">
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Selesai (Opsional)</label>
                                            <input type="date" name="end_date" x-model="editEndDate"
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
                                        <span class="flex items-center gap-1.5">
                                            <span>Pilih Pegawai</span>
                                            <span class="text-[11px] text-slate-500 font-normal" x-show="editSelectedEmps.length > 0"> (<span x-text="editSelectedEmps.length" class="font-bold text-indigo-650 dark:text-indigo-400"></span> terpilih)</span>
                                            <span x-show="isLoadingEditEmployees" class="text-[10px] text-indigo-500 animate-pulse font-normal" x-cloak>Memuat data...</span>
                                            <span x-show="editShowError && editSelectedEmps.length === 0" class="text-[10px] text-rose-500 font-bold animate-pulse" x-cloak>* Pilih minimal 1 pegawai</span>
                                        </span>
                                        <label x-show="editEmployees.length > 0" class="flex items-center gap-1.5 cursor-pointer text-[11px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            <input type="checkbox" x-model="editSelectAll" @change="toggleEditAll()" class="rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer">
                                            Pilih Semua
                                        </label>
                                    </label>

                                    <div class="flex flex-col bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex-1 min-h-[420px] md:h-full">
                                        <!-- Search & Filter Bar -->
                                        <div class="p-2 border-b border-slate-200/60 dark:border-slate-800/80 shrink-0 bg-white dark:bg-slate-900/40 space-y-2">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <!-- Search Input -->
                                                <div class="relative flex items-center bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 absolute left-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                                    <input type="text" x-model="editSearchQuery" x-ref="editSearchQueryInput" :disabled="editEmployees.length === 0" placeholder="Cari nama..."
                                                        style="border: none !important; outline: none !important; box-shadow: none !important;"
                                                        class="text-xs w-full h-8 px-2.5 pl-8 pr-8 bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-0">
                                                    <button type="button" x-show="editSearchQuery.trim() !== ''" @click="editSearchQuery = ''; $refs.editSearchQueryInput.focus();" class="absolute right-2 h-8 px-1 text-slate-400 hover:text-slate-600 transition-colors border-0 bg-transparent cursor-pointer flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                    </button>
                                                </div>
                                                
                                                <!-- Position Filter -->
                                                <select x-model="editSelectedPosition" :disabled="editEmployees.length === 0"
                                                    class="text-xs h-8 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                                    <option value="">Semua Jabatan</option>
                                                    <template x-for="pos in uniqueEditPositions" :key="pos">
                                                        <option :value="pos" x-text="pos"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- List -->
                                        <div class="p-3 space-y-2 custom-scrollbar overflow-y-auto max-h-[380px] md:max-h-[420px] flex-1">
                                            <div x-show="editEmployees.length === 0 && !isLoadingEditEmployees" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-550 italic text-[11px] py-12">
                                                Silakan pilih unit sekolah terlebih dahulu.
                                            </div>

                                            <div x-show="editEmployees.length > 0 && filteredEditEmployees.length === 0" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-555 italic text-[11px] py-12">
                                                Pegawai tidak ditemukan.
                                            </div>
                                            
                                            <template x-for="emp in filteredEditEmployees" :key="emp.id">
                                                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-900/80 border border-slate-200 dark:border-slate-900 rounded-xl transition-all shadow-2xs hover:border-slate-300 dark:hover:border-slate-800">
                                                    <input type="checkbox" :value="emp.id" x-model="editSelectedEmps"
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
                        <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex gap-2.5 justify-end bg-slate-50 dark:bg-slate-900/40 shrink-0">
                             <button type="button" @click="showEditModal = false"
                                 class="h-9 px-4 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold rounded-lg cursor-pointer transition-colors shadow-2xs hover:shadow-xs">
                                 Batal
                             </button>
                             <button type="submit"
                                 class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm transition-all cursor-pointer flex items-center justify-center dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:text-white">
                                 Simpan Perubahan
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
                     class="relative w-full sm:max-w-4xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden text-left text-xs z-10">
                    
                    <form action="{{ route('employee-working-shifts.roster') }}" method="GET" 
                          @submit.prevent="if (!createUnitId || selectedEmps.length === 0 || selectedShifts.length === 0) { createShowError = true; } else { createShowError = false; $el.submit(); }"
                          class="flex flex-col flex-1 overflow-hidden">
                        <input type="hidden" name="old_roster_name" x-model="createOldRosterName">
                        <!-- Header -->
                        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                             <div>
                                 <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                     <span>Buat Jadwal Bergilir (Roster) Baru</span>
                                 </h3>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Pilih grup pegawai yang akan dibuatkan jadwal roster.</p>
                            </div>
                            <button type="button" @click="showCreateModal = false" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 bg-transparent border-0 cursor-pointer flex items-center justify-center p-1 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-4">
                                    <!-- Unit Sekolah -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex justify-between items-center">
                                            <span>Unit Sekolah <span class="text-rose-500">*</span></span>
                                            <span x-show="createShowError && !createUnitId" class="text-[10px] text-rose-500 font-bold animate-pulse" x-cloak>* Wajib pilih unit</span>
                                        </label>
                                        <select x-model="createUnitId" name="unit_id" @change="loadEmployeesForUnit()"
                                            class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                            <option value="">Pilih Unit Sekolah...</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Nama Roster -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Roster <span class="text-rose-500">*</span></label>
                                        <input type="text" name="roster_name" x-model="createRosterName" required placeholder="Misal: Roster Satpam"
                                            class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Bulan -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bulan</label>
                                            <select name="month" x-model="createMonth" @change="loadEmployeesForUnit()"
                                                class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
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
                                                    <option value="{{ $i }}">{{ $bulanIndo[$i] }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <!-- Tahun -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tahun</label>
                                            <input type="number" name="year" x-model="createYear" @change="loadEmployeesForUnit()"
                                                class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono">
                                        </div>
                                    </div>

                                    <!-- Pilih Shift yang Digunakan -->
                                    <div class="mt-4 text-left">
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                                <span>Pilih Shift yang Digunakan <span class="text-rose-500">*</span></span>
                                                <span x-show="createShowError && selectedShifts.length === 0" class="text-[10px] text-rose-500 font-bold animate-pulse" x-cloak>* Pilih minimal 1 shift</span>
                                            </label>
                                            <div class="flex gap-2">
                                                <button type="button" @click="selectedShifts = {{ json_encode($shifts->where('is_shift', true)->pluck('id')->toArray()) }}"
                                                    class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 cursor-pointer bg-transparent border-0 p-0">
                                                    Pilih Semua
                                                </button>
                                                <span class="text-slate-300 dark:text-slate-700 text-[10px]">|</span>
                                                <button type="button" @click="selectedShifts = []"
                                                    class="text-[10px] font-bold text-slate-500 hover:text-slate-650 dark:text-slate-400 cursor-pointer bg-transparent border-0 p-0">
                                                    Kosongkan
                                                </button>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl max-h-[170px] overflow-y-auto custom-scrollbar">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($shifts->where('is_shift', true) as $s)
                                                    <label :class="selectedShifts.includes({{ $s->id }}) 
                                                        ? 'border-indigo-500/80 bg-indigo-50/50 dark:bg-indigo-950/20 ring-1 ring-indigo-500/20' 
                                                        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-700'"
                                                        class="flex items-center justify-between p-2.5 rounded-xl border cursor-pointer transition-all duration-200 select-none group">
                                                        <div class="flex items-center gap-2.5 min-w-0">
                                                            <div class="flex items-center">
                                                                <input type="checkbox" name="shift_ids[]" value="{{ $s->id }}" x-model="selectedShifts"
                                                                    class="rounded border-slate-300 dark:border-slate-700 text-indigo-650 shadow-sm focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                                            </div>
                                                            <div class="flex flex-col min-w-0">
                                                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 truncate leading-tight">{{ $s->name }}</span>
                                                                @if($s->description)
                                                                    <span class="text-[9px] text-slate-400 dark:text-slate-500 truncate mt-1">{{ $s->description }}</span>
                                                                @else
                                                                    <span class="text-[9px] text-slate-400 dark:text-slate-500 mt-1">Kode: {{ $s->short_code ?: $s->code }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <span :class="selectedShifts.includes({{ $s->id }}) ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-850 dark:text-slate-400'"
                                                            class="px-1.5 py-0.5 rounded text-[8px] font-bold tracking-wide uppercase shrink-0">
                                                            {{ $s->short_code ?: $s->code }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informasi Jadwal Roster (Di bawah Bulan/Tahun) -->
                                    <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100/60 dark:border-indigo-900/40 rounded-xl space-y-2 mt-4 text-left">
                                        <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-[11px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                            <span>Informasi Pembuatan Jadwal Roster</span>
                                        </div>
                                        <ul class="list-disc pl-4 space-y-2 text-[10px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                            <li>
                                                <span><strong>Periode Bulan & Tahun</strong>: Jadwal bergilir harian hanya akan berlaku spesifik pada bulan dan tahun yang telah ditentukan.</span>
                                            </li>
                                            <li>
                                                <span><strong>Deteksi Otomatis Roster</strong>: Jika nama roster pada unit terpilih di bulan yang sama sudah terdaftar di database, sistem akan memuat data roster lama untuk dilanjutkan/diedit. Jika belum ada, lembar roster baru akan otomatis dibuat.</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Pegawai List -->
                                <div class="flex flex-col h-full">
                                    <label class="font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex justify-between items-center shrink-0">
                                        <span class="flex items-center gap-1.5">
                                            <span>Pilih Pegawai</span>
                                            <span x-show="loadingEmp" class="text-[10px] text-indigo-500 animate-pulse font-normal" x-cloak>Memuat data...</span>
                                            <span x-show="createShowError && selectedEmps.length === 0" class="text-[10px] text-rose-500 font-bold animate-pulse" x-cloak>* Pilih minimal 1 pegawai</span>
                                            <span x-show="createShowError && selectedEmps.some(id => { const emp = empList.find(e => e.id === id); return emp && emp.active_roster_name; })" class="text-[10px] text-rose-500 font-bold animate-pulse" x-cloak>* Terdapat pegawai bentrok</span>
                                        </span>
                                        <label x-show="empList.length > 0" class="flex items-center gap-1.5 cursor-pointer text-[11px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            <input type="checkbox" x-model="selectAllEmp" @change="toggleCreateAll()" class="rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer">
                                            Pilih Semua
                                        </label>
                                    </label>

                                    <!-- Alert Pegawai Bentrok -->
                                    <div x-show="selectedEmps.some(id => { const emp = empList.find(e => e.id === id); return emp && emp.active_roster_name; })"
                                        class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 rounded-xl flex items-start gap-2.5 text-left text-[10px] text-amber-700 dark:text-amber-400 shrink-0 font-medium mb-3"
                                        x-cloak>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        <div>
                                            <span class="font-bold text-amber-800 dark:text-amber-300">Perhatian Roster Ganda:</span> Beberapa pegawai terpilih sudah terdaftar pada roster aktif lain di bulan ini. Silakan hapus centang nama tersebut terlebih dahulu, atau edit roster yang aktif.
                                        </div>
                                    </div>

                                    <div class="flex flex-col bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex-1 min-h-[280px]">
                                        <!-- Search & Filter Bar -->
                                        <div class="p-2 border-b border-slate-200/60 dark:border-slate-800/80 shrink-0 bg-white dark:bg-slate-900/40 space-y-2">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <!-- Search Input -->
                                                <div class="relative flex items-center bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 absolute left-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                                    <input type="text" x-model="empSearch" x-ref="createSearchQueryInput" :disabled="empList.length === 0" placeholder="Cari nama..."
                                                        style="border: none !important; outline: none !important; box-shadow: none !important;"
                                                        class="text-xs w-full h-8 px-2.5 pl-8 pr-8 bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-0">
                                                    <button type="button" x-show="empSearch.trim() !== ''" @click="empSearch = ''; $refs.createSearchQueryInput.focus();" class="absolute right-2 h-8 px-1 text-slate-400 hover:text-slate-660 transition-colors border-0 bg-transparent cursor-pointer flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                    </button>
                                                </div>
                                                
                                                <!-- Position Filter -->
                                                <select x-model="createSelectedPosition" :disabled="empList.length === 0"
                                                    class="text-xs h-8 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                                    <option value="">Semua Jabatan</option>
                                                    <template x-for="pos in uniqueCreatePositions" :key="pos">
                                                        <option :value="pos" x-text="pos"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- List -->
                                        <div class="p-3 space-y-2 custom-scrollbar overflow-y-auto max-h-[480px] flex-1">
                                            <div x-show="empList.length === 0 && !loadingEmp" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-550 italic text-[11px] py-12">
                                                Silakan pilih unit sekolah terlebih dahulu.
                                            </div>

                                            <div x-show="empList.length > 0 && filteredCreateEmployees.length === 0" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-555 italic text-[11px] py-12">
                                                Pegawai tidak ditemukan.
                                            </div>
                                            
                                            <template x-for="emp in filteredCreateEmployees" :key="emp.id">
                                                <label class="flex items-center gap-3 cursor-pointer p-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-900/80 border border-slate-200 dark:border-slate-900 rounded-xl transition-all shadow-2xs hover:border-slate-300 dark:hover:border-slate-800">
                                                    <input type="checkbox" name="emp_ids[]" :value="emp.id" x-model="selectedEmps"
                                                        class="employee-checkbox w-4.5 h-4.5 rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 shrink-0 cursor-pointer">
                                                    <div class="flex flex-col min-w-0 flex-1">
                                                        <span class="text-xs text-slate-900 dark:text-slate-100 font-bold leading-snug truncate" x-text="emp.name"></span>
                                                        <span class="text-[10px] text-slate-450 mt-0.5 truncate" x-text="emp.position || emp.subject_position || '-'"></span>
                                                        <template x-if="emp.active_roster_name">
                                                            <span class="inline-flex items-center gap-1 mt-1.5 text-[9px] font-bold text-amber-650 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 px-1.5 py-0.5 rounded w-fit">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                                                <span x-text="'Roster: ' + emp.active_roster_name" class="truncate max-w-[120px]"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5 bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button"
                                class="h-9 px-4 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold rounded-lg cursor-pointer transition-colors shadow-2xs"
                                @click="showCreateModal = false">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm transition-colors cursor-pointer">
                                Lanjutkan ke Grid
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL DAFTAR PEGAWAI TANPA JADWAL -->
        <template x-teleport="body">
            <div x-cloak x-show="showNeglectedModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showNeglectedModal = false"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4 text-left" style="display: none;">
                
                <!-- Backdrop overlay -->
                <div class="fixed inset-0 transition-opacity" 
                     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);" 
                     @click="showNeglectedModal = false"></div>
                
                <!-- Content Box -->
                <div x-show="showNeglectedModal"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full sm:max-w-2xl rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 max-h-[80vh] flex flex-col overflow-hidden text-left text-xs z-10">
                     
                     <!-- Header -->
                     <div class="p-5 border-b border-slate-100 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                         <div>
                              <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                  <span>Pegawai Tanpa Jadwal Kerja Aktif</span>
                              </h3>
                              <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Daftar pegawai di unit aktif yang tidak memiliki jadwal shift hari ini.</p>
                         </div>
                         <button type="button" @click="showNeglectedModal = false" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400 bg-transparent border-0 cursor-pointer flex items-center justify-center p-1 rounded-lg">
                             <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                         </button>
                     </div>

                     <!-- Body -->
                     <div class="flex-1 overflow-y-auto p-5">
                         <div class="border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden bg-slate-50/20 dark:bg-slate-900/10">
                             <table class="w-full text-left border-collapse">
                                 <thead>
                                     <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-850 text-[10px] uppercase font-bold text-slate-450 dark:text-slate-400">
                                         <th class="px-4 py-3">Nama / Jabatan</th>
                                         <th class="px-4 py-3">NIP</th>
                                         <th class="px-4 py-3">Unit Sekolah</th>
                                         <th class="px-4 py-3 text-right">Aksi</th>
                                     </tr>
                                 </thead>
                                 <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                                     @forelse ($neglectedEmployees as $emp)
                                         <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-slate-750 dark:text-slate-350">
                                             <td class="px-4 py-3.5">
                                                 <div class="font-bold text-slate-900 dark:text-slate-100 text-xs">{{ $emp['name'] }}</div>
                                                 <div class="text-[10px] text-slate-450 mt-0.5">{{ $emp['position'] }}</div>
                                             </td>
                                             <td class="px-4 py-3.5 font-mono text-[11px]">{{ $emp['nip'] }}</td>
                                             <td class="px-4 py-3.5">
                                                 <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-300">
                                                     {{ $emp['unit_name'] }}
                                                 </span>
                                             </td>
                                             <td class="px-4 py-3.5 text-right">
                                                 <button type="button" 
                                                     @click="
                                                         showNeglectedModal = false;
                                                         setTimeout(() => {
                                                             showAssignmentModal = true;
                                                             const assignModalEl = document.querySelector('[x-data*=\'selectedUnit\']');
                                                             if (assignModalEl) {
                                                                 const assignData = Alpine.$data(assignModalEl);
                                                                 assignData.selectedUnit = '{{ $emp['unit_id'] }}';
                                                                 assignData.fetchEmployees().then(() => {
                                                                     assignData.selectedEmps = [{{ $emp['id'] }}];
                                                                 });
                                                             }
                                                         }, 200);
                                                     "
                                                     class="px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800 text-indigo-650 dark:text-indigo-400 font-bold rounded-lg transition-colors cursor-pointer text-[10px]">
                                                     Tugaskan
                                                 </button>
                                             </td>
                                         </tr>
                                     @empty
                                         <tr>
                                             <td colspan="4" class="px-4 py-8 text-center text-slate-450 dark:text-slate-500 italic">
                                                 Semua pegawai pada unit aktif telah memiliki jadwal hari ini.
                                             </td>
                                         </tr>
                                     @endforelse
                                 </tbody>
                             </table>
                         </div>
                     </div>
                </div>
            </div>
        </template>

    </div>
</x-admin-layout>
