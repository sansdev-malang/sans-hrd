<x-admin-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    @php
        $earlySchemas = $schemas->filter(fn($s) => ($s->calculation_mode ?? 'early_arrival') === 'early_arrival');
        $lateSchemas = $schemas->filter(fn($s) => ($s->calculation_mode ?? 'early_arrival') === 'late_tolerance');

        $earlyActiveSchemas = $earlySchemas->filter(fn($s) => $s->is_active);
        $earlyInactiveSchemas = $earlySchemas->filter(fn($s) => !$s->is_active);

        $lateActiveSchemas = $lateSchemas->filter(fn($s) => $s->is_active);
        $lateInactiveSchemas = $lateSchemas->filter(fn($s) => !$s->is_active);
    @endphp

    <div class="p-3.5 sm:p-5 lg:p-6 space-y-3.5 sm:space-y-5 lg:space-y-6 text-left" x-cloak x-data="{ 
        activeTab: (new URLSearchParams(window.location.search).get('tab')) || localStorage.getItem('bonus_schema_active_tab') || '{{ request('tab', 'late_tolerance') }}',
        statusFilter: (new URLSearchParams(window.location.search).get('status')) || 'active',
        showAddModal: false, 
        showEditModal: false,
        modalMode: 'late_tolerance',
        editId: null,
        editName: '',
        editIsActive: true,
        tiers: [],

        get currentCounts() {
            if (this.activeTab === 'early_arrival') {
                return {
                    active: {{ $earlyActiveSchemas->count() }},
                    inactive: {{ $earlyInactiveSchemas->count() }},
                    total: {{ $earlySchemas->count() }}
                };
            }
            return {
                active: {{ $lateActiveSchemas->count() }},
                inactive: {{ $lateInactiveSchemas->count() }},
                total: {{ $lateSchemas->count() }}
            };
        },

        init() {
            let paramTab = new URLSearchParams(window.location.search).get('tab');
            if (paramTab && (paramTab === 'early_arrival' || paramTab === 'late_tolerance')) {
                this.activeTab = paramTab;
                localStorage.setItem('bonus_schema_active_tab', paramTab);
            } else {
                let storedTab = localStorage.getItem('bonus_schema_active_tab');
                if (storedTab && (storedTab === 'early_arrival' || storedTab === 'late_tolerance')) {
                    this.activeTab = storedTab;
                }
            }
        },

        setTab(tab) {
            this.activeTab = tab;
            localStorage.setItem('bonus_schema_active_tab', tab);
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        addTier() {
            let nextLevel = this.tiers.length + 1;
            let lastNominal = this.tiers.length > 0 ? this.tiers[this.tiers.length - 1].nominal : 10000;
            if (this.modalMode === 'early_arrival') {
                let lastEarly = this.tiers.length > 0 ? this.tiers[this.tiers.length - 1].min_early_minutes : 15;
                this.tiers.push({
                    tier_level: nextLevel,
                    nominal: Math.max(0, lastNominal - 2000),
                    min_early_minutes: Math.max(0, lastEarly - 5),
                    max_late_minutes: 0,
                    max_absent_days: 0
                });
            } else {
                let lastLate = this.tiers.length > 0 ? this.tiers[this.tiers.length - 1].max_late_minutes : 0;
                this.tiers.push({
                    tier_level: nextLevel,
                    nominal: Math.max(0, lastNominal - 1000),
                    min_early_minutes: 0,
                    max_late_minutes: lastLate + 5,
                    max_absent_days: 0
                });
            }
        },

        removeTier(index) {
            this.tiers.splice(index, 1);
            this.tiers.forEach((t, i) => {
                t.tier_level = i + 1;
            });
        },

        setModalMode(mode) {
            this.modalMode = mode;
            if (this.tiers.length === 0) {
                this.loadDefaultTiers(mode);
            }
        },

        loadDefaultTiers(mode) {
            if (mode === 'early_arrival') {
                this.tiers = [
                    { tier_level: 1, nominal: 15000, min_early_minutes: 30, max_late_minutes: 0, max_absent_days: 0 },
                    { tier_level: 2, nominal: 10000, min_early_minutes: 15, max_late_minutes: 0, max_absent_days: 0 },
                    { tier_level: 3, nominal: 7000, min_early_minutes: 5, max_late_minutes: 0, max_absent_days: 0 },
                    { tier_level: 4, nominal: 5000, min_early_minutes: 0, max_late_minutes: 0, max_absent_days: 0 }
                ];
            } else {
                this.tiers = [
                    { tier_level: 1, nominal: 10000, min_early_minutes: 0, max_late_minutes: 0, max_absent_days: 0 },
                    { tier_level: 2, nominal: 9000, min_early_minutes: 0, max_late_minutes: 5, max_absent_days: 0 },
                    { tier_level: 3, nominal: 8000, min_early_minutes: 0, max_late_minutes: 10, max_absent_days: 0 },
                    { tier_level: 4, nominal: 7000, min_early_minutes: 0, max_late_minutes: 15, max_absent_days: 0 }
                ];
            }
        },

        openEdit(schema) {
            this.editId = schema.id;
            this.editName = schema.name;
            this.editIsActive = !!schema.is_active;
            this.modalMode = schema.calculation_mode || 'early_arrival';
            this.tiers = schema.tiers.map(t => ({
                tier_level: t.tier_level,
                nominal: parseFloat(t.nominal),
                min_early_minutes: t.min_early_minutes !== undefined && t.min_early_minutes !== null ? parseInt(t.min_early_minutes) : 0,
                max_late_minutes: t.max_late_minutes !== undefined && t.max_late_minutes !== null ? parseInt(t.max_late_minutes) : 0,
                max_absent_days: t.max_absent_days || 0
            }));
            this.showEditModal = true;
        },

        resetAdd(mode = 'late_tolerance') {
            this.editName = '';
            this.modalMode = mode;
            this.loadDefaultTiers(mode);
            this.showAddModal = true;
        }
    }">

        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 sm:gap-3 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-50">Skema Uang Kehadiran</h2>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Kelola jenjang nominal uang kehadiran harian pegawai berdasarkan waktu kedatangan dan toleransi absensi.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto shrink-0">
                <a :href="'{{ route('bonus-schemas.sync') }}?tab=' + activeTab" data-no-loader="true" onclick="this.style.pointerEvents = 'none'; let icon = this.querySelector('svg'); if(icon) icon.classList.add('animate-spin');" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] sm:text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm transition-all cursor-pointer w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>Sync Ulang ke Unit</span>
                </a>
                <button @click="resetAdd(activeTab)" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 bg-slate-900 dark:bg-slate-50 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-[11px] sm:text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Buat Skema Baru</span>
                </button>
            </div>
        </section>

        <!-- TABS & STATUS FILTER NAVIGATION -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-slate-800 gap-2.5 pb-2 sm:pb-0">
            <!-- Mode Tabs (Left) -->
            <div class="flex gap-1.5 sm:gap-2">
                <button type="button" 
                    @click="setTab('late_tolerance')" 
                    :class="activeTab === 'late_tolerance' 
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900/50'" 
                    class="px-3.5 sm:px-4 py-2 sm:py-2.5 text-xs font-bold rounded-t-xl border-b-2 flex items-center gap-2 transition-all cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Toleransi Keterlambatan</span>
                    <span class="ml-1 px-1.5 py-0.2 rounded-full bg-slate-200 dark:bg-slate-800 text-[10px] font-mono" title="Total Skema">{{ $lateSchemas->count() }}</span>
                </button>

                <button type="button" 
                    @click="setTab('early_arrival')" 
                    :class="activeTab === 'early_arrival' 
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900/50'" 
                    class="px-3.5 sm:px-4 py-2 sm:py-2.5 text-xs font-bold rounded-t-xl border-b-2 flex items-center gap-2 transition-all cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Hadir Lebih Awal</span>
                    <span class="ml-1 px-1.5 py-0.2 rounded-full bg-slate-200 dark:bg-slate-800 text-[10px] font-mono" title="Total Skema">{{ $earlySchemas->count() }}</span>
                </button>
            </div>

            <!-- Status Filter Segmented Control (Right) -->
            <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800/70 rounded-xl border border-slate-200/80 dark:border-slate-800 self-start sm:self-end mb-1 sm:mb-1.5">
                <button type="button" 
                    @click="statusFilter = 'active'" 
                    :class="statusFilter === 'active' 
                        ? 'bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 font-bold shadow-2xs' 
                        : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-medium'"
                    class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer"
                    title="Hanya tampilkan skema yang sedang aktif digunakan">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Skema Aktif</span>
                    <span class="px-1.5 py-0.2 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-[10px] font-mono text-emerald-700 dark:text-emerald-300 font-bold" x-text="currentCounts.active"></span>
                </button>

                <button type="button" 
                    @click="statusFilter = 'inactive'" 
                    :class="statusFilter === 'inactive' 
                        ? 'bg-white dark:bg-slate-900 text-amber-700 dark:text-amber-400 font-bold shadow-2xs' 
                        : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-medium'"
                    class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer"
                    title="Tampilkan skema yang berstatus nonaktif / arsip lampau">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span>Nonaktif</span>
                    <span class="px-1.5 py-0.2 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-mono text-slate-700 dark:text-slate-300 font-bold" x-text="currentCounts.inactive"></span>
                </button>

                <button type="button" 
                    @click="statusFilter = 'all'" 
                    :class="statusFilter === 'all' 
                        ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-bold shadow-2xs' 
                        : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-medium'"
                    class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer"
                    title="Tampilkan semua skema baik aktif maupun nonaktif">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.2 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-mono text-slate-700 dark:text-slate-300 font-semibold" x-text="currentCounts.total"></span>
                </button>
            </div>
        </div>

        <!-- TAB 1: HADIR LEBIH AWAL (SEBELUM JAM MASUK / MULAI TUGAS) -->
        <div x-cloak x-show="activeTab === 'early_arrival'" x-transition:enter="ease-out " x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <!-- Accordion Info Tab 1 -->
            <div class="mb-5 rounded-xl border border-emerald-200/60 dark:border-emerald-900/40 bg-emerald-50/50 dark:bg-emerald-950/20 overflow-hidden text-xs" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between text-left font-bold text-emerald-900 dark:text-emerald-300 hover:bg-emerald-100/50 dark:hover:bg-emerald-950/30 transition-colors cursor-pointer border-0 bg-transparent">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span class="text-xs">Informasi & Ketentuan Skema Hadir Lebih Awal</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold">
                        <span x-text="open ? 'Tutup Informasi' : 'Lihat Informasi & Aturan'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transform transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </button>
                <div x-cloak x-show="open" x-transition:enter="transition ease-out " x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-4 pb-4 pt-2 text-slate-700 dark:text-slate-300 border-t border-emerald-200/40 dark:border-emerald-900/30 space-y-2.5">
                    <p class="leading-relaxed">Skema ini menghitung bonus ketepatan waktu berdasarkan kehadiran <strong>sebelum jam mulai tugas atau shift</strong>. Dihitung secara berjenjang (<em>tiered</em>) untuk mengapresiasi pegawai yang hadir lebih awal:</p>
                    <ul class="list-disc list-inside space-y-1 text-[11px] text-slate-600 dark:text-slate-400 ml-1">
                        <li><strong>Jenjang Tier:</strong> Pegawai yang datang lebih pagi akan memperoleh nominal bonus sesuai batas minimal menit kehadiran lebih awal (tier teratas untuk kehadiran paling awal).</li>
                        <li><strong>Kehadiran Tepat Waktu:</strong> Jika tier memiliki batas minimal 0 menit, scan sebelum atau tepat pada jam mulai tetap berhak memperoleh bonus tier tersebut.</li>
                        <li><strong>Setelah Jam Masuk:</strong> Pegawai yang scan setelah jam mulai (&ge; 1 menit lewat) dianggap terlambat sehingga tidak memperoleh bonus tier Hadir Lebih Awal.</li>
                        <li><strong>Fleksibilitas Penggunaan:</strong> Skema jenis ini dapat digunakan untuk jadwal piket maupun shift kerja reguler sesuai penugasan.</li>
                    </ul>
                </div>
            </div>

            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5 lg:gap-6 text-left">
                <!-- Banner Notification when Viewing Inactive Schemas -->
                <div x-show="statusFilter === 'inactive' && currentCounts.inactive > 0" class="col-span-full py-2.5 px-4 rounded-xl border border-amber-200/80 dark:border-amber-900/50 bg-amber-50/60 dark:bg-amber-950/20 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 text-amber-900 dark:text-amber-300">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Sedang menampilkan skema berstatus <strong>Nonaktif / Riwayat Lampau</strong> (<span x-text="currentCounts.inactive"></span> skema).</span>
                    </div>
                    <button type="button" @click="statusFilter = 'active'" class="inline-flex items-center gap-1.5 px-3 py-1 bg-white dark:bg-slate-900 hover:bg-amber-100 dark:hover:bg-slate-800 text-amber-800 dark:text-amber-300 text-xs font-bold rounded-lg border border-amber-200 dark:border-amber-800 transition-all cursor-pointer shrink-0 shadow-3xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                        <span>Kembali ke Skema Aktif</span>
                    </button>
                </div>

                @forelse($earlySchemas as $schema)
                    <div x-show="statusFilter === 'all' || (statusFilter === 'active' && {{ $schema->is_active ? 'true' : 'false' }}) || (statusFilter === 'inactive' && {{ !$schema->is_active ? 'true' : 'false' }})"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-all duration-200 {{ ($picketBonusSchemaId ?? 0) == $schema->id ? 'ring-2 ring-amber-400/50 dark:ring-amber-500/40 border-amber-300 dark:border-amber-700/60' : '' }} {{ !$schema->is_active ? 'opacity-90 bg-slate-50/40 dark:bg-slate-900/50' : '' }}">
                        <div class="space-y-3.5">
                            <div class="flex justify-between items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-50 truncate" title="{{ $schema->name }}">{{ $schema->name }}</h4>
                                        @if(($picketBonusSchemaId ?? 0) == $schema->id)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60 shadow-2xs whitespace-nowrap">
                                                <span>⚡ Piket Aktif</span>
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">ID: {{ $schema->id }}</span>
                                </div>
                                <div class="shrink-0">
                                    @if($schema->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-450 border border-emerald-100/30 dark:border-emerald-900/30 uppercase">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-650 dark:text-rose-455 border border-rose-100/30 dark:border-rose-900/30 uppercase">Non-Aktif</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Tiers Table -->
                            <div class="border border-slate-100 dark:border-slate-800 rounded-lg overflow-hidden mt-2.5">
                                <table class="w-full text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="px-2.5 sm:px-3 py-2 text-left">Tier</th>
                                            <th class="px-2 sm:px-3 py-2 text-center">Minimal Hadir Awal</th>
                                            <th class="px-2.5 sm:px-3 py-2 text-right">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                                        @foreach($schema->tiers->sortBy('tier_level') as $tier)
                                            <tr>
                                                <td class="px-2.5 sm:px-3 py-2 text-left flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-4.5 h-4.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                                        {{ $tier->tier_level }}
                                                    </span>
                                                    <span>Tier {{ $tier->tier_level }}</span>
                                                </td>
                                                <td class="px-2 sm:px-3 py-2 text-center font-mono text-[11px] text-slate-800 dark:text-slate-200">
                                                    @if(($tier->min_early_minutes ?? 0) == 0)
                                                        <span class="text-slate-600 dark:text-slate-400">0m (Tepat Waktu)</span>
                                                    @else
                                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">&ge; {{ $tier->min_early_minutes }}m</span> awal
                                                    @endif
                                                </td>
                                                <td class="px-2.5 sm:px-3 py-2 text-right font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-mono">
                                                    Rp {{ number_format($tier->nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mt-5 border-t border-slate-100 dark:border-slate-900 pt-4 justify-end">
                            @if(($picketBonusSchemaId ?? 0) == $schema->id)
                                <form action="{{ route('bonus-schemas.unset-picket') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 text-xs font-bold rounded-lg border border-amber-200 dark:border-amber-800 transition-all flex items-center gap-1.5 cursor-pointer" title="Lepaskan status skema piket aktif">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Lepas Piket</span>
                                    </button>
                                </form>
                            @elseif($schema->is_active)
                                <form action="{{ route('bonus-schemas.set-picket', $schema->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-lg border border-emerald-200 dark:border-emerald-800 transition-all flex items-center gap-1.5 cursor-pointer" title="Jadikan skema ini sebagai acuan perhitungan bonus hari piket">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Pilih Piket</span>
                                    </button>
                                </form>
                            @endif

                            @if($schema->is_active)
                                <form action="{{ route('bonus-schemas.toggle-active', $schema->id) }}" method="POST" class="inline" onsubmit="return confirm('Nonaktifkan skema ini? Skema tidak akan digunakan untuk kalkulasi kehadiran yang akan datang.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all flex items-center gap-1 cursor-pointer" title="Nonaktifkan skema ini">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Nonaktifkan</span>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('bonus-schemas.toggle-active', $schema->id) }}" method="POST" class="inline" onsubmit="return confirm('Aktifkan skema ini agar dapat digunakan kembali?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer" title="Aktifkan skema ini agar dapat digunakan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Aktifkan</span>
                                    </button>
                                </form>
                            @endif

                            <button @click="openEdit({{ json_encode($schema) }})" class="h-8 px-3.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-800 transition-all hover:scale-105 duration-150 flex items-center gap-1.5 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                <span>Edit Skema</span>
                            </button>
                            <form :action="`{{ url('bonus-schemas') }}/${{ $schema->id }}?tab=early_arrival`" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skema bonus ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="h-8 px-3.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-950/50 text-rose-650 dark:text-rose-400 text-xs font-bold rounded-lg border border-rose-100/30 dark:border-rose-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1.5 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.78 0L9 9m12 6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada skema bonus Hadir Lebih Awal yang terdaftar.</p>
                        <button @click="resetAdd('early_arrival')" class="mt-3 px-3.5 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold cursor-pointer hover:bg-indigo-700">Buat Skema Hadir Lebih Awal</button>
                    </div>
                @endforelse

                <!-- Notification at bottom of active list if inactive schemas exist -->
                <div x-show="statusFilter === 'active' && currentCounts.inactive > 0" class="col-span-full py-3 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        <span>Terdapat <strong class="text-slate-800 dark:text-slate-200" x-text="currentCounts.inactive"></strong> skema berstatus <strong>Nonaktif / Riwayat Lampau</strong> yang disembunyikan agar tampilan tetap rapi.</span>
                    </div>
                    <button type="button" @click="statusFilter = 'inactive'" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 shadow-3xs transition-all cursor-pointer shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <span>Buka Skema Nonaktif</span>
                    </button>
                </div>

                <!-- Empty state when active filter has 0 items but collection is not empty -->
                <div x-show="currentCounts.active === 0 && statusFilter === 'active' && currentCounts.total > 0" class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada skema aktif saat ini pada kategori ini.</p>
                    <div class="flex items-center justify-center gap-2 mt-3">
                        <button @click="resetAdd('early_arrival')" class="px-3.5 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold cursor-pointer hover:bg-indigo-700">Buat Skema Baru</button>
                        <button @click="statusFilter = 'inactive'" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold cursor-pointer">Lihat Skema Nonaktif</button>
                    </div>
                </div>

                <!-- Empty state when inactive filter has 0 items but collection is not empty -->
                <div x-show="currentCounts.inactive === 0 && statusFilter === 'inactive' && currentCounts.total > 0" class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada skema berstatus nonaktif pada kategori ini.</p>
                    <button @click="statusFilter = 'active'" class="mt-3 px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold cursor-pointer">Kembali ke Skema Aktif</button>
                </div>
            </section>
        </div>

        <!-- TAB 2: TOLERANSI KETERLAMBATAN (SETELAH JAM MASUK) -->
        <div x-cloak x-show="activeTab === 'late_tolerance'" x-transition:enter="ease-out " x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
            <!-- Accordion Info Tab 2 -->
            <div class="mb-5 rounded-xl border border-amber-200/60 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/20 overflow-hidden text-xs" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between text-left font-bold text-amber-900 dark:text-amber-300 hover:bg-amber-100/50 dark:hover:bg-amber-950/30 transition-colors cursor-pointer border-0 bg-transparent">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        <span class="text-xs">Informasi & Ketentuan Skema Toleransi Keterlambatan</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-amber-700 dark:text-amber-400 font-semibold">
                        <span x-text="open ? 'Tutup Informasi' : 'Lihat Informasi & Aturan'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transform transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </button>
                <div x-cloak x-show="open" x-transition:enter="transition ease-out " x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-4 pb-4 pt-2 text-slate-700 dark:text-slate-300 border-t border-amber-200/40 dark:border-amber-900/30 space-y-2">
                    <p class="leading-relaxed">Skema ini menghitung bonus ketepatan waktu berdasarkan batas toleransi menit keterlambatan <strong>setelah jam masuk shift atau penugasan</strong>. Pegawai tetap berhak memperoleh bonus selama keterlambatan tidak melebihi batas maksimal menit tier yang ditentukan. Dapat digunakan untuk shift kerja reguler maupun penugasan piket.</p>
                </div>
            </div>

            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5 lg:gap-6 text-left">
                <!-- Banner Notification when Viewing Inactive Schemas -->
                <div x-show="statusFilter === 'inactive' && currentCounts.inactive > 0" class="col-span-full py-2.5 px-4 rounded-xl border border-amber-200/80 dark:border-amber-900/50 bg-amber-50/60 dark:bg-amber-950/20 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 text-amber-900 dark:text-amber-300">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Sedang menampilkan skema berstatus <strong>Nonaktif / Riwayat Lampau</strong> (<span x-text="currentCounts.inactive"></span> skema).</span>
                    </div>
                    <button type="button" @click="statusFilter = 'active'" class="inline-flex items-center gap-1.5 px-3 py-1 bg-white dark:bg-slate-900 hover:bg-amber-100 dark:hover:bg-slate-800 text-amber-800 dark:text-amber-300 text-xs font-bold rounded-lg border border-amber-200 dark:border-amber-800 transition-all cursor-pointer shrink-0 shadow-3xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                        <span>Kembali ke Skema Aktif</span>
                    </button>
                </div>

                @forelse($lateSchemas as $schema)
                    <div x-show="statusFilter === 'all' || (statusFilter === 'active' && {{ $schema->is_active ? 'true' : 'false' }}) || (statusFilter === 'inactive' && {{ !$schema->is_active ? 'true' : 'false' }})"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-all duration-200 {{ in_array($schema->id, [1, 2]) ? 'opacity-90 bg-slate-50/40 dark:bg-slate-900/50' : '' }} {{ ($picketBonusSchemaId ?? 0) == $schema->id ? 'ring-2 ring-amber-400/50 dark:ring-amber-500/40 border-amber-300 dark:border-amber-700/60' : '' }}">
                        <div class="space-y-3.5">
                            <div class="flex justify-between items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-50 truncate" title="{{ $schema->name }}">{{ $schema->name }}</h4>
                                        @if(in_array($schema->id, [1, 2]))
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                                <span>Terkunci</span>
                                            </span>
                                        @endif
                                        @if(($picketBonusSchemaId ?? 0) == $schema->id)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60 shadow-2xs whitespace-nowrap">
                                                <span>⚡ Piket Aktif</span>
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">ID: {{ $schema->id }}</span>
                                </div>
                                <div class="shrink-0">
                                    @if($schema->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-450 border border-emerald-100/30 dark:border-emerald-900/30 uppercase">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 uppercase">Nonaktif</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Tiers Table -->
                            <div class="border border-slate-100 dark:border-slate-800 rounded-lg overflow-hidden mt-2.5">
                                <table class="w-full text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="px-2.5 sm:px-3 py-2 text-left">Tier</th>
                                            <th class="px-2 sm:px-3 py-2 text-center">Batas Keterlambatan</th>
                                            <th class="px-2.5 sm:px-3 py-2 text-right">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                                        @foreach($schema->tiers->sortBy('tier_level') as $tier)
                                            <tr>
                                                <td class="px-2.5 sm:px-3 py-2 text-left flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-4.5 h-4.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                                                        {{ $tier->tier_level }}
                                                    </span>
                                                    <span>Tier {{ $tier->tier_level }}</span>
                                                </td>
                                                <td class="px-2 sm:px-3 py-2 text-center font-mono text-[11px] text-slate-800 dark:text-slate-200">
                                                    @if($tier->max_late_minutes == 0)
                                                        Tepat Waktu (0m)
                                                    @elseif($tier->max_late_minutes >= 720)
                                                        &gt; 14m (Intinya Hadir)
                                                    @else
                                                        &le; {{ $tier->max_late_minutes }} menit
                                                    @endif
                                                </td>
                                                <td class="px-2.5 sm:px-3 py-2 text-right font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-mono">
                                                    Rp {{ number_format($tier->nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-4 sm:mt-5 border-t border-slate-100 dark:border-slate-900 pt-3 sm:pt-4 justify-end">
                            @if(in_array($schema->id, [1, 2]))
                                <span class="mr-auto inline-flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-medium" title="Struktur tier skema lampau terkunci demi integritas data absensi masa lalu">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                    <span>Arsip Terkunci</span>
                                </span>
                            @endif

                            @if(($picketBonusSchemaId ?? 0) == $schema->id)
                                <form action="{{ route('bonus-schemas.unset-picket') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 text-xs font-bold rounded-lg border border-amber-200 dark:border-amber-800 transition-all flex items-center gap-1.5 cursor-pointer" title="Lepaskan status skema piket aktif">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Lepas Piket</span>
                                    </button>
                                </form>
                            @elseif($schema->is_active)
                                <form action="{{ route('bonus-schemas.set-picket', $schema->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-lg border border-emerald-200 dark:border-emerald-800 transition-all flex items-center gap-1.5 cursor-pointer" title="Jadikan skema ini sebagai acuan perhitungan bonus hari piket">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Pilih Piket</span>
                                    </button>
                                </form>
                            @endif

                            @if($schema->is_active)
                                <form action="{{ route('bonus-schemas.toggle-active', $schema->id) }}" method="POST" class="inline" onsubmit="return confirm('Nonaktifkan skema ini? Skema tidak akan digunakan untuk kalkulasi kehadiran yang akan datang.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all flex items-center gap-1 cursor-pointer" title="Nonaktifkan skema ini">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Nonaktifkan</span>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('bonus-schemas.toggle-active', $schema->id) }}" method="POST" class="inline" onsubmit="return confirm('Aktifkan skema ini agar dapat digunakan kembali?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer" title="Aktifkan skema ini agar dapat digunakan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Aktifkan</span>
                                    </button>
                                </form>
                            @endif

                            @if(!in_array($schema->id, [1, 2]))
                                <button @click="openEdit({{ json_encode($schema) }})" class="h-8 px-2.5 sm:px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-800 transition-all hover:scale-105 duration-150 flex items-center gap-1.5 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                    <span>Edit Skema</span>
                                </button>
                                <form :action="`{{ url('bonus-schemas') }}/${{ $schema->id }}?tab=late_tolerance`" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skema bonus ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 px-2.5 sm:px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-950/50 text-rose-650 dark:text-rose-400 text-xs font-bold rounded-lg border border-rose-100/30 dark:border-rose-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.78 0L9 9m12 6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada skema bonus Toleransi Keterlambatan yang terdaftar.</p>
                    </div>
                @endforelse

                <!-- Notification at bottom of active list if inactive schemas exist -->
                <div x-show="statusFilter === 'active' && currentCounts.inactive > 0" class="col-span-full py-3 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        <span>Terdapat <strong class="text-slate-800 dark:text-slate-200" x-text="currentCounts.inactive"></strong> skema berstatus <strong>Nonaktif / Riwayat Lampau</strong> yang disembunyikan agar tampilan tetap rapi.</span>
                    </div>
                    <button type="button" @click="statusFilter = 'inactive'" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 shadow-3xs transition-all cursor-pointer shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <span>Buka Skema Nonaktif</span>
                    </button>
                </div>

                <!-- Empty state when active filter has 0 items but collection is not empty -->
                <div x-show="currentCounts.active === 0 && statusFilter === 'active' && currentCounts.total > 0" class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada skema aktif saat ini pada kategori ini.</p>
                    <div class="flex items-center justify-center gap-2 mt-3">
                        <button @click="resetAdd('late_tolerance')" class="px-3.5 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold cursor-pointer hover:bg-indigo-700">Buat Skema Baru</button>
                        <button @click="statusFilter = 'inactive'" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold cursor-pointer">Lihat Skema Nonaktif</button>
                    </div>
                </div>

                <!-- Empty state when inactive filter has 0 items but collection is not empty -->
                <div x-show="currentCounts.inactive === 0 && statusFilter === 'inactive' && currentCounts.total > 0" class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada skema berstatus nonaktif pada kategori ini.</p>
                    <button @click="statusFilter = 'active'" class="mt-3 px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold cursor-pointer">Kembali ke Skema Aktif</button>
                </div>
            </section>
        </div>

        <!-- ADD MODAL -->
        <div x-show="showAddModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showAddModal" x-transition:enter="ease-out " x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in " x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showAddModal" @click.away="showAddModal = false" x-transition:enter="ease-out " x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in " x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-xl p-6 text-left flex flex-col max-h-[90vh]">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Buat Skema Bonus Baru</h3>
                            <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors border-0 bg-transparent cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bonus-schemas.store') }}" class="space-y-4 text-xs overflow-y-auto pr-1">
                            @csrf
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Skema</label>
                                <input type="text" name="name" required placeholder="Contoh: Skema Guru & Staff" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium">
                            </div>

                            <!-- Mode Selector -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Metode Perhitungan Bonus</label>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                                        :class="modalMode === 'early_arrival' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950'">
                                        <input type="radio" name="calculation_mode" value="early_arrival" x-model="modalMode" @change="setModalMode('early_arrival')" class="text-emerald-600 focus:ring-emerald-500">
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs">Hadir Lebih Awal</p>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Sebelum Jam Masuk Shift</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                                        :class="modalMode === 'late_tolerance' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950'">
                                        <input type="radio" name="calculation_mode" value="late_tolerance" x-model="modalMode" @change="setModalMode('late_tolerance')" class="text-amber-600 focus:ring-amber-500">
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs">Toleransi Telat</p>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Setelah Jam Masuk (Lama)</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="add_is_active" name="is_active" value="1" checked class="rounded border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                <label for="add_is_active" class="ml-2 font-semibold text-slate-750 dark:text-slate-300 select-none">Skema Aktif</label>
                            </div>

                            <!-- Dynamic Tiers List -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block font-bold text-slate-750 dark:text-slate-300 uppercase tracking-wide text-[10px]">Tingkatan Bonus (Tiers)</label>
                                    <button type="button" @click="addTier()" class="text-indigo-650 dark:text-indigo-400 font-bold hover:underline cursor-pointer border-0 bg-transparent flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Tambah Tier</span>
                                    </button>
                                </div>
                                
                                <div class="space-y-3 bg-slate-50/50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/40">
                                    <template x-for="(tier, index) in tiers" :key="index">
                                        <div class="flex flex-row items-end gap-3 pb-3 border-b border-slate-200/50 dark:border-slate-800/40 last:border-0 last:pb-0">
                                            <div class="flex-none w-8 flex flex-col items-center justify-center pb-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-400" x-text="tier.tier_level"></div>
                                                <input type="hidden" :name="`tiers[${index}][tier_level]`" x-model="tier.tier_level">
                                            </div>
                                            
                                            <div class="flex-1">
                                                <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                                                <input type="number" :name="`tiers[${index}][nominal]`" x-model="tier.nominal" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-right font-mono font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                            </div>
                                            
                                            <!-- Field if early arrival -->
                                            <div class="flex-1" x-show="modalMode === 'early_arrival'">
                                                <label class="block text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Min. Hadir (Mnt Sblm)</label>
                                                <input type="number" :name="`tiers[${index}][min_early_minutes]`" x-model="tier.min_early_minutes" :required="modalMode === 'early_arrival'" placeholder="30" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono font-semibold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                                            </div>

                                            <!-- Field if late tolerance -->
                                            <div class="flex-1" x-show="modalMode === 'late_tolerance'">
                                                <label class="block text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">Maks. Telat (Mnt)</label>
                                                <input type="number" :name="`tiers[${index}][max_late_minutes]`" x-model="tier.max_late_minutes" :required="modalMode === 'late_tolerance'" placeholder="5" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono font-semibold text-amber-600 dark:text-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-all">
                                            </div>
                                            
                                            <div class="flex-none w-20">
                                                <button type="button" @click="removeTier(index)" class="inline-flex items-center justify-center gap-1.5 w-full px-2 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-455 bg-rose-50 dark:bg-rose-900/20 border border-rose-200/50 dark:border-rose-900/30 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors cursor-pointer mb-0.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.78 0L9 9m12 6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Helper Simulation Box -->
                                <div class="mt-3 p-3 rounded-lg bg-slate-100 dark:bg-slate-950 text-[11px] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">💡 Logika Evaluasi Sistem:</span>
                                    <template x-if="modalMode === 'early_arrival'">
                                        <p class="mt-1">Sistem mengecek menit kedatangan sebelum jam masuk shift mulai dari Tier 1 (kehadiran paling awal). Pegawai yang memenuhi syarat memperoleh nominal tier tersebut. Jika scan setelah jam masuk (&ge; 1 menit terlambat), bonus otomatis <strong>Rp 0</strong>.</p>
                                    </template>
                                    <template x-if="modalMode === 'late_tolerance'">
                                        <p class="mt-1">Sistem mengecek keterlambatan setelah jam shift dimulai. Jika menit terlambat melebihi batas tertinggi tier skema, bonus otomatis <strong>Rp 0</strong>.</p>
                                    </template>
                                </div>
                            </div>

                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                                    Simpan Skema
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showEditModal" x-transition:enter="ease-out " x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in " x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showEditModal" @click.away="showEditModal = false" x-transition:enter="ease-out " x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in " x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-xl p-6 text-left flex flex-col max-h-[90vh]">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Edit Skema Bonus</h3>
                            <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors border-0 bg-transparent cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" :action="`{{ url('bonus-schemas') }}/${editId}`" class="space-y-4 text-xs overflow-y-auto pr-1">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Skema</label>
                                <input type="text" name="name" required x-model="editName" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium">
                            </div>

                            <!-- Mode Selector -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Metode Perhitungan Bonus</label>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                                        :class="modalMode === 'early_arrival' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950'">
                                        <input type="radio" name="calculation_mode" value="early_arrival" x-model="modalMode" class="text-emerald-600 focus:ring-emerald-500">
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs">Hadir Lebih Awal</p>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Sebelum Jam Masuk Shift</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                                        :class="modalMode === 'late_tolerance' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950'">
                                        <input type="radio" name="calculation_mode" value="late_tolerance" x-model="modalMode" class="text-amber-600 focus:ring-amber-500">
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs">Toleransi Telat</p>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Setelah Jam Masuk (Lama)</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="edit_is_active" name="is_active" value="1" x-model="editIsActive" class="rounded border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                <label for="edit_is_active" class="ml-2 font-semibold text-slate-750 dark:text-slate-300 select-none">Skema Aktif</label>
                            </div>

                            <!-- Dynamic Tiers List -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block font-bold text-slate-750 dark:text-slate-300 uppercase tracking-wide text-[10px]">Tingkatan Bonus (Tiers)</label>
                                    <button type="button" @click="addTier()" class="text-indigo-650 dark:text-indigo-400 font-bold hover:underline cursor-pointer border-0 bg-transparent flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Tambah Tier</span>
                                    </button>
                                </div>
                                
                                <div class="space-y-3 bg-slate-50/50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/40">
                                    <template x-for="(tier, index) in tiers" :key="index">
                                        <div class="flex flex-row items-end gap-3 pb-3 border-b border-slate-200/50 dark:border-slate-800/40 last:border-0 last:pb-0">
                                            <div class="flex-none w-8 flex flex-col items-center justify-center pb-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-400" x-text="tier.tier_level"></div>
                                                <input type="hidden" :name="`tiers[${index}][tier_level]`" x-model="tier.tier_level">
                                            </div>
                                            
                                            <div class="flex-1">
                                                <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                                                <input type="number" :name="`tiers[${index}][nominal]`" x-model="tier.nominal" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-right font-mono font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                            </div>
                                            
                                            <!-- Field if early arrival -->
                                            <div class="flex-1" x-show="modalMode === 'early_arrival'">
                                                <label class="block text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Min. Hadir (Mnt Sblm)</label>
                                                <input type="number" :name="`tiers[${index}][min_early_minutes]`" x-model="tier.min_early_minutes" :required="modalMode === 'early_arrival'" placeholder="30" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono font-semibold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                                            </div>

                                            <!-- Field if late tolerance -->
                                            <div class="flex-1" x-show="modalMode === 'late_tolerance'">
                                                <label class="block text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">Maks. Telat (Mnt)</label>
                                                <input type="number" :name="`tiers[${index}][max_late_minutes]`" x-model="tier.max_late_minutes" :required="modalMode === 'late_tolerance'" placeholder="5" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono font-semibold text-amber-600 dark:text-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-all">
                                            </div>
                                            
                                            <div class="flex-none w-20">
                                                <button type="button" @click="removeTier(index)" class="inline-flex items-center justify-center gap-1.5 w-full px-2 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-455 bg-rose-50 dark:bg-rose-900/20 border border-rose-200/50 dark:border-rose-900/30 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors cursor-pointer mb-0.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.78 0L9 9m12 6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Helper Simulation Box -->
                                <div class="mt-3 p-3 rounded-lg bg-slate-100 dark:bg-slate-950 text-[11px] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">💡 Logika Evaluasi Sistem:</span>
                                    <template x-if="modalMode === 'early_arrival'">
                                        <p class="mt-1">Sistem mengecek menit kedatangan sebelum jam masuk shift mulai dari Tier 1 (kehadiran paling awal). Pegawai yang memenuhi syarat memperoleh nominal tier tersebut. Jika scan setelah jam masuk (&ge; 1 menit terlambat), bonus otomatis <strong>Rp 0</strong>.</p>
                                    </template>
                                    <template x-if="modalMode === 'late_tolerance'">
                                        <p class="mt-1">Sistem mengecek keterlambatan setelah jam shift dimulai. Jika menit terlambat melebihi batas tertinggi tier skema, bonus otomatis <strong>Rp 0</strong>.</p>
                                    </template>
                                </div>
                            </div>

                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
